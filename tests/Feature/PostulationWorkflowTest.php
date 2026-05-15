<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Evenement;
use App\Models\Mission;
use App\Models\Postulation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostulationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_apply_to_mission_and_event_is_hydrated(): void
    {
        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-06-15', '08:00', '10:00');

        $response = $this->postJson("/api/missions/{$mission->id_mission}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('postulation.id_mission', $mission->id_mission)
            ->assertJsonPath('postulation.id_evenement', $mission->id_evenement)
            ->assertJsonPath('postulation.id_utilisateur', $user->id_utilisateur)
            ->assertJsonPath('postulation.statut_postulation', 'en_attente');

        $this->assertDatabaseHas('postulations', [
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'en_attente',
        ]);
    }

    public function test_duplicate_mission_application_returns_conflict(): void
    {
        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-06-16', '09:00', '11:00');

        Postulation::create([
            'id_mission' => $mission->id_mission,
            'id_evenement' => $mission->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'en_attente',
        ]);

        $this->postJson("/api/missions/{$mission->id_mission}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Cet utilisateur est déjà inscrit ou a déjà postulé à cette mission.');
    }

    public function test_cannot_apply_when_mission_registration_is_closed(): void
    {
        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-06-17', '10:00', '12:00', false);

        $this->postJson("/api/missions/{$mission->id_mission}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Les inscriptions sont fermées pour cette mission.');
    }

    public function test_accepting_application_creates_assignment(): void
    {
        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-06-18', '07:00', '09:00');

        $response = $this->postJson('/api/postulations', [
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'accepte',
        ]);

        $postulationId = (int) $response->json('postulation.id_postulation');

        $response
            ->assertStatus(201)
            ->assertJsonPath('postulation.statut_postulation', 'accepte');

        $this->assertDatabaseHas('affectations', [
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_affectation' => 'assigne',
            'est_responsable' => 0,
        ]);

        $this->assertGreaterThan(0, $postulationId);
    }

    public function test_event_waiting_list_with_accepted_status_is_rejected(): void
    {
        $user = User::factory()->create();
        $event = $this->createEventForDateRange('2026-06-19', '2026-06-20');

        $this->postJson("/api/evenements/{$event->id_evenement}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'accepte',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Une postulation acceptée doit être rattachée à une mission.');
    }

    public function test_waiting_list_conflict_between_overlapping_events_returns_conflict(): void
    {
        $user = User::factory()->create();
        $eventA = $this->createEventForDateRange('2026-06-21', '2026-06-22', '08:00', '17:00');
        $eventB = $this->createEventForDateRange('2026-06-21', '2026-06-22', '09:00', '18:00');

        Postulation::create([
            'id_mission' => null,
            'id_evenement' => $eventA->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'en_attente',
        ]);

        $this->postJson("/api/evenements/{$eventB->id_evenement}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', "Inscription impossible: l'utilisateur est déjà en liste d'attente sur un autre événement au même créneau.");
    }

    public function test_locked_assignment_conflict_blocks_new_application(): void
    {
        $user = User::factory()->create();

        $existingMission = $this->createMissionForDate('2026-06-23', '10:00', '12:00');
        Affectation::create([
            'id_mission' => $existingMission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_affectation' => 'assigne',
            'est_responsable' => false,
            'date_affectation' => now(),
        ]);

        $newMission = $this->createMissionForDate('2026-06-23', '11:00', '13:00');

        $this->postJson("/api/missions/{$newMission->id_mission}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', "Inscription impossible: l'utilisateur est déjà affecté sur une mission pendant ce créneau.");
    }

    public function test_locked_postulation_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-06-24', '14:00', '16:00');

        $postulation = Postulation::create([
            'id_mission' => $mission->id_mission,
            'id_evenement' => $mission->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'accepte',
        ]);

        $this->deleteJson("/api/postulations/{$postulation->id_postulation}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Cette inscription est verrouillée: une fois assigné à une mission, le bénévole ne peut pas se désinscrire.');
    }

    private function createMissionForDate(string $date, string $start, string $end, bool $registrationRequired = true): Mission
    {
        $event = $this->createEventForDateRange($date, $date, '06:00', '23:00', 200);
        $responsable = User::factory()->create();

        return Mission::factory()->create([
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $responsable->id_utilisateur,
            'date_mission' => $date,
            'heure_debut_mission' => $start,
            'heure_fin_mission' => $end,
            'inscription_requise' => $registrationRequired,
            'nombre_benevoles_max' => 10,
            'statut_mission' => 'À venir',
        ]);
    }

    private function createEventForDateRange(
        string $startDate,
        string $endDate,
        string $startTime = '08:00',
        string $endTime = '18:00',
        int $requiredVolunteers = 100
    ): Evenement {
        $creator = User::factory()->create();

        return Evenement::factory()->create([
            'date_debut_evenement' => $startDate,
            'date_fin_evenement' => $endDate,
            'heure_debut_evenement' => $startTime,
            'heure_fin_evenement' => $endTime,
            'nombre_benevoles_requis' => $requiredVolunteers,
            'cree_par_utilisateur_id' => $creator->id_utilisateur,
            'mode_localisation_evenement' => 'manual',
            'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'rayon_localisation_evenement' => 500,
            'est_publie_evenement' => true,
        ]);
    }
}
