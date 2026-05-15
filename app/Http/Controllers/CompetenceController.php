<?php

namespace App\Http\Controllers;

use App\Models\Competence;
use Illuminate\Http\Request;

class CompetenceController extends Controller
{
    private const MISSION_TYPES = ['secours', 'logistique', 'accueil', 'technique', 'animation', 'autre'];

    public function index(Request $request)
    {
        // Récupère toutes les compétences
        $competences = Competence::all();

        return response()->json($competences);
    }

    public function show($id)
    {
        $competences = Competence::find($id);
        if (! empty($competences)) {
            return response()->json($competences);
        } else {
            return response()->json([
                'message' => 'Compétence inexistante',
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom_competence' => 'required|string|max:255|unique:competences,nom_competence',
            'types_mission_suggeres' => 'nullable|array',
            'types_mission_suggeres.*' => 'string|in:secours,logistique,accueil,technique,animation,autre',
        ]);

        $typesMissionSuggeres = collect($request->input('types_mission_suggeres', []))
            ->map(fn ($type) => strtolower(trim((string) $type)))
            ->filter(fn ($type) => in_array($type, self::MISSION_TYPES, true))
            ->unique()
            ->values()
            ->all();

        $competence = new Competence;
        $competence->nom_competence = $request->nom_competence;
        $competence->types_mission_suggeres = $typesMissionSuggeres;
        $competence->save();

        return response()->json([
            'message' => 'Compétence ajoutée',
            'competence' => $competence,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $competence = Competence::find($id);

        if (! $competence) {
            return response()->json([
                'message' => 'Compétence inexistante',
            ], 404);
        }

        $request->validate([
            'nom_competence' => 'required|string|max:255|unique:competences,nom_competence,'.$id.',id_competence',
            'types_mission_suggeres' => 'nullable|array',
            'types_mission_suggeres.*' => 'string|in:secours,logistique,accueil,technique,animation,autre',
        ]);

        $typesMissionSuggeres = collect($request->input('types_mission_suggeres', []))
            ->map(fn ($type) => strtolower(trim((string) $type)))
            ->filter(fn ($type) => in_array($type, self::MISSION_TYPES, true))
            ->unique()
            ->values()
            ->all();

        $competence->nom_competence = $request->nom_competence;
        $competence->types_mission_suggeres = $typesMissionSuggeres;
        $competence->save();

        return response()->json([
            'message' => 'Compétence mise à jour',
            'competence' => $competence,
        ], 200);
    }

    public function destroy($id)
    {
        if (Competence::where('id_competence', $id)->exists()) {
            $competence = Competence::find($id);
            $competence->delete();

            return response()->json([
                'message' => 'Compétence supprimée',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Compétence inexistante',
            ], 404);
        }
    }
}
