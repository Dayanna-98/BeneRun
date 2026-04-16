<?php

namespace App\Http\Controllers;

use App\Models\Certificat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificatController extends Controller
{
    private function normalizeStatusForStorage(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        $normalized = str_replace(['_', '-'], ' ', strtolower(trim($status)));

        return match ($normalized) {
            'pending', 'en attente' => 'en attente',
            'approved', 'approuve', 'approuvé' => 'approuvé',
            'rejected', 'rejete', 'rejeté' => 'rejeté',
            default => null,
        };
    }

    private function resolveActorFromBearerToken(Request $request): ?User
    {
        $authorizationHeader = $request->header('Authorization');
        if (!is_string($authorizationHeader) || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($authorizationHeader, 7));
        if (!ctype_digit($token)) {
            return null;
        }

        return User::find((int) $token);
    }

    private function normalizeRole(?string $role): string
    {
        return str_replace(['-', '_', ' '], '', strtolower((string) $role));
    }

    private function isSuperAdminRequest(Request $request): bool
    {
        $headerRole = $request->header('X-User-Role');
        if (!empty($headerRole) && $this->normalizeRole($headerRole) === 'superadmin') {
            return true;
        }

        $actor = $this->resolveActorFromBearerToken($request);

        return $actor !== null && $this->normalizeRole($actor->role_utilisateur) === 'superadmin';
    }

    public function index(Request $request)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if ($actor === null) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $isSuperAdmin = $this->isSuperAdminRequest($request);
        $query = Certificat::query()->orderByDesc('id_certificat');

        if ($isSuperAdmin) {
            if ($request->filled('id_utilisateur')) {
                $query->where('id_utilisateur', (int) $request->id_utilisateur);
            }
        } else {
            if ($request->filled('id_utilisateur') && (int) $request->id_utilisateur !== (int) $actor->id_utilisateur) {
                return response()->json(['message' => 'Action non autorisée'], 403);
            }
            $query->where('id_utilisateur', (int) $actor->id_utilisateur);
        }

        return response()->json($query->get());
    }

    public function show(Request $request, $id)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if ($actor === null) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $certificat = Certificat::find($id);

        if (empty($certificat)) {
            return response()->json(['message' => 'Certificat inexistant'], 404);
        }

        if (
            !$this->isSuperAdminRequest($request)
            && (int) $certificat->id_utilisateur !== (int) $actor->id_utilisateur
        ) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        return response()->json($certificat);
    }

    public function store(Request $request)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if ($actor === null) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $isSuperAdmin = $this->isSuperAdminRequest($request);

        $validated = $request->validate([
            'id_utilisateur' => 'required|integer|exists:users,id_utilisateur',
            'titre_certificat' => 'required|string|max:150',
            'emetteur_certificat' => 'nullable|string|max:150',
            'date_emission_certificat' => 'nullable|date',
            'date_expiration_certificat' => 'nullable|date|after_or_equal:date_emission_certificat',
            'type_certificat' => 'nullable|in:platform,external',
            'statut_certificat' => 'nullable|in:pending,approved,rejected,en attente,approuvé,rejeté',
            'chemin_fichier_certificat' => 'nullable|string|max:500',
            'file_certificat' => 'nullable|file|mimes:pdf,png,jpg,jpeg,webp|max:10240',
        ]);

        if (!$isSuperAdmin && (int) $validated['id_utilisateur'] !== (int) $actor->id_utilisateur) {
            return response()->json([
                'message' => 'Vous ne pouvez soumettre un certificat que pour votre propre compte'
            ], 403);
        }

        if ($isSuperAdmin) {
            $validated['type_certificat'] = $validated['type_certificat'] ?? 'platform';
            $validated['statut_certificat'] = $this->normalizeStatusForStorage($validated['statut_certificat'] ?? 'pending') ?? 'en attente';
        } else {
            // Toute soumission utilisateur passe en attente de validation super-admin.
            $validated['type_certificat'] = 'external';
            $validated['statut_certificat'] = 'en attente';
        }

        if ($request->hasFile('file_certificat')) {
            $validated['chemin_fichier_certificat'] = $request->file('file_certificat')->store('certificats', 'public');
        }

        unset($validated['file_certificat']);

        $certificat = Certificat::create($validated);

        return response()->json([
            'message' => 'Certificat ajouté',
            'certificat' => $certificat,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if (!$this->isSuperAdminRequest($request)) {
            return response()->json([
                'message' => 'Action réservée aux super-admins'
            ], 403);
        }

        $certificat = Certificat::find($id);
        if (!$certificat) {
            return response()->json(['message' => 'Certificat inexistant'], 404);
        }

        $validated = $request->validate([
            'id_utilisateur' => 'sometimes|integer|exists:users,id_utilisateur',
            'titre_certificat' => 'sometimes|string|max:150',
            'emetteur_certificat' => 'nullable|string|max:150',
            'date_emission_certificat' => 'nullable|date',
            'date_expiration_certificat' => 'nullable|date|after_or_equal:date_emission_certificat',
            'type_certificat' => 'sometimes|in:platform,external',
            'statut_certificat' => 'sometimes|in:pending,approved,rejected,en attente,approuvé,rejeté',
            'chemin_fichier_certificat' => 'nullable|string|max:500',
            'file_certificat' => 'nullable|file|mimes:pdf,png,jpg,jpeg,webp|max:10240',
        ]);

        if (array_key_exists('statut_certificat', $validated)) {
            $validated['statut_certificat'] = $this->normalizeStatusForStorage($validated['statut_certificat']) ?? 'en attente';
        }

        if ($request->hasFile('file_certificat')) {
            if (!empty($certificat->chemin_fichier_certificat)) {
                Storage::disk('public')->delete($certificat->chemin_fichier_certificat);
            }
            $validated['chemin_fichier_certificat'] = $request->file('file_certificat')->store('certificats', 'public');
        }

        unset($validated['file_certificat']);

        $certificat->fill($validated);
        $certificat->save();

        return response()->json([
            'message' => 'Certificat mis à jour',
            'certificat' => $certificat,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        if (!$this->isSuperAdminRequest($request)) {
            return response()->json([
                'message' => 'Action réservée aux super-admins'
            ], 403);
        }

        $certificat = Certificat::find($id);
        if (!$certificat) {
            return response()->json(['message' => 'Certificat inexistant'], 404);
        }

        if (!empty($certificat->chemin_fichier_certificat)) {
            Storage::disk('public')->delete($certificat->chemin_fichier_certificat);
        }

        $certificat->delete();

        return response()->json(['message' => 'Certificat supprimé'], 200);
    }

}
