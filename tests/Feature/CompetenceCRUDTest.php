<?php

namespace Tests\Feature;

use App\Models\Competence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetenceCRUDTest extends TestCase
{
    use RefreshDatabase;

    private const MISSION_TYPES = ['secours', 'logistique', 'accueil', 'technique', 'animation', 'autre'];

    // INDEX TESTS
    public function test_list_all_competences()
    {
        Competence::factory()->count(5)->create();

        $response = $this->getJson('/api/competences');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_list_competences_empty()
    {
        $response = $this->getJson('/api/competences');

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    // SHOW TESTS
    public function test_show_competence_returns_404_when_not_found()
    {
        $response = $this->getJson('/api/competences/999999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Compétence inexistante']);
    }

    public function test_show_existing_competence()
    {
        $competence = Competence::factory()->create([
            'nom_competence' => 'First Aid',
        ]);

        $response = $this->getJson('/api/competences/'.$competence->id_competence);

        $response->assertStatus(200);
        $response->assertJson([
            'id_competence' => $competence->id_competence,
            'nom_competence' => 'First Aid',
        ]);
    }

    // STORE TESTS
    public function test_create_competence_requires_name()
    {
        $response = $this->postJson('/api/competences', [
            'types_mission_suggeres' => ['secours'],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('nom_competence');
    }

    public function test_create_competence_with_unique_name_constraint()
    {
        Competence::factory()->create([
            'nom_competence' => 'Unique Competence',
        ]);

        $response = $this->postJson('/api/competences', [
            'nom_competence' => 'Unique Competence',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('nom_competence');
    }

    public function test_create_competence_with_valid_mission_types()
    {
        $response = $this->postJson('/api/competences', [
            'nom_competence' => 'Rescue Operations',
            'types_mission_suggeres' => ['secours', 'logistique'],
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Compétence ajoutée',
            'competence' => [
                'nom_competence' => 'Rescue Operations',
                'types_mission_suggeres' => ['secours', 'logistique'],
            ],
        ]);
    }

    public function test_create_competence_with_invalid_mission_type()
    {
        $response = $this->postJson('/api/competences', [
            'nom_competence' => 'Test Competence',
            'types_mission_suggeres' => ['invalid_type'],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('types_mission_suggeres.0');
    }

    public function test_create_competence_without_mission_types()
    {
        $response = $this->postJson('/api/competences', [
            'nom_competence' => 'Simple Competence',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Compétence ajoutée',
            'competence' => [
                'nom_competence' => 'Simple Competence',
            ],
        ]);
    }

    public function test_create_competence_normalizes_mission_types()
    {
        $response = $this->postJson('/api/competences', [
            'nom_competence' => 'Test Competence',
            'types_mission_suggeres' => ['secours', 'logistique', 'accueil'],
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'competence' => [
                'types_mission_suggeres' => ['secours', 'logistique', 'accueil'],
            ],
        ]);
    }

    public function test_create_competence_removes_duplicates()
    {
        $response = $this->postJson('/api/competences', [
            'nom_competence' => 'Test',
            'types_mission_suggeres' => ['secours', 'secours', 'secours'],
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'competence' => [
                'types_mission_suggeres' => ['secours'],
            ],
        ]);
    }

    // UPDATE TESTS
    public function test_update_nonexistent_competence_returns_404()
    {
        $response = $this->patchJson('/api/competences/999999', [
            'nom_competence' => 'Updated',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Compétence inexistante']);
    }

    public function test_update_competence_name()
    {
        $competence = Competence::factory()->create([
            'nom_competence' => 'Original Name',
        ]);

        $response = $this->patchJson('/api/competences/'.$competence->id_competence, [
            'nom_competence' => 'Updated Name',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Compétence mise à jour',
            'competence' => [
                'nom_competence' => 'Updated Name',
            ],
        ]);
    }

    public function test_update_competence_requires_unique_name_excluding_self()
    {
        $competence1 = Competence::factory()->create(['nom_competence' => 'First']);
        $competence2 = Competence::factory()->create(['nom_competence' => 'Second']);

        $response = $this->patchJson('/api/competences/'.$competence1->id_competence, [
            'nom_competence' => 'Second',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('nom_competence');
    }

    public function test_update_competence_mission_types()
    {
        $competence = Competence::factory()->create([
            'types_mission_suggeres' => ['secours'],
        ]);

        $response = $this->patchJson('/api/competences/'.$competence->id_competence, [
            'nom_competence' => $competence->nom_competence,
            'types_mission_suggeres' => ['technique', 'animation'],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'competence' => [
                'types_mission_suggeres' => ['technique', 'animation'],
            ],
        ]);
    }

    // DESTROY TESTS
    public function test_delete_nonexistent_competence_returns_404()
    {
        $response = $this->deleteJson('/api/competences/999999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Compétence inexistante']);
    }

    public function test_delete_existing_competence()
    {
        $competence = Competence::factory()->create();

        $response = $this->deleteJson('/api/competences/'.$competence->id_competence);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Compétence supprimée']);

        $this->assertDatabaseMissing('competences', [
            'id_competence' => $competence->id_competence,
        ]);
    }
}
