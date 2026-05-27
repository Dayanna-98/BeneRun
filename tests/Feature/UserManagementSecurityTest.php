<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function baseUpdatePayload(User $user): array
    {
        return [
            'nom_utilisateur' => $user->nom_utilisateur,
            'prenom_utilisateur' => $user->prenom_utilisateur,
            'email' => $user->email,
        ];
    }

    public function test_non_superadmin_cannot_anonymize_user(): void
    {
        $actor = User::factory()->create([
            'role_utilisateur' => 'bénévole',
            'est_anonyme_utilisateur' => false,
        ]);

        $payload = $this->baseUpdatePayload($actor);
        $payload['est_anonyme_utilisateur'] = true;

        $this->actingAs($actor, 'sanctum')
            ->putJson('/api/users/'.$actor->id_utilisateur, $payload)
            ->assertStatus(403)
            ->assertJsonPath('message', 'Action réservée aux super-admins');
    }

    public function test_superadmin_can_anonymize_user(): void
    {
        $actor = User::factory()->create([
            'role_utilisateur' => 'superadmin',
        ]);

        $target = User::factory()->create([
            'est_anonyme_utilisateur' => false,
        ]);

        $payload = $this->baseUpdatePayload($target);
        $payload['est_anonyme_utilisateur'] = true;

        $this->actingAs($actor, 'sanctum')
            ->putJson('/api/users/'.$target->id_utilisateur, $payload)
            ->assertStatus(200)
            ->assertJsonPath('user.est_anonyme_utilisateur', true);
    }

    public function test_user_changing_own_password_requires_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('AncienPass123'),
        ]);

        $payload = $this->baseUpdatePayload($user);
        $payload['password'] = 'NouveauPass123';

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/users/'.$user->id_utilisateur, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_user_changing_own_password_with_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('AncienPass123'),
        ]);

        $payload = $this->baseUpdatePayload($user);
        $payload['password'] = 'NouveauPass123';
        $payload['current_password'] = 'MauvaisPass123';

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/users/'.$user->id_utilisateur, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_user_changing_own_password_with_current_password_succeeds(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('AncienPass123'),
        ]);

        $payload = $this->baseUpdatePayload($user);
        $payload['password'] = 'NouveauPass123';
        $payload['current_password'] = 'AncienPass123';

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/users/'.$user->id_utilisateur, $payload)
            ->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('NouveauPass123', $user->password));
    }

    public function test_non_superadmin_cannot_delete_user(): void
    {
        $actor = User::factory()->create([
            'role_utilisateur' => 'admin',
        ]);

        $target = User::factory()->create();

        $this->actingAs($actor, 'sanctum')
            ->deleteJson('/api/users/'.$target->id_utilisateur)
            ->assertStatus(403)
            ->assertJsonPath('message', 'Action réservée aux super-admins');
    }

    public function test_superadmin_can_delete_user(): void
    {
        $actor = User::factory()->create([
            'role_utilisateur' => 'superadmin',
        ]);

        $target = User::factory()->create();

        $this->actingAs($actor, 'sanctum')
            ->deleteJson('/api/users/'.$target->id_utilisateur)
            ->assertStatus(200)
            ->assertJsonPath('message', 'User supprimé');

        $this->assertDatabaseMissing('users', [
            'id_utilisateur' => $target->id_utilisateur,
        ]);
    }
}
