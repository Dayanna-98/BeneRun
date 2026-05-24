<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Mission;
use App\Models\Postulation;
use App\Models\User;
use Tests\TestCase;

class NotificationFeedTest extends TestCase
{
    public function test_notifications_feed_requires_authentication(): void
    {
        $this->getJson('/api/notifications')
            ->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_responsable_receives_pending_postulation_notifications(): void
    {
        $responsable = User::factory()->create([
            'role_utilisateur' => 'responsable',
        ]);

        $applicant = User::factory()->create([
            'role_utilisateur' => 'bénévole',
        ]);

        $event = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $responsable->id_utilisateur,
        ]);

        $mission = Mission::factory()->create([
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $responsable->id_utilisateur,
        ]);

        Postulation::create([
            'id_mission' => $mission->id_mission,
            'id_evenement' => $event->id_evenement,
            'id_utilisateur' => $applicant->id_utilisateur,
            'statut_postulation' => 'en_attente',
            'date_postulation' => now(),
        ]);

        $this->actingAs($responsable, 'sanctum')
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('data.0.type', 'pending_postulation')
            ->assertJsonPath('data.0.is_read', false);
    }

    public function test_user_can_mark_notifications_as_read(): void
    {
        $manager = User::factory()->create([
            'role_utilisateur' => 'admin',
        ]);

        $applicant = User::factory()->create([
            'role_utilisateur' => 'bénévole',
        ]);

        $event = Evenement::factory()->create([
            'cree_par_utilisateur_id' => $manager->id_utilisateur,
        ]);

        $mission = Mission::factory()->create([
            'id_evenement' => $event->id_evenement,
            'responsable_utilisateur_id' => $manager->id_utilisateur,
        ]);

        Postulation::create([
            'id_mission' => $mission->id_mission,
            'id_evenement' => $event->id_evenement,
            'id_utilisateur' => $applicant->id_utilisateur,
            'statut_postulation' => 'en_attente',
            'date_postulation' => now(),
        ]);

        $firstFeed = $this->actingAs($manager, 'sanctum')
            ->getJson('/api/notifications')
            ->assertOk();

        $notificationId = (string) $firstFeed->json('data.0.id');

        $this->actingAs($manager, 'sanctum')
            ->postJson('/api/notifications/read', [
                'ids' => [$notificationId],
            ])
            ->assertOk()
            ->assertJsonPath('count', 1);

        $this->assertDatabaseHas('notification_reads', [
            'id_utilisateur' => $manager->id_utilisateur,
            'notification_key' => $notificationId,
        ]);

        $this->actingAs($manager, 'sanctum')
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.id', $notificationId)
            ->assertJsonPath('data.0.is_read', true)
            ->assertJsonPath('unread_count', 0);
    }
}
