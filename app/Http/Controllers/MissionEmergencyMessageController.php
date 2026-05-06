<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\MissionEmergencyMessage;
use App\Models\MissionEmergencyMessageView;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class MissionEmergencyMessageController extends Controller
{
    private function resolveActorFromBearerToken(Request $request): ?User
    {
        $actor = $request->user('sanctum');
        if ($actor instanceof User) {
            return $actor;
        }

        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);
        $tokenable = $accessToken?->tokenable;

        return $tokenable instanceof User ? $tokenable : null;
    }

    private function normalizedRoleFromRequest(Request $request): string
    {
        $actor = $this->resolveActorFromBearerToken($request);
        $role = $actor?->role_utilisateur ?? $request->header('X-User-Role', '');

        return str_replace(['-', '_', ' '], '', strtolower((string) $role));
    }

    private function resolveActorUser(Request $request): ?User
    {
        $authActor = $this->resolveActorFromBearerToken($request);
        if ($authActor instanceof User) {
            return $authActor;
        }

        $inputUserId = (int) ($request->input('id_utilisateur') ?? 0);
        if ($inputUserId <= 0) {
            return null;
        }

        return User::find($inputUserId);
    }

    private function assertSuperAdmin(Request $request): ?User
    {
        $actor = $this->resolveActorUser($request);
        $role = $this->normalizedRoleFromRequest($request);

        if (!$actor || $role !== 'superadmin') {
            return null;
        }

        return $actor;
    }

    public function index(Request $request)
    {
        $superAdmin = $this->assertSuperAdmin($request);
        if (!$superAdmin) {
            return response()->json(['message' => 'Accès réservé aux superadmins.'], 403);
        }

        $urgences = MissionEmergencyMessage::with([
            'emetteur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'mission:id_mission,titre_mission',
            'evenement:id_evenement,nom_evenement',
            'prisEnChargePar:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'consultations.utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($urgences);
    }

    public function storeForMission(Request $request, $idMission)
    {
        $actor = $this->resolveActorUser($request);
        if (!$actor) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 401);
        }

        $validated = $request->validate([
            'id_utilisateur' => 'required|integer|exists:users,id_utilisateur',
            'categorie_urgence' => 'nullable|string|max:50',
            'message_urgence' => 'required|string|max:2000',
        ]);

        if ((int) $validated['id_utilisateur'] !== (int) $actor->id_utilisateur) {
            return response()->json(['message' => 'Action non autorisée pour cet utilisateur.'], 403);
        }

        $mission = Mission::with('evenement:id_evenement,nom_evenement')->find($idMission);
        if (!$mission) {
            return response()->json(['message' => 'Mission inexistante.'], 404);
        }

        if ($mission->statut_mission !== 'En cours') {
            return response()->json(['message' => 'Les urgences ne peuvent être envoyées que pendant une mission en cours.'], 422);
        }

        $isParticipant = Affectation::where('id_mission', $mission->id_mission)
            ->where('id_utilisateur', $actor->id_utilisateur)
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->exists();

        $isSuperAdmin = str_replace(['-', '_', ' '], '', strtolower((string) $actor->role_utilisateur)) === 'superadmin';

        if (!$isParticipant && !$isSuperAdmin) {
            return response()->json(['message' => 'Seuls les participants de la mission peuvent envoyer une urgence.'], 403);
        }

        $urgence = MissionEmergencyMessage::create([
            'id_mission' => (int) $mission->id_mission,
            'id_evenement' => (int) $mission->id_evenement,
            'id_emetteur_utilisateur' => (int) $actor->id_utilisateur,
            'categorie_urgence' => $validated['categorie_urgence'] ?? null,
            'message_urgence' => $validated['message_urgence'],
        ]);

        return response()->json([
            'message' => 'Message d\'urgence transmis aux superadmins.',
            'urgence' => $urgence->load([
                'emetteur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
                'mission:id_mission,titre_mission',
                'evenement:id_evenement,nom_evenement',
            ]),
        ], 201);
    }

    public function markViewed(Request $request, $idUrgence)
    {
        $superAdmin = $this->assertSuperAdmin($request);
        if (!$superAdmin) {
            return response()->json(['message' => 'Accès réservé aux superadmins.'], 403);
        }

        $urgence = MissionEmergencyMessage::find($idUrgence);
        if (!$urgence) {
            return response()->json(['message' => 'Message d\'urgence introuvable.'], 404);
        }

        MissionEmergencyMessageView::updateOrCreate(
            [
                'id_mission_emergency_message' => (int) $urgence->id_mission_emergency_message,
                'id_utilisateur' => (int) $superAdmin->id_utilisateur,
            ],
            [
                'consulte_le' => now(),
            ]
        );

        $urgence->load([
            'consultations.utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'prisEnChargePar:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ]);

        return response()->json([
            'message' => 'Consultation enregistrée.',
            'urgence' => $urgence,
        ]);
    }

    public function takeOwnership(Request $request, $idUrgence)
    {
        $superAdmin = $this->assertSuperAdmin($request);
        if (!$superAdmin) {
            return response()->json(['message' => 'Accès réservé aux superadmins.'], 403);
        }

        $urgence = MissionEmergencyMessage::find($idUrgence);
        if (!$urgence) {
            return response()->json(['message' => 'Message d\'urgence introuvable.'], 404);
        }

        if (
            !empty($urgence->pris_en_charge_par_utilisateur_id)
            && (int) $urgence->pris_en_charge_par_utilisateur_id !== (int) $superAdmin->id_utilisateur
        ) {
            return response()->json([
                'message' => 'Cette urgence est déjà prise en charge par un autre superadmin.',
            ], 409);
        }

        $urgence->update([
            'pris_en_charge_par_utilisateur_id' => (int) $superAdmin->id_utilisateur,
            'pris_en_charge_le' => now(),
        ]);

        MissionEmergencyMessageView::updateOrCreate(
            [
                'id_mission_emergency_message' => (int) $urgence->id_mission_emergency_message,
                'id_utilisateur' => (int) $superAdmin->id_utilisateur,
            ],
            [
                'consulte_le' => now(),
            ]
        );

        $urgence->load([
            'emetteur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'mission:id_mission,titre_mission',
            'evenement:id_evenement,nom_evenement',
            'prisEnChargePar:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'consultations.utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ]);

        return response()->json([
            'message' => 'Urgence prise en charge.',
            'urgence' => $urgence,
        ]);
    }
}
