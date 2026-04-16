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

            $users = [
                [
                    'nom_utilisateur' => 'Dupont',
                    'prenom_utilisateur' => 'Camille',
                    'email' => 'camille.dupont@benerun.test',
                ],
                [
                    'nom_utilisateur' => 'Martin',
                    'prenom_utilisateur' => 'Luca',
                    'email' => 'luca.martin@benerun.test',
                ],
                [
                    'nom_utilisateur' => 'Bernard',
                    'prenom_utilisateur' => 'Sarah',
                    'email' => 'sarah.bernard@benerun.test',
                ],
                [
                    'nom_utilisateur' => 'Petit',
                    'prenom_utilisateur' => 'Noah',
                    'email' => 'noah.petit@benerun.test',
                ],
                [
                    'nom_utilisateur' => 'Rossi',
                    'prenom_utilisateur' => 'Elena',
                    'email' => 'elena.rossi@benerun.test',
                ],
                [
                    'nom_utilisateur' => 'Admin',
                    'prenom_utilisateur' => 'Super',
                    'email' => 'admin@benerun.test',
                ],
            ];

            foreach ($users as $user) {
                DB::table('users')->insert([
                    ...$user,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'remember_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $allUsers = DB::table('users')->pluck('id_utilisateur')->values();
            $benevoleIds = [];

            foreach ($allUsers->take(5) as $userId) {
                DB::table('benevoles')->insert([
                    'id_utilisateur' => $userId,
                    'nb_missions_benevole' => fake()->numberBetween(0, 12),
                ]);

                $benevoleIds[] = DB::getPdo()->lastInsertId();
            }

            DB::table('admins')->insert([
                'id_utilisateur' => $allUsers->last(),
                'est_organisateur_admin' => true,
                'permission_admin' => 'creer_course',
            ]);

            $competences = [
                'Premiers secours',
                'Gestion de ravitaillement',
                'Signalisation parcours',
                'Accueil coureurs',
                'Communication radio',
                'Logistique evenementielle',
            ];

            foreach ($competences as $competence) {
                DB::table('competences')->insert([
                    'nom_competence' => $competence,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $competenceIds = DB::table('competences')->pluck('id_competence')->values();

            foreach ($allUsers->take(5) as $userId) {
                $sampleCompetences = $competenceIds->shuffle()->take(fake()->numberBetween(2, 4));
                foreach ($sampleCompetences as $competenceId) {
                    DB::table('user_competences')->insert([
                        'id_utilisateur' => $userId,
                        'id_competence' => $competenceId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $courses = [
                [
                    'nom_course' => 'Running Geneva 10K',
                    'lieu_course' => 'Geneve - Quai du Mont-Blanc',
                    'informations_course' => 'Course populaire sur route, acces a tous les niveaux.',
                    'date_debut_course' => '2026-05-09',
                    'date_fin_course' => '2026-05-09',
                    'heure_debut_course' => '08:30:00',
                    'heure_fin_course' => '13:00:00',
                    'annule_course' => false,
                    'date_annulation_course' => null,
                    'raison_annulation_course' => null,
                    'publie_course' => true,
                ],
                [
                    'nom_course' => 'Trail du Saleve',
                    'lieu_course' => 'Veyrier',
                    'informations_course' => 'Trail nature avec sections techniques et assistance benevoles.',
                    'date_debut_course' => '2026-06-21',
                    'date_fin_course' => '2026-06-21',
                    'heure_debut_course' => '07:00:00',
                    'heure_fin_course' => '15:30:00',
                    'annule_course' => false,
                    'date_annulation_course' => null,
                    'raison_annulation_course' => null,
                    'publie_course' => true,
                ],
            ];

            foreach ($courses as $course) {
                DB::table('courses')->insert($course);
            }

            $eventData = [
                [
                    'nom_evenement' => 'Running Geneva 10K',
                    'description_evenement' => 'Course populaire sur route 10km, accessible à tous les niveaux.',
                    'date_debut_evenement' => '2026-05-09',
                    'date_fin_evenement' => '2026-05-09',
                    'heure_debut_evenement' => '08:30:00',
                    'heure_fin_evenement' => '13:00:00',
                    'lieu_evenement' => 'Geneve - Quai du Mont-Blanc',
                    'organisateur_evenement' => 'Fédération Suisse Athlétisme',
                    'nombre_benevoles_requis' => 20,
                    'est_publie_evenement' => true,
                    'cree_par_utilisateur_id' => $allUsers->last(),
                ],
                [
                    'nom_evenement' => 'Trail du Saleve',
                    'description_evenement' => 'Trail nature avec sections techniques et assistance bénévoles.',
                    'date_debut_evenement' => '2026-06-21',
                    'date_fin_evenement' => '2026-06-21',
                    'heure_debut_evenement' => '07:00:00',
                    'heure_fin_evenement' => '15:30:00',
                    'lieu_evenement' => 'Veyrier',
                    'organisateur_evenement' => 'Club Montagne',
                    'nombre_benevoles_requis' => 15,
                    'est_publie_evenement' => true,
                    'cree_par_utilisateur_id' => $allUsers->last(),
                ],
            ];

            foreach ($eventData as $event) {
                DB::table('evenements')->insert(array_merge(
                    $event,
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ));
            }

            $courseIds = DB::table('courses')->pluck('id_course')->values();
            $evenementIds = DB::table('evenements')->pluck('id_evenement')->values();
            $missions = [
                [
                    'id_evenement' => $evenementIds[0] ?? 1,
                    'responsable_utilisateur_id' => $allUsers[0],
                    'titre_mission' => 'Ravitaillement Km 5',
                    'type_mission' => 'logistique',
                    'description_mission' => 'Distribution d eau et controle de la zone de ravitaillement.',
                    'date_mission' => '2026-05-09',
                    'heure_debut_mission' => '08:00:00',
                    'heure_fin_mission' => '11:30:00',
                    'lieu_mission' => 'Parc des Bastions',
                    'nombre_benevoles_max' => 8,
                    'nombre_benevoles_backup' => 2,
                    'statut_mission' => 'À venir',
                    'inscription_requise' => true,
                    'visibilite_mission' => 'publique',
                    'publie_le_mission' => now(),
                ],
                [
                    'id_evenement' => $evenementIds[0] ?? 1,
                    'responsable_utilisateur_id' => $allUsers[1],
                    'titre_mission' => 'Signalisation virage centre-ville',
                    'type_mission' => 'technique',
                    'description_mission' => 'Orienter les coureurs et securiser les traverses pietonnes.',
                    'date_mission' => '2026-05-09',
                    'heure_debut_mission' => '08:15:00',
                    'heure_fin_mission' => '12:00:00',
                    'lieu_mission' => 'Rue de Rhone',
                    'nombre_benevoles_max' => 6,
                    'nombre_benevoles_backup' => 1,
                    'statut_mission' => 'À venir',
                    'inscription_requise' => true,
                    'visibilite_mission' => 'publique',
                    'publie_le_mission' => now(),
                ],
                [
                    'id_evenement' => $evenementIds[1] ?? 2,
                    'responsable_utilisateur_id' => $allUsers[2],
                    'titre_mission' => 'Accueil retrait dossards',
                    'type_mission' => 'accueil',
                    'description_mission' => 'Verification des inscriptions et remise des dossards.',
                    'date_mission' => '2026-06-21',
                    'heure_debut_mission' => '06:00:00',
                    'heure_fin_mission' => '09:30:00',
                    'lieu_mission' => 'Salle communale de Veyrier',
                    'nombre_benevoles_max' => 5,
                    'nombre_benevoles_backup' => 1,
                    'statut_mission' => 'À venir',
                    'inscription_requise' => true,
                    'visibilite_mission' => 'publique',
                    'publie_le_mission' => now(),
                ],
            ];

            foreach ($missions as $mission) {
                DB::table('missions')->insert([
                    ...$mission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $missionIds = DB::table('missions')->pluck('id_mission')->values();

            DB::table('postulations')->insert([
                [
                    'id_mission' => $missionIds[0],
                    'id_utilisateur' => $allUsers[3],
                    'date_decision' => now()->subDays(5),
                    'date_annulation' => now()->subDays(2),
                    'remarque' => 'Disponible toute la matinee. Experience sur des courses 5k.',
                    'statut_postulation' => 'accepte',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_mission' => $missionIds[1],
                    'id_utilisateur' => $allUsers[4],
                    'date_decision' => now()->subDays(4),
                    'date_annulation' => now()->subDays(1),
                    'remarque' => 'Preference pour poste de signalisation. Peut venir avec velo pour se deplacer.',
                    'statut_postulation' => 'refuse',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_mission' => $missionIds[2],
                    'id_utilisateur' => $allUsers[0],
                    'date_decision' => now()->subDays(1),
                    'remarque' => 'Souhaite aider en debut de journee. Aisance avec public international.',
                    'statut_postulation' => 'en_attente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::table('affectations')->insert([
                [
                    'id_utilisateur' => $allUsers[3],
                    'id_mission' => $missionIds[0],
                    'statut_affectation' => 'confirme',
                    'remarque' => 'Chef de poste ravitaillement.',
                    'est_responsable' => true,
                    'date_affectation' => now()->subDays(5),
                    'date_confirmation' => now()->subDays(3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_utilisateur' => $allUsers[0],
                    'id_mission' => $missionIds[2],
                    'statut_affectation' => 'assigne',
                    'remarque' => 'A confirmer 48h avant la course.',
                    'est_responsable' => false,
                    'date_affectation' => now()->subDays(3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::table('badges')->insert([
                'titre_badge' => 'Sprinteur solidaire',
                'description_badge' => 'Complété votre première mission',
                'score_badge' => 50,
                'regle_auto' => '5 missions completees',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $badgeIds = DB::table('badges')->pluck('id_badge')->values();

            DB::table('user_badges')->insert([
                'id_utilisateur' => $allUsers[0],
                'id_badge' => $badgeIds[0],
                'attribue_le' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('certificats')->insert([
                'id_utilisateur' => $allUsers[3],
                'titre_certificat' => 'Participation Benevole Running Geneva 10K 2026',
                'type_certificat' => 'external',
                'statut_certificat' => 'approuvé',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $telephones = [
                [
                    'id_mission' => $missionIds[0],
                    'id_course' => $courseIds[0],
                    'description_telephone' => 'Coordination ravitaillement',
                    'numero_telephone' => 794567890,
                    'detail_telephone' => 'Actif la veille et le jour J',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_mission' => $missionIds[2],
                    'id_course' => $courseIds[1],
                    'description_telephone' => 'Urgence parcours trail',
                    'numero_telephone' => 785432109,
                    'detail_telephone' => 'Ligne prioritaire securite',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($telephones as $telephone) {
                DB::table('telephones')->insert($telephone);
            }

            $telephoneIds = DB::table('telephones')->pluck('id_telephone')->values();

            foreach ($telephoneIds as $index => $telephoneId) {
                DB::table('mission_telephones')->insert([
                    'id_mission' => $telephones[$index]['id_mission'],
                    'id_telephone' => $telephoneId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('course_telephones')->insert([
                    'id_course' => $telephones[$index]['id_course'],
                    'id_telephone' => $telephoneId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($missionIds as $missionId) {
                foreach ($competenceIds->shuffle()->take(2) as $competenceId) {
                    DB::table('mission_competences')->insert([
                        'id_mission' => $missionId,
                        'id_competence' => $competenceId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    private function truncateTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'mission_competences',
            'course_telephones',
            'mission_telephones',
            'telephones',
            'documents',
            'affectations',
            'postulations',
            'missions',
            'certificats',
            'badges',
            'user_competences',
            'competences',
            'evenements',
            'courses',
            'admins',
            'benevoles',
            'users',
        ];

        foreach ($tables as $table) {
            DB::table($table)->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
