<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\User;
use Illuminate\Http\Request;

class EvenementController extends Controller
{
    private function resolveCreatorId($requestedId): ?int
    {
        $requestedId = is_numeric($requestedId) ? (int) $requestedId : null;

        if ($requestedId && User::where('id_utilisateur', $requestedId)->exists()) {
            return $requestedId;
        }

        return User::query()->orderBy('id_utilisateur')->value('id_utilisateur');
    }

    public function index()
    {
        return response()->json(Evenement::all());
    }

    public function show($id)
    {
        $evenement = Evenement::find($id);

        if (!$evenement) {
            return response()->json(['message' => 'Evenement inexistant'], 404);
        }

        return response()->json($evenement);
    }

    public function store(Request $request)
    {
        $creatorId = $this->resolveCreatorId($request->cree_par_utilisateur_id);

        if (!$creatorId) {
            return response()->json([
                'message' => 'Aucun utilisateur disponible pour creer un evenement.'
            ], 422);
        }

        $evenement = new Evenement();
        $evenement->nom_evenement = $request->nom_evenement;
        $evenement->description_evenement = $request->description_evenement;
        $evenement->date_debut_evenement = $request->date_debut_evenement;
        $evenement->date_fin_evenement = $request->date_fin_evenement;
        $evenement->heure_debut_evenement = $request->heure_debut_evenement;
        $evenement->heure_fin_evenement = $request->heure_fin_evenement;
        $evenement->lieu_evenement = $request->lieu_evenement;
        $evenement->latitude_evenement = $request->latitude_evenement;
        $evenement->longitude_evenement = $request->longitude_evenement;
        $evenement->organisateur_evenement = $request->organisateur_evenement;
        $evenement->image_evenement = $request->image_evenement;
        $evenement->nombre_benevoles_requis = $request->nombre_benevoles_requis ?? 0;
        $evenement->est_annule_evenement = $request->est_annule_evenement ?? false;
        $evenement->date_annulation_evenement = $request->date_annulation_evenement;
        $evenement->raison_annulation_evenement = $request->raison_annulation_evenement;
        $evenement->est_publie_evenement = $request->est_publie_evenement ?? false;
        $evenement->cree_par_utilisateur_id = $creatorId;
        $evenement->save();

        return response()->json([
            'message' => 'Evenement ajoute',
            'evenement' => $evenement,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $evenement = Evenement::find($id);

        if (!$evenement) {
            return response()->json(['message' => 'Evenement inexistant'], 404);
        }

        $creatorId = $this->resolveCreatorId($request->cree_par_utilisateur_id ?? $evenement->cree_par_utilisateur_id);

        if (!$creatorId) {
            return response()->json([
                'message' => 'Aucun utilisateur disponible pour mettre a jour un evenement.'
            ], 422);
        }

        $evenement->nom_evenement = $request->nom_evenement;
        $evenement->description_evenement = $request->description_evenement;
        $evenement->date_debut_evenement = $request->date_debut_evenement;
        $evenement->date_fin_evenement = $request->date_fin_evenement;
        $evenement->heure_debut_evenement = $request->heure_debut_evenement;
        $evenement->heure_fin_evenement = $request->heure_fin_evenement;
        $evenement->lieu_evenement = $request->lieu_evenement;
        $evenement->latitude_evenement = $request->latitude_evenement;
        $evenement->longitude_evenement = $request->longitude_evenement;
        $evenement->organisateur_evenement = $request->organisateur_evenement;
        $evenement->image_evenement = $request->image_evenement;
        $evenement->nombre_benevoles_requis = $request->nombre_benevoles_requis ?? $evenement->nombre_benevoles_requis;
        $evenement->est_annule_evenement = $request->est_annule_evenement ?? $evenement->est_annule_evenement;
        $evenement->date_annulation_evenement = $request->date_annulation_evenement;
        $evenement->raison_annulation_evenement = $request->raison_annulation_evenement;
        $evenement->est_publie_evenement = $request->est_publie_evenement ?? $evenement->est_publie_evenement;
        $evenement->cree_par_utilisateur_id = $creatorId;
        $evenement->save();

        return response()->json([
            'message' => 'Evenement mis a jour',
            'evenement' => $evenement,
        ]);
    }

    public function destroy($id)
    {
        $evenement = Evenement::find($id);

        if (!$evenement) {
            return response()->json(['message' => 'Evenement inexistant'], 404);
        }

        $evenement->delete();

        return response()->json(['message' => 'Evenement supprime']);
    }
}
