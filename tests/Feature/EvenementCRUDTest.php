<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvenementCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_create_evenement_with_valid_data(): void
    {
        $creator = User::factory()->create();

        $data = [
            'nom_evenement' => 'Marathon Solidaire 2026',
            'description_evenement' => 'Course de 10km au profit de BeneRun',
            'date_debut_evenement' => '2026-06-01',
            'date_fin_evenement' => '2026-06-02',
            'heure_debut_evenement' => '08:00',
            'heure_fin_evenement' => '18:00',
            'lieu_evenement' => 'Genève, Suisse',
            'mode_localisation_evenement' => 'manual',
            'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'rayon_localisation_evenement' => 500,
            'organisateur_evenement' => 'BeneRun Association',
            'nombre_benevoles_requis' => 50,
            'est_publie_evenement' => true,
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
        ];

        $response = $this->postJson('/api/evenements', $data)
            ->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'event' => [
                    'id_evenement',
                    'nom_evenement',
                    'description_evenement',
                    'date_debut_evenement',
                    'date_fin_evenement',
                ],
            ]);

        $this->assertDatabaseHas('evenements', [
            'nom_evenement' => 'Marathon Solidaire 2026',
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
        ]);
    }

    public function test_cannot_create_evenement_without_required_fields(): void
    {
        $response = $this->postJson('/api/evenements', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'nom_evenement',
                'description_evenement',
                'date_debut_evenement',
                'date_fin_evenement',
                'lieu_evenement',
                'mode_localisation_evenement',
                'organisateur_evenement',
                'nombre_benevoles_requis',
                'cree_par_utilisateur_id',
            ]);
    }

    public function test_cannot_create_evenement_with_end_date_before_start_date(): void
    {
        $creator = User::factory()->create();

        $data = [
            'nom_evenement' => 'Test Event',
            'description_evenement' => 'Test',
            'date_debut_evenement' => '2026-06-02',
            'date_fin_evenement' => '2026-06-01',
            'lieu_evenement' => 'Test',
            'mode_localisation_evenement' => 'manual',
            'organisateur_evenement' => 'Test',
            'nombre_benevoles_requis' => 10,
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
        ];

        $response = $this->postJson('/api/evenements', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date_fin_evenement']);
    }

    public function test_can_list_evenements(): void
    {
        $creator = User::factory()->create();
        $evenements = Evenement::factory(5)->create(['cree_par_utilisateur_id' => $creator->id_utilisateur]);

        $response = $this->getJson('/api/evenements')
            ->assertStatus(200)
            ->assertJsonCount(5);

        $response->assertJsonFragment([
            'id_evenement' => $evenements->first()->id_evenement,
            'nom_evenement' => $evenements->first()->nom_evenement,
        ]);
    }

    public function test_can_show_single_evenement(): void
    {
        $creator = User::factory()->create();
        $evenement = Evenement::factory()->create(['cree_par_utilisateur_id' => $creator->id_utilisateur]);

        $response = $this->getJson("/api/evenements/{$evenement->id_evenement}")
            ->assertStatus(200)
            ->assertJsonPath('id_evenement', $evenement->id_evenement)
            ->assertJsonPath('nom_evenement', $evenement->nom_evenement);
    }

    public function test_returns_404_for_nonexistent_evenement(): void
    {
        $this->getJson('/api/evenements/9999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Événement inexistant']);
    }

    public function test_can_update_evenement(): void
    {
        $creator = User::factory()->create();
        $evenement = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'mode_localisation_evenement' => 'manual',
            'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2044,6.1432',
        ]);

        $updateData = [
            'nom_evenement' => 'Updated Event Name',
            'nombre_benevoles_requis' => 100,
            'est_publie_evenement' => false,
            'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'mode_localisation_evenement' => 'manual',
        ];

        $response = $this->patchJson("/api/evenements/{$evenement->id_evenement}", $updateData)
            ->assertStatus(200);

        $this->assertDatabaseHas('evenements', [
            'id_evenement' => $evenement->id_evenement,
            'nom_evenement' => 'Updated Event Name',
            'nombre_benevoles_requis' => 100,
        ]);
    }

    public function test_can_delete_evenement(): void
    {
        $creator = User::factory()->create();
        $evenement = Evenement::factory()->create(['cree_par_utilisateur_id' => $creator->id_utilisateur]);

        $response = $this->deleteJson("/api/evenements/{$evenement->id_evenement}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('evenements', [
            'id_evenement' => $evenement->id_evenement,
        ]);
    }

    public function test_can_filter_evenements_by_search(): void
    {
        $creator = User::factory()->create();
        Evenement::factory()->create([
            'nom_evenement' => 'Marathon 2026',
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
        ]);
        Evenement::factory()->create([
            'nom_evenement' => 'Triathlon Mondial',
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
        ]);

        $response = $this->getJson('/api/evenements?search=Marathon')
            ->assertStatus(200)
            ->assertJsonCount(1);

        $response->assertJsonFragment(['nom_evenement' => 'Marathon 2026']);
    }

    public function test_can_filter_evenements_by_timeline(): void
    {
        $creator = User::factory()->create();
        Evenement::factory()->create([
            'date_debut_evenement' => now()->subDays(5),
            'date_fin_evenement' => now()->subDays(1),
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
        ]);
        Evenement::factory()->create([
            'date_debut_evenement' => now()->addDays(1),
            'date_fin_evenement' => now()->addDays(5),
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
        ]);

        $upcomingResponse = $this->getJson('/api/evenements?timeline=upcoming')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }
}
