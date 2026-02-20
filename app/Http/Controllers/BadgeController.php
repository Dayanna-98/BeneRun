<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index()// Récupérer tous les badges
    {
        $badges = Badge::all();
        return response()->json($badges);
    }

    public function show($id) // Rechercher une affectation selon son id
    {
        $badge = Badge::find($id);
        if (!empty($badge)){
            return response()->json($badge);
        } 
        else {
            return response()->json(["message"=>"Badge inexistant"], 404);
        }
    }

    public function store(Request $request)
    {
        $badge= new Badge;
        $badge->id_benevole = $request->id_benevole;
        $badge->titre_badge = $request->titre_badge;
        $badge->valeur_badge = $request->valeur_badge;
        $badge->regle_auto_badge = $request->regle_auto_badge;

        $badge->save();
        return response()->json([
            'message'=>'Badge ajouté'
        ], 200);
    }
 
    public function update(Request $request, $id)
    {
        if (Badge::where('id_badge', $id)->exists())
        {
            $badge = Badge::find($id);
            $badge->id_benevole = $request->id_benevole;
            $badge->titre_badge = $request->titre_badge;
            $badge->valeur_badge = $request->valeur_badge;
            $badge->regle_auto_badge = $request->regle_auto_badge;
     
            $badge->save();
            return response()->json([
                'message'=>'Badge mise à jour'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Badge inexistant'
            ], 404);
        }
    }

    public function destroy($id) // Supprimer un badge
    {
        if(Badge::where('id_badge', $id)->exists()){
            $badge = Badge::find($id);
            $badge->delete();
            return response()->json(['message'=>'Badge supprimé'], 200);
        } else {
            return response()->json(['message'=>'Badge inexistant'], 404);
        }
    }

}
