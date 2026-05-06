<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Postulation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) $request->query('user_id', 0);
        $role = strtolower((string) ($request->header('X-User-Role') ?: $request->query('role', 'volunteer')));

        $notifications = collect();

        if (in_array($role, ['organizer', 'mission_manager', 'admin', 'superadmin'], true)) {
            $notifications = $notifications->merge($this->pendingPostulationNotifications());
        }

        if ($userId > 0) {
            $notifications = $notifications
                ->merge($this->postulationStatusNotifications($userId))
                ->merge($this->affectationNotifications($userId));
        }

        $items = $notifications
            ->sortByDesc('created_at')
            ->values()
            ->take(20)
            ->all();

        return response()->json([
            'data' => $items,
            'count' => count($items),
        ]);
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
                $userName = trim(($postulation->utilisateur?->prenom_utilisateur ?? '') . ' ' . ($postulation->utilisateur?->nom_utilisateur ?? ''));
                $target = $postulation->mission?->titre_mission ?: $postulation->evenement?->nom_evenement ?: 'une mission';

                return [
                    'id' => 'pending-postulation-' . $postulation->id_postulation,
                    'type' => 'pending_postulation',
                    'level' => 'info',
                    'title' => 'Nouvelle demande d’inscription',
                    'body' => trim(($userName ?: 'Un bénévole') . ' a postulé pour ' . $target . '.'),
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
                    'id' => 'postulation-status-' . $postulation->id_postulation,
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
                    'id' => 'affectation-' . $affectation->id_affectation,
                    'type' => 'affectation',
                    'level' => 'success',
                    'title' => 'Mission mise à jour',
                    'body' => trim($statusLabel . ' ' . ($affectation->mission?->titre_mission ?: 'votre mission') . '.'),
                    'href' => $affectation->mission ? '/mission/' . $affectation->mission->id_mission : '/my-missions',
                    'created_at' => optional($affectation->date_presence ?? $affectation->date_confirmation ?? $affectation->date_affectation ?? $affectation->updated_at)?->toIso8601String(),
                ];
            });
    }
}