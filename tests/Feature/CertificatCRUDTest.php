<?php

namespace Tests\Feature;

use App\Models\Certificat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificatCRUDTest extends TestCase
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

    // INDEX TESTS
    public function test_unauthenticated_user_cannot_list_certificats()
    {
        $response = $this->getJson('/api/certificats');

        $response->assertStatus(401);
    }

    public function test_user_can_list_their_own_certificats()
    {
        Certificat::factory()->count(3)->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
        ]);
        Certificat::factory()->create([
            'id_utilisateur' => $this->superAdmin->id_utilisateur,
        ]);

        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->getJson('/api/certificats');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_user_cannot_list_other_users_certificats()
    {
        Certificat::factory()->create([
            'id_utilisateur' => $this->superAdmin->id_utilisateur,
        ]);

        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->getJson('/api/certificats?id_utilisateur='.$this->superAdmin->id_utilisateur);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Action non autorisée']);
    }

    public function test_superadmin_can_list_all_certificats()
    {
        Certificat::factory()->count(2)->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
        ]);
        Certificat::factory()->count(2)->create([
            'id_utilisateur' => $this->superAdmin->id_utilisateur,
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/certificats');

        $response->assertStatus(200);
        $response->assertJsonCount(4);
    }

    public function test_superadmin_can_filter_certificats_by_user()
    {
        Certificat::factory()->count(2)->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
        ]);
        Certificat::factory()->create([
            'id_utilisateur' => $this->superAdmin->id_utilisateur,
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/certificats?id_utilisateur='.$this->normalUser->id_utilisateur);

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    // SHOW TESTS
    public function test_unauthenticated_user_cannot_show_certificat()
    {
        $certificat = Certificat::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
        ]);

        $response = $this->getJson('/api/certificats/'.$certificat->id_certificat);

        $response->assertStatus(401);
    }

    public function test_show_nonexistent_certificat_returns_404()
    {
        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->getJson('/api/certificats/999999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Certificat inexistant']);
    }

    public function test_user_can_show_their_own_certificat()
    {
        $certificat = Certificat::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
            'titre_certificat' => 'Test Certificate',
        ]);

        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->getJson('/api/certificats/'.$certificat->id_certificat);

        $response->assertStatus(200);
        $response->assertJson([
            'id_certificat' => $certificat->id_certificat,
            'titre_certificat' => 'Test Certificate',
        ]);
    }

    public function test_user_cannot_show_other_users_certificat()
    {
        $certificat = Certificat::factory()->create([
            'id_utilisateur' => $this->superAdmin->id_utilisateur,
        ]);

        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->getJson('/api/certificats/'.$certificat->id_certificat);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Action non autorisée']);
    }

    public function test_superadmin_can_show_any_certificat()
    {
        $certificat = Certificat::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/certificats/'.$certificat->id_certificat);

        $response->assertStatus(200);
        $response->assertJson(['id_certificat' => $certificat->id_certificat]);
    }

    // STORE TESTS
    public function test_unauthenticated_user_cannot_create_certificat()
    {
        $response = $this->postJson('/api/certificats', [
            'id_utilisateur' => $this->normalUser->id_utilisateur,
            'titre_certificat' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_certificat_requires_titre()
    {
        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->postJson('/api/certificats', [
                'id_utilisateur' => $this->normalUser->id_utilisateur,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('titre_certificat');
    }

    public function test_create_certificat_requires_valid_user_id()
    {
        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->postJson('/api/certificats', [
                'id_utilisateur' => 999999,
                'titre_certificat' => 'Test',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('id_utilisateur');
    }

    public function test_user_can_create_own_certificat()
    {
        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->postJson('/api/certificats', [
                'id_utilisateur' => $this->normalUser->id_utilisateur,
                'titre_certificat' => 'My Certificate',
                'emetteur_certificat' => 'Issuer',
                'date_emission_certificat' => '2024-01-01',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Certificat ajouté',
            'certificat' => [
                'id_utilisateur' => $this->normalUser->id_utilisateur,
                'titre_certificat' => 'My Certificate',
                'type_certificat' => 'external',
                'statut_certificat' => 'en attente',
            ],
        ]);
    }

    public function test_user_cannot_create_certificat_for_other_user()
    {
        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->postJson('/api/certificats', [
                'id_utilisateur' => $this->superAdmin->id_utilisateur,
                'titre_certificat' => 'Hack',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Vous ne pouvez soumettre un certificat que pour votre propre compte',
        ]);
    }

    public function test_superadmin_can_create_certificat_for_any_user()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/certificats', [
                'id_utilisateur' => $this->normalUser->id_utilisateur,
                'titre_certificat' => 'Admin Certificate',
                'type_certificat' => 'platform',
                'statut_certificat' => 'approuvé',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Certificat ajouté',
            'certificat' => [
                'titre_certificat' => 'Admin Certificate',
                'type_certificat' => 'platform',
                'statut_certificat' => 'approuvé',
            ],
        ]);
    }

    public function test_expiration_date_must_be_after_emission_date()
    {
        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->postJson('/api/certificats', [
                'id_utilisateur' => $this->normalUser->id_utilisateur,
                'titre_certificat' => 'Test',
                'date_emission_certificat' => '2025-12-31',
                'date_expiration_certificat' => '2024-01-01',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date_expiration_certificat');
    }

    // UPDATE TESTS
    public function test_unauthenticated_user_cannot_update_certificat()
    {
        $certificat = Certificat::factory()->create();

        $response = $this->patchJson('/api/certificats/'.$certificat->id_certificat, [
            'titre_certificat' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_superadmin_cannot_update_certificat()
    {
        $certificat = Certificat::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
        ]);

        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->patchJson('/api/certificats/'.$certificat->id_certificat, [
                'titre_certificat' => 'Updated',
            ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Action réservée aux super-admins']);
    }

    public function test_update_nonexistent_certificat_returns_404()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->patchJson('/api/certificats/999999', [
                'titre_certificat' => 'Updated',
            ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Certificat inexistant']);
    }

    public function test_superadmin_can_update_certificat()
    {
        $certificat = Certificat::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
            'titre_certificat' => 'Original',
            'statut_certificat' => 'en attente',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->patchJson('/api/certificats/'.$certificat->id_certificat, [
                'titre_certificat' => 'Updated Title',
                'statut_certificat' => 'approuvé',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Certificat mis à jour',
            'certificat' => [
                'titre_certificat' => 'Updated Title',
                'statut_certificat' => 'approuvé',
            ],
        ]);
    }

    public function test_superadmin_can_normalize_status_on_update()
    {
        $certificat = Certificat::factory()->create([
            'statut_certificat' => 'en attente',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->patchJson('/api/certificats/'.$certificat->id_certificat, [
                'statut_certificat' => 'rejeté',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'certificat' => [
                'statut_certificat' => 'rejeté',
            ],
        ]);
    }

    // DESTROY TESTS
    public function test_unauthenticated_user_cannot_delete_certificat()
    {
        $certificat = Certificat::factory()->create();

        $response = $this->deleteJson('/api/certificats/'.$certificat->id_certificat);

        $response->assertStatus(401);
    }

    public function test_non_superadmin_cannot_delete_certificat()
    {
        $certificat = Certificat::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
        ]);

        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->deleteJson('/api/certificats/'.$certificat->id_certificat);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Action réservée aux super-admins']);
    }

    public function test_delete_nonexistent_certificat_returns_404()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->deleteJson('/api/certificats/999999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Certificat inexistant']);
    }

    public function test_superadmin_can_delete_certificat()
    {
        $certificat = Certificat::factory()->create([
            'id_utilisateur' => $this->normalUser->id_utilisateur,
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->deleteJson('/api/certificats/'.$certificat->id_certificat);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Certificat supprimé']);

        $this->assertDatabaseMissing('certificats', [
            'id_certificat' => $certificat->id_certificat,
        ]);
    }
}
