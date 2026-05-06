<?php

namespace App\Http\Controllers;

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
        $positions = MissionPosition::with('utilisateur:id_utilisateur,prenom_utilisateur,nom_utilisateur')
            ->where('id_mission', $missionId)
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->get()
            ->map(fn ($p) => [
                'id_utilisateur' => $p->id_utilisateur,
                'name' => trim(($p->utilisateur->prenom_utilisateur ?? '') . ' ' . ($p->utilisateur->nom_utilisateur ?? '')) ?: 'Participant',
                'latitude'  => (float) $p->latitude,
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
            'id_utilisateur' => 'required|integer|exists:users,id_utilisateur',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
        ]);

        MissionPosition::updateOrCreate(
            [
                'id_mission'     => (int) $missionId,
                'id_utilisateur' => (int) $validated['id_utilisateur'],
            ],
            [
                'latitude'  => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]
        );

        return response()->json(['message' => 'Position mise à jour'], 200);
    }
}
