<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Mission::query();

        if ($request->filled('id_evenement')) {
            $query->where('id_evenement', (int) $request->id_evenement);
        }

        if ($request->filled('statut_mission')) {
            $query->where('statut_mission', $request->statut_mission);
        }

        if ($request->filled('visibilite_mission')) {
            $query->where('visibilite_mission', $request->visibilite_mission);
        }

        return response()->json($query->orderBy('date_mission')->get());
    }

    public function show($id)
    {
        $mission = Mission::with(['evenement', 'responsable', 'medias'])->find($id);

        if (!$mission) {
            return response()->json(['message' => 'Mission inexistante'], 404);
        }

        return response()->json($mission);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_evenement' => 'required|integer|exists:evenements,id_evenement',
            'responsable_utilisateur_id' => 'required|integer|exists:users,id_utilisateur',
            'titre_mission' => 'required|string|max:150',
            'type_mission' => 'required|in:secours,logistique,accueil,technique,animation,autre',
            'description_mission' => 'required|string',
            'date_mission' => 'required|date',
            'heure_debut_mission' => 'required|date_format:H:i',
            'heure_fin_mission' => 'required|date_format:H:i|after:heure_debut_mission',
            'lieu_mission' => 'required|string|max:255',
            'latitude_mission' => 'nullable|numeric|between:-90,90',
            'longitude_mission' => 'nullable|numeric|between:-180,180',
            'nombre_benevoles_max' => 'required|integer|min:1',
            'nombre_benevoles_backup' => 'nullable|integer|min:0',
            'statut_mission' => 'nullable|in:À venir,En cours,Terminée,Annulée',
            'inscription_requise' => 'nullable|boolean',
            'visibilite_mission' => 'nullable|in:publique,privée,limitée',
            'consignes_securite' => 'nullable|string',
            'image_mission' => 'nullable|string|max:500',
        ]);

        $mission = Mission::create($validated);

        return response()->json([
            'message' => 'Mission ajoutée',
            'mission' => $mission,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $mission = Mission::find($id);

        if (!$mission) {
            return response()->json(['message' => 'Mission inexistante'], 404);
        }

        $validated = $request->validate([
            'id_evenement' => 'sometimes|integer|exists:evenements,id_evenement',
            'responsable_utilisateur_id' => 'sometimes|integer|exists:users,id_utilisateur',
            'titre_mission' => 'sometimes|string|max:150',
            'type_mission' => 'sometimes|in:secours,logistique,accueil,technique,animation,autre',
            'description_mission' => 'sometimes|string',
            'date_mission' => 'sometimes|date',
            'heure_debut_mission' => 'sometimes|date_format:H:i',
            'heure_fin_mission' => 'sometimes|date_format:H:i',
            'lieu_mission' => 'sometimes|string|max:255',
            'latitude_mission' => 'nullable|numeric|between:-90,90',
            'longitude_mission' => 'nullable|numeric|between:-180,180',
            'nombre_benevoles_max' => 'sometimes|integer|min:1',
            'nombre_benevoles_backup' => 'nullable|integer|min:0',
            'statut_mission' => 'nullable|in:À venir,En cours,Terminée,Annulée',
            'inscription_requise' => 'nullable|boolean',
            'visibilite_mission' => 'nullable|in:publique,privée,limitée',
            'consignes_securite' => 'nullable|string',
            'image_mission' => 'nullable|string|max:500',
        ]);

        $mission->update($validated);

        return response()->json([
            'message' => 'Mission mise à jour',
            'mission' => $mission,
        ], 200);
    }

    public function destroy($id)
    {
        $mission = Mission::find($id);

        if (!$mission) {
            return response()->json(['message' => 'Mission inexistante'], 404);
        }

        $mission->delete();

        return response()->json(['message' => 'Mission supprimée'], 200);
    }
}
