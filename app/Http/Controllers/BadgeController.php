<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index()// Récupérer tous les badges
    {
        $badges = Badge::with('competenceRules')->get();

        return response()->json($badges);
    }

    public function show($id) // Rechercher une affectation selon son id
    {
        $badge = Badge::with('competenceRules')->find($id);
        if (! empty($badge)) {
            return response()->json($badge);
        } else {
            return response()->json(['message' => 'Badge inexistant'], 404);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre_badge' => 'required|string|max:255',
            'description_badge' => 'nullable|string|max:1000',
            'score_badge' => 'nullable|integer|min:0',
            'regle_auto' => 'nullable|string|max:255',
            'competence_rules' => 'nullable|array',
            'competence_rules.*.id_competence' => 'required|integer|exists:competences,id_competence',
            'competence_rules.*.points_requis' => 'required|integer|min:1',
        ]);

        $badge = Badge::create([
            'titre_badge' => $validated['titre_badge'],
            'description_badge' => $validated['description_badge'] ?? null,
            'score_badge' => $validated['score_badge'] ?? 0,
            'regle_auto' => $validated['regle_auto'] ?? null,
        ]);

        $this->syncCompetenceRules($badge, $request->input('competence_rules', []));

        return response()->json([
            'message' => 'Badge ajouté',
            'badge' => $badge->load('competenceRules'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $badge = Badge::find($id);
        if (! $badge) {
            return response()->json([
                'message' => 'Badge inexistant',
            ], 404);
        }

        $validated = $request->validate([
            'titre_badge' => 'required|string|max:255',
            'description_badge' => 'nullable|string|max:1000',
            'score_badge' => 'nullable|integer|min:0',
            'regle_auto' => 'nullable|string|max:255',
            'competence_rules' => 'nullable|array',
            'competence_rules.*.id_competence' => 'required|integer|exists:competences,id_competence',
            'competence_rules.*.points_requis' => 'required|integer|min:1',
        ]);

        $badge->update([
            'titre_badge' => $validated['titre_badge'],
            'description_badge' => $validated['description_badge'] ?? null,
            'score_badge' => $validated['score_badge'] ?? 0,
            'regle_auto' => $validated['regle_auto'] ?? null,
        ]);

        if ($request->has('competence_rules')) {
            $this->syncCompetenceRules($badge, $request->input('competence_rules', []));
        }

        return response()->json([
            'message' => 'Badge mis à jour',
            'badge' => $badge->load('competenceRules'),
        ], 200);
    }

    public function destroy($id) // Supprimer un badge
    {
        if (Badge::where('id_badge', $id)->exists()) {
            $badge = Badge::find($id);
            $badge->delete();

            return response()->json(['message' => 'Badge supprimé'], 200);
        } else {
            return response()->json(['message' => 'Badge inexistant'], 404);
        }
    }

    private function syncCompetenceRules(Badge $badge, array $rules): void
    {
        $badge->competenceRules()->delete();

        foreach ($rules as $rule) {
            $competenceId = (int) ($rule['id_competence'] ?? 0);
            $pointsRequis = (int) ($rule['points_requis'] ?? 0);

            if ($competenceId <= 0 || $pointsRequis <= 0) {
                continue;
            }

            $badge->competenceRules()->create([
                'id_competence' => $competenceId,
                'points_requis' => $pointsRequis,
            ]);
        }
    }
}
