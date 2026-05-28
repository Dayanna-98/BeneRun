<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class UserAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Connexion réussie')
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login-fail@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Email ou mot de passe incorrect');
    }

    public function test_login_accepts_unverified_email_with_valid_credentials(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'not-verified@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Connexion réussie')
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_validates_required_email_and_password(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_validates_email_format(): void
    {
        $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'secret-password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_password_with_letter_number_and_minimum_length(): void
    {
        $this->postJson('/api/users', [
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'Inscription',
            'email' => 'register-password@example.com',
            'password' => 'abcdefghij',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_sends_verification_email(): void
    {
        Notification::fake();

        $this->postJson('/api/users', [
            'nom_utilisateur' => 'Test',
            'prenom_utilisateur' => 'Inscription',
            'email' => 'register-verify@example.com',
            'password' => 'motdepasse123',
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'User ajouté. Compte actif immédiatement. Vérifiez votre email: cette vérification sera requise si vous oubliez votre mot de passe.');

        $user = User::where('email', 'register-verify@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verify_email_endpoint_marks_email_as_verified(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'verify-link@example.com',
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id_utilisateur,
                'hash' => sha1($user->email),
            ]
        );

        $this->getJson($verificationUrl)
            ->assertStatus(200)
            ->assertJsonPath('message', 'Email vérifié avec succès.');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_verify_email_endpoint_redirects_browser_to_frontend_status_page(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'verify-redirect@example.com',
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id_utilisateur,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($verificationUrl)
            ->assertStatus(302);

        $location = (string) $response->headers->get('Location');
        $this->assertStringContainsString('/email-verification?', $location);
        $this->assertStringContainsString('status=success', $location);
    }

    public function test_verify_email_invalid_signature_redirects_browser_to_error_page(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'verify-invalid-signature@example.com',
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id_utilisateur,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($verificationUrl.'&tamper=1')
            ->assertStatus(302);

        $location = (string) $response->headers->get('Location');
        $this->assertStringContainsString('/email-verification?', $location);
        $this->assertStringContainsString('status=error', $location);
    }

    public function test_resend_verification_email_returns_generic_message(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create([
            'email' => 'resend-verify@example.com',
        ]);

        $this->postJson('/api/email/verification-notification', [
            'email' => $user->email,
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Si un compte non vérifié existe pour cet email, un nouveau lien de vérification a été envoyé.');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')
            ->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me')
            ->assertStatus(200)
            ->assertJsonPath('id_utilisateur', $user->id_utilisateur)
            ->assertJsonPath('email', $user->email);
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/logout')
            ->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertStatus(200)
            ->assertJsonPath('message', 'Déconnexion réussie');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
