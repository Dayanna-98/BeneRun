<?php

namespace Tests\Feature;

use App\Models\Mission;
use App\Models\User;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    private User $user;

    private Mission $mission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role_utilisateur' => 'bénévole']);
        $this->mission = Mission::factory()->create();
    }

    public function test_user_can_add_mission_to_favorites(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/favorites/{$this->mission->id_mission}")
            ->assertStatus(201)
            ->assertJson(['message' => 'Mission ajoutée aux favoris']);
    }

    public function test_user_can_view_favorite_missions(): void
    {
        $this->user->missions_favorites()->attach($this->mission->id_mission);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/favorites')
            ->assertStatus(200);
    }

    public function test_user_can_remove_mission_from_favorites(): void
    {
        $this->user->missions_favorites()->attach($this->mission->id_mission);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/favorites/{$this->mission->id_mission}")
            ->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_favorites(): void
    {
        $this->getJson('/api/favorites')
            ->assertStatus(401);
    }

    public function test_cannot_add_nonexistent_mission_to_favorites(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/favorites/99999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Mission introuvable']);
    }
}
