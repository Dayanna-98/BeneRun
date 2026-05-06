<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserRoleUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_change_a_user_role_and_role_permissions_are_synced(): void
    {
        $superAdmin = User::factory()->create([
            'role_utilisateur' => 'superadmin',
            'permissions_utilisateur' => 'createSkills,createBadges,issueCertificate,createAccount,assignPermissions',
        ]);

        $user = User::factory()->create([
            'role_utilisateur' => 'bénévole',
            'permissions_utilisateur' => 'manageSkills,manageCertificates,favoriteMission',
        ]);

        Sanctum::actingAs($superAdmin);

        $response = $this->putJson('/api/users/'.$user->id_utilisateur, [
            'nom_utilisateur' => $user->nom_utilisateur,
            'prenom_utilisateur' => $user->prenom_utilisateur,
            'email' => $user->email,
            'role_utilisateur' => 'admin',
            'telephone_utilisateur' => $user->telephone_utilisateur,
            'adresse_utilisateur' => $user->adresse_utilisateur,
            'date_naissance_utilisateur' => optional($user->date_naissance_utilisateur)->format('Y-m-d'),
            'allergies_utilisateur' => $user->allergies_utilisateur,
            'problemes_sante_utilisateur' => $user->problemes_sante_utilisateur,
            'possede_permis_utilisateur' => $user->possede_permis_utilisateur,
            'est_motorise_utilisateur' => $user->est_motorise_utilisateur,
            'possede_vehicule_utilisateur' => $user->possede_vehicule_utilisateur,
            'taille_tshirt_utilisateur' => $user->taille_tshirt_utilisateur,
            'est_anonyme_utilisateur' => $user->est_anonyme_utilisateur,
            'est_suspendu_utilisateur' => $user->est_suspendu_utilisateur,
            'raison_suspension_utilisateur' => $user->raison_suspension_utilisateur,
            'nombre_missions_utilisateur' => $user->nombre_missions_utilisateur,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.role_utilisateur', 'admin')
            ->assertJsonPath('user.permissions_utilisateur', 'createEvent,editEvent,createMission,editMission');

        $this->assertDatabaseHas('users', [
            'id_utilisateur' => $user->id_utilisateur,
            'role_utilisateur' => 'admin',
            'permissions_utilisateur' => 'createEvent,editEvent,createMission,editMission',
        ]);
    }

    public function test_non_superadmin_cannot_change_another_users_role(): void
    {
        $admin = User::factory()->create([
            'role_utilisateur' => 'admin',
        ]);

        $user = User::factory()->create([
            'role_utilisateur' => 'bénévole',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson('/api/users/'.$user->id_utilisateur, [
            'nom_utilisateur' => $user->nom_utilisateur,
            'prenom_utilisateur' => $user->prenom_utilisateur,
            'email' => $user->email,
            'role_utilisateur' => 'admin',
            'telephone_utilisateur' => $user->telephone_utilisateur,
            'adresse_utilisateur' => $user->adresse_utilisateur,
            'date_naissance_utilisateur' => optional($user->date_naissance_utilisateur)->format('Y-m-d'),
            'allergies_utilisateur' => $user->allergies_utilisateur,
            'problemes_sante_utilisateur' => $user->problemes_sante_utilisateur,
            'possede_permis_utilisateur' => $user->possede_permis_utilisateur,
            'est_motorise_utilisateur' => $user->est_motorise_utilisateur,
            'possede_vehicule_utilisateur' => $user->possede_vehicule_utilisateur,
            'taille_tshirt_utilisateur' => $user->taille_tshirt_utilisateur,
            'est_anonyme_utilisateur' => $user->est_anonyme_utilisateur,
            'est_suspendu_utilisateur' => $user->est_suspendu_utilisateur,
            'raison_suspension_utilisateur' => $user->raison_suspension_utilisateur,
            'nombre_missions_utilisateur' => $user->nombre_missions_utilisateur,
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id_utilisateur' => $user->id_utilisateur,
            'role_utilisateur' => 'bénévole',
        ]);
    }

    public function test_role_change_cannot_be_spoofed_with_only_a_header(): void
    {
        $user = User::factory()->create([
            'role_utilisateur' => 'bénévole',
        ]);

        $response = $this
            ->withHeader('X-User-Role', 'superadmin')
            ->putJson('/api/users/'.$user->id_utilisateur, [
                'nom_utilisateur' => $user->nom_utilisateur,
                'prenom_utilisateur' => $user->prenom_utilisateur,
                'email' => $user->email,
                'role_utilisateur' => 'admin',
                'telephone_utilisateur' => $user->telephone_utilisateur,
                'adresse_utilisateur' => $user->adresse_utilisateur,
                'date_naissance_utilisateur' => optional($user->date_naissance_utilisateur)->format('Y-m-d'),
                'allergies_utilisateur' => $user->allergies_utilisateur,
                'problemes_sante_utilisateur' => $user->problemes_sante_utilisateur,
                'possede_permis_utilisateur' => $user->possede_permis_utilisateur,
                'est_motorise_utilisateur' => $user->est_motorise_utilisateur,
                'possede_vehicule_utilisateur' => $user->possede_vehicule_utilisateur,
                'taille_tshirt_utilisateur' => $user->taille_tshirt_utilisateur,
                'est_anonyme_utilisateur' => $user->est_anonyme_utilisateur,
                'est_suspendu_utilisateur' => $user->est_suspendu_utilisateur,
                'raison_suspension_utilisateur' => $user->raison_suspension_utilisateur,
                'nombre_missions_utilisateur' => $user->nombre_missions_utilisateur,
            ]);

        $response->assertUnauthorized();

        $this->assertDatabaseHas('users', [
            'id_utilisateur' => $user->id_utilisateur,
            'role_utilisateur' => 'bénévole',
        ]);
    }

    public function test_user_can_update_own_personal_information(): void
    {
        $user = User::factory()->create([
            'nom_utilisateur' => 'Avant',
            'prenom_utilisateur' => 'Profil',
            'email' => 'profil@example.test',
            'telephone_utilisateur' => '0700000000',
            'adresse_utilisateur' => 'Ancienne adresse',
            'date_naissance_utilisateur' => '1995-01-01',
            'allergies_utilisateur' => 'lactose',
            'problemes_sante_utilisateur' => 'asthme',
            'possede_permis_utilisateur' => false,
            'est_motorise_utilisateur' => false,
            'possede_vehicule_utilisateur' => false,
            'taille_tshirt_utilisateur' => 'S',
            'est_anonyme_utilisateur' => false,
            'est_suspendu_utilisateur' => false,
            'raison_suspension_utilisateur' => null,
            'nombre_missions_utilisateur' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/users/'.$user->id_utilisateur, [
            'nom_utilisateur' => 'Apres',
            'prenom_utilisateur' => 'Modification',
            'email' => 'nouveau.profil@example.test',
            'role_utilisateur' => 'bénévole',
            'telephone_utilisateur' => '0791234567',
            'adresse_utilisateur' => 'Nouvelle adresse',
            'date_naissance_utilisateur' => '1996-02-03',
            'allergies_utilisateur' => 'arachides, pollen',
            'problemes_sante_utilisateur' => 'migraine',
            'possede_permis_utilisateur' => true,
            'est_motorise_utilisateur' => true,
            'possede_vehicule_utilisateur' => true,
            'taille_tshirt_utilisateur' => 'M',
            'est_anonyme_utilisateur' => false,
            'est_suspendu_utilisateur' => false,
            'raison_suspension_utilisateur' => null,
            'nombre_missions_utilisateur' => 1,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.nom_utilisateur', 'Apres')
            ->assertJsonPath('user.prenom_utilisateur', 'Modification')
            ->assertJsonPath('user.email', 'nouveau.profil@example.test');

        $this->assertDatabaseHas('users', [
            'id_utilisateur' => $user->id_utilisateur,
            'nom_utilisateur' => 'Apres',
            'prenom_utilisateur' => 'Modification',
            'email' => 'nouveau.profil@example.test',
            'telephone_utilisateur' => '0791234567',
            'adresse_utilisateur' => 'Nouvelle adresse',
            'allergies_utilisateur' => 'arachides, pollen',
            'problemes_sante_utilisateur' => 'migraine',
            'possede_permis_utilisateur' => 1,
            'est_motorise_utilisateur' => 1,
            'possede_vehicule_utilisateur' => 1,
            'taille_tshirt_utilisateur' => 'M',
        ]);
    }

    public function test_user_can_toggle_live_location_sharing_and_coordinates_are_cleared_when_disabled(): void
    {
        $user = User::factory()->create([
            'role_utilisateur' => 'bénévole',
            'partage_localisation_directe_utilisateur' => false,
            'latitude_localisation_directe_utilisateur' => null,
            'longitude_localisation_directe_utilisateur' => null,
            'date_localisation_directe_utilisateur' => null,
        ]);

        Sanctum::actingAs($user);

        $enableResponse = $this->putJson('/api/users/'.$user->id_utilisateur, [
            'nom_utilisateur' => $user->nom_utilisateur,
            'prenom_utilisateur' => $user->prenom_utilisateur,
            'email' => $user->email,
            'role_utilisateur' => $user->role_utilisateur,
            'telephone_utilisateur' => $user->telephone_utilisateur,
            'adresse_utilisateur' => $user->adresse_utilisateur,
            'date_naissance_utilisateur' => optional($user->date_naissance_utilisateur)->format('Y-m-d'),
            'allergies_utilisateur' => $user->allergies_utilisateur,
            'problemes_sante_utilisateur' => $user->problemes_sante_utilisateur,
            'possede_permis_utilisateur' => $user->possede_permis_utilisateur,
            'est_motorise_utilisateur' => $user->est_motorise_utilisateur,
            'possede_vehicule_utilisateur' => $user->possede_vehicule_utilisateur,
            'taille_tshirt_utilisateur' => $user->taille_tshirt_utilisateur,
            'est_anonyme_utilisateur' => $user->est_anonyme_utilisateur,
            'est_suspendu_utilisateur' => $user->est_suspendu_utilisateur,
            'raison_suspension_utilisateur' => $user->raison_suspension_utilisateur,
            'nombre_missions_utilisateur' => $user->nombre_missions_utilisateur,
            'partage_localisation_directe_utilisateur' => true,
            'latitude_localisation_directe_utilisateur' => 46.2044,
            'longitude_localisation_directe_utilisateur' => 6.1432,
            'date_localisation_directe_utilisateur' => '2026-04-27T14:00:00Z',
        ]);

        $enableResponse
            ->assertOk()
            ->assertJsonPath('user.partage_localisation_directe_utilisateur', true)
            ->assertJsonPath('user.latitude_localisation_directe_utilisateur', '46.2044000')
            ->assertJsonPath('user.longitude_localisation_directe_utilisateur', '6.1432000');

        $this->assertDatabaseHas('users', [
            'id_utilisateur' => $user->id_utilisateur,
            'partage_localisation_directe_utilisateur' => 1,
            'latitude_localisation_directe_utilisateur' => 46.2044,
            'longitude_localisation_directe_utilisateur' => 6.1432,
        ]);

        $disableResponse = $this->putJson('/api/users/'.$user->id_utilisateur, [
            'nom_utilisateur' => $user->nom_utilisateur,
            'prenom_utilisateur' => $user->prenom_utilisateur,
            'email' => $user->email,
            'role_utilisateur' => $user->role_utilisateur,
            'telephone_utilisateur' => $user->telephone_utilisateur,
            'adresse_utilisateur' => $user->adresse_utilisateur,
            'date_naissance_utilisateur' => optional($user->date_naissance_utilisateur)->format('Y-m-d'),
            'allergies_utilisateur' => $user->allergies_utilisateur,
            'problemes_sante_utilisateur' => $user->problemes_sante_utilisateur,
            'possede_permis_utilisateur' => $user->possede_permis_utilisateur,
            'est_motorise_utilisateur' => $user->est_motorise_utilisateur,
            'possede_vehicule_utilisateur' => $user->possede_vehicule_utilisateur,
            'taille_tshirt_utilisateur' => $user->taille_tshirt_utilisateur,
            'est_anonyme_utilisateur' => $user->est_anonyme_utilisateur,
            'est_suspendu_utilisateur' => $user->est_suspendu_utilisateur,
            'raison_suspension_utilisateur' => $user->raison_suspension_utilisateur,
            'nombre_missions_utilisateur' => $user->nombre_missions_utilisateur,
            'partage_localisation_directe_utilisateur' => false,
            'latitude_localisation_directe_utilisateur' => 46.3000,
            'longitude_localisation_directe_utilisateur' => 6.2000,
            'date_localisation_directe_utilisateur' => '2026-04-27T15:00:00Z',
        ]);

        $disableResponse
            ->assertOk()
            ->assertJsonPath('user.partage_localisation_directe_utilisateur', false)
            ->assertJsonPath('user.latitude_localisation_directe_utilisateur', null)
            ->assertJsonPath('user.longitude_localisation_directe_utilisateur', null)
            ->assertJsonPath('user.date_localisation_directe_utilisateur', null);

        $this->assertDatabaseHas('users', [
            'id_utilisateur' => $user->id_utilisateur,
            'partage_localisation_directe_utilisateur' => 0,
        ]);

        $this->assertDatabaseMissing('users', [
            'id_utilisateur' => $user->id_utilisateur,
            'latitude_localisation_directe_utilisateur' => 46.3000,
        ]);
    }
}