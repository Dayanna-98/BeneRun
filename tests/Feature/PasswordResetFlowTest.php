<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_reset_validates_email(): void
    {
        $this->postJson('/api/password-reset/request', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_request_reset_returns_generic_success_when_email_not_found(): void
    {
        $this->postJson('/api/password-reset/request', [
            'email' => 'inconnu@example.com',
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Si un compte existe pour cet email, un lien de réinitialisation a été envoyé.');
    }

    public function test_request_reset_creates_token_record_for_existing_email(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'alice@example.com']);

        $this->postJson('/api/password-reset/request', [
            'email' => $user->email,
        ])
            ->assertStatus(200);

        $record = DB::table('password_resets')->where('email', $user->email)->first();
        $this->assertNotNull($record);
        $this->assertNotEmpty($record->token);
        $this->assertNotNull($record->created_at);
    }

    public function test_request_reset_returns_generic_success_when_mail_send_fails(): void
    {
        $user = User::factory()->create(['email' => 'smtp-fail@example.com']);

        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('SMTP failure'));

        $this->postJson('/api/password-reset/request', [
            'email' => $user->email,
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Si un compte existe pour cet email, un lien de réinitialisation a été envoyé.');

        $this->assertDatabaseHas('password_resets', [
            'email' => $user->email,
        ]);
    }

    public function test_verify_token_accepts_valid_unexpired_token(): void
    {
        $email = 'bob@example.com';
        $rawToken = 'tokentest123';

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => Hash::make($rawToken),
            'created_at' => now(),
        ]);

        $this->postJson('/api/password-reset/verify', [
            'email' => $email,
            'token' => $rawToken,
        ])
            ->assertStatus(200)
            ->assertJsonPath('valid', true)
            ->assertJsonPath('message', 'Token valide');
    }

    public function test_verify_token_validates_required_fields(): void
    {
        $this->postJson('/api/password-reset/verify', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'token']);
    }

    public function test_verify_token_rejects_when_no_reset_record_exists(): void
    {
        $this->postJson('/api/password-reset/verify', [
            'email' => 'missing@example.com',
            'token' => 'some-token',
        ])
            ->assertStatus(401)
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'Token invalide ou expiré');
    }

    public function test_verify_token_rejects_invalid_token_for_existing_record(): void
    {
        $email = 'wrong-token@example.com';

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => Hash::make('expected-token'),
            'created_at' => now(),
        ]);

        $this->postJson('/api/password-reset/verify', [
            'email' => $email,
            'token' => 'bad-token',
        ])
            ->assertStatus(401)
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'Token invalide');
    }

    public function test_verify_token_accepts_matching_token_even_when_record_is_old(): void
    {
        $email = 'expired@example.com';
        $rawToken = 'expiredtoken';

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => Hash::make($rawToken),
            'created_at' => '2000-01-01 00:00:00',
        ]);

        $this->postJson('/api/password-reset/verify', [
            'email' => $email,
            'token' => $rawToken,
        ])
            ->assertStatus(200)
            ->assertJsonPath('valid', true)
            ->assertJsonPath('message', 'Token valide');

        $this->assertDatabaseHas('password_resets', [
            'email' => $email,
        ]);
    }

    public function test_reset_password_with_valid_token_updates_password_and_deletes_token(): void
    {
        $user = User::factory()->create([
            'email' => 'resetok@example.com',
            'password' => Hash::make('old-password-123'),
        ]);
        $rawToken = 'valid-reset-token';

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => Hash::make($rawToken),
            'created_at' => now(),
        ]);

        $this->postJson('/api/password-reset/reset', [
            'email' => $user->email,
            'token' => $rawToken,
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Mot de passe réinitialisé avec succès');

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-456', $user->password));
        $this->assertDatabaseMissing('password_resets', [
            'email' => $user->email,
        ]);
    }

    public function test_reset_password_rejects_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'invalidtoken@example.com',
            'password' => Hash::make('old-password-123'),
        ]);

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => Hash::make('good-token'),
            'created_at' => now(),
        ]);

        $this->postJson('/api/password-reset/reset', [
            'email' => $user->email,
            'token' => 'bad-token',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Token invalide');

        $user->refresh();
        $this->assertFalse(Hash::check('new-password-456', $user->password));
    }

    public function test_reset_password_validates_confirmation_and_minimum_length(): void
    {
        $this->postJson('/api/password-reset/reset', [
            'email' => 'someone@example.com',
            'token' => 'any-token',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_password_rejects_password_without_number_or_letter(): void
    {
        $user = User::factory()->create([
            'email' => 'complexity@example.com',
            'password' => Hash::make('Ancienpass123'),
        ]);
        $rawToken = 'complexity-token';

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => Hash::make($rawToken),
            'created_at' => now(),
        ]);

        $this->postJson('/api/password-reset/reset', [
            'email' => $user->email,
            'token' => $rawToken,
            'password' => 'abcdefghij',
            'password_confirmation' => 'abcdefghij',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $user->refresh();
        $this->assertTrue(Hash::check('Ancienpass123', $user->password));
    }

    public function test_reset_password_returns_404_when_email_does_not_exist(): void
    {
        $this->postJson('/api/password-reset/reset', [
            'email' => 'unknown@example.com',
            'token' => 'any-token',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])
            ->assertStatus(404)
            ->assertJsonPath('message', 'Email non trouvé');
    }

    public function test_reset_password_returns_401_when_no_reset_record_exists(): void
    {
        $user = User::factory()->create([
            'email' => 'no-record@example.com',
            'password' => Hash::make('old-password-123'),
        ]);

        $this->postJson('/api/password-reset/reset', [
            'email' => $user->email,
            'token' => 'any-token',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Token invalide ou expiré');
    }
}
