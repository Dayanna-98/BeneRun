<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionLocationPerimeterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_a_mission_outside_the_event_perimeter(): void
    {
        $creator = User::factory()->create();
        $responsable = User::factory()->create();

        $event = Evenement::create([
            'nom_evenement' => 'Course test',
            'description_evenement' => 'Événement de test',
            'date_debut_evenement' => '2026-05-01',
            'date_fin_evenement' => '2026-05-02',
            'heure_debut_evenement' => '08:00',
            'heure_fin_evenement' => '18:00',
            'lieu_evenement' => 'Genève',
            'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'rayon_localisation_evenement' => 500,
            'latitude_evenement' => 46.2044,
            'longitude_evenement' => 6.1432,
            'organisateur_evenement' => 'BeneRun',
            'nombre_benevoles_requis' => 20,
            'est_publie_evenement' => true,
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
        ]);

        $response = $this->postJson('/api/missions', [
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $responsable->id_utilisateur,
            'titre_mission' => 'Point A',
            'type_mission' => 'logistique',
            'description_mission' => 'Mission test',
            'date_mission' => '2026-05-01',
            'heure_debut_mission' => '09:00',
            'heure_fin_mission' => '10:00',
            'lieu_mission' => 'Hors périmètre',
            'google_maps_url_mission' => 'https://www.google.com/maps?q=46.2150,6.1600',
            'nombre_benevoles_max' => 5,
            'nombre_benevoles_backup' => 1,
            'visibilite_mission' => 'publique',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['google_maps_url_mission']);
    }

    public function test_it_accepts_a_mission_inside_the_event_perimeter(): void
    {
        $creator = User::factory()->create();
        $responsable = User::factory()->create();

        $event = Evenement::create([
            'nom_evenement' => 'Course test',
            'description_evenement' => 'Événement de test',
            'date_debut_evenement' => '2026-05-01',
            'date_fin_evenement' => '2026-05-02',
            'heure_debut_evenement' => '08:00',
            'heure_fin_evenement' => '18:00',
            'lieu_evenement' => 'Genève',
            'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'rayon_localisation_evenement' => 500,
            'latitude_evenement' => 46.2044,
            'longitude_evenement' => 6.1432,
            'organisateur_evenement' => 'BeneRun',
            'nombre_benevoles_requis' => 20,
            'est_publie_evenement' => true,
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
        ]);

        $response = $this->postJson('/api/missions', [
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $responsable->id_utilisateur,
            'titre_mission' => 'Point B',
            'type_mission' => 'logistique',
            'description_mission' => 'Mission test',
            'date_mission' => '2026-05-01',
            'heure_debut_mission' => '09:00',
            'heure_fin_mission' => '10:00',
            'lieu_mission' => 'Dans le périmètre',
            'google_maps_url_mission' => 'https://www.google.com/maps?q=46.2050,6.1430',
            'nombre_benevoles_max' => 5,
            'nombre_benevoles_backup' => 1,
            'visibilite_mission' => 'publique',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('mission.google_maps_url_mission', 'https://www.google.com/maps?q=46.2050,6.1430');
    }
}