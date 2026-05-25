<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\NotificationRead;
use App\Models\Postulation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role_utilisateur' => 'superadmin',
        ]);

        $this->normalUser = User::factory()->create([
            'role_utilisateur' => 'bénévole',
        ]);
    }

    public function test_notifications_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/notifications');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_authenticated_superadmin_sees_pending_postulations(): void
    {
        $mission = Mission::factory()->create();
        Postulation::factory()->count(3)->create([
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'en_attente',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonPath('count', 3)
            ->assertJsonPath('unread_count', 3)
            ->assertJsonPath('data.0.type', 'pending_postulation')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'level', 'title', 'is_read', 'read_at'],
                ],
                'count',
                'unread_count',
            ]);
    }

    public function test_benevole_does_not_see_global_pending_postulations_but_sees_own_updates(): void
    {
        $mission = Mission::factory()->create();

        Postulation::factory()->count(2)->create([
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'en_attente',
        ]);

        Postulation::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'accepte',
        ]);

        Affectation::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
            'id_mission' => $mission->id_mission,
            'statut_affectation' => 'assigne',
        ]);

        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertEqualsCanonicalizing(
            ['affectation', 'postulation_status'],
            collect($response->json('data'))->pluck('type')->all()
        );
    }

    public function test_mark_read_requires_authentication(): void
    {
        $response = $this->postJson('/api/notifications/read', [
            'ids' => ['pending-postulation-1'],
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_authenticated_user_can_mark_notifications_as_read(): void
    {
        $mission = Mission::factory()->create();

        Postulation::factory()->create([
            'id_mission' => $mission->id_mission,
            'statut_postulation' => 'en_attente',
        ]);

        $feed = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/notifications')
            ->assertOk();

        $notificationId = (string) $feed->json('data.0.id');

        $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/notifications/read', [
                'ids' => [$notificationId],
            ])
            ->assertOk()
            ->assertJsonPath('count', 1);

        $this->assertDatabaseHas('notification_reads', [
            'id_utilisateur' => $this->superAdmin->id_utilisateur,
            'notification_key' => $notificationId,
        ]);

        $stored = NotificationRead::query()
            ->where('id_utilisateur', $this->superAdmin->id_utilisateur)
            ->where('notification_key', $notificationId)
            ->first();

        $this->assertNotNull($stored?->read_at);

        $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.id', $notificationId)
            ->assertJsonPath('data.0.is_read', true)
            ->assertJsonPath('unread_count', 0);
    }
}
