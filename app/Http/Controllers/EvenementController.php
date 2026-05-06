<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Support\GoogleMapsUrl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EvenementController extends Controller
{
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
            $today = now()->startOfDay()->toDateString();

            if ($request->query('timeline') === 'upcoming') {
                $query->whereDate('date_fin_evenement', '>=', $today);
            }

            if ($request->query('timeline') === 'past') {
                $query->whereDate('date_fin_evenement', '<', $today);
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
            'google_maps_url_evenement' => 'required|string|max:1000',
            'rayon_localisation_evenement' => 'required|integer|min:1|max:100000',
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
            'google_maps_url_evenement' => 'sometimes|string|max:1000',
            'rayon_localisation_evenement' => 'sometimes|integer|min:1|max:100000',
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

        if (!$event) {
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

        if (!$startDate) {
            return false;
        }

        $startTime = $event->heure_debut_evenement ?: '00:00:00';
        $eventStart = Carbon::parse("{$startDate} {$startTime}");

        return now()->greaterThanOrEqualTo($eventStart);
    }

    private function hydrateEventLocation(array $validated, ?Evenement $event = null): array
    {
        $mapsUrl = $validated['google_maps_url_evenement'] ?? $event?->google_maps_url_evenement;
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
        $validated['latitude_evenement'] = $coordinates['latitude'];
        $validated['longitude_evenement'] = $coordinates['longitude'];

        return $validated;
    }

    private function hydrateEventImage(Request $request, array $validated, ?Evenement $event = null): array
    {
        if ($request->hasFile('image_file')) {
            $validated['image_evenement'] = $this->storeUploadedImage($request->file('image_file'), 'events');
        } elseif (!array_key_exists('image_evenement', $validated) && $event?->image_evenement) {
            $validated['image_evenement'] = $event->image_evenement;
        }

        return $validated;
    }

    private function storeUploadedImage($file, string $directory): string
    {
        $targetDirectory = public_path("uploads/{$directory}");

        if (!File::exists($targetDirectory)) {
            File::makeDirectory($targetDirectory, 0755, true);
        }

        $filename = uniqid("{$directory}_", true) . '.' . $file->getClientOriginalExtension();
        $file->move($targetDirectory, $filename);

        return url("uploads/{$directory}/{$filename}");
    }
}