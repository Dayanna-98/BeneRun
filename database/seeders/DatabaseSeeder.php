<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->truncateTables();

        $users = $this->seedUsers();
        $competences = $this->seedCompetences();
        $events = $this->seedEvents($users);
        $missions = $this->seedMissions($users, $events);

        $this->seedUserCompetences($users, $competences);
        $this->seedMissionCompetences($missions, $competences);
        $this->seedMissionContacts($missions);
        $this->seedMissionMedias($missions, $users);
        $this->seedAffectations($users, $missions);
        $this->seedPostulations($users, $missions);
        $badges = $this->seedBadges();
        $this->seedUserBadges($users, $badges);
        $this->seedCertificates($users);
        $this->seedFavorites($users, $missions);
    }

    private function seedUsers(): array
    {
        $defaultPassword = Hash::make('password');
        $emmaPassword = Hash::make('Soleil1234');

        $rows = [
            'emma' => [
                'nom_utilisateur' => 'Rougeron',
                'prenom_utilisateur' => 'Emma',
                'email' => 'emmazeghdoud@gmail.com',
                'role_utilisateur' => 'superadmin',
                'telephone_utilisateur' => '0791112233',
                'adresse_utilisateur' => 'Rue du Rhone 14, Geneve',
                'date_naissance_utilisateur' => '1999-08-17',
                'allergies_utilisateur' => 'arachides',
                'problemes_sante_utilisateur' => null,
                'possede_permis_utilisateur' => true,
                'est_motorise_utilisateur' => true,
                'possede_vehicule_utilisateur' => true,
                'taille_tshirt_utilisateur' => 'M',
                'est_anonyme_utilisateur' => false,
                'est_suspendu_utilisateur' => false,
                'raison_suspension_utilisateur' => null,
                'permissions_utilisateur' => 'createSkills,createBadges,issueCertificate,createAccount,assignPermissions',
                'nombre_missions_utilisateur' => 6,
            ],
            'alexandre' => [
                'nom_utilisateur' => 'Rousseau',
                'prenom_utilisateur' => 'Alexandre',
                'email' => 'admin@benerun.ch',
                'role_utilisateur' => 'superadmin',
                'telephone_utilisateur' => '0793334455',
                'adresse_utilisateur' => 'Avenue de France 10, Geneve',
                'date_naissance_utilisateur' => '1994-04-02',
                'allergies_utilisateur' => null,
                'problemes_sante_utilisateur' => null,
                'possede_permis_utilisateur' => true,
                'est_motorise_utilisateur' => true,
                'possede_vehicule_utilisateur' => true,
                'taille_tshirt_utilisateur' => 'L',
                'est_anonyme_utilisateur' => false,
                'est_suspendu_utilisateur' => false,
                'raison_suspension_utilisateur' => null,
                'permissions_utilisateur' => 'createSkills,createBadges,issueCertificate,createAccount,assignPermissions',
                'nombre_missions_utilisateur' => 10,
            ],
            'clara' => [
                'nom_utilisateur' => 'Martin',
                'prenom_utilisateur' => 'Clara',
                'email' => 'clara.admin@benerun.test',
                'role_utilisateur' => 'admin',
                'telephone_utilisateur' => '0795556677',
                'adresse_utilisateur' => 'Rue de Lausanne 8, Geneve',
                'date_naissance_utilisateur' => '1992-12-11',
                'allergies_utilisateur' => null,
                'problemes_sante_utilisateur' => null,
                'possede_permis_utilisateur' => true,
                'est_motorise_utilisateur' => true,
                'possede_vehicule_utilisateur' => false,
                'taille_tshirt_utilisateur' => 'M',
                'est_anonyme_utilisateur' => false,
                'est_suspendu_utilisateur' => false,
                'raison_suspension_utilisateur' => null,
                'permissions_utilisateur' => 'createEvent,editEvent,createMission,editMission',
                'nombre_missions_utilisateur' => 8,
            ],
            'marc' => [
                'nom_utilisateur' => 'Duval',
                'prenom_utilisateur' => 'Marc',
                'email' => 'marc.manager@benerun.test',
                'role_utilisateur' => 'responsable',
                'telephone_utilisateur' => '0782223344',
                'adresse_utilisateur' => 'Chemin des Sports 3, Carouge',
                'date_naissance_utilisateur' => '1988-06-09',
                'allergies_utilisateur' => null,
                'problemes_sante_utilisateur' => null,
                'possede_permis_utilisateur' => true,
                'est_motorise_utilisateur' => true,
                'possede_vehicule_utilisateur' => true,
                'taille_tshirt_utilisateur' => 'XL',
                'est_anonyme_utilisateur' => false,
                'est_suspendu_utilisateur' => false,
                'raison_suspension_utilisateur' => null,
                'permissions_utilisateur' => 'createMission,editMission,deleteMission,contactMissionMembers',
                'nombre_missions_utilisateur' => 5,
            ],
            'marie' => [
                'nom_utilisateur' => 'Cossart',
                'prenom_utilisateur' => 'Marie',
                'email' => 'momo@gmail.com',
                'role_utilisateur' => 'bénévole',
                'telephone_utilisateur' => '0771234567',
                'adresse_utilisateur' => 'Boulevard Carl-Vogt 21, Geneve',
                'date_naissance_utilisateur' => '2000-03-14',
                'allergies_utilisateur' => 'gluten',
                'problemes_sante_utilisateur' => 'asthme leger',
                'possede_permis_utilisateur' => false,
                'est_motorise_utilisateur' => false,
                'possede_vehicule_utilisateur' => false,
                'taille_tshirt_utilisateur' => 'S',
                'est_anonyme_utilisateur' => false,
                'est_suspendu_utilisateur' => false,
                'raison_suspension_utilisateur' => null,
                'permissions_utilisateur' => 'manageSkills,manageCertificates,favoriteMission',
                'nombre_missions_utilisateur' => 2,
            ],
            'leo' => [
                'nom_utilisateur' => 'Morel',
                'prenom_utilisateur' => 'Leo',
                'email' => 'leo.benevole@benerun.test',
                'role_utilisateur' => 'bénévole',
                'telephone_utilisateur' => '0762221188',
                'adresse_utilisateur' => 'Rue des Eaux-Vives 31, Geneve',
                'date_naissance_utilisateur' => '1998-09-25',
                'allergies_utilisateur' => null,
                'problemes_sante_utilisateur' => null,
                'possede_permis_utilisateur' => true,
                'est_motorise_utilisateur' => true,
                'possede_vehicule_utilisateur' => false,
                'taille_tshirt_utilisateur' => 'M',
                'est_anonyme_utilisateur' => false,
                'est_suspendu_utilisateur' => false,
                'raison_suspension_utilisateur' => null,
                'permissions_utilisateur' => 'manageSkills,manageCertificates,favoriteMission',
                'nombre_missions_utilisateur' => 4,
            ],
            'nina' => [
                'nom_utilisateur' => 'Borel',
                'prenom_utilisateur' => 'Nina',
                'email' => 'nina.suspendue@benerun.test',
                'role_utilisateur' => 'bénévole',
                'telephone_utilisateur' => '0769988776',
                'adresse_utilisateur' => 'Route de Florissant 12, Geneve',
                'date_naissance_utilisateur' => '1997-01-19',
                'allergies_utilisateur' => 'lactose',
                'problemes_sante_utilisateur' => null,
                'possede_permis_utilisateur' => false,
                'est_motorise_utilisateur' => false,
                'possede_vehicule_utilisateur' => false,
                'taille_tshirt_utilisateur' => 'M',
                'est_anonyme_utilisateur' => false,
                'est_suspendu_utilisateur' => true,
                'raison_suspension_utilisateur' => 'Absences répétées non justifiées',
                'permissions_utilisateur' => 'manageSkills,manageCertificates,favoriteMission',
                'nombre_missions_utilisateur' => 1,
            ],
            'zoe' => [
                'nom_utilisateur' => 'Rey',
                'prenom_utilisateur' => 'Zoe',
                'email' => 'zoe.anonyme@benerun.test',
                'role_utilisateur' => 'bénévole',
                'telephone_utilisateur' => '0784455667',
                'adresse_utilisateur' => 'Rue de Carouge 55, Geneve',
                'date_naissance_utilisateur' => '2001-11-07',
                'allergies_utilisateur' => null,
                'problemes_sante_utilisateur' => null,
                'possede_permis_utilisateur' => false,
                'est_motorise_utilisateur' => false,
                'possede_vehicule_utilisateur' => false,
                'taille_tshirt_utilisateur' => 'XS',
                'est_anonyme_utilisateur' => true,
                'est_suspendu_utilisateur' => false,
                'raison_suspension_utilisateur' => null,
                'permissions_utilisateur' => 'manageSkills,manageCertificates,favoriteMission',
                'nombre_missions_utilisateur' => 3,
            ],
        ];

        $ids = [];

        foreach ($rows as $key => $row) {
            $ids[$key] = DB::table('users')->insertGetId([
                ...$row,
                'password' => $key === 'emma' ? $emmaPassword : $defaultPassword,
                'email_verified_at' => now(),
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_utilisateur');
        }

        return $ids;
    }

    private function seedCompetences(): array
    {
        $rows = [
            'premiers_secours' => 'Premiers secours',
            'ravitaillement' => 'Gestion de ravitaillement',
            'signalisation' => 'Signalisation parcours',
            'accueil' => 'Accueil coureurs',
            'radio' => 'Communication radio',
            'logistique' => 'Logistique événementielle',
            'orientation' => 'Orientation public',
        ];

        $ids = [];

        foreach ($rows as $key => $label) {
            $ids[$key] = DB::table('competences')->insertGetId([
                'nom_competence' => $label,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_competence');
        }

        return $ids;
    }

    private function seedEvents(array $users): array
    {
        $rows = [
            'geneva10k' => [
                'nom_evenement' => 'Running Geneva 10K',
                'description_evenement' => 'Événement principal de démonstration avec missions publiques, quotas et certificats à valider.',
                'date_debut_evenement' => '2026-05-09',
                'date_fin_evenement' => '2026-05-09',
                'heure_debut_evenement' => '08:30:00',
                'heure_fin_evenement' => '13:00:00',
                'lieu_evenement' => 'Quai du Mont-Blanc, Geneve',
                'latitude_evenement' => 46.2094471,
                'longitude_evenement' => 6.1475688,
                'organisateur_evenement' => 'Running Geneva',
                'image_evenement' => 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=1200',
                'nombre_benevoles_requis' => 18,
                'est_annule_evenement' => false,
                'date_annulation_evenement' => null,
                'raison_annulation_evenement' => null,
                'est_publie_evenement' => true,
                'cree_par_utilisateur_id' => $users['clara'],
            ],
            'night_run' => [
                'nom_evenement' => 'Geneva Night Run',
                'description_evenement' => 'Événement en brouillon pratique pour montrer un statut non publié.',
                'date_debut_evenement' => '2026-06-12',
                'date_fin_evenement' => '2026-06-12',
                'heure_debut_evenement' => '20:00:00',
                'heure_fin_evenement' => '23:00:00',
                'lieu_evenement' => 'Parc La Grange, Geneve',
                'latitude_evenement' => 46.2049939,
                'longitude_evenement' => 6.1628491,
                'organisateur_evenement' => 'Ville de Geneve',
                'image_evenement' => 'https://images.unsplash.com/photo-1508609349937-5ec4ae374ebf?w=1200',
                'nombre_benevoles_requis' => 10,
                'est_annule_evenement' => false,
                'date_annulation_evenement' => null,
                'raison_annulation_evenement' => null,
                'est_publie_evenement' => false,
                'cree_par_utilisateur_id' => $users['clara'],
            ],
            'tri_rhone' => [
                'nom_evenement' => 'Tri du Rhone',
                'description_evenement' => 'Événement annulé pour montrer les états et motifs d annulation côté gestion.',
                'date_debut_evenement' => '2026-07-04',
                'date_fin_evenement' => '2026-07-04',
                'heure_debut_evenement' => '09:00:00',
                'heure_fin_evenement' => '15:30:00',
                'lieu_evenement' => 'Jonction, Geneve',
                'latitude_evenement' => 46.1965072,
                'longitude_evenement' => 6.1324421,
                'organisateur_evenement' => 'Club Rhone Sport',
                'image_evenement' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=1200',
                'nombre_benevoles_requis' => 12,
                'est_annule_evenement' => true,
                'date_annulation_evenement' => '2026-06-28',
                'raison_annulation_evenement' => 'Météo et contraintes de sécurité',
                'est_publie_evenement' => true,
                'cree_par_utilisateur_id' => $users['emma'],
            ],
        ];

        $ids = [];

        foreach ($rows as $key => $row) {
            $ids[$key] = DB::table('evenements')->insertGetId([
                ...$row,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_evenement');
        }

        return $ids;
    }

    private function seedMissions(array $users, array $events): array
    {
        $rows = [
            'ravitaillement' => [
                'id_evenement' => $events['geneva10k'],
                'responsable_utilisateur_id' => $users['marc'],
                'titre_mission' => 'Ravitaillement Km 5',
                'type_mission' => 'logistique',
                'description_mission' => 'Préparer les tables, distribuer l eau et gérer le réapprovisionnement du poste.',
                'date_mission' => '2026-05-09',
                'heure_debut_mission' => '07:30:00',
                'heure_fin_mission' => '11:30:00',
                'lieu_mission' => 'Parc des Bastions',
                'latitude_mission' => 46.1997540,
                'longitude_mission' => 6.1423040,
                'nombre_benevoles_max' => 6,
                'nombre_benevoles_backup' => 2,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Prévoir gants et radio de coordination.',
                'image_mission' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=1200',
                'publie_le_mission' => now()->subDays(8),
            ],
            'signalisation' => [
                'id_evenement' => $events['geneva10k'],
                'responsable_utilisateur_id' => $users['clara'],
                'titre_mission' => 'Signalisation centre-ville',
                'type_mission' => 'technique',
                'description_mission' => 'Orienter les coureurs et sécuriser les traversées piétonnes en centre-ville.',
                'date_mission' => '2026-05-09',
                'heure_debut_mission' => '08:00:00',
                'heure_fin_mission' => '12:30:00',
                'lieu_mission' => 'Rue du Rhone',
                'latitude_mission' => 46.2021205,
                'longitude_mission' => 6.1499801,
                'nombre_benevoles_max' => 5,
                'nombre_benevoles_backup' => 1,
                'statut_mission' => 'En cours',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Port du gilet haute visibilité obligatoire.',
                'image_mission' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=1200',
                'publie_le_mission' => now()->subDays(7),
            ],
            'vip' => [
                'id_evenement' => $events['geneva10k'],
                'responsable_utilisateur_id' => $users['alexandre'],
                'titre_mission' => 'Accueil zone VIP',
                'type_mission' => 'accueil',
                'description_mission' => 'Accueillir les partenaires et orienter les invités dans la zone protocolaire.',
                'date_mission' => '2026-05-09',
                'heure_debut_mission' => '08:15:00',
                'heure_fin_mission' => '13:15:00',
                'lieu_mission' => 'Village partenaires',
                'latitude_mission' => 46.2081000,
                'longitude_mission' => 6.1479000,
                'nombre_benevoles_max' => 3,
                'nombre_benevoles_backup' => 1,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'privée',
                'consignes_securite' => 'Badge accès requis, tenue soignée.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(5),
            ],
            'dossards' => [
                'id_evenement' => $events['night_run'],
                'responsable_utilisateur_id' => $users['marc'],
                'titre_mission' => 'Retrait des dossards',
                'type_mission' => 'accueil',
                'description_mission' => 'Accueil des participants et remise des dossards avant le départ nocturne.',
                'date_mission' => '2026-06-12',
                'heure_debut_mission' => '17:30:00',
                'heure_fin_mission' => '20:15:00',
                'lieu_mission' => 'Parc La Grange - entrée nord',
                'latitude_mission' => 46.2048100,
                'longitude_mission' => 6.1632100,
                'nombre_benevoles_max' => 4,
                'nombre_benevoles_backup' => 1,
                'statut_mission' => 'Terminée',
                'inscription_requise' => true,
                'visibilite_mission' => 'limitée',
                'consignes_securite' => 'Arrivée 15 min avant ouverture du stand.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(20),
            ],
            'canoe' => [
                'id_evenement' => $events['tri_rhone'],
                'responsable_utilisateur_id' => $users['clara'],
                'titre_mission' => 'Zone départ canoë',
                'type_mission' => 'secours',
                'description_mission' => 'Sécurisation de la mise à l eau et coordination avec l équipe secours.',
                'date_mission' => '2026-07-04',
                'heure_debut_mission' => '08:00:00',
                'heure_fin_mission' => '12:00:00',
                'lieu_mission' => 'Quai du Rhone',
                'latitude_mission' => 46.1991000,
                'longitude_mission' => 6.1327000,
                'nombre_benevoles_max' => 4,
                'nombre_benevoles_backup' => 1,
                'statut_mission' => 'Annulée',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Mission annulée avec l événement.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(12),
            ],
        ];

        $ids = [];

        foreach ($rows as $key => $row) {
            $ids[$key] = DB::table('missions')->insertGetId([
                ...$row,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_mission');
        }

        return $ids;
    }

    private function seedUserCompetences(array $users, array $competences): void
    {
        $rows = [
            [$users['emma'], $competences['radio'], 'expert'],
            [$users['emma'], $competences['logistique'], 'expert'],
            [$users['alexandre'], $competences['accueil'], 'expert'],
            [$users['alexandre'], $competences['orientation'], 'avancé'],
            [$users['clara'], $competences['signalisation'], 'expert'],
            [$users['clara'], $competences['ravitaillement'], 'avancé'],
            [$users['marc'], $competences['ravitaillement'], 'expert'],
            [$users['marc'], $competences['logistique'], 'expert'],
            [$users['marie'], $competences['accueil'], 'avancé'],
            [$users['marie'], $competences['premiers_secours'], 'débutant'],
            [$users['leo'], $competences['signalisation'], 'avancé'],
            [$users['leo'], $competences['radio'], 'intermédaire'],
            [$users['nina'], $competences['ravitaillement'], 'débutant'],
            [$users['zoe'], $competences['orientation'], 'intermédaire'],
        ];

        foreach ($rows as [$userId, $competenceId, $niveau]) {
            DB::table('user_competences')->insert([
                'id_utilisateur' => $userId,
                'id_competence' => $competenceId,
                'niveau' => $niveau,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedMissionCompetences(array $missions, array $competences): void
    {
        $rows = [
            [$missions['ravitaillement'], $competences['ravitaillement']],
            [$missions['ravitaillement'], $competences['logistique']],
            [$missions['signalisation'], $competences['signalisation']],
            [$missions['signalisation'], $competences['radio']],
            [$missions['vip'], $competences['accueil']],
            [$missions['vip'], $competences['orientation']],
            [$missions['dossards'], $competences['accueil']],
            [$missions['canoe'], $competences['premiers_secours']],
        ];

        foreach ($rows as [$missionId, $competenceId]) {
            DB::table('mission_competences')->insert([
                'id_mission' => $missionId,
                'id_competence' => $competenceId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedMissionContacts(array $missions): void
    {
        $rows = [
            [$missions['ravitaillement'], 'Marc Duval', '0782223344', 'marc.manager@benerun.test', true, true],
            [$missions['signalisation'], 'Clara Martin', '0795556677', 'clara.admin@benerun.test', true, true],
            [$missions['vip'], 'Alexandre Rousseau', '0793334455', 'admin@benerun.ch', true, false],
            [$missions['dossards'], 'Marc Duval', '0782223344', 'marc.manager@benerun.test', true, true],
        ];

        foreach ($rows as [$missionId, $name, $phone, $email, $isMain, $isDay]) {
            DB::table('mission_contacts')->insert([
                'id_mission' => $missionId,
                'nom_contact' => $name,
                'telephone_contact' => $phone,
                'email_contact' => $email,
                'est_contact_principal' => $isMain,
                'est_contact_jour_j' => $isDay,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedMissionMedias(array $missions, array $users): void
    {
        $rows = [
            [$missions['ravitaillement'], 'missions/ravitaillement-plan.pdf', 'application/pdf', 245760, $users['marc']],
            [$missions['signalisation'], 'missions/signalisation-zone.jpg', 'image/jpeg', 184320, $users['clara']],
        ];

        foreach ($rows as [$missionId, $path, $mime, $size, $uploaderId]) {
            DB::table('mission_medias')->insert([
                'id_mission' => $missionId,
                'chemin_fichier' => $path,
                'type_mime' => $mime,
                'taille_fichier' => $size,
                'telecharge_par_utilisateur_id' => $uploaderId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedAffectations(array $users, array $missions): void
    {
        $rows = [
            [$users['marc'], $missions['ravitaillement'], 'assigne', true, 'Responsable mission.', now()->subDays(8), null, null],
            [$users['marie'], $missions['ravitaillement'], 'confirme', false, 'Disponible dès 07h15.', now()->subDays(6), now()->subDays(4), null],
            [$users['leo'], $missions['ravitaillement'], 'assigne', false, 'Renfort ravitaillement.', now()->subDays(5), null, null],
            [$users['clara'], $missions['signalisation'], 'present', true, 'Coordination parcours.', now()->subDays(7), now()->subDays(3), now()->subDays(1)],
            [$users['zoe'], $missions['signalisation'], 'confirme', false, 'Disponible en zone 2.', now()->subDays(5), now()->subDays(2), null],
            [$users['alexandre'], $missions['vip'], 'assigne', true, 'Pilotage partenaires.', now()->subDays(5), null, null],
            [$users['marc'], $missions['dossards'], 'present', true, 'Mission terminée.', now()->subDays(20), now()->subDays(18), now()->subDays(16)],
            [$users['marie'], $missions['dossards'], 'present', false, 'Accueil participants.', now()->subDays(20), now()->subDays(18), now()->subDays(16)],
            [$users['clara'], $missions['canoe'], 'annule', true, 'Mission annulée avec l événement.', now()->subDays(12), null, null],
        ];

        foreach ($rows as [$userId, $missionId, $status, $isResponsible, $note, $assignedAt, $confirmedAt, $presentAt]) {
            DB::table('affectations')->insert([
                'id_utilisateur' => $userId,
                'id_mission' => $missionId,
                'statut_affectation' => $status,
                'est_responsable' => $isResponsible,
                'remarque' => $note,
                'date_affectation' => $assignedAt,
                'date_confirmation' => $confirmedAt,
                'date_presence' => $presentAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedPostulations(array $users, array $missions): void
    {
        $rows = [
            [$missions['ravitaillement'], $users['zoe'], 'en_attente', 'Disponible toute la matinée.', now()->subDays(2), null, null],
            [$missions['signalisation'], $users['leo'], 'accepte', 'Déjà présent sur des courses urbaines.', now()->subDays(6), now()->subDays(4), null],
            [$missions['vip'], $users['marie'], 'refuse', 'Souhaite un poste au contact public.', now()->subDays(3), now()->subDays(2), null],
            [$missions['canoe'], $users['nina'], 'annule', 'Se retire suite à l annulation.', now()->subDays(10), now()->subDays(8), now()->subDays(7)],
        ];

        foreach ($rows as [$missionId, $userId, $status, $note, $postedAt, $decisionAt, $cancelledAt]) {
            DB::table('postulations')->insert([
                'id_mission' => $missionId,
                'id_utilisateur' => $userId,
                'statut_postulation' => $status,
                'remarque' => $note,
                'date_postulation' => $postedAt,
                'date_decision' => $decisionAt,
                'date_annulation' => $cancelledAt,
                'created_at' => $postedAt,
                'updated_at' => now(),
            ]);
        }
    }

    private function seedBadges(): array
    {
        $rows = [
            'starter' => [
                'titre_badge' => 'Premier engagement',
                'description_badge' => 'Attribué après la première mission validée.',
                'icone_badge' => 'Award',
                'score_badge' => 50,
                'regle_auto' => '1 mission completee',
            ],
            'team' => [
                'titre_badge' => 'Pilier d équipe',
                'description_badge' => 'Attribué aux bénévoles présents sur plusieurs missions.',
                'icone_badge' => 'Users',
                'score_badge' => 150,
                'regle_auto' => '5 missions completees',
            ],
            'secours' => [
                'titre_badge' => 'Réflexe secours',
                'description_badge' => 'Valorise les profils avec certificat secourisme validé.',
                'icone_badge' => 'Shield',
                'score_badge' => 100,
                'regle_auto' => 'certificat approuve',
            ],
        ];

        $ids = [];

        foreach ($rows as $key => $row) {
            $ids[$key] = DB::table('badges')->insertGetId([
                ...$row,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_badge');
        }

        return $ids;
    }

    private function seedUserBadges(array $users, array $badges): void
    {
        $rows = [
            [$users['marie'], $badges['starter'], now()->subDays(10)],
            [$users['leo'], $badges['team'], now()->subDays(30)],
            [$users['marie'], $badges['secours'], now()->subDays(2)],
        ];

        foreach ($rows as [$userId, $badgeId, $grantedAt]) {
            DB::table('user_badges')->insert([
                'id_utilisateur' => $userId,
                'id_badge' => $badgeId,
                'attribue_le' => $grantedAt,
                'created_at' => $grantedAt,
                'updated_at' => now(),
            ]);
        }
    }

    private function seedCertificates(array $users): void
    {
        $rows = [
            [
                'id_utilisateur' => $users['marie'],
                'titre_certificat' => 'PSC1',
                'emetteur_certificat' => 'Croix-Rouge genevoise',
                'date_emission_certificat' => '2025-11-15',
                'date_expiration_certificat' => '2027-11-15',
                'type_certificat' => 'external',
                'statut_certificat' => 'en attente',
                'chemin_fichier_certificat' => 'certificats/psc1-marie.pdf',
            ],
            [
                'id_utilisateur' => $users['leo'],
                'titre_certificat' => 'Formation accueil BeneRun',
                'emetteur_certificat' => 'BeneRun',
                'date_emission_certificat' => '2026-01-10',
                'date_expiration_certificat' => null,
                'type_certificat' => 'platform',
                'statut_certificat' => 'approuvé',
                'chemin_fichier_certificat' => null,
            ],
            [
                'id_utilisateur' => $users['zoe'],
                'titre_certificat' => 'Radio niveau 1',
                'emetteur_certificat' => 'Association Sportive Locale',
                'date_emission_certificat' => '2025-09-01',
                'date_expiration_certificat' => '2026-09-01',
                'type_certificat' => 'external',
                'statut_certificat' => 'rejeté',
                'chemin_fichier_certificat' => 'certificats/radio-zoe.jpg',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('certificats')->insert([
                ...$row,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedFavorites(array $users, array $missions): void
    {
        $rows = [
            [$users['marie'], $missions['ravitaillement']],
            [$users['marie'], $missions['signalisation']],
            [$users['leo'], $missions['vip']],
        ];

        foreach ($rows as [$userId, $missionId]) {
            DB::table('favorites')->insert([
                'id_utilisateur' => $userId,
                'id_mission' => $missionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();

        $tables = [
            'personal_access_tokens',
            'favorites',
            'user_badges',
            'certificats',
            'mission_medias',
            'mission_contacts',
            'affectations',
            'postulations',
            'mission_competences',
            'user_competences',
            'badges',
            'competences',
            'missions',
            'evenements',
            'users',
            'password_reset_tokens',
            'sessions',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();
    }
}