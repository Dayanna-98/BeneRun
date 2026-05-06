<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    /**
     * Stocker la localisation actuelle de l'utilisateur en cache (temps réel)
     * Cache pendant 5 minutes
     */
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();
        $cacheKey = 'user_location_' . $user->id_utilisateur;

        // Stocker en cache pour 5 minutes
        Cache::put($cacheKey, [
            'id_utilisateur' => $user->id_utilisateur,
            'nom_utilisateur' => $user->nom_utilisateur,
            'prenom_utilisateur' => $user->prenom_utilisateur,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'accuracy' => $validated['accuracy'] ?? null,
            'updated_at' => now(),
        ], now()->addMinutes(5));

        return response()->json([
            'message' => 'Localisation mise à jour',
            'location' => Cache::get($cacheKey),
        ], 200);
    }

    /**
     * Récupérer la localisation actuelle de l'utilisateur
     */
    public function getLocation(Request $request)
    {
        $user = $request->user();
        $cacheKey = 'user_location_' . $user->id_utilisateur;
        $location = Cache::get($cacheKey);

        if (!$location) {
            return response()->json([
                'message' => 'Aucune localisation trouvée',
                'location' => null,
            ], 404);
        }

        return response()->json([
            'location' => $location,
        ], 200);
    }

    /**
     * Récupérer les localisations de tous les volontaires actifs
     * Utilisé pour afficher tous les volontaires sur la carte
     */
    public function getAllLocations(Request $request)
    {
        $user = $request->user();

        // Récupérer toutes les clés de cache pour les localisations
        $patterns = ['user_location_*'];
        $locations = [];

        // Récupérer toutes les localisations en cache
        $allKeys = collect(Cache::getStore()->getPrefix() . 'user_location_*')->toArray();
        
        // Alternative plus efficace: utiliser Redis si disponible
        // Sinon, on peut faire une requête groupée
        foreach (range(1, 10000) as $userId) {
            $cacheKey = 'user_location_' . $userId;
            $location = Cache::get($cacheKey);
            if ($location && $userId !== $user->id_utilisateur) {
                $locations[] = $location;
            }
        }

        return response()->json([
            'locations' => $locations,
            'count' => count($locations),
        ], 200);
    }

    /**
     * Supprimer la localisation de l'utilisateur
     */
    public function deleteLocation(Request $request)
    {
        $user = $request->user();
        $cacheKey = 'user_location_' . $user->id_utilisateur;
        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'Localisation supprimée',
        ], 200);
    }
}
