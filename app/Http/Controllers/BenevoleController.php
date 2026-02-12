<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Benevole;


class BenevoleController extends Controller
{
    public function index(Request $request)
{
    // Récupère tous les bénévoles
    $benevoles = Benevole::all();
    return response()->json($benevoles);
}

    public function show($id)
    {
        $benevole = Benevole::find($id);
        if (!empty($benevole ))
        {
            return response()->json($benevole);
        } else {
            return response()->json([
                "message"=>"Bénévole inexistant"
            ], 404);
        }
    }
 
    public function store(Request $request)
    {

        // Valider que l'id_utilisateur est présent
        $request->validate([
            'nb_missions_benevole' => 'required|integer',
            'id_utilisateur' => 'required|exists:users,id_utilisateur'
        ]);

        $benevole = new Benevole;
        $benevole->id_utilisateur = $request->id_utilisateur;
        $benevole->nb_missions_benevole = $request->nb_missions_benevole;


        $benevole->save();

        return response()->json([
            'message'=>'Bénévole  ajouté',
            'benevole' => $benevole
        ], 200);
    }
 
    public function update(Request $request, $id)
    {
        if (Benevole::where('id_benevole', $id)->exists())
        {
            $benevole = Benevole::find($id);
            $benevole->nb_missions_benevole = $request->nb_missions_benevole;
            $benevole->id_utilisateur = $request->id_utilisateur;
           
            $benevole->save();
            return response()->json([
                'message'=>'Bénévole mis à jour'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Bénévole inexistant'
            ], 404);
        }
    }
 
    public function destroy($id)
    {
        if (Benevole::where('id_benevole', $id)->exists())
        {
            $benevole = Benevole::find($id);
            $benevole->delete();
            return response()->json([
                'message'=>'Bénévole supprimé'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Bénévole inexistant'
            ], 404);
        }
    } 
}
