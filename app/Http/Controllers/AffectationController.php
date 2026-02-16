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

        public function store(Request $request)
    {
       	
        $affectation= new Affectation;
        $affectation->id_benevole = $request->id_benevole;
        $affectation->id_mission = $request->id_mission;
        $affectation->statut_affectation = $request->statut_affectation;
        $affectation->remarque_affectation = $request->remarque_affectation;
        $affectation->est_responsable_affectation = $request->est_responsable_affectation;

        $affectation->save();
        return response()->json([
            'message'=>'Affectation ajoutée'
        ], 200);
    }


        public function update(Request $request, $id)
    {
        if (Affectation::where('id_affectation', $id)->exists())
        {
            $affectation = Affectation::find($id);
            $affectation->id_benevole = $request->id_benevole;
            $affectation->id_mission = $request->id_mission;
            $affectation->statut_affectation = $request->statut_affectation;
            $affectation->remarque_affectation = $request->remarque_affectation;
            $affectation->est_responsable_affectation = $request->est_responsable_affectation;
     

            $affectation->save();
            return response()->json([
                'message'=>'Affectation mise à jour'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Affectation inexistante'
            ], 404);
        }
    }

         public function destroy($id)
    {
        if (Affectation::where('id_affectation', $id)->exists())
        {
            $affectation = Affectation::find($id);
            $affectation->delete();
            return response()->json([
                'message'=>'Affectation supprimée'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Affectation inexistante'
            ], 404);
        }
    } 

}
