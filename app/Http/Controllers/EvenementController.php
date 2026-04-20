<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use Illuminate\Http\Request;

class EvenementController extends Controller
{
    public function index()
    {
        $events = Evenement::withCount('missions')
            ->orderBy('date_debut_evenement')
            ->get();

        return response()->json($events);
    }

    public function show($id)
    {
        $event = Evenement::withCount('missions')->find($id);

        if (!$event) {
            return response()->json(['message' => 'Événement inexistant'], 404);
        }

        return response()->json($event);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_evenement' => 'required|string|max:255',
            'description_evenement' => 'required|string',
            'date_debut_evenement' => 'required|date',
            'date_fin_evenement' => 'required|date|after_or_equal:date_debut_evenement',
            'heure_debut_evenement' => 'nullable|date_format:H:i',
            'heure_fin_evenement' => 'nullable|date_format:H:i',
            'lieu_evenement' => 'required|string|max:255',
            'latitude_evenement' => 'nullable|numeric|between:-90,90',
            'longitude_evenement' => 'nullable|numeric|between:-180,180',
            'organisateur_evenement' => 'required|string|max:255',
            'image_evenement' => 'nullable|string|max:500',
            'nombre_benevoles_requis' => 'required|integer|min:1',
            'est_annule_evenement' => 'nullable|boolean',
            'date_annulation_evenement' => 'nullable|date',
            'raison_annulation_evenement' => 'nullable|string|max:255',
            'est_publie_evenement' => 'nullable|boolean',
            'cree_par_utilisateur_id' => 'required|integer|exists:users,id_utilisateur',
        ]);

        $event = Evenement::create($validated);

        return response()->json([
            'message' => 'Événement ajouté',
            'event' => $event,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $event = Evenement::find($id);

        if (!$event) {
            return response()->json(['message' => 'Événement inexistant'], 404);
        }

        $validated = $request->validate([
            'nom_evenement' => 'sometimes|string|max:255',
            'description_evenement' => 'sometimes|string',
            'date_debut_evenement' => 'sometimes|date',
            'date_fin_evenement' => 'sometimes|date|after_or_equal:date_debut_evenement',
            'heure_debut_evenement' => 'nullable|date_format:H:i',
            'heure_fin_evenement' => 'nullable|date_format:H:i',
            'lieu_evenement' => 'sometimes|string|max:255',
            'latitude_evenement' => 'nullable|numeric|between:-90,90',
            'longitude_evenement' => 'nullable|numeric|between:-180,180',
            'organisateur_evenement' => 'sometimes|string|max:255',
            'image_evenement' => 'nullable|string|max:500',
            'nombre_benevoles_requis' => 'sometimes|integer|min:1',
            'est_annule_evenement' => 'nullable|boolean',
            'date_annulation_evenement' => 'nullable|date',
            'raison_annulation_evenement' => 'nullable|string|max:255',
            'est_publie_evenement' => 'nullable|boolean',
            'cree_par_utilisateur_id' => 'sometimes|integer|exists:users,id_utilisateur',
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Événement mis à jour',
            'event' => $event,
        ], 200);
    }

    public function destroy($id)
    {
        $event = Evenement::find($id);

        if (!$event) {
            return response()->json(['message' => 'Événement inexistant'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Événement supprimé'], 200);
    }
}