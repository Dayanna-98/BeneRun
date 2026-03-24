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

            $allUsers = DB::table('users')->orderBy('id_utilisateur')->pluck('id_utilisateur')->values();
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
                    DB::table('utilisateur_competences')->insert([
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
                [
                    'nom_course' => 'Marche Sante Geneve',
                    'lieu_course' => 'Parc de la Grange, Geneve',
                    'informations_course' => 'Marche organisee pour la promotion de la sante et du bien-etre.',
                    'date_debut_course' => '2026-03-24',
                    'date_fin_course' => '2026-03-24',
                    'heure_debut_course' => '09:00:00',
                    'heure_fin_course' => '12:00:00',
                    'annule_course' => false,
                    'date_annulation_course' => null,
                    'raison_annulation_course' => null,
                    'publie_course' => true,
                ],
            ];

            foreach ($courses as $course) {
                DB::table('courses')->insert($course);
            }

            $courseIds = DB::table('courses')->pluck('id_course')->values();
            $missions = [
                [
                    'id_course' => $courseIds[0],
                    'id_benevole' => $benevoleIds[0],
                    'titre_mission' => 'Ravitaillement Km 5',
                    'description_mission' => 'Distribution d eau et controle de la zone de ravitaillement.',
                    'date_debut_mission' => '2026-05-09',
                    'date_fin_mission' => '2026-05-09',
                    'heure_debut_mission' => '08:00:00',
                    'heure_fin_mission' => '11:30:00',
                    'lieu_mission' => 'Parc des Bastions',
                    'nombre_mission' => 8,
                    'statut_mission' => 'ouverte',
                    'publie_mission' => true,
                ],
                [
                    'id_course' => $courseIds[0],
                    'id_benevole' => $benevoleIds[0],
                    'titre_mission' => 'Signalisation virage centre-ville',
                    'description_mission' => 'Orienter les coureurs et securiser les traverses pietonnes.',
                    'date_debut_mission' => '2026-05-09',
                    'date_fin_mission' => '2026-05-09',
                    'heure_debut_mission' => '08:15:00',
                    'heure_fin_mission' => '12:00:00',
                    'lieu_mission' => 'Rue de Rhone',
                    'nombre_mission' => 6,
                    'statut_mission' => 'ouverte',
                    'publie_mission' => true,
                ],
                [
                    'id_course' => $courseIds[1],
                    'id_benevole' => $benevoleIds[2],
                    'titre_mission' => 'Accueil retrait dossards',
                    'description_mission' => 'Verification des inscriptions et remise des dossards.',
                    'date_debut_mission' => '2026-06-21',
                    'date_fin_mission' => '2026-06-21',
                    'heure_debut_mission' => '06:00:00',
                    'heure_fin_mission' => '09:30:00',
                    'lieu_mission' => 'Salle communale de Veyrier',
                    'nombre_mission' => 5,
                    'statut_mission' => 'ouverte',
                    'publie_mission' => true,
                ],
                [
                    'id_course' => $courseIds[2],
                    'id_benevole' => $benevoleIds[3],
                    'titre_mission' => 'Accueil et orientation marche',
                    'description_mission' => 'Accueillir les marchers et les orienter sur le parcours.',
                    'date_debut_mission' => '2026-03-24',
                    'date_fin_mission' => '2026-03-24',
                    'heure_debut_mission' => '08:30:00',
                    'heure_fin_mission' => '12:30:00',
                    'lieu_mission' => 'Parc de la Grange - Entree principale',
                    'nombre_mission' => 4,
                    'statut_mission' => 'ouverte',
                    'publie_mission' => true,
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
                    'id_benevole' => $benevoleIds[3],
                    'date_postulation' => now()->subDays(7),
                    'date_acceptation_refus_postulation' => now()->subDays(5),
                    'date_annulation_postulation' => now()->subDays(2),
                    'remarque_postulation' => 'Disponible toute la matinee.',
                    'presence_postulation' => true,
                    'commentaire_postulation' => 'Experience sur des courses 5k.',
                    'statut_postulation' => 'accepte',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_mission' => $missionIds[1],
                    'id_benevole' => $benevoleIds[4],
                    'date_postulation' => now()->subDays(6),
                    'date_acceptation_refus_postulation' => now()->subDays(4),
                    'date_annulation_postulation' => now()->subDays(1),
                    'remarque_postulation' => 'Preference pour poste de signalisation.',
                    'presence_postulation' => false,
                    'commentaire_postulation' => 'Peut venir avec velo pour se deplacer.',
                    'statut_postulation' => 'refuse',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_mission' => $missionIds[2],
                    'id_benevole' => $benevoleIds[0],
                    'date_postulation' => now()->subDays(3),
                    'date_acceptation_refus_postulation' => now()->subDays(1),
                    'date_annulation_postulation' => now(),
                    'remarque_postulation' => 'Souhaite aider en debut de journee.',
                    'presence_postulation' => true,
                    'commentaire_postulation' => 'Aisance avec public international.',
                    'statut_postulation' => 'en_attente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::table('affectations')->insert([
                [
                    'id_benevole' => $benevoleIds[3],
                    'id_mission' => $missionIds[0],
                    'statut_affectation' => 'confirmee',
                    'remarque_affectation' => 'Chef de poste ravitaillement.',
                    'estResponsable_affectation' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_benevole' => $benevoleIds[0],
                    'id_mission' => $missionIds[2],
                    'statut_affectation' => 'provisoire',
                    'remarque_affectation' => 'A confirmer 48h avant la course.',
                    'estResponsable_affectation' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_benevole' => $benevoleIds[1],
                    'id_mission' => $missionIds[3],
                    'statut_affectation' => 'confirmee',
                    'remarque_affectation' => 'Mission d accueil pour la marche de ce jour.',
                    'estResponsable_affectation' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::table('badges')->insert([
                'id_benevole' => $benevoleIds[0],
                'titre_badge' => 'Sprinteur solidaire',
                'valeur_badge' => 50,
                'regle_auto_badge' => '5 missions completees',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('certificats')->insert([
                'id_benevole' => $benevoleIds[3],
                'titre_certificat' => 'Participation Benevole Running Geneva 10K 2026',
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
            'utilisateur_competences',
            'competences',
            'admins',
            'benevoles',
            'courses',
            'users',
        ];

        foreach ($tables as $table) {
            DB::table($table)->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
