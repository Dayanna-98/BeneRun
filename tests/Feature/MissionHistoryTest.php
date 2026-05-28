<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Evenement;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_get_mission_history(): void
    {
        $this->getJson('/api/me/missions/history')
            ->assertStatus(401);
    }

    public function test_user_history_includes_past_participations_and_responsibilities(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Evenement::factory()->create();

        $pastParticipantMission = Mission::factory()->create([
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $otherUser->id_utilisateur,
            'date_mission' => now()->subDays(3)->toDateString(),
            'heure_debut_mission' => '08:00',
            'heure_fin_mission' => '10:00',
            'statut_mission' => 'À venir',
        ]);

        Affectation::factory()->create([
            'id_mission' => $pastParticipantMission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_affectation' => 'present',
            'est_responsable' => false,
        ]);

        $pastResponsibleMission = Mission::factory()->create([
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $user->id_utilisateur,
            'date_mission' => now()->subDays(2)->toDateString(),
            'heure_debut_mission' => '09:00',
            'heure_fin_mission' => '11:00',
            'statut_mission' => 'À venir',
        ]);

        $futureMission = Mission::factory()->create([
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $otherUser->id_utilisateur,
            'date_mission' => now()->addDays(2)->toDateString(),
            'heure_debut_mission' => '09:00',
            'heure_fin_mission' => '11:00',
            'statut_mission' => 'À venir',
        ]);

        Affectation::factory()->create([
            'id_mission' => $futureMission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_affectation' => 'confirme',
            'est_responsable' => false,
        ]);

        $cancelledParticipation = Mission::factory()->create([
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $otherUser->id_utilisateur,
            'date_mission' => now()->subDays(4)->toDateString(),
            'heure_debut_mission' => '09:00',
            'heure_fin_mission' => '11:00',
            'statut_mission' => 'À venir',
        ]);

        Affectation::factory()->create([
            'id_mission' => $cancelledParticipation->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_affectation' => 'annule',
            'est_responsable' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/missions/history')
            ->assertStatus(200)
            ->assertJsonPath('count', 2);

        $ids = collect($response->json('missions'))->pluck('id_mission')->all();

        $this->assertContains($pastParticipantMission->id_mission, $ids);
        $this->assertContains($pastResponsibleMission->id_mission, $ids);
        $this->assertNotContains($futureMission->id_mission, $ids);
        $this->assertNotContains($cancelledParticipation->id_mission, $ids);

        $pastResponsiblePayload = collect($response->json('missions'))
            ->firstWhere('id_mission', $pastResponsibleMission->id_mission);

        $this->assertSame('responsable', $pastResponsiblePayload['my_role']);
    }
}
