<?php

namespace App\Services;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\MissionUserReward;
use App\Models\UserCompetencePoint;
use Illuminate\Support\Facades\DB;

class MissionRewardService
{
    /**
     * Apply mission rewards once per user when a mission is completed.
     */
    public function rewardMissionParticipants(Mission $mission): array
    {
        $mission->loadMissing('rewardCompetences:id_competence');

        $rewardMap = $mission->rewardCompetences
            ->mapWithKeys(function ($competence): array {
                return [(int) $competence->id_competence => (int) ($competence->pivot->points_gagnes ?? 0)];
            })
            ->filter(static fn (int $points): bool => $points > 0)
            ->all();

        if ($rewardMap === []) {
            return [
                'mission_id' => (int) $mission->id_mission,
                'users_rewarded' => 0,
                'badges_awarded' => 0,
                'skipped' => 'no_reward_competences',
            ];
        }

        $participantIds = Affectation::where('id_mission', $mission->id_mission)
            ->whereIn('statut_affectation', ['assigne', 'confirme', 'present'])
            ->distinct()
            ->pluck('id_utilisateur')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $usersRewarded = 0;
        $badgesAwarded = 0;

        foreach ($participantIds as $userId) {
            $result = DB::transaction(function () use ($mission, $userId, $rewardMap): array {
                $alreadyRewarded = MissionUserReward::where('id_mission', $mission->id_mission)
                    ->where('id_utilisateur', $userId)
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyRewarded) {
                    return ['rewarded' => false, 'badges' => 0];
                }

                foreach ($rewardMap as $competenceId => $pointsGagnes) {
                    $pointsRow = UserCompetencePoint::firstOrCreate(
                        [
                            'id_utilisateur' => $userId,
                            'id_competence' => $competenceId,
                        ],
                        ['points_total' => 0]
                    );

                    $pointsRow->increment('points_total', $pointsGagnes);
                }

                $newBadges = $this->assignBadgesFromThresholds($userId, array_keys($rewardMap));

                MissionUserReward::create([
                    'id_mission' => $mission->id_mission,
                    'id_utilisateur' => $userId,
                    'rewarded_at' => now(),
                    'details_recompense' => [
                        'points_by_competence' => $rewardMap,
                        'badges_awarded' => $newBadges,
                    ],
                ]);

                return ['rewarded' => true, 'badges' => count($newBadges)];
            });

            if ($result['rewarded']) {
                $usersRewarded++;
                $badgesAwarded += $result['badges'];
            }
        }

        return [
            'mission_id' => (int) $mission->id_mission,
            'users_rewarded' => $usersRewarded,
            'badges_awarded' => $badgesAwarded,
        ];
    }

    /**
     * Award badges whose point threshold is now reached.
     *
     * @return int[]
     */
    private function assignBadgesFromThresholds(int $userId, array $touchedCompetenceIds): array
    {
        if ($touchedCompetenceIds === []) {
            return [];
        }

        $eligibleBadgeIds = DB::table('badge_competence_rules as rules')
            ->join('user_competence_points as points', function ($join) use ($userId) {
                $join->on('points.id_competence', '=', 'rules.id_competence')
                    ->where('points.id_utilisateur', '=', $userId);
            })
            ->whereIn('rules.id_competence', $touchedCompetenceIds)
            ->whereColumn('points.points_total', '>=', 'rules.points_requis')
            ->pluck('rules.id_badge')
            ->unique()
            ->map(static fn ($id): int => (int) $id)
            ->values();

        if ($eligibleBadgeIds->isEmpty()) {
            return [];
        }

        $alreadyOwned = DB::table('user_badges')
            ->where('id_utilisateur', $userId)
            ->whereIn('id_badge', $eligibleBadgeIds->all())
            ->pluck('id_badge')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $newBadgeIds = $eligibleBadgeIds
            ->reject(static fn (int $badgeId): bool => in_array($badgeId, $alreadyOwned, true))
            ->values()
            ->all();

        if ($newBadgeIds === []) {
            return [];
        }

        $now = now();
        $rows = array_map(static function (int $badgeId) use ($userId, $now): array {
            return [
                'id_utilisateur' => $userId,
                'id_badge' => $badgeId,
                'attribue_le' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $newBadgeIds);

        DB::table('user_badges')->insertOrIgnore($rows);

        return $newBadgeIds;
    }
}
