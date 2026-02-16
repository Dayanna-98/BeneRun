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

    $postulation = new Postulation;
    $postulation->id_mission = $request->id_mission;
    $postulation->id_benevole = $request->id_benevole;
    $postulation->date_postulation = $request->date_postulation;
    $postulation->date_acceptation_refus_postulation = $request->date_acceptation_refus_postulation;
    $postulation->date_annulation_postulation = $request->date_annulation_postulation;
    $postulation->remarque_postulation = $request->remarque_postulation;
    $postulation->presence_postulation = $request->presence_postulation;
    $postulation->commentaire_postulation = $request->commentaire_postulation;
    $postulation->statut_postulation = $request->statut_postulation;

    $postulation->save();

    return response()->json([
        'message' => 'Postulation créée',
        'postulation' => $postulation
        ], 201);
    }


    public function update(Request $request, $id) // Modifier une postulation
    {

    if (!Postulation::where('id_postulation', operator: $id)->exists()) {
        return response()->json([
            'message' => 'Postulation inexistante'
        ], 404);
    }

    $postulation = Postulation::find($id);
    $postulation->id_mission = $request->id_mission;
    $postulation->id_benevole = $request->id_benevole;
    $postulation->date_postulation = $request->date_postulation;
    $postulation->date_acceptation_refus_postulation = $request->date_acceptation_refus_postulation;
    $postulation->date_annulation_postulation = $request->date_annulation_postulation;
    $postulation->remarque_postulation = $request->remarque_postulation;
    $postulation->presence_postulation = $request->presence_postulation;
    $postulation->commentaire_postulation = $request->commentaire_postulation;
    $postulation->statut_postulation = $request->statut_postulation;

    $postulation->save();

    return response()->json([
        'message' => 'Postulation mise à jour',
        'postulation' => $postulation
    ], 200);
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
