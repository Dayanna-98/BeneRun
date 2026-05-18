<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Competence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgeCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create test competences for badge rules
        Competence::factory()->count(3)->create();
    }

    // INDEX TESTS
    public function test_list_all_badges()
    {
        Badge::factory()->count(5)->create();

        $response = $this->getJson('/api/badges');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_list_badges_returns_data()
    {
        Badge::factory()->count(5)->create();

        $response = $this->getJson('/api/badges');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_list_badges_empty()
    {
        $response = $this->getJson('/api/badges');

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    // SHOW TESTS
    public function test_show_badge_returns_404_when_not_found()
    {
        $response = $this->getJson('/api/badges/999999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Badge inexistant']);
    }

    public function test_show_existing_badge()
    {
        $badge = Badge::factory()->create([
            'titre_badge' => 'Expert Badge',
        ]);

        $response = $this->getJson('/api/badges/'.$badge->id_badge);

        $response->assertStatus(200);
        $response->assertJson([
            'id_badge' => $badge->id_badge,
            'titre_badge' => 'Expert Badge',
        ]);
    }

    public function test_show_badge_with_data()
    {
        $badge = Badge::factory()->create([
            'titre_badge' => 'Expert Badge',
        ]);

        $response = $this->getJson('/api/badges/'.$badge->id_badge);

        $response->assertStatus(200);
        $response->assertJson([
            'id_badge' => $badge->id_badge,
            'titre_badge' => 'Expert Badge',
        ]);
    }

    // STORE TESTS
    public function test_create_badge_requires_title()
    {
        $response = $this->postJson('/api/badges', [
            'description_badge' => 'Test Description',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('titre_badge');
    }

    public function test_create_badge_with_minimal_data()
    {
        $response = $this->postJson('/api/badges', [
            'titre_badge' => 'New Badge',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Badge ajouté',
            'badge' => [
                'titre_badge' => 'New Badge',
                'score_badge' => 0,
            ],
        ]);
    }

    public function test_create_badge_with_full_data()
    {
        $response = $this->postJson('/api/badges', [
            'titre_badge' => 'Complete Badge',
            'description_badge' => 'Full description',
            'score_badge' => 100,
            'regle_auto' => 'auto_rule',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'badge' => [
                'titre_badge' => 'Complete Badge',
                'description_badge' => 'Full description',
                'score_badge' => 100,
                'regle_auto' => 'auto_rule',
            ],
        ]);
    }

    public function test_create_badge_score_must_be_positive()
    {
        $response = $this->postJson('/api/badges', [
            'titre_badge' => 'Badge',
            'score_badge' => -10,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('score_badge');
    }

    public function test_create_badge_with_competence_rules()
    {
        $competence = Competence::first();

        $response = $this->postJson('/api/badges', [
            'titre_badge' => 'Competence Badge',
            'competence_rules' => [
                [
                    'id_competence' => $competence->id_competence,
                    'points_requis' => 50,
                ],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Badge ajouté',
            'badge' => [
                'titre_badge' => 'Competence Badge',
            ],
        ]);
    }

    public function test_create_badge_competence_rules_validation()
    {
        $response = $this->postJson('/api/badges', [
            'titre_badge' => 'Badge',
            'competence_rules' => [
                [
                    'id_competence' => 999999,
                    'points_requis' => 50,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('competence_rules.0.id_competence');
    }

    // UPDATE TESTS
    public function test_update_nonexistent_badge_returns_404()
    {
        $response = $this->patchJson('/api/badges/999999', [
            'titre_badge' => 'Updated',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Badge inexistant']);
    }

    public function test_update_badge_title()
    {
        $badge = Badge::factory()->create([
            'titre_badge' => 'Original Title',
        ]);

        $response = $this->patchJson('/api/badges/'.$badge->id_badge, [
            'titre_badge' => 'Updated Title',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Badge mis à jour',
            'badge' => [
                'titre_badge' => 'Updated Title',
            ],
        ]);
    }

    public function test_update_badge_score()
    {
        $badge = Badge::factory()->create([
            'score_badge' => 50,
        ]);

        $response = $this->patchJson('/api/badges/'.$badge->id_badge, [
            'titre_badge' => $badge->titre_badge,
            'score_badge' => 200,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'badge' => [
                'score_badge' => 200,
            ],
        ]);
    }

    public function test_update_badge_with_partial_fields()
    {
        $badge = Badge::factory()->create([
            'titre_badge' => 'Original',
            'description_badge' => 'Original Description',
            'score_badge' => 100,
        ]);

        $response = $this->patchJson('/api/badges/'.$badge->id_badge, [
            'titre_badge' => 'Updated',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'badge' => [
                'titre_badge' => 'Updated',
            ],
        ]);

        // Verify the badge was updated
        $this->assertDatabaseHas('badges', [
            'id_badge' => $badge->id_badge,
            'titre_badge' => 'Updated',
        ]);
    }

    // DESTROY TESTS
    public function test_delete_nonexistent_badge_returns_404()
    {
        $response = $this->deleteJson('/api/badges/999999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Badge inexistant']);
    }

    public function test_delete_existing_badge()
    {
        $badge = Badge::factory()->create();

        $response = $this->deleteJson('/api/badges/'.$badge->id_badge);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Badge supprimé']);

        $this->assertDatabaseMissing('badges', [
            'id_badge' => $badge->id_badge,
        ]);
    }
}
