<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Evenement;
use App\Models\Mission;
use App\Models\User;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Statistiques globales de la plateforme (admins/superadmins uniquement).
     */
    public function index(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $role = strtolower($user->role_utilisateur ?? '');
        if (!in_array($role, ['admin', 'superadmin'])) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        // Comptages généraux
        $totalUsers     = User::count();
        $totalEvents    = Evenement::count();
        $totalMissions  = Mission::count();
        $totalBadges    = Badge::count();

        $today = now()->toDateString();

        $activeMissions = Mission::whereDate('date_mission', '>=', $today)
            ->where('statut_mission', '!=', 'Annulée')
            ->count();

        $confirmedAffectations = Affectation::where('statut_affectation', 'confirmé')->count();

        // Répartition par rôle
        $roleStats = User::selectRaw('role_utilisateur, COUNT(*) as total')
            ->groupBy('role_utilisateur')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->role_utilisateur ?? 'inconnu' => $row->total
            ]);

        $volunteerCount = $roleStats['bénévole'] ?? $roleStats['volunteer'] ?? 0;

        // Taux de complétion moyen des missions actives
        $completionRate = 0;
        $missions = Mission::whereDate('date_mission', '>=', $today)
            ->where('statut_mission', '!=', 'Annulée')
            ->withCount(['affectations as confirmed_count' => function ($q) {
                $q->where('statut_affectation', 'confirmé');
            }])
            ->get();

        if ($missions->count() > 0) {
            $sum = $missions->reduce(function ($carry, $m) {
                $max = max(1, $m->nombre_benevoles_max);
                return $carry + min(100, ($m->confirmed_count / $max) * 100);
            }, 0);
            $completionRate = round($sum / $missions->count());
        }

        // Missions par événement
        $missionsByEvent = Evenement::select('id_evenement', 'nom_evenement', 'nombre_benevoles_requis')
            ->withCount('missions')
            ->with(['missions' => function ($q) {
                $q->withCount(['affectations as confirmed_count' => function ($q2) {
                    $q2->where('statut_affectation', 'confirmé');
                }]);
            }])
            ->orderByDesc('missions_count')
            ->limit(10)
            ->get()
            ->map(function ($event) {
                $currentVolunteers = $event->missions->sum('confirmed_count');
                return [
                    'id'               => $event->id_evenement,
                    'name'             => $event->nom_evenement,
                    'missionsCount'    => $event->missions_count,
                    'currentVolunteers'=> $currentVolunteers,
                    'totalNeeded'      => $event->nombre_benevoles_requis,
                ];
            });

        return response()->json([
            'kpis' => [
                'totalUsers'            => $totalUsers,
                'totalEvents'           => $totalEvents,
                'totalMissions'         => $totalMissions,
                'totalBadges'           => $totalBadges,
                'activeMissions'        => $activeMissions,
                'confirmedAffectations' => $confirmedAffectations,
                'volunteerCount'        => $volunteerCount,
                'completionRate'        => $completionRate,
            ],
            'roleStats'       => $roleStats,
            'missionsByEvent' => $missionsByEvent,
        ]);
    }

    /**
     * Statistiques personnelles de l'utilisateur connecté (tableau de bord).
     */
    public function me(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $today = now()->toDateString();

        // Missions de l'utilisateur via affectations confirmées
        $myAffectations = Affectation::where('id_utilisateur', $user->id_utilisateur)
            ->where('statut_affectation', 'confirmé')
            ->with(['mission' => function ($q) {
                $q->with('evenement')
                  ->withCount(['affectations as current_volunteers_count' => function ($q2) {
                      $q2->where('statut_affectation', 'confirmé');
                  }]);
            }])
            ->get();

        $myMissions = $myAffectations->pluck('mission')->filter()->values();

        // Prochaine mission (la plus proche dans le futur)
        $nextMission = $myMissions
            ->filter(fn ($m) => $m && $m->date_mission && $m->date_mission->toDateString() >= $today)
            ->sortBy(fn ($m) => $m->date_mission->toDateString())
            ->first();

        // Missions actives (en cours aujourd'hui ou à venir)
        $activeMissions = $myMissions
            ->filter(fn ($m) => $m && $m->date_mission && $m->date_mission->toDateString() >= $today)
            ->values();

        // Total toutes missions (historique complet)
        $totalMissions = $myMissions->count();

        // Badges de l'utilisateur
        $badges = $user->badges()->get();

        // Pour les managers : missions sous leur responsabilité
        $managedMissions = collect();
        $role = strtolower($user->role_utilisateur ?? '');
        if (in_array($role, ['responsable', 'admin', 'superadmin', 'mission_manager'])) {
            $managedMissions = Mission::where('responsable_utilisateur_id', $user->id_utilisateur)
                ->whereDate('date_mission', '>=', $today)
                ->with('evenement')
                ->withCount(['affectations as current_volunteers_count' => function ($q) {
                    $q->where('statut_affectation', 'confirmé');
                }])
                ->orderBy('date_mission')
                ->limit(5)
                ->get();
        }

        // Missions suggérées pour les bénévoles
        $suggestedMissions = collect();
        if ($role === 'bénévole' || $role === 'volunteer') {
            $myMissionIds = $myMissions->pluck('id_mission')->toArray();

            $suggestedMissions = Mission::whereNotIn('id_mission', $myMissionIds)
                ->where('visibilite_mission', 'publique')
                ->where('inscription_requise', true)
                ->whereDate('date_mission', '>=', $today)
                ->where('statut_mission', '!=', 'Annulée')
                ->with('evenement')
                ->withCount(['affectations as current_volunteers_count' => function ($q) {
                    $q->where('statut_affectation', 'confirmé');
                }])
                ->orderBy('date_mission')
                ->limit(3)
                ->get();
        }

        return response()->json([
            'totalMissions'    => $totalMissions,
            'activeMissions'   => $activeMissions,
            'nextMission'      => $nextMission,
            'badges'           => $badges,
            'managedMissions'  => $managedMissions,
            'suggestedMissions'=> $suggestedMissions,
        ]);
    }
}
