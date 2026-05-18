<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\MissionEmergencyMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionEmergencyMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_send_urgence_on_running_mission(): void
    {
        $participant = User::factory()->create(['role_utilisateur' => 'bénévole']);
        $mission = Mission::factory()->create(['statut_mission' => 'En cours']);

        Affectation::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $participant->id_utilisateur,
            'statut_affectation' => 'assigne',
            'est_responsable' => false,
            'date_affectation' => now(),
        ]);

        $this->actingAs($participant, 'sanctum')
            ->postJson("/api/missions/{$mission->id_mission}/urgences", [
                'id_utilisateur' => $participant->id_utilisateur,
                'categorie_urgence' => 'medicale',
                'message_urgence' => 'Bénévole blessé au point A',
            ])
            ->assertStatus(201)
            ->assertJsonPath('message', "Message d'urgence transmis aux superadmins.")
            ->assertJsonPath('urgence.id_mission', $mission->id_mission)
            ->assertJsonPath('urgence.id_emetteur_utilisateur', $participant->id_utilisateur);
    }

    public function test_non_participant_cannot_send_urgence(): void
    {
        $user = User::factory()->create(['role_utilisateur' => 'bénévole']);
        $mission = Mission::factory()->create(['statut_mission' => 'En cours']);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/missions/{$mission->id_mission}/urgences", [
                'id_utilisateur' => $user->id_utilisateur,
                'message_urgence' => 'Alerte',
            ])
            ->assertStatus(403)
            ->assertJsonPath('message', 'Seuls les participants de la mission peuvent envoyer une urgence.');
    }

    public function test_urgence_requires_running_mission(): void
    {
        $participant = User::factory()->create();
        $mission = Mission::factory()->create(['statut_mission' => 'À venir']);

        Affectation::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $participant->id_utilisateur,
            'statut_affectation' => 'assigne',
            'est_responsable' => false,
            'date_affectation' => now(),
        ]);

        $this->actingAs($participant, 'sanctum')
            ->postJson("/api/missions/{$mission->id_mission}/urgences", [
                'id_utilisateur' => $participant->id_utilisateur,
                'message_urgence' => 'Urgence avant mission',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Les urgences ne peuvent être envoyées que pendant une mission en cours.');
    }

    public function test_urgence_sender_must_match_authenticated_user(): void
    {
        $actor = User::factory()->create();
        $other = User::factory()->create();
        $mission = Mission::factory()->create(['statut_mission' => 'En cours']);

        Affectation::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $actor->id_utilisateur,
            'statut_affectation' => 'assigne',
            'est_responsable' => false,
            'date_affectation' => now(),
        ]);

        $this->actingAs($actor, 'sanctum')
            ->postJson("/api/missions/{$mission->id_mission}/urgences", [
                'id_utilisateur' => $other->id_utilisateur,
                'message_urgence' => 'Spoof utilisateur',
            ])
            ->assertStatus(403)
            ->assertJsonPath('message', 'Action non autorisée pour cet utilisateur.');
    }

    public function test_only_superadmin_can_list_urgences(): void
    {
        $normal = User::factory()->create(['role_utilisateur' => 'admin']);

        $this->actingAs($normal, 'sanctum')
            ->getJson('/api/urgences')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Accès réservé aux superadmins.');
    }

    public function test_superadmin_can_view_mark_and_take_ownership(): void
    {
        $superadmin = User::factory()->create(['role_utilisateur' => 'superadmin']);
        $participant = User::factory()->create();
        $mission = Mission::factory()->create(['statut_mission' => 'En cours']);

        Affectation::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $participant->id_utilisateur,
            'statut_affectation' => 'assigne',
            'est_responsable' => false,
            'date_affectation' => now(),
        ]);

        $urgenceId = (int) $this->actingAs($participant, 'sanctum')
            ->postJson("/api/missions/{$mission->id_mission}/urgences", [
                'id_utilisateur' => $participant->id_utilisateur,
                'categorie_urgence' => 'logistique',
                'message_urgence' => 'Manque de matériel',
            ])
            ->assertStatus(201)
            ->json('urgence.id_mission_emergency_message');

        $this->actingAs($superadmin, 'sanctum')
            ->getJson('/api/urgences')
            ->assertStatus(200)
            ->assertJsonFragment(['id_mission_emergency_message' => $urgenceId]);

        $this->actingAs($superadmin, 'sanctum')
            ->postJson("/api/urgences/{$urgenceId}/consultation", [
                'id_utilisateur' => $superadmin->id_utilisateur,
            ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Consultation enregistrée.');

        $this->actingAs($superadmin, 'sanctum')
            ->postJson("/api/urgences/{$urgenceId}/prise-en-charge", [
                'id_utilisateur' => $superadmin->id_utilisateur,
            ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Urgence prise en charge.')
            ->assertJsonPath('urgence.pris_en_charge_par_utilisateur_id', $superadmin->id_utilisateur);
    }

    public function test_take_ownership_conflicts_when_already_owned_by_other_superadmin(): void
    {
        $owner = User::factory()->create(['role_utilisateur' => 'superadmin']);
        $other = User::factory()->create(['role_utilisateur' => 'superadmin']);
        $participant = User::factory()->create();
        $mission = Mission::factory()->create(['statut_mission' => 'En cours']);

        Affectation::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $participant->id_utilisateur,
            'statut_affectation' => 'assigne',
            'est_responsable' => false,
            'date_affectation' => now(),
        ]);

        $urgence = MissionEmergencyMessage::create([
            'id_mission' => $mission->id_mission,
            'id_evenement' => $mission->id_evenement,
            'id_emetteur_utilisateur' => $participant->id_utilisateur,
            'categorie_urgence' => 'medicale',
            'message_urgence' => 'Besoin d\'aide',
            'pris_en_charge_par_utilisateur_id' => $owner->id_utilisateur,
            'pris_en_charge_le' => now(),
        ]);

        $this->actingAs($other, 'sanctum')
            ->postJson("/api/urgences/{$urgence->id_mission_emergency_message}/prise-en-charge", [
                'id_utilisateur' => $other->id_utilisateur,
            ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Cette urgence est déjà prise en charge par un autre superadmin.');
    }

    public function test_superadmin_endpoints_return_404_for_missing_urgence(): void
    {
        $superadmin = User::factory()->create(['role_utilisateur' => 'superadmin']);

        $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/urgences/999999/consultation', [
                'id_utilisateur' => $superadmin->id_utilisateur,
            ])
            ->assertStatus(404)
            ->assertJsonPath('message', "Message d'urgence introuvable.");

        $this->actingAs($superadmin, 'sanctum')
            ->postJson('/api/urgences/999999/prise-en-charge', [
                'id_utilisateur' => $superadmin->id_utilisateur,
            ])
            ->assertStatus(404)
            ->assertJsonPath('message', "Message d'urgence introuvable.");
    }
}
