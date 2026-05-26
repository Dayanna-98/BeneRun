<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Evenement;
use App\Models\Mission;
use App\Models\Postulation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostulationUpdateDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_status_to_accepte_creates_assignment_and_decision_date(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-07-01', '09:00', '11:00');

        $postulation = Postulation::create([
            'id_mission' => $mission->id_mission,
            'id_evenement' => $mission->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'en_attente',
        ]);

        $this->patchJson("/api/postulations/{$postulation->id_postulation}", [
            'statut_postulation' => 'accepte',
        ])
            ->assertStatus(200)
            ->assertJsonPath('postulation.statut_postulation', 'accepte');

        $updated = Postulation::findOrFail($postulation->id_postulation);
        $this->assertNotNull($updated->date_decision);

        $this->assertDatabaseHas('affectations', [
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_affectation' => 'assigne',
            'est_responsable' => 0,
        ]);
    }

    public function test_update_status_to_annule_marks_existing_assignment_as_annule(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-07-02', '10:00', '12:00');

        $postulation = Postulation::create([
            'id_mission' => $mission->id_mission,
            'id_evenement' => $mission->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'accepte',
        ]);

        Affectation::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_affectation' => 'assigne',
            'est_responsable' => false,
            'date_affectation' => now(),
        ]);

        $this->patchJson("/api/postulations/{$postulation->id_postulation}", [
            'statut_postulation' => 'annule',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Cette inscription est verrouillée: une fois assigné à une mission, le bénévole ne peut pas changer de mission ni se désinscrire.');
    }

    public function test_locked_postulation_cannot_change_mission(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();
        $missionA = $this->createMissionForDate('2026-07-03', '08:00', '10:00');
        $missionB = $this->createMissionForDate('2026-07-04', '08:00', '10:00');

        $postulation = Postulation::create([
            'id_mission' => $missionA->id_mission,
            'id_evenement' => $missionA->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'accepte',
        ]);

        $this->patchJson("/api/postulations/{$postulation->id_postulation}", [
            'id_mission' => $missionB->id_mission,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Cette inscription est verrouillée: une fois assigné à une mission, le bénévole ne peut pas changer de mission ni se désinscrire.');
    }

    public function test_non_locked_postulation_can_be_cancelled(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-07-05', '13:00', '15:00');

        $postulation = Postulation::create([
            'id_mission' => $mission->id_mission,
            'id_evenement' => $mission->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'en_attente',
        ]);

        $this->patchJson("/api/postulations/{$postulation->id_postulation}", [
            'statut_postulation' => 'annule',
        ])
            ->assertStatus(200)
            ->assertJsonPath('postulation.statut_postulation', 'annule');

        $updated = Postulation::findOrFail($postulation->id_postulation);
        $this->assertNotNull($updated->date_annulation);
    }

    public function test_unlocked_postulation_can_be_deleted(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-07-06', '14:00', '16:00');

        $postulation = Postulation::create([
            'id_mission' => $mission->id_mission,
            'id_evenement' => $mission->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'en_attente',
        ]);

        $this->deleteJson("/api/postulations/{$postulation->id_postulation}")
            ->assertStatus(200)
            ->assertJsonPath('message', 'Postulation supprimée');

        $this->assertDatabaseMissing('postulations', [
            'id_postulation' => $postulation->id_postulation,
        ]);
    }

    public function test_show_nonexistent_postulation_returns_404(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/postulations/999999')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Postulation inexistante');
    }

    private function actingAsAdmin(): void
    {
        $admin = User::factory()->create([
            'role_utilisateur' => 'admin',
        ]);

        Sanctum::actingAs($admin);
    }

    private function createMissionForDate(string $date, string $start, string $end): Mission
    {
        $event = $this->createEventForDateRange($date, $date);
        $responsable = User::factory()->create();

        return Mission::factory()->create([
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $responsable->id_utilisateur,
            'date_mission' => $date,
            'heure_debut_mission' => $start,
            'heure_fin_mission' => $end,
            'inscription_requise' => true,
            'statut_mission' => 'À venir',
            'nombre_benevoles_max' => 15,
        ]);
    }

    private function createEventForDateRange(string $startDate, string $endDate): Evenement
    {
        $creator = User::factory()->create();

        return Evenement::factory()->create([
            'date_debut_evenement' => $startDate,
            'date_fin_evenement' => $endDate,
            'heure_debut_evenement' => '07:00',
            'heure_fin_evenement' => '21:00',
            'nombre_benevoles_requis' => 150,
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'mode_localisation_evenement' => 'manual',
            'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'rayon_localisation_evenement' => 500,
            'est_publie_evenement' => true,
        ]);
    }
}
