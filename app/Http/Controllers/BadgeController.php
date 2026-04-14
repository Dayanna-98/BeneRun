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
        $validated = $request->validate([
            'titre_badge' => 'required|string|max:255',
            'description_badge' => 'nullable|string|max:1000',
            'score_badge' => 'nullable|integer|min:0',
            'regle_auto' => 'nullable|string|max:255',
        ]);

        $badge = Badge::create([
            'titre_badge' => $validated['titre_badge'],
            'description_badge' => $validated['description_badge'] ?? null,
            'score_badge' => $validated['score_badge'] ?? 0,
            'regle_auto' => $validated['regle_auto'] ?? null,
        ]);

        return response()->json([
            'message' => 'Badge ajouté',
            'badge' => $badge,
        ], 201);
    }
 
    public function update(Request $request, $id)
    {
        $badge = Badge::find($id);
        if (!$badge) {
            return response()->json([
                'message' => 'Badge inexistant'
            ], 404);
        }

        $validated = $request->validate([
            'titre_badge' => 'required|string|max:255',
            'description_badge' => 'nullable|string|max:1000',
            'score_badge' => 'nullable|integer|min:0',
            'regle_auto' => 'nullable|string|max:255',
        ]);

        $badge->update([
            'titre_badge' => $validated['titre_badge'],
            'description_badge' => $validated['description_badge'] ?? null,
            'score_badge' => $validated['score_badge'] ?? 0,
            'regle_auto' => $validated['regle_auto'] ?? null,
        ]);

        return response()->json([
            'message' => 'Badge mis à jour',
            'badge' => $badge,
        ], 200);
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
