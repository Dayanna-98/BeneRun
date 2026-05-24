<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\NotificationRead;
use App\Models\Postulation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Sanctum\PersonalAccessToken;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if (! $actor) {
            return response()->json(['message' => 'Non authentifie'], 401);
        }

        $userId = (int) $actor->id_utilisateur;
        $role = $this->normalizeRole((string) $actor->role_utilisateur);

        $notifications = collect();

        if ($this->isManagerRole($role)) {
            $notifications = $notifications->merge($this->pendingPostulationNotifications());
        }

        $notifications = $notifications
            ->merge($this->postulationStatusNotifications($userId))
            ->merge($this->affectationNotifications($userId));

        $items = $notifications
            ->sortByDesc('created_at')
            ->values()
            ->take(20)
            ->values();

        $readKeys = $items->pluck('id')
            ->filter(fn ($id) => is_string($id) && $id !== '')
            ->values();

        $readsByKey = NotificationRead::query()
            ->where('id_utilisateur', $userId)
            ->whereIn('notification_key', $readKeys)
            ->get(['notification_key', 'read_at'])
            ->keyBy('notification_key');

        $enriched = $items->map(function (array $item) use ($readsByKey): array {
            $read = $readsByKey->get((string) ($item['id'] ?? ''));

            return [
                ...$item,
                'is_read' => $read !== null,
                'read_at' => $read?->read_at?->toIso8601String(),
            ];
        })->values();

        $unreadCount = $enriched->filter(fn (array $item) => ! ((bool) ($item['is_read'] ?? false)))->count();

        return response()->json([
            'data' => $enriched,
            'count' => $enriched->count(),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead(Request $request)
    {
        $actor = $this->resolveActorFromBearerToken($request);
        if (! $actor) {
            return response()->json(['message' => 'Non authentifie'], 401);
        }

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string|max:255',
        ]);

        $keys = collect($validated['ids'])
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn ($id) => $id !== '')
            ->unique()
            ->values();

        if ($keys->isEmpty()) {
            return response()->json([
                'message' => 'Aucune notification fournie.',
            ], 422);
        }

        $now = now();

        NotificationRead::query()->upsert(
            $keys->map(fn ($key) => [
                'id_utilisateur' => (int) $actor->id_utilisateur,
                'notification_key' => $key,
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['id_utilisateur', 'notification_key'],
            ['read_at', 'updated_at']
        );

        return response()->json([
            'message' => 'Notifications marquees comme lues.',
            'count' => $keys->count(),
        ]);
    }

    private function resolveActorFromBearerToken(Request $request): ?User
    {
        $actor = $request->user('sanctum');
        if ($actor instanceof User) {
            return $actor;
        }

        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);
        $tokenable = $accessToken?->tokenable;

        return $tokenable instanceof User ? $tokenable : null;
    }

    private function normalizeRole(string $role): string
    {
        $normalized = strtolower(trim($role));
        $normalized = str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c'],
            $normalized
        );

        return str_replace(['-', '_', ' '], '', $normalized);
    }

    private function isManagerRole(string $normalizedRole): bool
    {
        return in_array($normalizedRole, [
            'organizer',
            'organisateur',
            'missionmanager',
            'manager',
            'responsable',
            'admin',
            'superadmin',
        ], true);
    }

    private function pendingPostulationNotifications(): Collection
    {
        return Postulation::with([
            'mission:id_mission,titre_mission',
            'evenement:id_evenement,nom_evenement',
            'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur',
        ])
            ->where('statut_postulation', 'en_attente')
            ->orderByDesc('date_postulation')
            ->limit(10)
            ->get()
            ->map(function (Postulation $postulation) {
                $userName = trim(($postulation->utilisateur?->prenom_utilisateur ?? '').' '.($postulation->utilisateur?->nom_utilisateur ?? ''));
                $target = $postulation->mission?->titre_mission ?: $postulation->evenement?->nom_evenement ?: 'une mission';

                return [
                    'id' => 'pending-postulation-'.$postulation->id_postulation,
                    'type' => 'pending_postulation',
                    'level' => 'info',
                    'title' => 'Nouvelle demande d’inscription',
                    'body' => trim(($userName ?: 'Un bénévole').' a postulé pour '.$target.'.'),
                    'href' => $postulation->id_mission ? '/manage-missions' : '/manage-events',
                    'created_at' => optional($postulation->date_postulation ?? $postulation->created_at)?->toIso8601String(),
                ];
            });
    }

    private function postulationStatusNotifications(int $userId): Collection
    {
        return Postulation::with([
            'mission:id_mission,titre_mission',
            'evenement:id_evenement,nom_evenement',
        ])
            ->where('id_utilisateur', $userId)
            ->whereIn('statut_postulation', ['accepte', 'refuse', 'annule'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(function (Postulation $postulation) {
                $target = $postulation->mission?->titre_mission ?: $postulation->evenement?->nom_evenement ?: 'votre demande';
                $statusLabel = match ($postulation->statut_postulation) {
                    'accepte' => 'acceptée',
                    'refuse' => 'refusée',
                    'annule' => 'annulée',
                    default => 'mise à jour',
                };

                return [
                    'id' => 'postulation-status-'.$postulation->id_postulation,
                    'type' => 'postulation_status',
                    'level' => $postulation->statut_postulation === 'accepte' ? 'success' : 'warning',
                    'title' => 'Inscription mise à jour',
                    'body' => "Votre inscription pour {$target} a été {$statusLabel}.",
                    'href' => $postulation->id_mission ? '/my-missions' : '/events',
                    'created_at' => optional($postulation->date_decision ?? $postulation->date_annulation ?? $postulation->updated_at)?->toIso8601String(),
                ];
            });
    }

    private function affectationNotifications(int $userId): Collection
    {
        return Affectation::with([
            'mission:id_mission,titre_mission,date_mission',
        ])
            ->where('id_utilisateur', $userId)
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(function (Affectation $affectation) {
                $statusLabel = match ($affectation->statut_affectation) {
                    'assigne' => 'Vous avez été affecté à',
                    'confirme' => 'Votre présence est confirmée pour',
                    'present' => 'Votre présence a été enregistrée pour',
                    default => 'Mise à jour pour',
                };

                return [
                    'id' => 'affectation-'.$affectation->id_affectation,
                    'type' => 'affectation',
                    'level' => 'success',
                    'title' => 'Mission mise à jour',
                    'body' => trim($statusLabel.' '.($affectation->mission?->titre_mission ?: 'votre mission').'.'),
                    'href' => $affectation->mission ? '/mission/'.$affectation->mission->id_mission : '/my-missions',
                    'created_at' => optional($affectation->date_presence ?? $affectation->date_confirmation ?? $affectation->date_affectation ?? $affectation->updated_at)?->toIso8601String(),
                ];
            });
    }
}
