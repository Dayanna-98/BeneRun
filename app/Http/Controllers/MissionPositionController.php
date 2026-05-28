<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\MissionPosition;
use Illuminate\Http\Request;

class MissionPositionController extends Controller
{
    /**
     * GET /missions/{id}/positions
     * Retourne les positions des participants actifs sur cette mission
     * (mises à jour dans les 30 dernières minutes).
     */
    public function index($missionId)
    {
        if (! $this->isMissionLiveLocationAvailable((int) $missionId)) {
            return response()->json(['message' => 'Le suivi de position n\'est plus disponible pour cette mission.'], 403);
        }

        $user = request()->user();
        if (! $user || ! $this->isActiveParticipantForMission((int) $user->id_utilisateur, (int) $missionId)) {
            return response()->json(['message' => 'Accès refusé à cette mission.'], 403);
        }

        $activeParticipantIds = Affectation::query()
            ->where('id_mission', (int) $missionId)
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->pluck('id_utilisateur');

        $responsableId = Mission::query()
            ->where('id_mission', (int) $missionId)
            ->value('responsable_utilisateur_id');

        if (! empty($responsableId)) {
            $activeParticipantIds->push((int) $responsableId);
        }

        $activeParticipantIds = $activeParticipantIds->unique()->values();

        $positions = MissionPosition::with('utilisateur:id_utilisateur,prenom_utilisateur,nom_utilisateur')
            ->where('id_mission', $missionId)
            ->whereIn('id_utilisateur', $activeParticipantIds)
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->get()
            ->map(fn ($p) => [
                'id_utilisateur' => $p->id_utilisateur,
                'name' => trim(($p->utilisateur->prenom_utilisateur ?? '').' '.($p->utilisateur->nom_utilisateur ?? '')) ?: 'Participant',
                'latitude' => (float) $p->latitude,
                'longitude' => (float) $p->longitude,
                'updated_at' => $p->updated_at,
            ]);

        return response()->json($positions);
    }

    /**
     * POST /missions/{id}/positions
     * Upsert la position d'un participant pour cette mission.
     */
    public function store(Request $request, $missionId)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'id_utilisateur' => 'prohibited',
        ]);

        if (! $this->isMissionLiveLocationAvailable((int) $missionId)) {
            return response()->json(['message' => 'Le suivi de position n\'est plus disponible pour cette mission.'], 403);
        }

        $user = $request->user();
        if (! $user || ! $this->isActiveParticipantForMission((int) $user->id_utilisateur, (int) $missionId)) {
            return response()->json(['message' => 'Accès refusé à cette mission.'], 403);
        }

        MissionPosition::updateOrCreate(
            [
                'id_mission' => (int) $missionId,
                'id_utilisateur' => (int) $user->id_utilisateur,
            ],
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]
        );

        return response()->json(['message' => 'Position mise à jour'], 200);
    }

    private function isActiveParticipantForMission(int $userId, int $missionId): bool
    {
        $isResponsible = Mission::query()
            ->where('id_mission', $missionId)
            ->where('responsable_utilisateur_id', $userId)
            ->exists();

        if ($isResponsible) {
            return true;
        }

        return Affectation::query()
            ->where('id_mission', $missionId)
            ->where('id_utilisateur', $userId)
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->exists();
    }

    private function isMissionLiveLocationAvailable(int $missionId): bool
    {
        $status = Mission::query()
            ->where('id_mission', $missionId)
            ->value('statut_mission');

        if (! is_string($status) || trim($status) === '') {
            return false;
        }

        return ! in_array($status, ['Terminée', 'Annulée'], true);
    }
}
