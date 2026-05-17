<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_update_location(): void
    {
        $this->postJson('/api/location', [
            'latitude' => 46.2044,
            'longitude' => 6.1432,
        ])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_user_can_update_and_get_location(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/location', [
                'latitude' => 46.2044,
                'longitude' => 6.1432,
                'accuracy' => 12.5,
            ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Localisation mise à jour')
            ->assertJsonPath('location.id_utilisateur', $user->id_utilisateur)
            ->assertJsonPath('location.latitude', 46.2044)
            ->assertJsonPath('location.longitude', 6.1432);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/location')
            ->assertStatus(200)
            ->assertJsonPath('location.id_utilisateur', $user->id_utilisateur)
            ->assertJsonPath('location.latitude', 46.2044)
            ->assertJsonPath('location.longitude', 6.1432)
            ->assertJsonPath('location.accuracy', 12.5);
    }

    public function test_update_location_validates_coordinates(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/location', [
                'latitude' => 123.456,
                'longitude' => 190,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_get_location_returns_404_when_no_location_exists(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/location')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Aucune localisation trouvée')
            ->assertJsonPath('location', null);
    }

    public function test_user_can_delete_location(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/location', [
                'latitude' => 46.2044,
                'longitude' => 6.1432,
            ])
            ->assertStatus(200);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/location')
            ->assertStatus(200)
            ->assertJsonPath('message', 'Localisation supprimée');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/location')
            ->assertStatus(404);
    }

    public function test_user_can_get_other_active_locations_excluding_self(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();

        $this->actingAs($userA, 'sanctum')
            ->postJson('/api/location', [
                'latitude' => 46.2044,
                'longitude' => 6.1432,
            ])
            ->assertStatus(200);

        $this->actingAs($userB, 'sanctum')
            ->postJson('/api/location', [
                'latitude' => 46.22,
                'longitude' => 6.15,
            ])
            ->assertStatus(200);

        $this->actingAs($userC, 'sanctum')
            ->postJson('/api/location', [
                'latitude' => 46.18,
                'longitude' => 6.10,
            ])
            ->assertStatus(200);

        $response = $this->actingAs($userA, 'sanctum')
            ->getJson('/api/locations')
            ->assertStatus(200)
            ->assertJsonPath('count', 2);

        $ids = collect($response->json('locations'))->pluck('id_utilisateur')->all();
        $this->assertContains($userB->id_utilisateur, $ids);
        $this->assertContains($userC->id_utilisateur, $ids);
        $this->assertNotContains($userA->id_utilisateur, $ids);
    }
}
