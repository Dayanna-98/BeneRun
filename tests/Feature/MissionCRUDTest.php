<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_create_mission_with_valid_data(): void
    {
        $creator = User::factory()->create();
        $responsable = User::factory()->create();
        $evenement = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'date_debut_evenement' => '2026-06-01',
            'date_fin_evenement' => '2026-06-02',
            'mode_localisation_evenement' => 'manual',
            'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'rayon_localisation_evenement' => 500,
            'nombre_benevoles_requis' => 100,
        ]);

        $data = [
            'id_evenement' => $evenement->id_evenement,
            'responsable_utilisateur_id' => $responsable->id_utilisateur,
            'titre_mission' => 'Point de ravitaillement',
            'type_mission' => 'logistique',
            'description_mission' => 'Distribution d\'eau et snacks',
            'date_mission' => '2026-06-01',
            'heure_debut_mission' => '08:00',
            'heure_fin_mission' => '18:00',
            'lieu_mission' => 'Genève, Suisse',
            'google_maps_url_mission' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'nombre_benevoles_max' => 10,
            'nombre_benevoles_backup' => 2,
            'inscription_requise' => true,
            'visibilite_mission' => 'publique',
            'consignes_securite' => 'Porter des chaussures de sport',
        ];

        $response = $this->postJson('/api/missions', $data)
            ->assertStatus(201)
            ->assertJsonStructure([
                'mission' => [
                    'id_mission',
                    'titre_mission',
                    'type_mission',
                    'date_mission',
                ],
            ]);

        $this->assertDatabaseHas('missions', [
            'titre_mission' => 'Point de ravitaillement',
            'id_evenement' => $evenement->id_evenement,
        ]);
    }

    public function test_cannot_create_mission_without_required_fields(): void
    {
        $response = $this->postJson('/api/missions', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'id_evenement',
                'responsable_utilisateur_id',
                'titre_mission',
                'type_mission',
                'description_mission',
                'date_mission',
                'heure_debut_mission',
                'heure_fin_mission',
                'lieu_mission',
                'google_maps_url_mission',
                'nombre_benevoles_max',
            ]);
    }

    public function test_cannot_create_mission_with_invalid_type(): void
    {
        $creator = User::factory()->create();
        $responsable = User::factory()->create();
        $evenement = Evenement::factory()->create(['cree_par_utilisateur_id' => $creator->id_utilisateur]);

        $data = [
            'id_evenement' => $evenement->id_evenement,
            'responsable_utilisateur_id' => $responsable->id_utilisateur,
            'titre_mission' => 'Test',
            'type_mission' => 'invalid_type',
            'description_mission' => 'Test',
            'date_mission' => '2026-06-01',
            'heure_debut_mission' => '08:00',
            'heure_fin_mission' => '18:00',
            'lieu_mission' => 'Test',
            'google_maps_url_mission' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'nombre_benevoles_max' => 10,
        ];

        $response = $this->postJson('/api/missions', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type_mission']);
    }

    public function test_cannot_create_mission_with_end_time_before_start_time(): void
    {
        $creator = User::factory()->create();
        $responsable = User::factory()->create();
        $evenement = Evenement::factory()->create(['cree_par_utilisateur_id' => $creator->id_utilisateur]);

        $data = [
            'id_evenement' => $evenement->id_evenement,
            'responsable_utilisateur_id' => $responsable->id_utilisateur,
            'titre_mission' => 'Test',
            'type_mission' => 'logistique',
            'description_mission' => 'Test',
            'date_mission' => '2026-06-01',
            'heure_debut_mission' => '18:00',
            'heure_fin_mission' => '08:00',
            'lieu_mission' => 'Test',
            'google_maps_url_mission' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'nombre_benevoles_max' => 10,
        ];

        $response = $this->postJson('/api/missions', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['heure_fin_mission']);
    }

    public function test_can_list_missions(): void
    {
        $creator = User::factory()->create();
        $evenement = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'nombre_benevoles_requis' => 100,
        ]);
        $missions = Mission::factory(5)->create([
            'id_evenement' => $evenement->id_evenement,
            'date_mission' => $evenement->date_debut_evenement,
        ]);

        $response = $this->getJson('/api/missions')
            ->assertStatus(200)
            ->assertJsonCount(5);

        $response->assertJsonFragment([
            'id_mission' => $missions->first()->id_mission,
            'titre_mission' => $missions->first()->titre_mission,
        ]);
    }

    public function test_can_show_single_mission(): void
    {
        $creator = User::factory()->create();
        $evenement = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'nombre_benevoles_requis' => 100,
        ]);
        $mission = Mission::factory()->create([
            'id_evenement' => $evenement->id_evenement,
            'date_mission' => $evenement->date_debut_evenement,
        ]);

        $response = $this->getJson("/api/missions/{$mission->id_mission}")
            ->assertStatus(200)
            ->assertJsonPath('id_mission', $mission->id_mission)
            ->assertJsonPath('titre_mission', $mission->titre_mission);
    }

    public function test_returns_404_for_nonexistent_mission(): void
    {
        $this->getJson('/api/missions/9999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Mission inexistante']);
    }

    public function test_can_update_mission(): void
    {
        $creator = User::factory()->create();
        $evenement = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'date_debut_evenement' => '2026-06-01',
            'date_fin_evenement' => '2026-06-02',
            'mode_localisation_evenement' => 'manual',
            'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'rayon_localisation_evenement' => 500,
            'nombre_benevoles_requis' => 100,
        ]);
        $mission = Mission::factory()->create([
            'id_evenement' => $evenement->id_evenement,
            'date_mission' => '2026-06-01',
        ]);

        $updateData = [
            'titre_mission' => 'Updated Mission Title',
            'nombre_benevoles_max' => 25,
            'statut_mission' => 'En cours',
            'date_mission' => '2026-06-01',
            'google_maps_url_mission' => 'https://www.google.com/maps?q=46.2044,6.1432',
        ];

        $response = $this->patchJson("/api/missions/{$mission->id_mission}", $updateData)
            ->assertStatus(200);

        $this->assertDatabaseHas('missions', [
            'id_mission' => $mission->id_mission,
            'titre_mission' => 'Updated Mission Title',
            'nombre_benevoles_max' => 25,
        ]);
    }

    public function test_can_delete_mission(): void
    {
        $creator = User::factory()->create();
        $evenement = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'nombre_benevoles_requis' => 100,
        ]);
        $mission = Mission::factory()->create([
            'id_evenement' => $evenement->id_evenement,
            'date_mission' => $evenement->date_debut_evenement,
        ]);

        $response = $this->deleteJson("/api/missions/{$mission->id_mission}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('missions', [
            'id_mission' => $mission->id_mission,
        ]);
    }

    public function test_can_list_missions_by_evenement(): void
    {
        $creator = User::factory()->create();
        $evenement1 = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'nombre_benevoles_requis' => 100,
        ]);
        $evenement2 = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'nombre_benevoles_requis' => 100,
        ]);

        Mission::factory(3)->create([
            'id_evenement' => $evenement1->id_evenement,
            'date_mission' => $evenement1->date_debut_evenement,
        ]);
        Mission::factory(2)->create([
            'id_evenement' => $evenement2->id_evenement,
            'date_mission' => $evenement2->date_debut_evenement,
        ]);

        $response = $this->getJson("/api/missions?id_evenement={$evenement1->id_evenement}")
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_filter_missions_by_status(): void
    {
        $creator = User::factory()->create();
        $evenement = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'nombre_benevoles_requis' => 100,
        ]);

        Mission::factory(3)->create([
            'id_evenement' => $evenement->id_evenement,
            'statut_mission' => 'À venir',
            'date_mission' => $evenement->date_debut_evenement,
        ]);
        Mission::factory(2)->create([
            'id_evenement' => $evenement->id_evenement,
            'statut_mission' => 'En cours',
            'date_mission' => $evenement->date_debut_evenement,
        ]);

        $response = $this->getJson('/api/missions?statut_mission=À+venir')
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_filter_missions_by_search(): void
    {
        $creator = User::factory()->create();
        $evenement = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'nombre_benevoles_requis' => 100,
        ]);

        Mission::factory()->create([
            'id_evenement' => $evenement->id_evenement,
            'titre_mission' => 'Point de ravitaillement',
            'date_mission' => $evenement->date_debut_evenement,
        ]);
        Mission::factory()->create([
            'id_evenement' => $evenement->id_evenement,
            'titre_mission' => 'Ambulance de secours',
            'date_mission' => $evenement->date_debut_evenement,
        ]);

        $response = $this->getJson('/api/missions?search=ravitaillement')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['titre_mission' => 'Point de ravitaillement']);
    }
}
