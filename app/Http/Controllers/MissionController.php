<?php
namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Evenement;
use App\Models\Mission;
use App\Support\GoogleMapsUrl;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Mission::with([
            'evenement:id_evenement,nom_evenement,date_debut_evenement,date_fin_evenement,nombre_benevoles_requis',
            'responsable:id_utilisateur,nom_utilisateur,prenom_utilisateur,email,telephone_utilisateur',
            'competences:id_competence,nom_competence',
        ])->withCount([
            'affectations as current_volunteers_count' => function ($query) {
                $query->whereIn('statut_affectation', ['assigne', 'confirme', 'present']);
            },
        ]);

        if ($request->filled('id_evenement')) {
            $query->where('id_evenement', (int) $request->id_evenement);
        }

        if ($request->filled('statut_mission')) {
            $query->where('statut_mission', $request->statut_mission);
        }

        if ($request->filled('visibilite_mission')) {
            $query->where('visibilite_mission', $request->visibilite_mission);
        }

        return response()->json($query->orderBy('date_mission')->get());
    }

    public function show($id)
    {
        $mission = Mission::with(['evenement', 'responsable', 'medias', 'competences'])
            ->withCount([
                'affectations as current_volunteers_count' => function ($query) {
                    $query->whereIn('statut_affectation', ['assigne', 'confirme', 'present']);
                },
            ])
            ->find($id);

        if (!$mission) {
            return response()->json(['message' => 'Mission inexistante'], 404);
        }

        return response()->json($mission);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_evenement' => 'required|integer|exists:evenements,id_evenement',
            'responsable_utilisateur_id' => 'required|integer|exists:users,id_utilisateur',
            'titre_mission' => 'required|string|max:150',
            'type_mission' => 'required|in:secours,logistique,accueil,technique,animation,autre',
            'description_mission' => 'required|string',
            'date_mission' => 'required|date',
            'heure_debut_mission' => 'required|date_format:H:i',
            'heure_fin_mission' => 'required|date_format:H:i|after:heure_debut_mission',
            'lieu_mission' => 'required|string|max:255',
            'google_maps_url_mission' => 'required|string|max:1000',
            'nombre_benevoles_max' => 'required|integer|min:1',
            'nombre_benevoles_backup' => 'nullable|integer|min:0',
            'statut_mission' => 'nullable|in:À venir,En cours,Terminée,Annulée',
            'inscription_requise' => 'nullable|boolean',
            'visibilite_mission' => 'nullable|in:publique,privée,limitée',
            'consignes_securite' => 'nullable|string',
            'image_mission' => 'nullable|string|max:500',
            'competence_ids' => 'nullable|array',
            'competence_ids.*' => 'integer|exists:competences,id_competence',
        ]);

        $validator->after(function ($validator) use ($request) {
            $this->validateMissionDateInsideEvent($validator, (int) $request->id_evenement, $request->date_mission);
            $this->validateMissionCapacityAgainstEvent($validator, (int) $request->id_evenement, (int) $request->nombre_benevoles_max);
            $this->validateMissionInsideEventPerimeter(
                $validator,
                (int) $request->id_evenement,
                $request->google_maps_url_mission
            );
        });

        $validated = $validator->validate();
        $validated = $this->hydrateMissionLocation($validated);

        $mission = Mission::create($validated);
        $mission->competences()->sync($request->input('competence_ids', []));
        $this->syncResponsibleAffectation($mission, (int) $validated['responsable_utilisateur_id']);

        return response()->json([
            'message' => 'Mission ajoutée',
            'mission' => $mission->load('competences:id_competence,nom_competence'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $mission = Mission::find($id);

        if (!$mission) {
            return response()->json(['message' => 'Mission inexistante'], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_evenement' => 'sometimes|integer|exists:evenements,id_evenement',
            'responsable_utilisateur_id' => 'sometimes|integer|exists:users,id_utilisateur',
            'titre_mission' => 'sometimes|string|max:150',
            'type_mission' => 'sometimes|in:secours,logistique,accueil,technique,animation,autre',
            'description_mission' => 'sometimes|string',
            'date_mission' => 'sometimes|date',
            'heure_debut_mission' => 'sometimes|date_format:H:i',
            'heure_fin_mission' => 'sometimes|date_format:H:i',
            'lieu_mission' => 'sometimes|string|max:255',
            'google_maps_url_mission' => 'sometimes|string|max:1000',
            'nombre_benevoles_max' => 'sometimes|integer|min:1',
            'nombre_benevoles_backup' => 'nullable|integer|min:0',
            'statut_mission' => 'nullable|in:À venir,En cours,Terminée,Annulée',
            'inscription_requise' => 'nullable|boolean',
            'visibilite_mission' => 'nullable|in:publique,privée,limitée',
            'consignes_securite' => 'nullable|string',
            'image_mission' => 'nullable|string|max:500',
            'competence_ids' => 'nullable|array',
            'competence_ids.*' => 'integer|exists:competences,id_competence',
        ]);

        $validator->after(function ($validator) use ($request, $mission) {
            $eventId = (int) ($request->id_evenement ?? $mission->id_evenement);
            $dateMission = $request->date_mission ?? $mission->date_mission;
            $newCapacity = (int) ($request->nombre_benevoles_max ?? $mission->nombre_benevoles_max);

            $this->validateMissionDateInsideEvent($validator, $eventId, $dateMission);
            $this->validateMissionCapacityAgainstEvent($validator, $eventId, $newCapacity, (int) $mission->id_mission);
            $this->validateMissionInsideEventPerimeter(
                $validator,
                $eventId,
                $request->google_maps_url_mission ?? $mission->google_maps_url_mission
            );
        });

        $validated = $validator->validate();
        $validated = $this->hydrateMissionLocation($validated, $mission);

        $mission->update($validated);
        $this->syncResponsibleAffectation($mission, (int) $mission->responsable_utilisateur_id);

        if ($request->has('competence_ids')) {
            $mission->competences()->sync($request->input('competence_ids', []));
        }

        return response()->json([
            'message' => 'Mission mise à jour',
            'mission' => $mission->load('competences:id_competence,nom_competence'),
        ], 200);
    }

    public function destroy($id)
    {
        $mission = Mission::find($id);

        if (!$mission) {
            return response()->json(['message' => 'Mission inexistante'], 404);
        }

        if ($this->hasMissionStarted($mission) || in_array($mission->statut_mission, ['En cours', 'Terminée'], true)) {
            return response()->json([
                'message' => 'Suppression interdite pour la traçabilité: cette mission est en cours ou passée. Utilisez l\'annulation à la place.',
            ], 409);
        }

        $mission->delete();

        return response()->json(['message' => 'Mission supprimée'], 200);
    }

    public function assignResponsable(Request $request, $id)
    {
        $mission = Mission::find($id);

        if (!$mission) {
            return response()->json(['message' => 'Mission inexistante'], 404);
        }

        $validated = $request->validate([
            'responsable_utilisateur_id' => 'required|integer|exists:users,id_utilisateur',
        ]);

        DB::transaction(function () use ($mission, $validated): void {
            $mission->update([
                'responsable_utilisateur_id' => (int) $validated['responsable_utilisateur_id'],
            ]);

            $this->syncResponsibleAffectation($mission, (int) $validated['responsable_utilisateur_id']);
        });

        return response()->json([
            'message' => 'Responsable de mission mis à jour',
            'mission' => $mission->load([
                'responsable:id_utilisateur,nom_utilisateur,prenom_utilisateur,email,telephone_utilisateur',
            ]),
        ]);
    }

    private function validateMissionDateInsideEvent($validator, int $eventId, $dateMission): void
    {
        if (empty($eventId) || empty($dateMission)) {
            return;
        }

        $event = Evenement::find($eventId);
        if (!$event) {
            return;
        }

        $missionDate = date('Y-m-d', strtotime((string) $dateMission));
        $eventStart = date('Y-m-d', strtotime((string) $event->date_debut_evenement));
        $eventEnd = date('Y-m-d', strtotime((string) $event->date_fin_evenement));

        if ($missionDate < $eventStart || $missionDate > $eventEnd) {
            $validator->errors()->add(
                'date_mission',
                "La date de mission doit être comprise entre {$eventStart} et {$eventEnd}."
            );
        }
    }

    private function validateMissionCapacityAgainstEvent($validator, int $eventId, int $newCapacity, ?int $missionIdToExclude = null): void
    {
        if (empty($eventId) || $newCapacity < 0) {
            return;
        }

        $event = Evenement::find($eventId);
        if (!$event) {
            return;
        }

        $usedCapacity = Mission::where('id_evenement', $eventId)
            ->when($missionIdToExclude !== null, function ($query) use ($missionIdToExclude) {
                $query->where('id_mission', '!=', $missionIdToExclude);
            })
            ->sum('nombre_benevoles_max');

        $remaining = (int) $event->nombre_benevoles_requis - (int) $usedCapacity;

        if ($newCapacity > $remaining) {
            $validator->errors()->add(
                'nombre_benevoles_max',
                "Le quota de l'événement est dépassé. Places restantes: {$remaining}."
            );
        }
    }

    private function validateMissionInsideEventPerimeter($validator, int $eventId, ?string $missionMapsUrl): void
    {
        if (empty($eventId) || empty($missionMapsUrl)) {
            return;
        }

        $event = Evenement::find($eventId);
        if (!$event) {
            return;
        }

        $missionCoordinates = GoogleMapsUrl::extractCoordinates($missionMapsUrl);
        if ($missionCoordinates === null) {
            $validator->errors()->add(
                'google_maps_url_mission',
                'Le lien Google Maps de la mission doit contenir une position exploitable.'
            );

            return;
        }

        if (
            $event->latitude_evenement === null
            || $event->longitude_evenement === null
            || empty($event->rayon_localisation_evenement)
        ) {
            $validator->errors()->add(
                'id_evenement',
                'L\'événement sélectionné ne possède pas de périmètre Google Maps valide.'
            );

            return;
        }

        $distance = GoogleMapsUrl::distanceInMeters(
            (float) $event->latitude_evenement,
            (float) $event->longitude_evenement,
            $missionCoordinates['latitude'],
            $missionCoordinates['longitude']
        );

        if ($distance > (int) $event->rayon_localisation_evenement) {
            $validator->errors()->add(
                'google_maps_url_mission',
                'La mission doit se trouver à l\'intérieur du périmètre défini pour l\'événement.'
            );
        }
    }

    private function hasMissionStarted(Mission $mission): bool
    {
        if (empty($mission->date_mission)) {
            return false;
        }

        $missionDate = Carbon::parse($mission->date_mission)->format('Y-m-d');
        $startTime = $mission->heure_debut_mission ?: '00:00:00';
        $missionStart = Carbon::parse("{$missionDate} {$startTime}");

        return now()->greaterThanOrEqualTo($missionStart);
    }

    private function hydrateMissionLocation(array $validated, ?Mission $mission = null): array
    {
        $mapsUrl = $validated['google_maps_url_mission'] ?? $mission?->google_maps_url_mission;
        $coordinates = GoogleMapsUrl::extractCoordinates($mapsUrl);

        if ($coordinates === null) {
            abort(response()->json([
                'message' => 'Le lien Google Maps de la mission doit contenir une position exploitable.',
                'errors' => [
                    'google_maps_url_mission' => [
                        'Le lien Google Maps de la mission doit contenir une position exploitable.',
                    ],
                ],
            ], 422));
        }

        $validated['google_maps_url_mission'] = $mapsUrl;
        $validated['latitude_mission'] = $coordinates['latitude'];
        $validated['longitude_mission'] = $coordinates['longitude'];

        return $validated;
    }

    private function syncResponsibleAffectation(Mission $mission, int $responsableUserId): void
    {
        if ($responsableUserId <= 0) {
            return;
        }

        Affectation::where('id_mission', $mission->id_mission)
            ->where('est_responsable', true)
            ->where('id_utilisateur', '!=', $responsableUserId)
            ->update(['est_responsable' => false]);

        Affectation::updateOrCreate(
            [
                'id_mission' => $mission->id_mission,
                'id_utilisateur' => $responsableUserId,
            ],
            [
                'statut_affectation' => 'assigne',
                'est_responsable' => true,
                'date_affectation' => now(),
            ]
        );
    }
}
