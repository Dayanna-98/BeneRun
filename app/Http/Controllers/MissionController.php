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

        public function store(Request $request)
    {
        $mission = new Mission;
        $mission->id_course = $request->id_course;
        $mission->id_benevole = $request->id_benevole;
        $mission->titre_mission = $request->titre_mission;
        $mission->type_mission = $request->type_mission;
        $mission->description_mission = $request->description_mission;
        $mission->date_debut_mission = $request->date_debut_mission;
        $mission->date_fin_mission = $request->date_fin_mission;
        $mission->heure_debut_mission = $request->heure_debut_mission;
        $mission->heure_fin_mission = $request->heure_fin_mission;
        $mission->lieu_mission = $request->lieu_mission;
        $mission->nombre_mission = $request->nombre_mission;
        $mission->statut_mission = $request->statut_mission;
        $mission->publie_mission = $request->publie_mission;

        $mission->save();

        return response()->json([
            'message' => 'Mission ajoutée'
        ], 201);
    }

    public function update(Request $request, $id) // Modifier une mission
    {
    if (Mission::where('id_mission', $id)->exists())
    {
    
        $mission = Mission::find($id);
        $mission->id_course = $request->id_course;
        $mission->id_benevole = $request->id_benevole;
        $mission->titre_mission = $request->titre_mission;
        $mission->type_mission = $request->type_mission;
        $mission->description_mission = $request->description_mission;
        $mission->date_debut_mission = $request->date_debut_mission;
        $mission->date_fin_mission = $request->date_fin_mission;
        $mission->heure_debut_mission = $request->heure_debut_mission;
        $mission->heure_fin_mission = $request->heure_fin_mission;
        $mission->lieu_mission = $request->lieu_mission;
        $mission->nombre_mission = $request->nombre_mission;
        $mission->statut_mission = $request->statut_mission;
        $mission->publie_mission = $request->publie_mission;

        $mission->save();

        return response()->json([
            'message' => 'Mission mise à jour',
            'mission' => $mission
        ], 200);
    }
    else {
        return response()->json([
            'message' => 'Mission inexistante'
            ], 404);
        }
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
