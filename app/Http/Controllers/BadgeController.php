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

    public function store(Request $request) // Ajouter un badge
    {
        $validated = $request->validate([
            'id_benevole' => 'required|exists:benevoles,id_benevole',
            'titre_badge' => 'required|string',
            'valeur_badge' => 'required|int',
            'regle_auto_badge' => 'required|string'
        ]);

        $badge = Badge::create($validated);

        return response()->json(['message' => 'Badge crée', 'badge' => $badge],201);
    }

    public function update(Request $request, $id) // Modifier un badge en le recherchant selon son id
    {
        $badge = Badge::find($id);
        if (!$badge) {
            return response()->json(['message' => 'Badge inexistant'], 404);
        }

        $validated = $request->validate([
            'id_benevole' => 'required|exists:benevoles,id_benevole',
            'titre_badge' => 'required|string',
            'valeur_badge' => 'required|int',
            'regle_auto_badge' => 'required|string'
        ]);

        $badge->update($validated);

        return response()->json(['message' => 'Badge mise à jour', 'badge' => $badge], 200);
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
