<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\Postulation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role_utilisateur' => 'superadmin',
        ]);

        $this->normalUser = User::factory()->create([
            'role_utilisateur' => 'bénévole',
        ]);
    }

    // PUBLIC NOTIFICATION TESTS
    public function test_notifications_index_without_params_returns_empty()
    {
        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
            'count' => 0,
        ]);
    }

    public function test_notifications_with_superadmin_role_shows_pending_postulations()
    {
        $mission = Mission::factory()->create();
        Postulation::factory()->count(3)->create([
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'en_attente',
        ]);

        $response = $this->getJson('/api/notifications?role=superadmin');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'level', 'title'],
            ],
            'count',
        ]);
        $response->assertJsonPath('data.0.type', 'pending_postulation');
    }

    public function test_notifications_with_organizer_role_shows_pending_postulations()
    {
        $mission = Mission::factory()->create();
        Postulation::factory()->count(2)->create([
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'en_attente',
        ]);

        $response = $this->getJson('/api/notifications?role=organizer');

        $response->assertStatus(200);
        $response->assertJsonPath('count', 2);
    }

    public function test_notifications_with_admin_role_shows_pending_postulations()
    {
        Mission::factory()->create();
        Postulation::factory()->count(1)->create([
            'statut_postulation' => 'en_attente',
        ]);

        $response = $this->getJson('/api/notifications?role=admin');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.type', 'pending_postulation');
    }

    public function test_notifications_with_volunteer_role_ignores_pending_postulations()
    {
        Mission::factory()->create();
        Postulation::factory()->count(3)->create([
            'statut_postulation' => 'en_attente',
        ]);

        $response = $this->getJson('/api/notifications?role=benevole');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
            'count' => 0,
        ]);
    }

    public function test_notifications_with_user_id_shows_postulation_status()
    {
        $mission = Mission::factory()->create();
        $postulation = Postulation::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'accepte',
        ]);

        $response = $this->getJson('/api/notifications?user_id='.$this->normalUser->id_utilisateur);

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.type', 'postulation_status');
    }

    public function test_notifications_with_user_id_shows_affectation_notifications()
    {
        $mission = Mission::factory()->create();
        Affectation::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
            'id_mission' => $mission->id_mission,
            'statut_affectation' => 'assigne',
        ]);

        $response = $this->getJson('/api/notifications?user_id='.$this->normalUser->id_utilisateur);

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.type', 'affectation');
    }

    public function test_notifications_limits_pending_to_10_items()
    {
        $mission = Mission::factory()->create();
        Postulation::factory()->count(30)->create([
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'en_attente',
        ]);

        $response = $this->getJson('/api/notifications?role=superadmin');

        $response->assertStatus(200);
        // Pending postulations are limited to 10 items
        $response->assertJsonPath('count', 10);
    }

    public function test_notifications_sorted_by_date_descending()
    {
        $mission = Mission::factory()->create();
        $first = Postulation::factory()->create([
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'en_attente',
            'date_postulation' => now()->subDays(10),
        ]);
        $second = Postulation::factory()->create([
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'en_attente',
            'date_postulation' => now(),
        ]);

        $response = $this->getJson('/api/notifications?role=superadmin');

        $response->assertStatus(200);
        // Most recent should be first
        $this->assertIsArray($response->json('data'));
    }

    public function test_notifications_with_header_role_superadmin()
    {
        Mission::factory()->create();
        Postulation::factory()->create([
            'statut_postulation' => 'en_attente',
        ]);

        $response = $this->getJson('/api/notifications', [
            'X-User-Role' => 'superadmin',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.type', 'pending_postulation');
    }

    public function test_notifications_query_role_organizer()
    {
        Mission::factory()->create();
        Postulation::factory()->create([
            'statut_postulation' => 'en_attente',
        ]);

        $response = $this->getJson('/api/notifications?role=organizer');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.type', 'pending_postulation');
    }

    public function test_notifications_combined_admin_and_user_notifications()
    {
        $mission = Mission::factory()->create();

        // Admin sees pending postulation
        Postulation::factory()->create([
            'statut_postulation' => 'en_attente',
        ]);

        // User also sees their own postulation status
        $userPostulation = Postulation::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'accepte',
        ]);

        $response = $this->getJson(
            '/api/notifications?role=superadmin&user_id='.$this->normalUser->id_utilisateur
        );

        $response->assertStatus(200);
        $response->assertJsonPath('count', 2);
    }

    public function test_notifications_empty_when_no_data()
    {
        $response = $this->getJson('/api/notifications?user_id='.$this->normalUser->id_utilisateur);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
            'count' => 0,
        ]);
    }
}
