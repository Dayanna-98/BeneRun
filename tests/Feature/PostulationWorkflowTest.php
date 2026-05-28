<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\ChatConversation;
use App\Models\Evenement;
use App\Models\Mission;
use App\Models\Postulation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostulationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_apply_to_mission_and_event_is_hydrated(): void
    {
        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-06-15', '08:00', '10:00');

        $response = $this->actingAsUser($user)->postJson("/api/missions/{$mission->id_mission}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('postulation.id_mission', $mission->id_mission)
            ->assertJsonPath('postulation.id_evenement', $mission->id_evenement)
            ->assertJsonPath('postulation.id_utilisateur', $user->id_utilisateur)
            ->assertJsonPath('postulation.statut_postulation', 'accepte');

        $this->assertDatabaseHas('postulations', [
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'accepte',
        ]);

        $this->assertDatabaseHas('affectations', [
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_affectation' => 'assigne',
        ]);

        $this->assertDatabaseHas('chat_conversations', [
            'type_conversation' => 'group',
            'id_mission' => $mission->id_mission,
        ]);

        $conversationId = (int) ChatConversation::query()
            ->where('type_conversation', 'group')
            ->where('id_mission', $mission->id_mission)
            ->value('id_chat_conversation');

        $this->assertGreaterThan(0, $conversationId);

        $this->assertDatabaseHas('chat_conversation_participants', [
            'id_chat_conversation' => $conversationId,
            'id_utilisateur' => $user->id_utilisateur,
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

        $this->actingAsUser($user)->postJson("/api/missions/{$mission->id_mission}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Cet utilisateur est déjà inscrit ou a déjà postulé à cette mission.');
    }

    public function test_cannot_apply_when_mission_registration_is_closed(): void
    {
        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-06-17', '10:00', '12:00', false);

        $this->actingAsUser($user)->postJson("/api/missions/{$mission->id_mission}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Les inscriptions sont fermées pour cette mission.');
    }

    public function test_accepting_application_creates_assignment(): void
    {
        $user = User::factory()->create();
        $mission = $this->createMissionForDate('2026-06-18', '07:00', '09:00');

        $response = $this->actingAsUser($user)->postJson('/api/postulations', [
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

        $this->actingAsUser($user)->postJson("/api/evenements/{$event->id_evenement}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'accepte',
        ])
            ->assertStatus(201)
            ->assertJsonPath('postulation.id_evenement', $event->id_evenement)
            ->assertJsonPath('postulation.statut_postulation', 'en_attente');
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

        $this->actingAsUser($user)->postJson("/api/evenements/{$eventB->id_evenement}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', "Inscription impossible: l'utilisateur est déjà en liste d'attente sur un autre événement au même créneau.");
    }

    public function test_same_event_waiting_list_duplicate_returns_conflict(): void
    {
        $user = User::factory()->create();
        $event = $this->createEventForDateRange('2026-06-25', '2026-06-26', '08:00', '17:00');

        Postulation::create([
            'id_mission' => null,
            'id_evenement' => $event->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'en_attente',
        ]);

        $this->actingAsUser($user)->postJson("/api/evenements/{$event->id_evenement}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', "Cet utilisateur est déjà en liste d'attente ou déjà dispatché sur cet événement.");
    }

    public function test_cannot_apply_to_two_missions_in_same_event(): void
    {
        $user = User::factory()->create();
        $event = $this->createEventForDateRange('2026-06-27', '2026-06-27', '06:00', '23:00', 200);
        $responsableA = User::factory()->create();
        $responsableB = User::factory()->create();

        $missionA = Mission::factory()->create([
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $responsableA->id_utilisateur,
            'date_mission' => '2026-06-27',
            'heure_debut_mission' => '09:00',
            'heure_fin_mission' => '10:00',
            'inscription_requise' => true,
            'nombre_benevoles_max' => 10,
        ]);

        $missionB = Mission::factory()->create([
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $responsableB->id_utilisateur,
            'date_mission' => '2026-06-27',
            'heure_debut_mission' => '11:00',
            'heure_fin_mission' => '12:00',
            'inscription_requise' => true,
            'nombre_benevoles_max' => 10,
        ]);

        Postulation::create([
            'id_mission' => $missionA->id_mission,
            'id_evenement' => $event->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'en_attente',
        ]);

        $this->actingAsUser($user)->postJson("/api/missions/{$missionB->id_mission}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', "Vous êtes déjà inscrit à une mission de cet événement. Il n'est pas possible de participer à deux missions du même événement.");
    }

    public function test_waiting_list_on_non_overlapping_event_is_allowed(): void
    {
        $user = User::factory()->create();
        $eventA = $this->createEventForDateRange('2026-07-01', '2026-07-01', '08:00', '12:00');
        $eventB = $this->createEventForDateRange('2026-07-02', '2026-07-02', '08:00', '12:00');

        Postulation::create([
            'id_mission' => null,
            'id_evenement' => $eventA->id_evenement,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_postulation' => 'en_attente',
        ]);

        $this->actingAsUser($user)->postJson("/api/evenements/{$eventB->id_evenement}/inscriptions", [
            'id_utilisateur' => $user->id_utilisateur,
        ])
            ->assertStatus(201)
            ->assertJsonPath('postulation.id_evenement', $eventB->id_evenement)
            ->assertJsonPath('postulation.statut_postulation', 'en_attente');
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

        $this->actingAsUser($user)->postJson("/api/missions/{$newMission->id_mission}/inscriptions", [
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

        $this->actingAsAdmin()->deleteJson("/api/postulations/{$postulation->id_postulation}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Cette inscription est verrouillée: une fois assigné à une mission, le bénévole ne peut pas se désinscrire.');
    }

    private function actingAsUser(User $user): self
    {
        Sanctum::actingAs($user);

        return $this;
    }

    private function actingAsAdmin(): self
    {
        $admin = User::factory()->create([
            'role_utilisateur' => 'admin',
        ]);

        Sanctum::actingAs($admin);

        return $this;
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
