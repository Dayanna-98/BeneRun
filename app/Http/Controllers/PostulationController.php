<?php

namespace App\Http\Controllers;

use App\Models\Postulation;
use Illuminate\Http\Request;

class PostulationController extends Controller
{
    public function index()// Récupérer toutes les postulations
    {
        $postulations = Postulation::all();
        return response()->json($postulations);
    }

    public function show($id) // Rechercher une postulation selon son id
    {
        $postulation = Postulation::find($id);
        if (!empty($postulation)){
            return response()->json($postulation);
        } 
        else {
            return response()->json(["message"=>"Postulation inexistante"], 404);
        }
    }

    public function store(Request $request) // Ajouter une postulation
    {
        $validated = $request->validate([
            'id_mission' => 'required|exists:missions,id_mission',
            'id_benevole' => 'required|exists:benevoles,id_benevole',
            'date_postulation' => 'required|date',
            'date_acceptation_refus_postulation' => 'required|date',
            'date_annulation_postulation' => 'required|date',
            'remarque_postulation' => 'sometimes|nullable|string',
            'presence_postulation' => 'required|boolean',
            'commentaire_postulation' => 'sometimes|nullable|string', 
            'statut_postulation' => 'required|string'
        ]);

        $postulation = Postulation::create($validated);

        return response()->json(['message' => 'Postulation créée', 'postulation' => $postulation],201);
    }

    public function update(Request $request, $id) // Modifier une postulation en la recherchant selon son id
    {
        $postulation = Postulation::find($id);
        if (!$postulation) {
            return response()->json(['message' => 'Postulation inexistante'], 404);
        }

        $validated = $request->validate([
            'id_mission' => 'required|exists:missions,id_mission',
            'id_benevole' => 'required|exists:benevoles,id_benevole',
            'date_postulation' => 'required|date',
            'date_acceptation_refus_postulation' => 'required|date',
            'date_annulation_postulation' => 'required|date',
            'remarque_postulation' => 'sometimes|nullable|string',
            'presence_postulation' => 'required|boolean',
            'commentaire_postulation' => 'sometimes|nullable|string', 
            'statut_postulation' => 'required|string'
        ]);

        $postulation->update($validated);

        return response()->json(['message' => 'Postulation mise à jour', 'postulation' => $postulation], 200);
    }

    public function destroy($id) // Supprimer une postulation
    {
        if(Postulation::where('id_postulation', $id)->exists()){
            $postulation = Postulation::find($id);
            $postulation->delete();
            return response()->json(['message'=>'Postulation supprimée'], 200);
        } else {
            return response()->json(['message'=>'Postulation inexistante'], 404);
        }
    }

}
