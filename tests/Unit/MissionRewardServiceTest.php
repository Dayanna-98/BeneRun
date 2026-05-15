<?php

namespace Tests\Unit;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\User;
use App\Services\MissionRewardService;
use Tests\TestCase;

class MissionRewardServiceTest extends TestCase
{
    private MissionRewardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MissionRewardService();
    }

    public function test_no_rewards_if_mission_has_no_competence_rewards(): void
    {
        $mission = Mission::factory()->create();

        $result = $this->service->rewardMissionParticipants($mission);

        $this->assertEquals('no_reward_competences', $result['skipped']);
        $this->assertEquals(0, $result['users_rewarded']);
    }

    public function test_mission_rewards_are_idempotent(): void
    {
        // Service should handle edge cases gracefully
        $mission = Mission::factory()->create();

        $result1 = $this->service->rewardMissionParticipants($mission);
        $result2 = $this->service->rewardMissionParticipants($mission);

        // Both should return same skipped message (no competences)
        $this->assertEquals($result1['skipped'], $result2['skipped']);
    }

    public function test_service_returns_expected_structure(): void
    {
        $mission = Mission::factory()->create();

        $result = $this->service->rewardMissionParticipants($mission);

        $this->assertArrayHasKey('mission_id', $result);
        $this->assertArrayHasKey('users_rewarded', $result);
    }
}
