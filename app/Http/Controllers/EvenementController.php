<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Support\GoogleMapsUrl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EvenementController extends Controller
{
    private function endDateTimeExpression(string $driver): string
    {
        if ($driver === 'sqlite') {
            return "datetime(substr(date_fin_evenement, 1, 10) || ' ' || COALESCE(heure_fin_evenement, '23:59:59'))";
        }

        return "TIMESTAMP(date_fin_evenement, COALESCE(heure_fin_evenement, '23:59:59'))";
    }

    public function index(Request $request)
    {
        $query = Evenement::withCount('missions')
            ->orderBy('date_debut_evenement');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));

            $query->where(function ($builder) use ($search) {
                $builder->where('nom_evenement', 'like', "%{$search}%")
                    ->orWhere('description_evenement', 'like', "%{$search}%")
                    ->orWhere('lieu_evenement', 'like', "%{$search}%")
                    ->orWhere('organisateur_evenement', 'like', "%{$search}%");
            });
        }

        if ($request->filled('timeline')) {
            $now = now()->format('Y-m-d H:i:s');
            $driver = $query->getModel()->getConnection()->getDriverName();
            $endDateTimeSql = $this->endDateTimeExpression($driver);

            if ($request->query('timeline') === 'upcoming') {
                $query->whereRaw(
                    "{$endDateTimeSql} >= ?",
                    [$now]
                );
            }

            if ($request->query('timeline') === 'past') {
                $query->whereRaw(
                    "{$endDateTimeSql} < ?",
                    [$now]
                );
            }
        }

        $perPage = min(max((int) $request->integer('per_page', 0), 0), 50);

        if ($perPage > 0) {
            return response()->json($query->paginate($perPage));
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        $event = Evenement::withCount('missions')->find($id);

        if (! $event) {
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
            'mode_localisation_evenement' => 'required|in:manual,missions',
            'google_maps_url_evenement' => 'nullable|string|max:1000',
            'rayon_localisation_evenement' => 'nullable|integer|min:1|max:100000',
            'organisateur_evenement' => 'required|string|max:255',
            'image_evenement' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:5120',
            'nombre_benevoles_requis' => 'required|integer|min:1',
            'est_annule_evenement' => 'nullable|boolean',
            'date_annulation_evenement' => 'nullable|date',
            'raison_annulation_evenement' => 'nullable|string|max:255',
            'est_publie_evenement' => 'nullable|boolean',
            'cree_par_utilisateur_id' => 'required|integer|exists:users,id_utilisateur',
        ]);

        $duplicateResponse = $this->ensureUniqueEventNameOnStartDate(
            (string) $validated['nom_evenement'],
            (string) $validated['date_debut_evenement']
        );
        if ($duplicateResponse !== null) {
            return $duplicateResponse;
        }

        if (
            ! empty($validated['date_debut_evenement'])
            && ! empty($validated['date_fin_evenement'])
            && $validated['date_debut_evenement'] === $validated['date_fin_evenement']
            && ! empty($validated['heure_debut_evenement'])
            && ! empty($validated['heure_fin_evenement'])
            && $validated['heure_fin_evenement'] <= $validated['heure_debut_evenement']
        ) {
            return response()->json([
                'message' => 'Sur une même journée, l\'heure de fin doit être après l\'heure de début.',
                'errors' => [
                    'heure_fin_evenement' => ['Sur une même journée, l\'heure de fin doit être après l\'heure de début.'],
                ],
            ], 422);
        }

        $validated = $this->hydrateEventLocation($validated);
        $validated = $this->hydrateEventImage($request, $validated);

        $event = Evenement::create($validated);

        return response()->json([
            'message' => 'Événement ajouté',
            'event' => $event,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $event = Evenement::find($id);

        if (! $event) {
            return response()->json(['message' => 'Événement inexistant'], 404);
        }

        if ($this->hasEventStarted($event)) {
            return response()->json([
                'message' => 'Modification interdite pour la traçabilité: cet événement est en cours ou passé.',
            ], 409);
        }

        $validated = $request->validate([
            'nom_evenement' => 'sometimes|string|max:255',
            'description_evenement' => 'sometimes|string',
            'date_debut_evenement' => 'sometimes|date',
            'date_fin_evenement' => 'sometimes|date|after_or_equal:date_debut_evenement',
            'heure_debut_evenement' => 'nullable|date_format:H:i',
            'heure_fin_evenement' => 'nullable|date_format:H:i',
            'lieu_evenement' => 'sometimes|string|max:255',
            'mode_localisation_evenement' => 'sometimes|in:manual,missions',
            'google_maps_url_evenement' => 'nullable|string|max:1000',
            'rayon_localisation_evenement' => 'nullable|integer|min:1|max:100000',
            'organisateur_evenement' => 'sometimes|string|max:255',
            'image_evenement' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:5120',
            'nombre_benevoles_requis' => 'sometimes|integer|min:1',
            'est_annule_evenement' => 'nullable|boolean',
            'date_annulation_evenement' => 'nullable|date',
            'raison_annulation_evenement' => 'nullable|string|max:255',
            'est_publie_evenement' => 'nullable|boolean',
            'cree_par_utilisateur_id' => 'sometimes|integer|exists:users,id_utilisateur',
        ]);

        $eventName = (string) ($validated['nom_evenement'] ?? $event->nom_evenement);
        $eventStartDate = (string) ($validated['date_debut_evenement'] ?? $event->date_debut_evenement);
        $duplicateResponse = $this->ensureUniqueEventNameOnStartDate($eventName, $eventStartDate, (int) $event->id_evenement);
        if ($duplicateResponse !== null) {
            return $duplicateResponse;
        }

        $startDate = $validated['date_debut_evenement'] ?? $event->date_debut_evenement;
        $endDate = $validated['date_fin_evenement'] ?? $event->date_fin_evenement;
        $startTime = $validated['heure_debut_evenement'] ?? $event->heure_debut_evenement;
        $endTime = $validated['heure_fin_evenement'] ?? $event->heure_fin_evenement;

        if (
            ! empty($startDate)
            && ! empty($endDate)
            && $startDate === $endDate
            && ! empty($startTime)
            && ! empty($endTime)
            && $endTime <= $startTime
        ) {
            return response()->json([
                'message' => 'Sur une même journée, l\'heure de fin doit être après l\'heure de début.',
                'errors' => [
                    'heure_fin_evenement' => ['Sur une même journée, l\'heure de fin doit être après l\'heure de début.'],
                ],
            ], 422);
        }

        $validated = $this->hydrateEventLocation($validated, $event);
        $validated = $this->hydrateEventImage($request, $validated, $event);

        $event->update($validated);

        return response()->json([
            'message' => 'Événement mis à jour',
            'event' => $event,
        ], 200);
    }

    public function destroy($id)
    {
        $event = Evenement::find($id);

        if (! $event) {
            return response()->json(['message' => 'Événement inexistant'], 404);
        }

        if ($this->hasEventStarted($event)) {
            return response()->json([
                'message' => 'Suppression interdite pour la traçabilité: cet événement est en cours ou passé. Utilisez l\'annulation à la place.',
            ], 409);
        }

        $event->delete();

        return response()->json(['message' => 'Événement supprimé'], 200);
    }

    private function hasEventStarted(Evenement $event): bool
    {
        $startDate = $event->date_debut_evenement
            ? Carbon::parse($event->date_debut_evenement)->format('Y-m-d')
            : null;

        if (! $startDate) {
            return false;
        }

        $startTime = $event->heure_debut_evenement ?: '00:00:00';
        $eventStart = Carbon::parse("{$startDate} {$startTime}");

        return now()->greaterThanOrEqualTo($eventStart);
    }

    private function ensureUniqueEventNameOnStartDate(string $eventName, string $startDate, ?int $excludeEventId = null)
    {
        $query = Evenement::query()
            ->where('nom_evenement', $eventName)
            ->whereDate('date_debut_evenement', $startDate)
            ->when($excludeEventId !== null, function ($builder) use ($excludeEventId) {
                $builder->where('id_evenement', '!=', $excludeEventId);
            });

        if ($query->exists()) {
            return response()->json([
                'message' => 'Un evenement avec ce nom existe deja a cette date.',
                'errors' => [
                    'nom_evenement' => ['Un evenement avec ce nom existe deja a cette date.'],
                ],
            ], 422);
        }

        return null;
    }

    private function hydrateEventLocation(array $validated, ?Evenement $event = null): array
    {
        $locationMode = $validated['mode_localisation_evenement']
            ?? $event?->mode_localisation_evenement
            ?? 'manual';

        if (! in_array($locationMode, ['manual', 'missions'], true)) {
            $locationMode = 'manual';
        }

        $mapsUrl = trim((string) ($validated['google_maps_url_evenement'] ?? $event?->google_maps_url_evenement ?? ''));

        if ($locationMode === 'manual') {
            $radius = $validated['rayon_localisation_evenement'] ?? $event?->rayon_localisation_evenement;
            if (empty($mapsUrl)) {
                abort(response()->json([
                    'message' => 'Le lien Google Maps du centre est obligatoire en mode manuel.',
                    'errors' => [
                        'google_maps_url_evenement' => ['Le lien Google Maps du centre est obligatoire en mode manuel.'],
                    ],
                ], 422));
            }

            if (empty($radius)) {
                abort(response()->json([
                    'message' => 'Le périmètre est obligatoire en mode manuel.',
                    'errors' => [
                        'rayon_localisation_evenement' => ['Le périmètre est obligatoire en mode manuel.'],
                    ],
                ], 422));
            }

            $coordinates = GoogleMapsUrl::extractCoordinates($mapsUrl);
            if ($coordinates === null) {
                abort(response()->json([
                    'message' => 'Le lien Google Maps de l\'événement doit contenir une position exploitable.',
                    'errors' => [
                        'google_maps_url_evenement' => [
                            'Le lien Google Maps de l\'événement doit contenir une position exploitable.',
                        ],
                    ],
                ], 422));
            }

            $validated['google_maps_url_evenement'] = $mapsUrl;
            $validated['rayon_localisation_evenement'] = (int) $radius;
            $validated['latitude_evenement'] = $coordinates['latitude'];
            $validated['longitude_evenement'] = $coordinates['longitude'];
            $validated['mode_localisation_evenement'] = 'manual';

            return $validated;
        }

        $coordinates = $mapsUrl !== '' ? GoogleMapsUrl::extractCoordinates($mapsUrl) : null;
        $validated['mode_localisation_evenement'] = 'missions';
        $validated['rayon_localisation_evenement'] = null;
        $validated['google_maps_url_evenement'] = $mapsUrl !== '' ? $mapsUrl : null;
        $validated['latitude_evenement'] = $coordinates['latitude'] ?? null;
        $validated['longitude_evenement'] = $coordinates['longitude'] ?? null;

        return $validated;
    }

    private function hydrateEventImage(Request $request, array $validated, ?Evenement $event = null): array
    {
        if ($request->hasFile('image_file')) {
            $validated['image_evenement'] = $this->storeUploadedImage($request->file('image_file'), 'events');
        } elseif (! array_key_exists('image_evenement', $validated) && $event?->image_evenement) {
            $validated['image_evenement'] = $event->image_evenement;
        }

        return $validated;
    }

    private function storeUploadedImage($file, string $directory): string
    {
        $targetDirectory = public_path("uploads/{$directory}");

        if (! File::exists($targetDirectory)) {
            File::makeDirectory($targetDirectory, 0755, true);
        }

        $filename = uniqid("{$directory}_", true).'.'.$file->getClientOriginalExtension();
        $file->move($targetDirectory, $filename);

        return url("uploads/{$directory}/{$filename}");
    }
}
