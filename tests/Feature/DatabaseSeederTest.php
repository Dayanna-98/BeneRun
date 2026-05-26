<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public function test_database_seeder_creates_default_users_when_empty(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(8, User::count());

        $this->assertDatabaseHas('users', [
            'email' => 'emmazeghdoud@gmail.com',
            'prenom_utilisateur' => 'Emma',
            'nom_utilisateur' => 'Rougeron',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'marc.manager@benerun.test',
            'prenom_utilisateur' => 'Marc',
            'nom_utilisateur' => 'Duval',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'leo.benevole@benerun.test',
            'prenom_utilisateur' => 'Leo',
            'nom_utilisateur' => 'Morel',
        ]);
    }
}
