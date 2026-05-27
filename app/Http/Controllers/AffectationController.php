<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\Postulation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class AffectationController extends Controller
{
    private function normalizeRole(?string $role): string
    {
        return str_replace(['-', '_', ' '], '', strtolower((string) $role));
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

    private function assertAdminOrSuperAdmin(Request $request): ?User
    {
        $actor = $this->resolveActorFromBearerToken($request);

        if (! $actor) {
            return null;
        }

        $role = $this->normalizeRole($actor->role_utilisateur);

        return in_array($role, ['admin', 'superadmin'], true) ? $actor : null;
    }

    private function assertMissionManagerOrAdmin(Request $request): ?User
    {
        $actor = $this->resolveActorFromBearerToken($request);

        if (! $actor) {
            return null;
        }

        $role = $this->normalizeRole($actor->role_utilisateur);

        return in_array($role, ['responsable', 'missionmanager', 'organisateur', 'admin', 'superadmin'], true) ? $actor : null;
    }

    private function hasMissionEnded(Mission $mission): bool
    {
        if (empty($mission->date_mission)) {
            return false;
        }

        $missionDate = Carbon::parse((string) $mission->date_mission)->format('Y-m-d');
        $endTime = $mission->heure_fin_mission ?: '23:59:59';

        return Carbon::parse("{$missionDate} {$endTime}")->isPast();
    }

    private function normalizeMeetingTime(array $validated): array
    {
        if (! array_key_exists('heure_rendez_vous_affectation', $validated) || $validated['heure_rendez_vous_affectation'] === null) {
            return $validated;
        }

        $validated['heure_rendez_vous_affectation'] = Carbon::createFromFormat(
            'H:i',
            $validated['heure_rendez_vous_affectation']
        )->format('H:i:s');

        return $validated;
    }

    public function index()// Récupérer toutes les affectations
    {
        $affectations = Affectation::with([
            'mission:id_mission,titre_mission',
            'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email,partage_localisation_directe_utilisateur,latitude_localisation_directe_utilisateur,longitude_localisation_directe_utilisateur,date_localisation_directe_utilisateur',
        ])->get();

        return response()->json($affectations);
    }

    public function show($id) // Rechercher une affectation selon son id
    {
        $affectation = Affectation::with([
            'mission:id_mission,titre_mission',
            'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email,partage_localisation_directe_utilisateur,latitude_localisation_directe_utilisateur,longitude_localisation_directe_utilisateur,date_localisation_directe_utilisateur',
        ])->find($id);
        if (! empty($affectation)) {
            return response()->json($affectation);
        } else {
            return response()->json(['message' => 'Affectation inexistante'], 404);
        }
    }

    public function store(Request $request)
    {
        if (! $this->assertAdminOrSuperAdmin($request)) {
            return response()->json(['message' => 'Action réservée aux admins et superadmins.'], 403);
        }

        $validated = $request->validate([
            'id_mission' => 'required|integer|exists:missions,id_mission',
            'id_utilisateur' => 'required|integer|exists:users,id_utilisateur',
            'statut_affectation' => 'nullable|in:assigne,confirme,present,absent,annule',
            'est_responsable' => 'nullable|boolean',
            'heure_rendez_vous_affectation' => 'nullable|date_format:H:i',
            'remarque' => 'nullable|string',
            'date_affectation' => 'nullable|date',
            'date_confirmation' => 'nullable|date',
            'date_presence' => 'nullable|date',
        ]);

        $validated = $this->normalizeMeetingTime($validated);

        $affectation = DB::transaction(function () use ($validated) {
            $affectation = Affectation::create($validated);

            if (($validated['est_responsable'] ?? false) === true) {
                $this->syncMissionResponsible((int) $affectation->id_mission, (int) $affectation->id_utilisateur);
                $affectation->refresh();
            }

            return $affectation;
        });

        return response()->json([
            'message' => 'Affectation ajoutée',
            'affectation' => $affectation,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if (! $this->assertAdminOrSuperAdmin($request)) {
            return response()->json(['message' => 'Action réservée aux admins et superadmins.'], 403);
        }

        if (Affectation::where('id_affectation', $id)->exists()) {
            $affectation = Affectation::find($id);
            $validated = $request->validate([
                'id_mission' => 'sometimes|integer|exists:missions,id_mission',
                'id_utilisateur' => 'sometimes|integer|exists:users,id_utilisateur',
                'statut_affectation' => 'nullable|in:assigne,confirme,present,absent,annule',
                'est_responsable' => 'nullable|boolean',
                'heure_rendez_vous_affectation' => 'nullable|date_format:H:i',
                'remarque' => 'nullable|string',
                'date_affectation' => 'nullable|date',
                'date_confirmation' => 'nullable|date',
                'date_presence' => 'nullable|date',
            ]);

            $validated = $this->normalizeMeetingTime($validated);

            DB::transaction(function () use ($affectation, $validated): void {
                $affectation->update($validated);

                if (($validated['est_responsable'] ?? false) === true) {
                    $this->syncMissionResponsible((int) $affectation->id_mission, (int) $affectation->id_utilisateur);
                }
            });

            $affectation->refresh();

            return response()->json([
                'message' => 'Affectation mise à jour',
                'affectation' => $affectation,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Affectation inexistante',
            ], 404);
        }
    }

    public function destroy($id)
    {
        if (! $this->assertAdminOrSuperAdmin(request())) {
            return response()->json(['message' => 'Action réservée aux admins et superadmins.'], 403);
        }

        if (Affectation::where('id_affectation', $id)->exists()) {
            $affectation = Affectation::find($id);
            $affectation->delete();

            return response()->json([
                'message' => 'Affectation supprimée',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Affectation inexistante',
            ], 404);
        }
    }

    public function replaceVolunteer(Request $request, $missionId)
    {
        if (! $this->assertMissionManagerOrAdmin($request)) {
            return response()->json(['message' => 'Action réservée aux responsables, admins et superadmins.'], 403);
        }

        $mission = Mission::find((int) $missionId);
        if (! $mission) {
            return response()->json(['message' => 'Mission inexistante'], 404);
        }

        if ($this->hasMissionEnded($mission)) {
            return response()->json([
                'message' => 'Remplacement impossible: cette mission est terminée.',
            ], 422);
        }

        $validated = $request->validate([
            'outgoing_user_id' => 'required|integer|exists:users,id_utilisateur',
            'incoming_postulation_id' => 'required|integer|exists:postulations,id_postulation',
        ]);

        $outgoingAffectation = Affectation::where('id_mission', (int) $mission->id_mission)
            ->where('id_utilisateur', (int) $validated['outgoing_user_id'])
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->first();

        if (! $outgoingAffectation) {
            return response()->json([
                'message' => 'Le bénévole à remplacer n\'est pas actuellement affecté à la mission.',
            ], 404);
        }

        $incomingPostulation = Postulation::find((int) $validated['incoming_postulation_id']);
        if (! $incomingPostulation) {
            return response()->json(['message' => 'Postulation introuvable.'], 404);
        }

        if ((string) $incomingPostulation->statut_postulation !== 'en_attente') {
            return response()->json([
                'message' => 'Seules les postulations en attente peuvent être utilisées pour un remplacement.',
            ], 422);
        }

        $isWaitingForMission = (int) $incomingPostulation->id_mission === (int) $mission->id_mission;
        $isWaitingForEvent = empty($incomingPostulation->id_mission)
            && (int) $incomingPostulation->id_evenement === (int) $mission->id_evenement;

        if (! $isWaitingForMission && ! $isWaitingForEvent) {
            return response()->json([
                'message' => 'La postulation sélectionnée ne correspond pas à la mission ou à l\'événement de cette mission.',
            ], 422);
        }

        if ((int) $incomingPostulation->id_utilisateur === (int) $validated['outgoing_user_id']) {
            return response()->json([
                'message' => 'Le bénévole remplaçant doit être différent du bénévole sortant.',
            ], 422);
        }

        $alreadyAssigned = Affectation::where('id_mission', (int) $mission->id_mission)
            ->where('id_utilisateur', (int) $incomingPostulation->id_utilisateur)
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->exists();

        if ($alreadyAssigned) {
            return response()->json([
                'message' => 'Le bénévole remplaçant est déjà affecté à cette mission.',
            ], 422);
        }

        DB::transaction(function () use ($mission, $validated, $outgoingAffectation, $incomingPostulation): void {
            $outgoingAffectation->update([
                'statut_affectation' => 'annule',
            ]);

            Postulation::where('id_mission', (int) $mission->id_mission)
                ->where('id_utilisateur', (int) $validated['outgoing_user_id'])
                ->whereIn('statut_postulation', ['en_attente', 'accepte'])
                ->update([
                    'statut_postulation' => 'annule',
                    'date_annulation' => now(),
                ]);

            $incomingPostulation->update([
                'id_mission' => (int) $mission->id_mission,
                'id_evenement' => (int) $mission->id_evenement,
                'statut_postulation' => 'accepte',
                'date_decision' => now(),
                'date_annulation' => null,
            ]);

            Affectation::updateOrCreate(
                [
                    'id_mission' => (int) $mission->id_mission,
                    'id_utilisateur' => (int) $incomingPostulation->id_utilisateur,
                ],
                [
                    'statut_affectation' => 'assigne',
                    'est_responsable' => false,
                    'date_affectation' => now(),
                ]
            );
        });

        return response()->json([
            'message' => 'Remplacement effectué avec succès.',
        ], 200);
    }

    private function syncMissionResponsible(int $missionId, int $userId): void
    {
        Mission::where('id_mission', $missionId)
            ->update(['responsable_utilisateur_id' => $userId]);

        Affectation::where('id_mission', $missionId)
            ->where('id_utilisateur', '!=', $userId)
            ->where('est_responsable', true)
            ->update(['est_responsable' => false]);

        Affectation::where('id_mission', $missionId)
            ->where('id_utilisateur', $userId)
            ->update([
                'est_responsable' => true,
                'date_affectation' => now(),
            ]);
    }
}
