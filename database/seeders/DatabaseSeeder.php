<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->truncateTables();

            $now = now();

            $users = [
                [
                    'nom_utilisateur' => 'Dupont',
                    'prenom_utilisateur' => 'Camille',
                    'email' => 'camille.dupont@benerun.test',
                    'role_utilisateur' => 'bénévole',
                    'telephone_utilisateur' => '+41791234567',
                    'taille_tshirt_utilisateur' => 'M',
                    'permissions_utilisateur' => null,
                ],
                [
                    'nom_utilisateur' => 'Martin',
                    'prenom_utilisateur' => 'Luca',
                    'email' => 'luca.martin@benerun.test',
                    'role_utilisateur' => 'bénévole',
                    'telephone_utilisateur' => '+41799876543',
                    'taille_tshirt_utilisateur' => 'L',
                    'permissions_utilisateur' => null,
                ],
                [
                    'nom_utilisateur' => 'Bernard',
                    'prenom_utilisateur' => 'Sarah',
                    'email' => 'sarah.bernard@benerun.test',
                    'role_utilisateur' => 'responsable',
                    'telephone_utilisateur' => '+41794443322',
                    'taille_tshirt_utilisateur' => 'S',
                    'permissions_utilisateur' => 'missions:create,missions:update',
                ],
                [
                    'nom_utilisateur' => 'Petit',
                    'prenom_utilisateur' => 'Noah',
                    'email' => 'noah.petit@benerun.test',
                    'role_utilisateur' => 'bénévole',
                    'telephone_utilisateur' => '+41793334455',
                    'taille_tshirt_utilisateur' => 'M',
                    'permissions_utilisateur' => null,
                ],
                [
                    'nom_utilisateur' => 'Rossi',
                    'prenom_utilisateur' => 'Elena',
                    'email' => 'elena.rossi@benerun.test',
                    'role_utilisateur' => 'admin',
                    'telephone_utilisateur' => '+41792221100',
                    'taille_tshirt_utilisateur' => 'M',
                    'permissions_utilisateur' => 'events:manage,users:read',
                ],
                [
                    'nom_utilisateur' => 'Admin',
                    'prenom_utilisateur' => 'Super',
                    'email' => 'admin@benerun.test',
                    'role_utilisateur' => 'superadmin',
                    'telephone_utilisateur' => '+41790000000',
                    'taille_tshirt_utilisateur' => 'XL',
                    'permissions_utilisateur' => 'all',
                ],
            ];

            foreach ($users as $user) {
                DB::table('users')->insert([
                    ...$user,
                    'password' => Hash::make('password'),
                    'email_verified_at' => $now,
                    'remember_token' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $userIds = DB::table('users')->orderBy('id_utilisateur')->pluck('id_utilisateur')->values();

            $competences = [
                'Premiers secours',
                'Gestion ravitaillement',
                'Signalisation parcours',
                'Accueil participants',
                'Communication radio',
                'Logistique evenementielle',
            ];

            foreach ($competences as $competence) {
                DB::table('competences')->insert([
                    'nom_competence' => $competence,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $competenceIds = DB::table('competences')->pluck('id_competence')->values();

            foreach ($userIds->take(5) as $userId) {
                foreach ($competenceIds->shuffle()->take(2) as $competenceId) {
                    DB::table('user_competences')->insert([
                        'id_utilisateur' => $userId,
                        'id_competence' => $competenceId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $evenements = [
                [
                    'nom_evenement' => 'Running Geneva 10K',
                    'description_evenement' => 'Course populaire sur route, acces a tous les niveaux.',
                    'date_debut_evenement' => '2026-05-09',
                    'date_fin_evenement' => '2026-05-09',
                    'heure_debut_evenement' => '08:30:00',
                    'heure_fin_evenement' => '13:00:00',
                    'lieu_evenement' => 'Geneve - Quai du Mont-Blanc',
                    'latitude_evenement' => 46.209743,
                    'longitude_evenement' => 6.147955,
                    'organisateur_evenement' => 'Running Geneva Association',
                    'image_evenement' => null,
                    'nombre_benevoles_requis' => 40,
                    'est_annule_evenement' => false,
                    'date_annulation_evenement' => null,
                    'raison_annulation_evenement' => null,
                    'est_publie_evenement' => true,
                    'cree_par_utilisateur_id' => $userIds[4],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'nom_evenement' => 'Trail du Saleve',
                    'description_evenement' => 'Trail nature avec sections techniques et zones assistance.',
                    'date_debut_evenement' => '2026-06-21',
                    'date_fin_evenement' => '2026-06-21',
                    'heure_debut_evenement' => '07:00:00',
                    'heure_fin_evenement' => '15:30:00',
                    'lieu_evenement' => 'Veyrier',
                    'latitude_evenement' => 46.173847,
                    'longitude_evenement' => 6.196744,
                    'organisateur_evenement' => 'Trail Club Saleve',
                    'image_evenement' => null,
                    'nombre_benevoles_requis' => 65,
                    'est_annule_evenement' => false,
                    'date_annulation_evenement' => null,
                    'raison_annulation_evenement' => null,
                    'est_publie_evenement' => true,
                    'cree_par_utilisateur_id' => $userIds[5],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'nom_evenement' => 'Marche Sante Geneve',
                    'description_evenement' => 'Marche grand public dediee au bien-etre et a la prevention.',
                    'date_debut_evenement' => '2026-09-12',
                    'date_fin_evenement' => '2026-09-12',
                    'heure_debut_evenement' => '09:00:00',
                    'heure_fin_evenement' => '12:00:00',
                    'lieu_evenement' => 'Parc de la Grange, Geneve',
                    'latitude_evenement' => 46.201233,
                    'longitude_evenement' => 6.164904,
                    'organisateur_evenement' => 'Ville de Geneve',
                    'image_evenement' => null,
                    'nombre_benevoles_requis' => 25,
                    'est_annule_evenement' => false,
                    'date_annulation_evenement' => null,
                    'raison_annulation_evenement' => null,
                    'est_publie_evenement' => false,
                    'cree_par_utilisateur_id' => $userIds[2],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            foreach ($evenements as $evenement) {
                DB::table('evenements')->insert($evenement);
            }

            $evenementIds = DB::table('evenements')->pluck('id_evenement')->values();

            $missions = [
                [
                    'id_evenement' => $evenementIds[0],
                    'responsable_utilisateur_id' => $userIds[2],
                    'titre_mission' => 'Ravitaillement Km 5',
                    'type_mission' => 'logistique',
                    'description_mission' => 'Distribution d eau et supervision de la zone ravitaillement.',
                    'date_mission' => '2026-05-09',
                    'heure_debut_mission' => '08:00:00',
                    'heure_fin_mission' => '11:30:00',
                    'lieu_mission' => 'Parc des Bastions',
                    'latitude_mission' => 46.199375,
                    'longitude_mission' => 6.143158,
                    'nombre_benevoles_max' => 8,
                    'nombre_benevoles_backup' => 2,
                    'statut_mission' => 'open',
                    'inscription_requise' => true,
                    'visibilite_mission' => 'public',
                    'consignes_securite' => 'Gants et gel hydroalcoolique obligatoires.',
                    'image_mission' => null,
                    'publie_le_mission' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_evenement' => $evenementIds[1],
                    'responsable_utilisateur_id' => $userIds[5],
                    'titre_mission' => 'Signalisation virage foret',
                    'type_mission' => 'technique',
                    'description_mission' => 'Orienter les coureurs et securiser les passages sensibles.',
                    'date_mission' => '2026-06-21',
                    'heure_debut_mission' => '06:30:00',
                    'heure_fin_mission' => '12:30:00',
                    'lieu_mission' => 'Sentier du Saleve - point 3',
                    'latitude_mission' => 46.163201,
                    'longitude_mission' => 6.178341,
                    'nombre_benevoles_max' => 6,
                    'nombre_benevoles_backup' => 2,
                    'statut_mission' => 'open',
                    'inscription_requise' => true,
                    'visibilite_mission' => 'restricted',
                    'consignes_securite' => 'Radio obligatoire, briefing securite a 06:00.',
                    'image_mission' => null,
                    'publie_le_mission' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_evenement' => $evenementIds[2],
                    'responsable_utilisateur_id' => $userIds[4],
                    'titre_mission' => 'Accueil participants',
                    'type_mission' => 'accueil',
                    'description_mission' => 'Accueil, orientation et support information a l entree du parc.',
                    'date_mission' => '2026-09-12',
                    'heure_debut_mission' => '08:15:00',
                    'heure_fin_mission' => '12:30:00',
                    'lieu_mission' => 'Parc de la Grange - entree principale',
                    'latitude_mission' => 46.200987,
                    'longitude_mission' => 6.165302,
                    'nombre_benevoles_max' => 5,
                    'nombre_benevoles_backup' => 1,
                    'statut_mission' => 'draft',
                    'inscription_requise' => false,
                    'visibilite_mission' => 'public',
                    'consignes_securite' => null,
                    'image_mission' => null,
                    'publie_le_mission' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            foreach ($missions as $mission) {
                DB::table('missions')->insert($mission);
            }

            $missionIds = DB::table('missions')->pluck('id_mission')->values();

            DB::table('mission_contacts')->insert([
                [
                    'id_mission' => $missionIds[0],
                    'nom_contact' => 'Sarah Bernard',
                    'telephone_contact' => '+41794443322',
                    'email_contact' => 'sarah.bernard@benerun.test',
                    'est_contact_principal' => true,
                    'est_contact_jour_j' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_mission' => $missionIds[1],
                    'nom_contact' => 'Super Admin',
                    'telephone_contact' => '+41790000000',
                    'email_contact' => 'admin@benerun.test',
                    'est_contact_principal' => true,
                    'est_contact_jour_j' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            DB::table('mission_medias')->insert([
                [
                    'id_mission' => $missionIds[0],
                    'chemin_fichier' => 'missions/ravitaillement-km5-plan.pdf',
                    'type_mime' => 'application/pdf',
                    'taille_fichier' => 245760,
                    'televerse_par_utilisateur_id' => $userIds[2],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_mission' => $missionIds[1],
                    'chemin_fichier' => 'missions/trail-signalisation.jpg',
                    'type_mime' => 'image/jpeg',
                    'taille_fichier' => 389120,
                    'televerse_par_utilisateur_id' => $userIds[5],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            foreach ($missionIds as $missionId) {
                foreach ($competenceIds->shuffle()->take(2) as $competenceId) {
                    DB::table('mission_competences')->insert([
                        'id_mission' => $missionId,
                        'id_competence' => $competenceId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            DB::table('postulations')->insert([
                [
                    'id_mission' => $missionIds[0],
                    'id_utilisateur' => $userIds[0],
                    'statut_postulation' => 'accepte',
                    'remarque' => 'Disponible toute la matinee.',
                    'date_postulation' => $now->copy()->subDays(9),
                    'date_decision' => $now->copy()->subDays(7),
                    'date_annulation' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_mission' => $missionIds[0],
                    'id_utilisateur' => $userIds[1],
                    'statut_postulation' => 'en_attente',
                    'remarque' => 'Je peux couvrir la plage 09h-11h.',
                    'date_postulation' => $now->copy()->subDays(2),
                    'date_decision' => null,
                    'date_annulation' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_mission' => $missionIds[1],
                    'id_utilisateur' => $userIds[3],
                    'statut_postulation' => 'refuse',
                    'remarque' => 'Pas de disponibilite le matin tres tot.',
                    'date_postulation' => $now->copy()->subDays(5),
                    'date_decision' => $now->copy()->subDays(4),
                    'date_annulation' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            DB::table('affectations')->insert([
                [
                    'id_mission' => $missionIds[0],
                    'id_utilisateur' => $userIds[0],
                    'statut_affectation' => 'confirme',
                    'est_responsable' => false,
                    'remarque' => 'Assigne au stand eau.',
                    'date_affectation' => $now->copy()->subDays(7),
                    'date_confirmation' => $now->copy()->subDays(7),
                    'date_presence' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_mission' => $missionIds[1],
                    'id_utilisateur' => $userIds[3],
                    'statut_affectation' => 'assigne',
                    'est_responsable' => false,
                    'remarque' => 'Attente confirmation finale.',
                    'date_affectation' => $now->copy()->subDays(3),
                    'date_confirmation' => null,
                    'date_presence' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_mission' => $missionIds[0],
                    'id_utilisateur' => $userIds[2],
                    'statut_affectation' => 'present',
                    'est_responsable' => true,
                    'remarque' => 'Responsable mission.',
                    'date_affectation' => $now->copy()->subDays(10),
                    'date_confirmation' => $now->copy()->subDays(10),
                    'date_presence' => $now->copy()->subDay(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            DB::table('badges')->insert([
                [
                    'titre_badge' => 'Sprinteur solidaire',
                    'description_badge' => 'Attribue apres 5 missions realisees.',
                    'icone_badge' => 'badges/sprinteur-solidaire.svg',
                    'score_badge' => 50,
                    'regle_auto' => '5 missions completees',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'titre_badge' => 'Pilier logistique',
                    'description_badge' => 'Participation active sur des missions logistiques.',
                    'icone_badge' => 'badges/pilier-logistique.svg',
                    'score_badge' => 80,
                    'regle_auto' => '10 missions logistiques',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            $badgeIds = DB::table('badges')->pluck('id_badge')->values();

            DB::table('user_badges')->insert([
                [
                    'id_utilisateur' => $userIds[0],
                    'id_badge' => $badgeIds[0],
                    'attribue_le' => $now->copy()->subDays(12),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_utilisateur' => $userIds[2],
                    'id_badge' => $badgeIds[1],
                    'attribue_le' => $now->copy()->subDays(4),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            DB::table('certificats')->insert([
                [
                    'id_utilisateur' => $userIds[0],
                    'titre_certificat' => 'Participation Benevole Running Geneva 10K 2026',
                    'emetteur_certificat' => 'Running Geneva Association',
                    'date_emission_certificat' => '2026-05-15',
                    'date_expiration_certificat' => null,
                    'type_certificat' => 'platform',
                    'statut_certificat' => 'approved',
                    'chemin_fichier_certificat' => 'certificats/rg10k-2026-camille-dupont.pdf',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_utilisateur' => $userIds[3],
                    'titre_certificat' => 'Formation premiers secours',
                    'emetteur_certificat' => 'Croix-Rouge Geneve',
                    'date_emission_certificat' => '2026-02-03',
                    'date_expiration_certificat' => '2028-02-03',
                    'type_certificat' => 'external',
                    'statut_certificat' => 'pending',
                    'chemin_fichier_certificat' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            DB::table('favorites')->insert([
                [
                    'id_utilisateur' => $userIds[0],
                    'id_mission' => $missionIds[1],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id_utilisateur' => $userIds[1],
                    'id_mission' => $missionIds[0],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        });
    }

    private function truncateTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'favorites',
            'user_badges',
            'user_competences',
            'mission_competences',
            'mission_medias',
            'mission_contacts',
            'affectations',
            'postulations',
            'missions',
            'certificats',
            'badges',
            'competences',
            'evenements',
            'users',
        ];

        foreach ($tables as $table) {
            DB::table($table)->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
