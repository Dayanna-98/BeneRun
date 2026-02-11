<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function index()// Récupérer toutes les missions
    {
        $missions = Mission::all();
        return response()->json($missions);
    }

    public function show($id) // Rechercher une mission selon son id
    {
        $mission = Mission::find($id);
        if (!empty($mission)){
            return response()->json($mission);
        } 
        else {
            return response()->json(["message"=>"Mission inexistante"], 404);
        }
    }

    public function store(Request $request) // Ajouter une mission
    {
        $validated = $request->validate([
            'id_course' => 'required|exists:courses,id_course',
            'id_benevole' => 'required|exists:benevoles,id_benevole',
            'titre_mission' => 'required|string',
            'type_mission' => 'required|string',
            'description_mission' => 'required|string',
            'date_debut_mission' => 'required|date',
            'date_fin_mission' => 'required|date',
            'heure_debut_mission' => 'required|time',
            'heure_fin_mission' => 'required|time',
            'lieu_mission' => 'required|string',
            'nombre_mission' => 'required|int',
            'statut_mission' => 'required|string',
            'publie_mission' => 'required|boolean'
        ]);

        $mission = Mission::create($validated);

        return response()->json(['message' => 'Mission créée', 'mission' => $mission],201);
    }

    public function update(Request $request, $id) // Modifier une mission en la recherchant selon son id
    {
        $mission = Mission::find($id);
        if (!$mission) {
            return response()->json(['message' => 'Mission inexistante'], 404);
        }

        $validated = $request->validate([
            'id_course' => 'required|exists:courses,id_course',
            'id_benevole' => 'required|exists:benevoles,id_benevole',
            'titre_mission' => 'required|string',
            'type_mission' => 'required|string',
            'description_mission' => 'required|string',
            'date_debut_mission' => 'required|date',
            'date_fin_mission' => 'required|date',
            'heure_debut_mission' => 'required|time',
            'heure_fin_mission' => 'required|time',
            'lieu_mission' => 'required|string',
            'nombre_mission' => 'required|int',
            'statut_mission' => 'required|string',
            'publie_mission' => 'required|boolean'
        ]);

        $mission->update($validated);

        return response()->json(['message' => 'Mission mise à jour', 'mission' => $mission], 200);
    }

    public function destroy($id) // Supprimer une mission
    {
        if(Mission::where('id_mission', $id)->exists()){
            $mission = Mission::find($id);
            $mission->delete();
            return response()->json(['message'=>'Mission supprimée'], 200);
        } else {
            return response()->json(['message'=>'Mission inexistante'], 404);
        }
    }

}
