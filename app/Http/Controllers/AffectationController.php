<?php
namespace App\Http\Controllers;

use App\Models\Affectation;
use Illuminate\Http\Request;

class AffectationController extends Controller
{
    public function index()// Récupérer toutes les affectations
    {
        $affectations = Affectation::with([
            'mission:id_mission,titre_mission',
            'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ])->get();
        return response()->json($affectations);
    }

    public function show($id) // Rechercher une affectation selon son id
    {
        $affectation = Affectation::with([
            'mission:id_mission,titre_mission',
            'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ])->find($id);
        if (!empty($affectation)){
            return response()->json($affectation);
        } 
        else {
            return response()->json(["message"=>"Affectation inexistante"], 404);
        }
    }

        public function store(Request $request)
    {
        $validated = $request->validate([
            'id_mission' => 'required|integer|exists:missions,id_mission',
            'id_utilisateur' => 'required|integer|exists:users,id_utilisateur',
            'statut_affectation' => 'nullable|in:assigne,confirme,present,absent,annule',
            'est_responsable' => 'nullable|boolean',
            'remarque' => 'nullable|string',
            'date_affectation' => 'nullable|date',
            'date_confirmation' => 'nullable|date',
            'date_presence' => 'nullable|date',
        ]);

        $affectation = Affectation::create($validated);

        return response()->json([
            'message' => 'Affectation ajoutée',
            'affectation' => $affectation,
        ], 201);
    }


        public function update(Request $request, $id)
    {
        if (Affectation::where('id_affectation', $id)->exists())
        {
            $affectation = Affectation::find($id);
            $validated = $request->validate([
                'id_mission' => 'sometimes|integer|exists:missions,id_mission',
                'id_utilisateur' => 'sometimes|integer|exists:users,id_utilisateur',
                'statut_affectation' => 'nullable|in:assigne,confirme,present,absent,annule',
                'est_responsable' => 'nullable|boolean',
                'remarque' => 'nullable|string',
                'date_affectation' => 'nullable|date',
                'date_confirmation' => 'nullable|date',
                'date_presence' => 'nullable|date',
            ]);

            $affectation->update($validated);
            return response()->json([
                'message' => 'Affectation mise à jour',
                'affectation' => $affectation,
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
