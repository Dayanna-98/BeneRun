<?php
namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Postulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PostulationController extends Controller
{
    public function index()// Récupérer toutes les postulations
    {
        $postulations = Postulation::with([
            'mission:id_mission,titre_mission,date_mission',
            'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ])->get();
        return response()->json($postulations);
    }

    public function show($id) // Rechercher une postulation selon son id
    {
        $postulation = Postulation::with([
            'mission:id_mission,titre_mission,date_mission',
            'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ])->find($id);
        if (!empty($postulation)){
            return response()->json($postulation);
        } 
        else {
            return response()->json(["message"=>"Postulation inexistante"], 404);
        }
    }

    public function store(Request $request) // Ajouter une postulation
    {
        $validated = $request->validate([
            'id_mission' => 'required|integer|exists:missions,id_mission',
            'id_utilisateur' => 'required|integer|exists:users,id_utilisateur',
            'statut_postulation' => ['nullable', Rule::in(['en_attente', 'accepte', 'refuse', 'annule'])],
            'remarque' => 'nullable|string',
            'date_postulation' => 'nullable|date',
            'date_decision' => 'nullable|date',
            'date_annulation' => 'nullable|date',
        ]);

        $existing = Postulation::where('id_mission', $validated['id_mission'])
            ->where('id_utilisateur', $validated['id_utilisateur'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Cet utilisateur est déjà inscrit ou a déjà postulé à cette mission.',
                'postulation' => $existing,
            ], 409);
        }

        $postulation = DB::transaction(function () use ($validated) {
            $postulation = Postulation::create([
                'id_mission' => $validated['id_mission'],
                'id_utilisateur' => $validated['id_utilisateur'],
                'statut_postulation' => $validated['statut_postulation'] ?? 'en_attente',
                'remarque' => $validated['remarque'] ?? null,
                'date_postulation' => $validated['date_postulation'] ?? now(),
                'date_decision' => $validated['date_decision'] ?? null,
                'date_annulation' => $validated['date_annulation'] ?? null,
            ]);

            $this->syncAffectationFromPostulation($postulation);

            return $postulation;
        });

        return response()->json([
        'message' => 'Postulation créée',
        'postulation' => $postulation->load([
            'mission:id_mission,titre_mission,date_mission',
            'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ])
        ], 201);
    }


    public function inscrireMission(Request $request, $idMission)
    {
        $payload = array_merge($request->all(), [
            'id_mission' => (int) $idMission,
            'statut_postulation' => $request->input('statut_postulation', 'en_attente'),
            'date_postulation' => $request->input('date_postulation', now()),
        ]);

        return $this->store(new Request($payload));
    }


    public function update(Request $request, $id) // Modifier une postulation
    {
        $postulation = Postulation::find($id);

        if (!$postulation) {
            return response()->json([
                'message' => 'Postulation inexistante'
            ], 404);
        }

        $validated = $request->validate([
            'id_mission' => 'sometimes|integer|exists:missions,id_mission',
            'id_utilisateur' => 'sometimes|integer|exists:users,id_utilisateur',
            'statut_postulation' => ['nullable', Rule::in(['en_attente', 'accepte', 'refuse', 'annule'])],
            'remarque' => 'nullable|string',
            'date_postulation' => 'nullable|date',
            'date_decision' => 'nullable|date',
            'date_annulation' => 'nullable|date',
        ]);

        $postulation = DB::transaction(function () use ($postulation, $validated) {
            if (array_key_exists('statut_postulation', $validated)) {
                if (in_array($validated['statut_postulation'], ['accepte', 'refuse'], true) && !array_key_exists('date_decision', $validated)) {
                    $validated['date_decision'] = now();
                }

                if ($validated['statut_postulation'] === 'annule' && !array_key_exists('date_annulation', $validated)) {
                    $validated['date_annulation'] = now();
                }
            }

            $postulation->update($validated);
            $this->syncAffectationFromPostulation($postulation);

            return $postulation;
        });

        return response()->json([
            'message' => 'Postulation mise à jour',
            'postulation' => $postulation->load([
                'mission:id_mission,titre_mission,date_mission',
                'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            ])
        ], 200);
    }


    public function destroy($id) // Supprimer une postulation
    {
        if(Postulation::where('id_postulation', $id)->exists()){
            $postulation = Postulation::find($id);
            $postulation->delete();
            return response()->json(['message'=>'Postulation supprimée'], 200);
        } else {
            return response()->json(['message'=>'Postulation inexistante'], 404);
        }
    }

    private function syncAffectationFromPostulation(Postulation $postulation): void
    {
        if ($postulation->statut_postulation === 'accepte') {
            Affectation::updateOrCreate(
                [
                    'id_mission' => $postulation->id_mission,
                    'id_utilisateur' => $postulation->id_utilisateur,
                ],
                [
                    'statut_affectation' => 'assigne',
                    'est_responsable' => false,
                    'date_affectation' => now(),
                ]
            );
        }

        if (in_array($postulation->statut_postulation, ['refuse', 'annule'], true)) {
            Affectation::where('id_mission', $postulation->id_mission)
                ->where('id_utilisateur', $postulation->id_utilisateur)
                ->where('est_responsable', false)
                ->update(['statut_affectation' => 'annule']);
        }
    }

}
