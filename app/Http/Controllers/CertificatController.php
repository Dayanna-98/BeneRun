<?php

namespace App\Http\Controllers;

use App\Models\Certificat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificatController extends Controller
{
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

        $authorizationHeader = $request->header('Authorization');
        if (!is_string($authorizationHeader) || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return false;
        }

        $token = trim(substr($authorizationHeader, 7));
        if (!ctype_digit($token)) {
            return false;
        }

        $actor = User::find((int) $token);

        return $actor !== null && $this->normalizeRole($actor->role_utilisateur) === 'superadmin';
    }

    public function index(Request $request)
    {
        $query = Certificat::query()->orderByDesc('id_certificat');

        if ($request->filled('id_utilisateur')) {
            $query->where('id_utilisateur', (int) $request->id_utilisateur);
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        $certificat = Certificat::find($id);

        if (!empty($certificat)) {
            return response()->json($certificat);
        }

        return response()->json(['message' => 'Certificat inexistant'], 404);
    }

    public function store(Request $request)
    {
        if (!$this->isSuperAdminRequest($request)) {
            return response()->json([
                'message' => 'Action réservée aux super-admins'
            ], 403);
        }

        $validated = $request->validate([
            'id_utilisateur' => 'required|integer|exists:users,id_utilisateur',
            'titre_certificat' => 'required|string|max:150',
            'emetteur_certificat' => 'nullable|string|max:150',
            'date_emission_certificat' => 'nullable|date',
            'date_expiration_certificat' => 'nullable|date|after_or_equal:date_emission_certificat',
            'type_certificat' => 'nullable|in:platform,external',
            'statut_certificat' => 'nullable|in:pending,approved,rejected',
            'chemin_fichier_certificat' => 'nullable|string|max:500',
            'file_certificat' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240',
        ]);

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
            'statut_certificat' => 'sometimes|in:pending,approved,rejected',
            'chemin_fichier_certificat' => 'nullable|string|max:500',
            'file_certificat' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240',
        ]);

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
