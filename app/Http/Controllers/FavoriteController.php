<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Liste les missions favorites de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $missions = $user->missions_favorites()
            ->with(['evenement'])
            ->withCount(['affectations as current_volunteers_count' => function ($q) {
                $q->where('statut_affectation', 'confirmé');
            }])
            ->get();

        return response()->json($missions);
    }

    /**
     * Ajoute une mission aux favoris de l'utilisateur connecté.
     */
    public function store(Request $request, $missionId)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $mission = Mission::find($missionId);

        if (!$mission) {
            return response()->json(['message' => 'Mission introuvable'], 404);
        }

        // Attach uniquement si pas déjà en favori (évite les doublons)
        $user->missions_favorites()->syncWithoutDetaching([$mission->id_mission]);

        return response()->json(['message' => 'Mission ajoutée aux favoris'], 201);
    }

    /**
     * Retire une mission des favoris de l'utilisateur connecté.
     */
    public function destroy(Request $request, $missionId)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $user->missions_favorites()->detach($missionId);

        return response()->json(['message' => 'Mission retirée des favoris']);
    }

    /**
     * Vérifie si une mission est en favori pour l'utilisateur connecté.
     */
    public function check(Request $request, $missionId)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $isFavorite = $user->missions_favorites()
            ->where('id_mission', $missionId)
            ->exists();

        return response()->json(['is_favorite' => $isFavorite]);
    }
}
