<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Evenement;
use App\Models\Mission;
use App\Models\NotificationRead;
use App\Models\Postulation;
use App\Models\User;
use Carbon\Carbon;
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
            ->merge($this->affectationNotifications($userId));

        $items = $notifications
            ->sortByDesc('created_at')
            ->values()
            ->take(30)
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
            'mission:id_mission,titre_mission,date_mission,heure_debut_mission,heure_fin_mission',
            'evenement:id_evenement,nom_evenement,date_debut_evenement,date_fin_evenement,heure_debut_evenement,heure_fin_evenement',
            'utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur',
        ])
            ->where('statut_postulation', 'en_attente')
            ->orderByDesc('date_postulation')
            ->limit(10)
            ->get()
            ->map(function (Postulation $postulation) {
                $userName = trim(($postulation->utilisateur?->prenom_utilisateur ?? '').' '.($postulation->utilisateur?->nom_utilisateur ?? ''));
                $target = $postulation->mission?->titre_mission ?: $postulation->evenement?->nom_evenement ?: 'une mission';
                $targetType = $postulation->id_mission ? 'mission' : 'événement';
                $slot = $this->formatTargetSlot($postulation->mission, $postulation->evenement);

                return [
                    'id' => 'pending-postulation-'.$postulation->id_postulation,
                    'type' => 'pending_postulation',
                    'level' => 'info',
                    'title' => 'Demande en attente à traiter',
                    'body' => trim(($userName ?: 'Un bénévole').' attend une réponse pour le '.$targetType.' "'.$target.'"'.$slot.'.'),
                    'action_label' => 'Traiter la demande',
                    'href' => $postulation->id_mission ? '/manage-missions' : '/manage-events',
                    'created_at' => optional($postulation->date_postulation ?? $postulation->created_at)?->toIso8601String(),
                ];
            });
    }

    private function postulationStatusNotifications(int $userId): Collection
    {
        return Postulation::with([
            'mission:id_mission,titre_mission,date_mission,heure_debut_mission,heure_fin_mission',
            'evenement:id_evenement,nom_evenement,date_debut_evenement,date_fin_evenement,heure_debut_evenement,heure_fin_evenement',
        ])
            ->where('id_utilisateur', $userId)
            ->whereIn('statut_postulation', ['accepte', 'refuse', 'annule'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(function (Postulation $postulation) {
                $target = $postulation->mission?->titre_mission ?: $postulation->evenement?->nom_evenement ?: 'votre demande';
                $targetType = $postulation->id_mission ? 'mission' : 'événement';
                $slot = $this->formatTargetSlot($postulation->mission, $postulation->evenement);

                $title = match ($postulation->statut_postulation) {
                    'accepte' => 'Inscription acceptée',
                    'refuse' => 'Inscription refusée',
                    'annule' => 'Inscription annulée',
                    default => 'Inscription mise à jour',
                };

                $body = match ($postulation->statut_postulation) {
                    'accepte' => "Bonne nouvelle: votre inscription pour le {$targetType} \"{$target}\" est acceptée{$slot}.",
                    'refuse' => "Votre inscription pour le {$targetType} \"{$target}\" a été refusée{$slot}.",
                    'annule' => "Votre inscription pour le {$targetType} \"{$target}\" a été annulée{$slot}.",
                    default => "Votre inscription pour le {$targetType} \"{$target}\" a été mise à jour{$slot}.",
                };

                $actionLabel = match ($postulation->statut_postulation) {
                    'accepte' => 'Voir mes missions',
                    'refuse' => 'Voir d\'autres événements',
                    'annule' => 'Voir les disponibilités',
                    default => 'Voir le détail',
                };

                return [
                    'id' => 'postulation-status-'.$postulation->id_postulation,
                    'type' => 'postulation_status',
                    'level' => match ($postulation->statut_postulation) {
                        'accepte' => 'success',
                        'refuse' => 'danger',
                        default => 'warning',
                    },
                    'title' => $title,
                    'body' => $body,
                    'action_label' => $actionLabel,
                    'href' => $postulation->id_mission ? '/my-missions' : '/events',
                    'created_at' => optional($postulation->date_decision ?? $postulation->date_annulation ?? $postulation->updated_at)?->toIso8601String(),
                ];
            });
    }

    private function affectationNotifications(int $userId): Collection
    {
        return Affectation::with([
            'mission:id_mission,titre_mission,date_mission,heure_debut_mission,heure_fin_mission',
        ])
            ->where('id_utilisateur', $userId)
            ->whereIn('statut_affectation', ['confirme', 'present'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(function (Affectation $affectation) {
                $missionName = $affectation->mission?->titre_mission ?: 'votre mission';
                $slot = $this->formatTargetSlot($affectation->mission, null);

                $title = match ($affectation->statut_affectation) {
                    'confirme' => 'Présence confirmée',
                    'present' => 'Présence enregistrée',
                    default => 'Mission mise à jour',
                };

                $body = match ($affectation->statut_affectation) {
                    'confirme' => 'Votre présence est confirmée pour "'.$missionName.'"'.$slot.'.',
                    'present' => 'Votre présence a été enregistrée pour "'.$missionName.'"'.$slot.'.',
                    default => 'Mise à jour reçue pour "'.$missionName.'"'.$slot.'.',
                };

                return [
                    'id' => 'affectation-'.$affectation->id_affectation,
                    'type' => 'affectation',
                    'level' => 'success',
                    'title' => $title,
                    'body' => $body,
                    'action_label' => 'Voir la mission',
                    'href' => $affectation->mission ? '/mission/'.$affectation->mission->id_mission : '/my-missions',
                    'created_at' => optional($affectation->date_presence ?? $affectation->date_confirmation ?? $affectation->date_affectation ?? $affectation->updated_at)?->toIso8601String(),
                ];
            });
    }

    private function formatTargetSlot(?Mission $mission, ?Evenement $event): string
    {
        if ($mission && $mission->date_mission) {
            $dateLabel = Carbon::parse((string) $mission->date_mission)->format('d/m/Y');
            $start = $this->toHourMinute((string) ($mission->heure_debut_mission ?? ''));
            $end = $this->toHourMinute((string) ($mission->heure_fin_mission ?? ''));

            $timeRange = trim($start.($end ? ' - '.$end : ''));

            return $timeRange !== ''
                ? " (le {$dateLabel}, {$timeRange})"
                : " (le {$dateLabel})";
        }

        if ($event && $event->date_debut_evenement) {
            $startDateLabel = Carbon::parse((string) $event->date_debut_evenement)->format('d/m/Y');
            $endDateLabel = $event->date_fin_evenement
                ? Carbon::parse((string) $event->date_fin_evenement)->format('d/m/Y')
                : $startDateLabel;

            if ($startDateLabel === $endDateLabel) {
                return " (le {$startDateLabel})";
            }

            return " (du {$startDateLabel} au {$endDateLabel})";
        }

        return '';
    }

    private function toHourMinute(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        return substr($trimmed, 0, 5);
    }
}
