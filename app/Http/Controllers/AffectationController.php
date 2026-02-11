<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use Illuminate\Http\Request;

class AffectationController extends Controller
{
    public function index()// Récupérer toutes les affectations
    {
        $affectations = Affectation::all();
        return response()->json($affectations);
    }

    public function show($id) // Rechercher une affectation selon son id
    {
        $affectation = Affectation::find($id);
        if (!empty($affectation)){
            return response()->json($affectation);
        } 
        else {
            return response()->json(["message"=>"Affectation inexistante"], 404);
        }
    }

    public function store(Request $request) // Ajouter une affectation
    {
        $validated = $request->validate([
            'id_benevole' => 'required|exists:benevoles,id_benevole',
            'id_mission' => 'required|exists:missions,id_mission',
            'statut_affectation' => 'required|string',
            'remarque_affectation' => 'nullable|string',
            'est_responsable_affectation' => 'nullable|boolean'
        ]);

        $affectation = Affectation::create($validated);

        return response()->json(['message' => 'Affectation créée', 'affectation' => $affectation],201);
    }

    public function update(Request $request, $id) // Modifier une affectation en la recherchant selon son id
    {
        $affectation = Affectation::find($id);
        if (!$affectation) {
            return response()->json(['message' => 'Affectation inexistante'], 404);
        }

       $validated = $request->validate([
            'id_benevole' => 'exists:benevoles,id_benevole',
            'id_mission' => 'exists:missions,id_mission',
            'statut_affectation' => 'sometimes|string',
            'remarque_affectation' => 'sometimes|nullable|string',
            'est_responsable_affectation'  => 'sometimes|boolean'
        ]);

        $affectation->update($validated);

        return response()->json(['message' => 'Affectation mise à jour', 'affectation' => $affectation], 200);
    }

    public function destroy($id) // Supprimer une affectation
    {
        if(Affectation::where('id_affectation', $id)->exists()){
            $affectation = Affectation::find($id);
            $affectation->delete();
            return response()->json(['message'=>'Affectation supprimée'], 200);
        } else {
            return response()->json(['message'=>'Affectation inexistante'], 404);
        }
    }

}
