<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\MissionPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionPositionApiTest extends TestCase
{
    use RefreshDatabase;

    private function assignActiveParticipant(Mission $mission, User $user): void
    {
        Affectation::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'statut_affectation' => 'assigne',
        ]);
    }

    public function test_store_position_creates_record(): void
    {
        $mission = Mission::factory()->create();
        $user = User::factory()->create();
        $this->assignActiveParticipant($mission, $user);

        $this->actingAs($user, 'sanctum')->postJson("/api/missions/{$mission->id_mission}/positions", [
            'latitude' => 46.2044,
            'longitude' => 6.1432,
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Position mise à jour');

        $this->assertDatabaseHas('mission_positions', [
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
        ]);
    }

    public function test_store_position_updates_existing_record_for_same_user_and_mission(): void
    {
        $mission = Mission::factory()->create();
        $user = User::factory()->create();
        $this->assignActiveParticipant($mission, $user);

        MissionPosition::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $user->id_utilisateur,
            'latitude' => 46.20,
            'longitude' => 6.14,
        ]);

        $this->actingAs($user, 'sanctum')->postJson("/api/missions/{$mission->id_mission}/positions", [
            'latitude' => 46.25,
            'longitude' => 6.18,
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Position mise à jour');

        $this->assertEquals(
            1,
            MissionPosition::where('id_mission', $mission->id_mission)
                ->where('id_utilisateur', $user->id_utilisateur)
                ->count()
        );

        $position = MissionPosition::where('id_mission', $mission->id_mission)
            ->where('id_utilisateur', $user->id_utilisateur)
            ->firstOrFail();

        $this->assertSame('46.2500000', $position->latitude);
        $this->assertSame('6.1800000', $position->longitude);
    }

    public function test_store_position_validates_payload(): void
    {
        $mission = Mission::factory()->create();
        $user = User::factory()->create();
        $this->assignActiveParticipant($mission, $user);

        $this->actingAs($user, 'sanctum')->postJson("/api/missions/{$mission->id_mission}/positions", [
            'latitude' => 200,
            'longitude' => -400,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_index_returns_only_recent_positions_for_mission(): void
    {
        $mission = Mission::factory()->create();
        $otherMission = Mission::factory()->create();
        $userRecent = User::factory()->create([
            'prenom_utilisateur' => 'Alice',
            'nom_utilisateur' => 'Martin',
        ]);
        $userOld = User::factory()->create();
        $userOtherMission = User::factory()->create();
        $this->assignActiveParticipant($mission, $userRecent);
        $this->assignActiveParticipant($mission, $userOld);
        $this->assignActiveParticipant($otherMission, $userOtherMission);

        $recent = MissionPosition::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $userRecent->id_utilisateur,
            'latitude' => 46.2044,
            'longitude' => 6.1432,
        ]);

        $old = MissionPosition::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $userOld->id_utilisateur,
            'latitude' => 46.3000,
            'longitude' => 6.2000,
        ]);

        $other = MissionPosition::create([
            'id_mission' => $otherMission->id_mission,
            'id_utilisateur' => $userOtherMission->id_utilisateur,
            'latitude' => 46.5000,
            'longitude' => 6.5000,
        ]);

        $old->updated_at = now()->subMinutes(31);
        $old->save();

        $other->updated_at = now();
        $other->save();

        $recent->updated_at = now()->subMinutes(5);
        $recent->save();

        $response = $this->actingAs($userRecent, 'sanctum')->getJson("/api/missions/{$mission->id_mission}/positions")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.id_utilisateur', $userRecent->id_utilisateur)
            ->assertJsonPath('0.name', 'Alice Martin');

        $this->assertSame(46.2044, $response->json('0.latitude'));
        $this->assertSame(6.1432, $response->json('0.longitude'));
    }

    public function test_positions_endpoints_require_authentication(): void
    {
        $mission = Mission::factory()->create();

        $this->getJson("/api/missions/{$mission->id_mission}/positions")
            ->assertStatus(401);

        $this->postJson("/api/missions/{$mission->id_mission}/positions", [
            'latitude' => 46.2,
            'longitude' => 6.1,
        ])->assertStatus(401);
    }

    public function test_non_participant_cannot_read_or_write_positions(): void
    {
        $mission = Mission::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->getJson("/api/missions/{$mission->id_mission}/positions")
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')->postJson("/api/missions/{$mission->id_mission}/positions", [
            'latitude' => 46.2,
            'longitude' => 6.1,
        ])->assertStatus(403);
    }

    public function test_store_rejects_user_spoofing_payload_field(): void
    {
        $mission = Mission::factory()->create();
        $user = User::factory()->create();
        $this->assignActiveParticipant($mission, $user);

        $this->actingAs($user, 'sanctum')->postJson("/api/missions/{$mission->id_mission}/positions", [
            'id_utilisateur' => 999999,
            'latitude' => 46.2044,
            'longitude' => 6.1432,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['id_utilisateur']);
    }

    public function test_mission_responsible_can_read_and_write_positions_without_affectation(): void
    {
        $responsible = User::factory()->create([
            'prenom_utilisateur' => 'Responsable',
            'nom_utilisateur' => 'Mission',
        ]);
        $participant = User::factory()->create();

        $mission = Mission::factory()->create([
            'responsable_utilisateur_id' => $responsible->id_utilisateur,
        ]);

        $this->assignActiveParticipant($mission, $participant);

        MissionPosition::create([
            'id_mission' => $mission->id_mission,
            'id_utilisateur' => $participant->id_utilisateur,
            'latitude' => 46.2044,
            'longitude' => 6.1432,
        ]);

        $this->actingAs($responsible, 'sanctum')->postJson("/api/missions/{$mission->id_mission}/positions", [
            'latitude' => 46.21,
            'longitude' => 6.15,
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Position mise à jour');

        $response = $this->actingAs($responsible, 'sanctum')
            ->getJson("/api/missions/{$mission->id_mission}/positions")
            ->assertStatus(200);

        $ids = collect($response->json())->pluck('id_utilisateur')->all();
        $this->assertContains($responsible->id_utilisateur, $ids);
        $this->assertContains($participant->id_utilisateur, $ids);
    }
}
