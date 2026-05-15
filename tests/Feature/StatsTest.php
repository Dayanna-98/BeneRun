<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Evenement;
use App\Models\Mission;
use App\Models\User;
use Tests\TestCase;

class StatsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_admin_can_view_platform_stats(): void
    {
        $admin = User::factory()->create(['role_utilisateur' => 'admin']);

        // Create test data
        User::factory(5)->create();
        Evenement::factory(3)->create();
        Mission::factory(4)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/stats')
            ->assertStatus(200);

        $response->assertJsonStructure([
            'kpis' => [
                'totalUsers',
                'totalEvents',
                'totalMissions',
                'totalBadges',
                'activeMissions',
                'confirmedAffectations',
                'volunteerCount',
                'completionRate',
            ],
            'roleStats',
            'missionsByEvent',
        ]);
    }

    public function test_non_admin_cannot_view_stats(): void
    {
        $user = User::factory()->create(['role_utilisateur' => 'bénévole']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/stats')
            ->assertStatus(403)
            ->assertJson(['message' => 'Accès refusé']);
    }

    public function test_unauthenticated_user_cannot_view_stats(): void
    {
        $this->getJson('/api/stats')
            ->assertStatus(401);
    }

    public function test_superadmin_can_view_stats(): void
    {
        $superadmin = User::factory()->create(['role_utilisateur' => 'superadmin']);

        User::factory(3)->create();
        Mission::factory(2)->create();

        $this->actingAs($superadmin, 'sanctum')
            ->getJson('/api/stats')
            ->assertStatus(200)
            ->assertJsonStructure([
                'kpis' => [
                    'totalUsers',
                    'totalMissions',
                ],
                'roleStats',
                'missionsByEvent',
            ]);
    }

    public function test_stats_show_correct_mission_count(): void
    {
        $admin = User::factory()->create(['role_utilisateur' => 'admin']);

        Mission::factory(5)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/stats')
            ->assertStatus(200);

        $response->assertJsonPath('kpis.totalMissions', 5);
    }
}
