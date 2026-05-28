<?php

namespace App\Http\Controllers;

use App\Events\MissionEmergencyMessageCreated;
use App\Models\Affectation;
use App\Models\Mission;
use App\Models\MissionEmergencyMessage;
use App\Models\MissionEmergencyMessageView;
use App\Models\User;
use App\Notifications\MissionEmergencyReceivedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class MissionEmergencyMessageController extends Controller
{
    private function toFrenchCategoryLabel(?string $category): string
    {
        $normalized = str_replace(
            ['-', '_', ' ', 'é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç'],
            ['', '', '', 'e', 'e', 'e', 'e', 'a', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c'],
            strtolower((string) $category)
        );

        return match ($normalized) {
            'medical', 'medicale' => 'Médicale',
            'security', 'securite' => 'Sécurité',
            'logistics', 'logistique' => 'Logistique',
            'other', 'autre' => 'Autre',
            default => 'Générale',
        };
    }

    private function buildFrontendDashboardUrl(): string
    {
        $frontendBaseUrl = rtrim((string) (env('FRONTEND_URL') ?: config('app.url')), '/');

        return $frontendBaseUrl.'/dashboard';
    }

    private function notifySuperAdminsByEmail(MissionEmergencyMessage $urgence): void
    {
        $missionName = (string) ($urgence->mission?->titre_mission ?? ('Mission #'.$urgence->id_mission));
        $eventName = (string) ($urgence->evenement?->nom_evenement ?? ('Evenement #'.$urgence->id_evenement));
        $senderName = trim(((string) ($urgence->emetteur?->prenom_utilisateur ?? '')).' '.((string) ($urgence->emetteur?->nom_utilisateur ?? '')));
        if ($senderName === '') {
            $senderName = (string) ($urgence->emetteur?->email ?? 'Un participant');
        }

        $recipients = User::query()
            ->whereNotNull('email')
            ->get()
            ->filter(function (User $user): bool {
                $normalizedRole = str_replace(['-', '_', ' '], '', strtolower((string) $user->role_utilisateur));

                return $normalizedRole === 'superadmin';
            })
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        $actionUrl = $this->buildFrontendDashboardUrl();
        $category = $this->toFrenchCategoryLabel((string) ($urgence->categorie_urgence ?? 'general'));

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new MissionEmergencyReceivedNotification(
                    senderName: $senderName,
                    missionName: $missionName,
                    eventName: $eventName,
                    category: $category,
                    messagePreview: (string) $urgence->message_urgence,
                    actionUrl: $actionUrl,
                ));
            } catch (\Throwable $exception) {
                Log::warning('Mission emergency email notification failed', [
                    'urgence_id' => $urgence->id_mission_emergency_message,
                    'recipient_user_id' => $recipient->id_utilisateur,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function normalizeMissionStatus(?string $status): string
    {
        return str_replace(['-', '_', ' '], '', strtolower((string) $status));
    }

    private function isMissionInProgress(Mission $mission): bool
    {
        $status = $this->normalizeMissionStatus($mission->statut_mission);
        if ($status === 'encours') {
            return true;
        }

        if (empty($mission->date_mission)) {
            return false;
        }

        try {
            $date = $mission->date_mission instanceof Carbon
                ? $mission->date_mission->toDateString()
                : Carbon::parse((string) $mission->date_mission)->toDateString();

            $startAt = Carbon::parse($date.' '.((string) ($mission->heure_debut_mission ?: '00:00:00')));
            $endAt = Carbon::parse($date.' '.((string) ($mission->heure_fin_mission ?: ($mission->heure_debut_mission ?: '23:59:59'))));

            return now()->betweenIncluded($startAt, $endAt);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function resolveActorFromBearerToken(Request $request): ?User
    {
        $actor = $request->user('sanctum');
        if ($actor instanceof User) {
            return $actor;
        }

        $token = $request->bearerToken();
        if (! $token) {
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

        if (! $actor || $role !== 'superadmin') {
            return null;
        }

        return $actor;
    }

    public function index(Request $request)
    {
        $superAdmin = $this->assertSuperAdmin($request);
        if (! $superAdmin) {
            return response()->json(['message' => 'Accès réservé aux superadmins.'], 403);
        }

        $urgences = MissionEmergencyMessage::with([
            'emetteur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'mission:id_mission,titre_mission,statut_mission,date_mission,heure_fin_mission',
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
        if (! $actor) {
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
        if (! $mission) {
            return response()->json(['message' => 'Mission inexistante.'], 404);
        }

        if (! $this->isMissionInProgress($mission)) {
            return response()->json(['message' => 'Les urgences ne peuvent être envoyées que pendant une mission en cours.'], 422);
        }

        $isParticipant = Affectation::where('id_mission', $mission->id_mission)
            ->where('id_utilisateur', $actor->id_utilisateur)
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->exists();

        $isSuperAdmin = str_replace(['-', '_', ' '], '', strtolower((string) $actor->role_utilisateur)) === 'superadmin';

        if (! $isParticipant && ! $isSuperAdmin) {
            return response()->json(['message' => 'Seuls les participants de la mission peuvent envoyer une urgence.'], 403);
        }

        $urgence = MissionEmergencyMessage::create([
            'id_mission' => (int) $mission->id_mission,
            'id_evenement' => (int) $mission->id_evenement,
            'id_emetteur_utilisateur' => (int) $actor->id_utilisateur,
            'categorie_urgence' => $validated['categorie_urgence'] ?? null,
            'message_urgence' => $validated['message_urgence'],
        ]);

        $urgence->load([
            'emetteur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'mission:id_mission,titre_mission,statut_mission,date_mission,heure_fin_mission',
            'evenement:id_evenement,nom_evenement',
            'prisEnChargePar:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'consultations.utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ]);

        broadcast(new MissionEmergencyMessageCreated($urgence));
        $this->notifySuperAdminsByEmail($urgence);

        return response()->json([
            'message' => 'Message d\'urgence transmis aux superadmins.',
            'urgence' => $urgence,
        ], 201);
    }

    public function markViewed(Request $request, $idUrgence)
    {
        $superAdmin = $this->assertSuperAdmin($request);
        if (! $superAdmin) {
            return response()->json(['message' => 'Accès réservé aux superadmins.'], 403);
        }

        $urgence = MissionEmergencyMessage::find($idUrgence);
        if (! $urgence) {
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
        if (! $superAdmin) {
            return response()->json(['message' => 'Accès réservé aux superadmins.'], 403);
        }

        $urgence = MissionEmergencyMessage::find($idUrgence);
        if (! $urgence) {
            return response()->json(['message' => 'Message d\'urgence introuvable.'], 404);
        }

        if (
            ! empty($urgence->pris_en_charge_par_utilisateur_id)
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
            'mission:id_mission,titre_mission,statut_mission,date_mission,heure_fin_mission',
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
