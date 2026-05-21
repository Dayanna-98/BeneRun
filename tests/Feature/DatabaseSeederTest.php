<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    public function test_database_seeder_preserves_existing_users_and_personal_data(): void
    {
        User::factory()->create([
            'email' => 'responsable@example.test',
            'role_utilisateur' => 'responsable',
        ]);

        $superAdminA = User::factory()->create([
            'nom_utilisateur' => 'Super',
            'prenom_utilisateur' => 'AdminA',
            'email' => 'super-a@example.test',
            'password' => Hash::make('SuperPass123'),
            'role_utilisateur' => 'superadmin',
        ]);

        User::factory()->create([
            'email' => 'super-b@example.test',
            'role_utilisateur' => 'superadmin',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'role_utilisateur' => 'admin',
        ]);

        User::factory()->count(4)->sequence(
            ['email' => 'benevole-1@example.test', 'role_utilisateur' => 'bénévole'],
            ['email' => 'benevole-2@example.test', 'role_utilisateur' => 'bénévole'],
            ['email' => 'benevole-3@example.test', 'role_utilisateur' => 'bénévole'],
            ['email' => 'benevole-4@example.test', 'role_utilisateur' => 'bénévole'],
        )->create();

        $originalName = $superAdminA->nom_utilisateur;
        $originalEmail = $superAdminA->email;
        $originalPasswordHash = $superAdminA->password;

        $this->seed(DatabaseSeeder::class);

        $superAdminA->refresh();

        $this->assertSame(8, User::count());
        $this->assertSame($originalName, $superAdminA->nom_utilisateur);
        $this->assertSame($originalEmail, $superAdminA->email);
        $this->assertSame('superadmin', $superAdminA->role_utilisateur);
        $this->assertSame($originalPasswordHash, $superAdminA->password);

        $this->assertDatabaseHas('evenements', [
            'nom_evenement' => 'Running Geneva 10K',
            'cree_par_utilisateur_id' => $admin->id_utilisateur,
        ]);

        $this->assertGreaterThan(0, DB::table('missions')->count());
        $this->assertGreaterThan(0, DB::table('postulations')->count());
    }

    public function test_database_seeder_requires_existing_users(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Aucun utilisateur trouvé.');

        $this->seed(DatabaseSeeder::class);
    }
}
