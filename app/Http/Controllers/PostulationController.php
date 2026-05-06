<?php
namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Evenement;
use App\Models\Mission;
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
            'evenement:id_evenement,nom_evenement,date_debut_evenement,date_fin_evenement,heure_debut_evenement,heure_fin_evenement',
            'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ])->get();
        return response()->json($postulations);
    }

    public function show($id) // Rechercher une postulation selon son id
    {
        $postulation = Postulation::with([
            'mission:id_mission,titre_mission,date_mission',
            'evenement:id_evenement,nom_evenement,date_debut_evenement,date_fin_evenement,heure_debut_evenement,heure_fin_evenement',
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
            'id_mission' => 'nullable|integer|exists:missions,id_mission|required_without:id_evenement',
            'id_evenement' => 'nullable|integer|exists:evenements,id_evenement|required_without:id_mission',
            'id_utilisateur' => 'required|integer|exists:users,id_utilisateur',
            'statut_postulation' => ['nullable', Rule::in(['en_attente', 'accepte', 'refuse', 'annule'])],
            'remarque' => 'nullable|string',
            'date_postulation' => 'nullable|date',
            'date_decision' => 'nullable|date',
            'date_annulation' => 'nullable|date',
        ]);

        $validated = $this->hydrateMissionAndEvent($validated);

        if (($validated['statut_postulation'] ?? 'en_attente') === 'accepte' && empty($validated['id_mission'])) {
            return response()->json([
                'message' => 'Une postulation acceptée doit être rattachée à une mission.',
            ], 422);
        }

        $targetWindow = $this->getTargetWindow($validated);
        if ($targetWindow === null) {
            return response()->json([
                'message' => 'Impossible de déterminer la période de disponibilité liée à cette postulation.',
            ], 422);
        }

        $existingMissionPostulation = !empty($validated['id_mission'])
            ? Postulation::where('id_mission', $validated['id_mission'])
            ->where('id_utilisateur', $validated['id_utilisateur'])
            ->first()
            : null;

        if ($existingMissionPostulation) {
            return response()->json([
                'message' => 'Cet utilisateur est déjà inscrit ou a déjà postulé à cette mission.',
                'postulation' => $existingMissionPostulation,
            ], 409);
        }

        // Vérifie si l'utilisateur est déjà inscrit à une autre mission du même événement
        if (!empty($validated['id_mission']) && !empty($validated['id_evenement'])) {
            $existingEventMissionPostulation = Postulation::whereNotNull('id_mission')
                ->where('id_evenement', $validated['id_evenement'])
                ->where('id_utilisateur', $validated['id_utilisateur'])
                ->whereIn('statut_postulation', ['en_attente', 'accepte'])
                ->first();

            if ($existingEventMissionPostulation) {
                return response()->json([
                    'message' => 'Vous êtes déjà inscrit à une mission de cet événement. Il n\'est pas possible de participer à deux missions du même événement.',
                ], 409);
            }
        }

        $existingEventWaitingList = !empty($validated['id_evenement'])
            ? Postulation::where('id_evenement', $validated['id_evenement'])
                ->whereNull('id_mission')
                ->where('id_utilisateur', $validated['id_utilisateur'])
                ->whereIn('statut_postulation', ['en_attente', 'accepte'])
                ->first()
            : null;

        if ($existingEventWaitingList) {
            return response()->json([
                'message' => 'Cet utilisateur est déjà en liste d\'attente ou déjà dispatché sur cet événement.',
                'postulation' => $existingEventWaitingList,
            ], 409);
        }

        $blockingWaitingList = $this->findBlockingWaitingList(
            (int) $validated['id_utilisateur'],
            $targetWindow['start'],
            $targetWindow['end'],
            isset($validated['id_evenement']) ? (int) $validated['id_evenement'] : null
        );

        if ($blockingWaitingList) {
            return response()->json([
                'message' => 'Inscription impossible: l\'utilisateur est déjà en liste d\'attente sur un autre événement au même créneau.',
                'blocking_postulation_id' => $blockingWaitingList->id_postulation,
            ], 409);
        }

        if ($this->hasLockedAssignmentConflict((int) $validated['id_utilisateur'], $targetWindow['start'], $targetWindow['end'])) {
            return response()->json([
                'message' => 'Inscription impossible: l\'utilisateur est déjà affecté sur une mission pendant ce créneau.',
            ], 409);
        }

        $postulation = DB::transaction(function () use ($validated) {
            $postulation = Postulation::create([
                'id_mission' => $validated['id_mission'] ?? null,
                'id_evenement' => $validated['id_evenement'] ?? null,
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
            'evenement:id_evenement,nom_evenement,date_debut_evenement,date_fin_evenement,heure_debut_evenement,heure_fin_evenement',
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

    public function inscrireEvenement(Request $request, $idEvenement)
    {
        $payload = array_merge($request->all(), [
            'id_evenement' => (int) $idEvenement,
            'id_mission' => null,
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
            'id_evenement' => 'sometimes|integer|exists:evenements,id_evenement',
            'id_utilisateur' => 'sometimes|integer|exists:users,id_utilisateur',
            'statut_postulation' => ['nullable', Rule::in(['en_attente', 'accepte', 'refuse', 'annule'])],
            'remarque' => 'nullable|string',
            'date_postulation' => 'nullable|date',
            'date_decision' => 'nullable|date',
            'date_annulation' => 'nullable|date',
        ]);

        $isLocked = $this->isLockedPostulation($postulation);
        if ($isLocked) {
            $missionChanged = array_key_exists('id_mission', $validated)
                && (int) $validated['id_mission'] !== (int) $postulation->id_mission;
            $statusChangedToCancelable = array_key_exists('statut_postulation', $validated)
                && in_array($validated['statut_postulation'], ['annule', 'refuse'], true);

            if ($missionChanged || $statusChangedToCancelable) {
                return response()->json([
                    'message' => 'Cette inscription est verrouillée: une fois assigné à une mission, le bénévole ne peut pas changer de mission ni se désinscrire.',
                ], 422);
            }
        }

        $merged = array_merge($postulation->toArray(), $validated);
        $merged = $this->hydrateMissionAndEvent($merged);

        if (($merged['statut_postulation'] ?? $postulation->statut_postulation) === 'accepte' && empty($merged['id_mission'])) {
            return response()->json([
                'message' => 'Une postulation acceptée doit être rattachée à une mission.',
            ], 422);
        }

        $postulation = DB::transaction(function () use ($postulation, $validated, $merged) {
            if (array_key_exists('id_evenement', $merged)) {
                $validated['id_evenement'] = $merged['id_evenement'] ?? null;
            }

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
                'evenement:id_evenement,nom_evenement,date_debut_evenement,date_fin_evenement,heure_debut_evenement,heure_fin_evenement',
                'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            ])
        ], 200);
    }


    public function destroy($id) // Supprimer une postulation
    {
        if(Postulation::where('id_postulation', $id)->exists()){
            $postulation = Postulation::find($id);

            if ($postulation && $this->isLockedPostulation($postulation)) {
                return response()->json([
                    'message' => 'Cette inscription est verrouillée: une fois assigné à une mission, le bénévole ne peut pas se désinscrire.',
                ], 422);
            }

            $postulation->delete();
            return response()->json(['message'=>'Postulation supprimée'], 200);
        } else {
            return response()->json(['message'=>'Postulation inexistante'], 404);
        }
    }

    private function syncAffectationFromPostulation(Postulation $postulation): void
    {
        if (empty($postulation->id_mission)) {
            return;
        }

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

    private function hydrateMissionAndEvent(array $validated): array
    {
        $missionId = isset($validated['id_mission']) && $validated['id_mission'] !== null
            ? (int) $validated['id_mission']
            : null;
        $eventId = isset($validated['id_evenement']) && $validated['id_evenement'] !== null
            ? (int) $validated['id_evenement']
            : null;

        if ($missionId !== null) {
            $mission = Mission::select('id_mission', 'id_evenement')->find($missionId);
            if ($mission) {
                $validated['id_evenement'] = (int) $mission->id_evenement;

                if ($eventId !== null && $eventId !== (int) $mission->id_evenement) {
                    abort(response()->json([
                        'message' => 'La mission sélectionnée n\'appartient pas à l\'événement indiqué.',
                    ], 422));
                }
            }
        }

        return $validated;
    }

    private function getTargetWindow(array $payload): ?array
    {
        if (!empty($payload['id_mission'])) {
            return $this->getMissionWindow((int) $payload['id_mission']);
        }

        if (!empty($payload['id_evenement'])) {
            return $this->getEventWindow((int) $payload['id_evenement']);
        }

        return null;
    }

    private function getMissionWindow(int $missionId): ?array
    {
        $mission = Mission::select('id_mission', 'date_mission', 'heure_debut_mission', 'heure_fin_mission')->find($missionId);
        if (!$mission || !$mission->date_mission) {
            return null;
        }

        $date = date('Y-m-d', strtotime((string) $mission->date_mission));
        $startTime = $mission->heure_debut_mission ?: '00:00:00';
        $endTime = $mission->heure_fin_mission ?: $startTime;

        $start = strtotime($date . ' ' . $startTime);
        $end = strtotime($date . ' ' . $endTime);

        if ($start === false || $end === false) {
            return null;
        }

        if ($end < $start) {
            $end = $start;
        }

        return ['start' => $start, 'end' => $end];
    }

    private function getEventWindow(int $eventId): ?array
    {
        $event = Evenement::select(
            'id_evenement',
            'date_debut_evenement',
            'date_fin_evenement',
            'heure_debut_evenement',
            'heure_fin_evenement'
        )->find($eventId);

        if (!$event || !$event->date_debut_evenement || !$event->date_fin_evenement) {
            return null;
        }

        $startDate = date('Y-m-d', strtotime((string) $event->date_debut_evenement));
        $endDate = date('Y-m-d', strtotime((string) $event->date_fin_evenement));
        $startTime = $event->heure_debut_evenement ?: '00:00:00';
        $endTime = $event->heure_fin_evenement ?: '23:59:59';

        $start = strtotime($startDate . ' ' . $startTime);
        $end = strtotime($endDate . ' ' . $endTime);

        if ($start === false || $end === false) {
            return null;
        }

        if ($end < $start) {
            $end = $start;
        }

        return ['start' => $start, 'end' => $end];
    }

    private function findBlockingWaitingList(int $userId, int $targetStart, int $targetEnd, ?int $exceptEventId = null): ?Postulation
    {
        $waitingLists = Postulation::with([
            'evenement:id_evenement,date_debut_evenement,date_fin_evenement,heure_debut_evenement,heure_fin_evenement',
        ])
            ->where('id_utilisateur', $userId)
            ->whereNull('id_mission')
            ->whereNotNull('id_evenement')
            ->where('statut_postulation', 'en_attente')
            ->get();

        foreach ($waitingLists as $waiting) {
            if ($exceptEventId !== null && (int) $waiting->id_evenement === $exceptEventId) {
                continue;
            }

            if (!$waiting->evenement) {
                continue;
            }

            $window = $this->getEventWindow((int) $waiting->id_evenement);
            if ($window === null) {
                continue;
            }

            if ($this->overlap($targetStart, $targetEnd, $window['start'], $window['end'])) {
                return $waiting;
            }
        }

        return null;
    }

    private function hasLockedAssignmentConflict(int $userId, int $targetStart, int $targetEnd): bool
    {
        $assignments = Affectation::with(['mission:id_mission,date_mission,heure_debut_mission,heure_fin_mission'])
            ->where('id_utilisateur', $userId)
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->get();

        foreach ($assignments as $assignment) {
            if (!$assignment->mission) {
                continue;
            }

            $window = $this->getMissionWindow((int) $assignment->id_mission);
            if ($window === null) {
                continue;
            }

            if ($this->overlap($targetStart, $targetEnd, $window['start'], $window['end'])) {
                return true;
            }
        }

        return false;
    }

    private function isLockedPostulation(Postulation $postulation): bool
    {
        if ($postulation->statut_postulation === 'accepte') {
            return true;
        }

        if (empty($postulation->id_mission)) {
            return false;
        }

        return Affectation::where('id_mission', $postulation->id_mission)
            ->where('id_utilisateur', $postulation->id_utilisateur)
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->exists();
    }

    private function overlap(int $startA, int $endA, int $startB, int $endB): bool
    {
        return $startA <= $endB && $startB <= $endA;
    }

}
