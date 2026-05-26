<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Competence;
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

    public function test_dashboard_suggested_missions_only_include_compatible_competences(): void
    {
        $volunteer = User::factory()->create(['role_utilisateur' => 'bénévole']);

        $supportedCompetence = Competence::factory()->create();
        $unsupportedCompetence = Competence::factory()->create();

        $volunteer->competences()->attach($supportedCompetence->id_competence);

        $compatibleMission = Mission::factory()->create([
            'id_evenement' => Evenement::factory()->create()->id_evenement,
            'date_mission' => now()->addDays(5),
            'visibilite_mission' => 'publique',
            'inscription_requise' => true,
            'statut_mission' => 'À venir',
        ]);
        $compatibleMission->competences()->attach($supportedCompetence->id_competence);

        $incompatibleMission = Mission::factory()->create([
            'id_evenement' => Evenement::factory()->create()->id_evenement,
            'date_mission' => now()->addDays(6),
            'visibilite_mission' => 'publique',
            'inscription_requise' => true,
            'statut_mission' => 'À venir',
        ]);
        $incompatibleMission->competences()->attach($unsupportedCompetence->id_competence);

        $noCompetenceMission = Mission::factory()->create([
            'id_evenement' => Evenement::factory()->create()->id_evenement,
            'date_mission' => now()->addDays(7),
            'visibilite_mission' => 'publique',
            'inscription_requise' => true,
            'statut_mission' => 'À venir',
        ]);

        $response = $this->actingAs($volunteer, 'sanctum')
            ->getJson('/api/stats/me')
            ->assertStatus(200);

        $suggestedMissionIds = collect($response->json('suggestedMissions', []))
            ->pluck('id_mission')
            ->all();

        $this->assertContains($compatibleMission->id_mission, $suggestedMissionIds);
        $this->assertContains($noCompetenceMission->id_mission, $suggestedMissionIds);
        $this->assertNotContains($incompatibleMission->id_mission, $suggestedMissionIds);
    }
}
