<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\Postulation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AffectationCRUDTest extends TestCase
{
    use RefreshDatabase;

    private Mission $mission;

    private User $user;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_utilisateur' => 'admin',
        ]);
        Sanctum::actingAs($this->admin);

        $this->mission = Mission::factory()->create();
        $this->user = User::factory()->create();
    }

    // INDEX TESTS
    public function test_list_all_affectations()
    {
        Affectation::factory()->count(5)->create();

        $response = $this->getJson('/api/affectations');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_list_affectations_with_relations()
    {
        $affectation = Affectation::factory()->create();

        $response = $this->getJson('/api/affectations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id_affectation', 'mission', 'utilisateur'],
        ]);
    }

    public function test_list_affectations_empty()
    {
        $response = $this->getJson('/api/affectations');

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    // SHOW TESTS
    public function test_show_affectation_returns_404_when_not_found()
    {
        $response = $this->getJson('/api/affectations/999999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Affectation inexistante']);
    }

    public function test_show_existing_affectation()
    {
        $affectation = Affectation::factory()->create([
            'id_mission' => $this->mission->id_mission,
            'id_utilisateur' => $this->user->id_utilisateur,
            'statut_affectation' => 'assigne',
        ]);

        $response = $this->getJson('/api/affectations/'.$affectation->id_affectation);

        $response->assertStatus(200);
        $response->assertJson([
            'id_affectation' => $affectation->id_affectation,
            'statut_affectation' => 'assigne',
        ]);
    }

    public function test_show_affectation_includes_relations()
    {
        $affectation = Affectation::factory()->create();

        $response = $this->getJson('/api/affectations/'.$affectation->id_affectation);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id_affectation',
            'mission',
            'utilisateur',
        ]);
    }

    // STORE TESTS
    public function test_create_affectation_requires_mission_id()
    {
        $response = $this->postJson('/api/affectations', [
            'id_utilisateur' => $this->user->id_utilisateur,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('id_mission');
    }

    public function test_create_affectation_requires_user_id()
    {
        $response = $this->postJson('/api/affectations', [
            'id_mission' => $this->mission->id_mission,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('id_utilisateur');
    }

    public function test_create_affectation_requires_valid_mission()
    {
        $response = $this->postJson('/api/affectations', [
            'id_mission' => 999999,
            'id_utilisateur' => $this->user->id_utilisateur,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('id_mission');
    }

    public function test_create_affectation_requires_valid_user()
    {
        $response = $this->postJson('/api/affectations', [
            'id_mission' => $this->mission->id_mission,
            'id_utilisateur' => 999999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('id_utilisateur');
    }

    public function test_create_affectation_with_minimal_data()
    {
        $response = $this->postJson('/api/affectations', [
            'id_mission' => $this->mission->id_mission,
            'id_utilisateur' => $this->user->id_utilisateur,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Affectation ajoutée',
            'affectation' => [
                'id_mission' => $this->mission->id_mission,
                'id_utilisateur' => $this->user->id_utilisateur,
            ],
        ]);
    }

    public function test_create_affectation_with_status()
    {
        $response = $this->postJson('/api/affectations', [
            'id_mission' => $this->mission->id_mission,
            'id_utilisateur' => $this->user->id_utilisateur,
            'statut_affectation' => 'confirme',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'affectation' => [
                'statut_affectation' => 'confirme',
            ],
        ]);
    }

    public function test_create_affectation_with_invalid_status()
    {
        $response = $this->postJson('/api/affectations', [
            'id_mission' => $this->mission->id_mission,
            'id_utilisateur' => $this->user->id_utilisateur,
            'statut_affectation' => 'invalid_status',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('statut_affectation');
    }

    public function test_create_affectation_as_responsible()
    {
        $response = $this->postJson('/api/affectations', [
            'id_mission' => $this->mission->id_mission,
            'id_utilisateur' => $this->user->id_utilisateur,
            'est_responsable' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'affectation' => [
                'est_responsable' => true,
            ],
        ]);
    }

    public function test_create_affectation_with_dates()
    {
        $response = $this->postJson('/api/affectations', [
            'id_mission' => $this->mission->id_mission,
            'id_utilisateur' => $this->user->id_utilisateur,
            'date_affectation' => '2025-01-01',
            'date_confirmation' => '2025-01-02',
            'date_presence' => '2025-01-03',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'affectation' => [
                'id_mission' => $this->mission->id_mission,
            ],
        ]);
    }

    // UPDATE TESTS
    public function test_update_nonexistent_affectation_returns_404()
    {
        $response = $this->patchJson('/api/affectations/999999', [
            'statut_affectation' => 'confirme',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Affectation inexistante']);
    }

    public function test_update_affectation_status()
    {
        $affectation = Affectation::factory()->create([
            'statut_affectation' => 'assigne',
        ]);

        $response = $this->patchJson('/api/affectations/'.$affectation->id_affectation, [
            'statut_affectation' => 'present',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Affectation mise à jour',
            'affectation' => [
                'statut_affectation' => 'present',
            ],
        ]);
    }

    public function test_update_affectation_remark()
    {
        $affectation = Affectation::factory()->create();

        $response = $this->patchJson('/api/affectations/'.$affectation->id_affectation, [
            'remarque' => 'Important note',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'affectation' => [
                'remarque' => 'Important note',
            ],
        ]);
    }

    public function test_update_affectation_to_responsible()
    {
        $affectation = Affectation::factory()->create([
            'est_responsable' => false,
        ]);

        $response = $this->patchJson('/api/affectations/'.$affectation->id_affectation, [
            'est_responsable' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'affectation' => [
                'est_responsable' => true,
            ],
        ]);
    }

    // DESTROY TESTS
    public function test_delete_nonexistent_affectation_returns_404()
    {
        $response = $this->deleteJson('/api/affectations/999999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Affectation inexistante']);
    }

    public function test_delete_existing_affectation()
    {
        $affectation = Affectation::factory()->create();

        $response = $this->deleteJson('/api/affectations/'.$affectation->id_affectation);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Affectation supprimée']);

        $this->assertDatabaseMissing('affectations', [
            'id_affectation' => $affectation->id_affectation,
        ]);
    }

    public function test_replace_volunteer_allows_responsible_users()
    {
        $responsible = User::factory()->create([
            'role_utilisateur' => 'responsable',
        ]);
        Sanctum::actingAs($responsible);

        $mission = Mission::factory()->create();
        $outgoingUser = User::factory()->create();
        $incomingUser = User::factory()->create();

        $outgoingAffectation = Affectation::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $outgoingUser->id_utilisateur,
            'statut_affectation' => 'assigne',
            'date_affectation' => now()->subDay(),
        ]);

        $incomingPostulation = Postulation::create([
            'id_evenement' => $mission->id_evenement,
            'id_mission' => null,
            'id_utilisateur' => $incomingUser->id_utilisateur,
            'statut_postulation' => 'en_attente',
            'date_postulation' => now()->subHours(2),
        ]);

        $response = $this->postJson('/api/missions/'.$mission->id_mission.'/replace-volunteer', [
            'outgoing_user_id' => $outgoingUser->id_utilisateur,
            'incoming_postulation_id' => $incomingPostulation->id_postulation,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Remplacement effectué avec succès.']);

        $this->assertDatabaseHas('affectations', [
            'id_affectation' => $outgoingAffectation->id_affectation,
            'statut_affectation' => 'annule',
        ]);
        $this->assertDatabaseHas('affectations', [
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $incomingUser->id_utilisateur,
            'statut_affectation' => 'assigne',
        ]);
        $this->assertDatabaseHas('postulations', [
            'id_postulation' => $incomingPostulation->id_postulation,
            'statut_postulation' => 'accepte',
        ]);
    }

    public function test_replace_volunteer_rejects_volunteers_without_permission()
    {
        $volunteer = User::factory()->create([
            'role_utilisateur' => 'bénévole',
        ]);
        Sanctum::actingAs($volunteer);

        $mission = Mission::factory()->create();

        $response = $this->postJson('/api/missions/'.$mission->id_mission.'/replace-volunteer', [
            'outgoing_user_id' => $this->user->id_utilisateur,
            'incoming_postulation_id' => 999999,
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Action réservée aux responsables, admins et superadmins.']);
    }
}
