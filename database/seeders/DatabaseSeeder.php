<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->truncateTables();

        if (DB::table('users')->count() === 0) {
            $this->seedUsers();
        }

        $users = $this->resolveSeedUsersFromExisting();
        $competences = $this->seedCompetences();
        $events = $this->seedEvents($users);
        $missions = $this->seedMissions($users, $events);

        $this->seedUserCompetences($users, $competences);
        $this->seedMissionCompetences($missions, $competences);
        $this->seedMissionContacts($missions);
        $this->seedMissionMedias($missions, $users);
        $this->seedAffectations($users, $missions);
        $this->seedPostulations($users, $events, $missions);
        $badges = $this->seedBadges();
        $this->seedUserBadges($users, $badges);
        $this->seedCertificates($users);
        $this->seedFavorites($users, $missions);
        $this->seedEmergencyMessages($users, $events, $missions);
    }

    private function normalizeRoleForSeed(?string $role): string
    {
        $normalized = strtr(strtolower((string) $role), [
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'à' => 'a',
            'ù' => 'u',
        ]);

        return str_replace(['-', '_', ' '], '', $normalized);
    }

    private function resolveSeedUsersFromExisting(): array
    {
        $rows = DB::table('users')
            ->select('id_utilisateur', 'role_utilisateur')
            ->orderBy('id_utilisateur')
            ->get();

        if ($rows->isEmpty()) {
            throw new RuntimeException('Aucun utilisateur trouvé.');
        }

        $all = $rows->pluck('id_utilisateur')->values()->all();

        $superadmins = $rows
            ->filter(fn ($row) => $this->normalizeRoleForSeed($row->role_utilisateur) === 'superadmin')
            ->pluck('id_utilisateur')
            ->values()
            ->all();

        $admins = $rows
            ->filter(fn ($row) => $this->normalizeRoleForSeed($row->role_utilisateur) === 'admin')
            ->pluck('id_utilisateur')
            ->values()
            ->all();

        $responsables = $rows
            ->filter(function ($row) {
                $role = $this->normalizeRoleForSeed($row->role_utilisateur);

                return in_array($role, ['responsable', 'missionmanager', 'organisateur'], true);
            })
            ->pluck('id_utilisateur')
            ->values()
            ->all();

        $managers = $rows
            ->filter(function ($row) {
                $role = $this->normalizeRoleForSeed($row->role_utilisateur);

                return in_array($role, ['responsable', 'missionmanager', 'organisateur', 'admin', 'superadmin'], true);
            })
            ->pluck('id_utilisateur')
            ->values()
            ->all();

        $volunteers = $rows
            ->filter(function ($row) {
                $role = $this->normalizeRoleForSeed($row->role_utilisateur);

                return in_array($role, ['benevole', 'volunteer'], true);
            })
            ->pluck('id_utilisateur')
            ->values()
            ->all();

        $pick = static function (array $preferred, int $index, array $fallback): int {
            if ($preferred !== []) {
                return (int) ($preferred[$index % count($preferred)]);
            }

            return (int) ($fallback[$index % count($fallback)]);
        };

        return [
            'emma' => $pick($superadmins, 0, $all),
            'alexandre' => $pick($superadmins, 1, $all),
            'sofian' => $pick($admins, 0, $all),
            'marc' => $pick($responsables, 0, $managers !== [] ? $managers : $all),
            'dayanna' => $pick($volunteers, 0, $all),
            'leo' => $pick($volunteers, 1, $all),
            'nina' => $pick($volunteers, 2, $all),
            'zoe' => $pick($volunteers, 3, $all),
        ];
    }

    private function seedUsers(): array
    {
        $rows = [
            [
                'nom_utilisateur' => 'Rougeron',
                'prenom_utilisateur' => 'Emma',
                'email' => 'emmazeghdoud@gmail.com',
                'password' => Hash::make('Soleil12345'),
                'role_utilisateur' => 'superadmin',
            ],
            [
                'nom_utilisateur' => 'Madani',
                'prenom_utilisateur' => 'Sofian',
                'email' => 'sofian.madani@benerun.test',
                'password' => Hash::make('Soleil12345'),
                'role_utilisateur' => 'admin',
            ],
            [
                'nom_utilisateur' => 'Duval',
                'prenom_utilisateur' => 'Marc',
                'email' => 'marc.manager@benerun.test',
                'password' => Hash::make('Soleil12345'),
                'role_utilisateur' => 'responsable',
            ],
            [
                'nom_utilisateur' => 'Tenecela',
                'prenom_utilisateur' => 'Dayanna',
                'email' => 'dayanna.tenecela@benerun.test',
                'password' => Hash::make('Soleil12345'),
                'role_utilisateur' => 'bénévole',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('users')->insert([
                ...$row,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return DB::table('users')
            ->select('id_utilisateur')
            ->orderBy('id_utilisateur')
            ->pluck('id_utilisateur')
            ->all();
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
                'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2094471,6.1475688',
                'rayon_localisation_evenement' => 2500,
                'latitude_evenement' => 46.2094471,
                'longitude_evenement' => 6.1475688,
                'organisateur_evenement' => 'Running Geneva',
                'image_evenement' => 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=1200',
                'nombre_benevoles_requis' => 12,
                'est_annule_evenement' => false,
                'date_annulation_evenement' => null,
                'raison_annulation_evenement' => null,
                'est_publie_evenement' => true,
                'cree_par_utilisateur_id' => $users['sofian'],
            ],
            'night_run' => [
                'nom_evenement' => 'Geneva Night Run',
                'description_evenement' => 'Événement en brouillon pratique pour montrer un statut non publié.',
                'date_debut_evenement' => '2026-06-12',
                'date_fin_evenement' => '2026-06-12',
                'heure_debut_evenement' => '20:00:00',
                'heure_fin_evenement' => '23:00:00',
                'lieu_evenement' => 'Parc La Grange, Geneve',
                'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2049939,6.1628491',
                'rayon_localisation_evenement' => 1500,
                'latitude_evenement' => 46.2049939,
                'longitude_evenement' => 6.1628491,
                'organisateur_evenement' => 'Ville de Geneve',
                'image_evenement' => 'https://images.unsplash.com/photo-1508609349937-5ec4ae374ebf?w=1200',
                'nombre_benevoles_requis' => 10,
                'est_annule_evenement' => false,
                'date_annulation_evenement' => null,
                'raison_annulation_evenement' => null,
                'est_publie_evenement' => false,
                'cree_par_utilisateur_id' => $users['sofian'],
            ],
            'semi_printemps' => [
                'nom_evenement' => 'Semi du Printemps',
                'description_evenement' => 'Deuxième événement public sur un créneau proche pour tester les blocages de liste d attente et les redirections.',
                'date_debut_evenement' => '2026-05-09',
                'date_fin_evenement' => '2026-05-09',
                'heure_debut_evenement' => '09:00:00',
                'heure_fin_evenement' => '12:00:00',
                'lieu_evenement' => 'Plainpalais, Geneve',
                'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.1984130,6.1420180',
                'rayon_localisation_evenement' => 1800,
                'latitude_evenement' => 46.1984130,
                'longitude_evenement' => 6.1420180,
                'organisateur_evenement' => 'Association Sportive Genevoise',
                'image_evenement' => 'https://images.unsplash.com/photo-1486218119243-13883505764c?w=1200',
                'nombre_benevoles_requis' => 9,
                'est_annule_evenement' => false,
                'date_annulation_evenement' => null,
                'raison_annulation_evenement' => null,
                'est_publie_evenement' => true,
                'cree_par_utilisateur_id' => $users['sofian'],
            ],
            'tri_rhone' => [
                'nom_evenement' => 'Tri du Rhone',
                'description_evenement' => 'Événement annulé pour montrer les états et motifs d annulation côté gestion.',
                'date_debut_evenement' => '2026-07-04',
                'date_fin_evenement' => '2026-07-04',
                'heure_debut_evenement' => '09:00:00',
                'heure_fin_evenement' => '15:30:00',
                'lieu_evenement' => 'Jonction, Geneve',
                'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.1965072,6.1324421',
                'rayon_localisation_evenement' => 1200,
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
            'tgg_2026' => [
                'nom_evenement' => 'TGG 2026',
                'description_evenement' => 'Import partiel du planning CSV TGG 2026: postes, créneaux et consignes opérationnelles.',
                'date_debut_evenement' => '2026-05-29',
                'date_fin_evenement' => '2026-05-31',
                'heure_debut_evenement' => '06:00:00',
                'heure_fin_evenement' => '19:00:00',
                'lieu_evenement' => 'Vessy et Salève, Geneve',
                'google_maps_url_evenement' => 'https://maps.app.goo.gl/VKBWiZwBPT2qfDy19',
                'rayon_localisation_evenement' => 8000,
                'latitude_evenement' => null,
                'longitude_evenement' => null,
                'organisateur_evenement' => 'TGG Organisation',
                'image_evenement' => null,
                'nombre_benevoles_requis' => 80,
                'est_annule_evenement' => false,
                'date_annulation_evenement' => null,
                'raison_annulation_evenement' => null,
                'est_publie_evenement' => true,
                'cree_par_utilisateur_id' => $users['sofian'],
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
                'google_maps_url_mission' => 'https://www.google.com/maps?q=46.1997540,6.1423040',
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
                'responsable_utilisateur_id' => $users['sofian'],
                'titre_mission' => 'Signalisation centre-ville',
                'type_mission' => 'technique',
                'description_mission' => 'Orienter les coureurs et sécuriser les traversées piétonnes en centre-ville.',
                'date_mission' => '2026-05-09',
                'heure_debut_mission' => '08:00:00',
                'heure_fin_mission' => '12:30:00',
                'lieu_mission' => 'Rue du Rhone',
                'google_maps_url_mission' => 'https://www.google.com/maps?q=46.2021205,6.1499801',
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
                'google_maps_url_mission' => 'https://www.google.com/maps?q=46.2081000,6.1479000',
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
                'google_maps_url_mission' => 'https://www.google.com/maps?q=46.2048100,6.1632100',
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
            'briefing_printemps' => [
                'id_evenement' => $events['semi_printemps'],
                'responsable_utilisateur_id' => $users['marc'],
                'titre_mission' => 'Briefing bénévoles départ',
                'type_mission' => 'accueil',
                'description_mission' => 'Coordonner l accueil des bénévoles et la mise en place sur la zone de départ.',
                'date_mission' => '2026-05-09',
                'heure_debut_mission' => '08:45:00',
                'heure_fin_mission' => '10:30:00',
                'lieu_mission' => 'Plainpalais - arche départ',
                'google_maps_url_mission' => 'https://www.google.com/maps?q=46.1988500,6.1422500',
                'latitude_mission' => 46.1988500,
                'longitude_mission' => 6.1422500,
                'nombre_benevoles_max' => 4,
                'nombre_benevoles_backup' => 1,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Radio remise au briefing, arrivée 20 min avant.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(4),
            ],
            'canoe' => [
                'id_evenement' => $events['tri_rhone'],
                'responsable_utilisateur_id' => $users['sofian'],
                'titre_mission' => 'Zone départ canoë',
                'type_mission' => 'secours',
                'description_mission' => 'Sécurisation de la mise à l eau et coordination avec l équipe secours.',
                'date_mission' => '2026-07-04',
                'heure_debut_mission' => '08:00:00',
                'heure_fin_mission' => '12:00:00',
                'lieu_mission' => 'Quai du Rhone',
                'google_maps_url_mission' => 'https://www.google.com/maps?q=46.1991000,6.1327000',
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
            'tgg_remise_dossard_vendredi_midi' => [
                'id_evenement' => $events['tgg_2026'],
                'responsable_utilisateur_id' => $users['marc'],
                'titre_mission' => 'TGG - Remise dossard (vendredi midi)',
                'type_mission' => 'accueil',
                'description_mission' => 'Bloc CSV: VENDREDI / REMISE DOSSARD / 29-05-2026 / 11:30-14:30, note RDV 11h00.',
                'date_mission' => '2026-05-29',
                'heure_debut_mission' => '11:30:00',
                'heure_fin_mission' => '14:30:00',
                'lieu_mission' => 'Zone dossards TGG',
                'google_maps_url_mission' => null,
                'latitude_mission' => null,
                'longitude_mission' => null,
                'nombre_benevoles_max' => 4,
                'nombre_benevoles_backup' => 1,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'RDV recommandé 11h00 (planning CSV).',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(3),
            ],
            'tgg_montage_village_samedi' => [
                'id_evenement' => $events['tgg_2026'],
                'responsable_utilisateur_id' => $users['marc'],
                'titre_mission' => 'TGG - Montage village',
                'type_mission' => 'logistique',
                'description_mission' => 'Bloc CSV: SAMEDI / MONTAGE VILLAGE / 30-05-2026 / 15:00-19:00.',
                'date_mission' => '2026-05-30',
                'heure_debut_mission' => '15:00:00',
                'heure_fin_mission' => '19:00:00',
                'lieu_mission' => 'Village TGG',
                'google_maps_url_mission' => null,
                'latitude_mission' => null,
                'longitude_mission' => null,
                'nombre_benevoles_max' => 16,
                'nombre_benevoles_backup' => 2,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Note CSV: Anais RDV 14h30 avec hospice pour barrières.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(3),
            ],
            'tgg_depart_vessy_dimanche' => [
                'id_evenement' => $events['tgg_2026'],
                'responsable_utilisateur_id' => $users['sofian'],
                'titre_mission' => 'TGG - Départ Vessy',
                'type_mission' => 'technique',
                'description_mission' => 'Bloc CSV: DIMANCHE / DEPART VESSY / 31-05-2026 / 06:00.',
                'date_mission' => '2026-05-31',
                'heure_debut_mission' => '06:00:00',
                'heure_fin_mission' => '10:00:00',
                'lieu_mission' => 'Route de Vessy - Tennis',
                'google_maps_url_mission' => 'https://maps.app.goo.gl/VKBWiZwBPT2qfDy19',
                'latitude_mission' => null,
                'longitude_mission' => null,
                'nombre_benevoles_max' => 8,
                'nombre_benevoles_backup' => 2,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Coordination départ et balisage initial.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(3),
            ],
            'tgg_ravito_monnetier_dimanche' => [
                'id_evenement' => $events['tgg_2026'],
                'responsable_utilisateur_id' => $users['marc'],
                'titre_mission' => 'TGG - Ravito Monnetier',
                'type_mission' => 'logistique',
                'description_mission' => 'Bloc CSV: DIMANCHE / RAVITO MONNETIER / 31-05-2026 / 08:00-12:00.',
                'date_mission' => '2026-05-31',
                'heure_debut_mission' => '08:00:00',
                'heure_fin_mission' => '12:00:00',
                'lieu_mission' => 'Monnetier',
                'google_maps_url_mission' => null,
                'latitude_mission' => null,
                'longitude_mission' => null,
                'nombre_benevoles_max' => 6,
                'nombre_benevoles_backup' => 1,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Note CSV: responsables attendus à 08h00.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(3),
            ],
            'tgg_douanes_veyrier_dimanche' => [
                'id_evenement' => $events['tgg_2026'],
                'responsable_utilisateur_id' => $users['sofian'],
                'titre_mission' => 'TGG - Douanes Veyrier',
                'type_mission' => 'technique',
                'description_mission' => 'Bloc CSV: DIMANCHE / DOUANES VEYRIER / 31-05-2026 / 08:00-10:00.',
                'date_mission' => '2026-05-31',
                'heure_debut_mission' => '08:00:00',
                'heure_fin_mission' => '10:00:00',
                'lieu_mission' => 'Douanes Veyrier',
                'google_maps_url_mission' => 'https://maps.app.goo.gl/budnSwiAk6TzFiyP8',
                'latitude_mission' => null,
                'longitude_mission' => null,
                'nombre_benevoles_max' => 7,
                'nombre_benevoles_backup' => 1,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Poste prioritaire signalé dans le secteur 2.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(3),
            ],
            'tgg_ravito_arrivee_dimanche' => [
                'id_evenement' => $events['tgg_2026'],
                'responsable_utilisateur_id' => $users['marc'],
                'titre_mission' => 'TGG - Ravito arrivée',
                'type_mission' => 'logistique',
                'description_mission' => 'Bloc CSV: DIMANCHE / RAVITO ARRIVEE / 31-05-2026 / 09:30-15:30.',
                'date_mission' => '2026-05-31',
                'heure_debut_mission' => '09:30:00',
                'heure_fin_mission' => '15:30:00',
                'lieu_mission' => 'Zone arrivée TGG',
                'google_maps_url_mission' => null,
                'latitude_mission' => null,
                'longitude_mission' => null,
                'nombre_benevoles_max' => 12,
                'nombre_benevoles_backup' => 2,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Notes CSV: responsable à 09h00, pic d activité 10h30-12h30.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(3),
            ],
            'tgg_sac_coureur_arrivee_dimanche' => [
                'id_evenement' => $events['tgg_2026'],
                'responsable_utilisateur_id' => $users['marc'],
                'titre_mission' => 'TGG - Sac coureur arrivée',
                'type_mission' => 'accueil',
                'description_mission' => 'Bloc CSV: DIMANCHE / SAC COUREUR ARRIVEE / 31-05-2026 / 09:15-15:00.',
                'date_mission' => '2026-05-31',
                'heure_debut_mission' => '09:15:00',
                'heure_fin_mission' => '15:00:00',
                'lieu_mission' => 'Zone arrivée TGG',
                'google_maps_url_mission' => null,
                'latitude_mission' => null,
                'longitude_mission' => null,
                'nombre_benevoles_max' => 10,
                'nombre_benevoles_backup' => 2,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Note CSV: responsable attendu à 08h00.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(3),
            ],
            'tgg_remise_podium_dimanche' => [
                'id_evenement' => $events['tgg_2026'],
                'responsable_utilisateur_id' => $users['alexandre'],
                'titre_mission' => 'TGG - Remise podium',
                'type_mission' => 'animation',
                'description_mission' => 'Bloc CSV: DIMANCHE / REMISE PODIUM, vagues 12h00, 13h30 et 14h20.',
                'date_mission' => '2026-05-31',
                'heure_debut_mission' => '12:00:00',
                'heure_fin_mission' => '14:30:00',
                'lieu_mission' => 'Podium arrivée TGG',
                'google_maps_url_mission' => null,
                'latitude_mission' => null,
                'longitude_mission' => null,
                'nombre_benevoles_max' => 3,
                'nombre_benevoles_backup' => 1,
                'statut_mission' => 'À venir',
                'inscription_requise' => true,
                'visibilite_mission' => 'publique',
                'consignes_securite' => 'Présence requise avant la première vague du podium.',
                'image_mission' => null,
                'publie_le_mission' => now()->subDays(3),
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
            [$users['sofian'], $competences['signalisation'], 'expert'],
            [$users['sofian'], $competences['ravitaillement'], 'avancé'],
            [$users['marc'], $competences['ravitaillement'], 'expert'],
            [$users['marc'], $competences['logistique'], 'expert'],
            [$users['dayanna'], $competences['accueil'], 'avancé'],
            [$users['dayanna'], $competences['premiers_secours'], 'débutant'],
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
            [$missions['briefing_printemps'], $competences['accueil']],
            [$missions['briefing_printemps'], $competences['radio']],
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
            [$missions['signalisation'], 'Sofian Madani', '0795556677', 'sofian.madani@benerun.test', true, true],
            [$missions['vip'], 'Alexandre Rousseau', '0793334455', 'alexandre.rousseau@benerun.test', true, false],
            [$missions['dossards'], 'Marc Duval', '0782223344', 'marc.manager@benerun.test', true, true],
            [$missions['briefing_printemps'], 'Marc Duval', '0782223344', 'marc.manager@benerun.test', true, true],
            [$missions['tgg_remise_dossard_vendredi_midi'], 'Coordination TGG', '0790000000', null, true, true],
            [$missions['tgg_montage_village_samedi'], 'Coordination TGG', '0790000000', null, true, true],
            [$missions['tgg_depart_vessy_dimanche'], 'Claudio Minetto', '0790000001', null, true, true],
            [$missions['tgg_douanes_veyrier_dimanche'], 'Stephane Ciutad', '0790000002', null, true, true],
            [$missions['tgg_ravito_arrivee_dimanche'], 'Coordination TGG Arrivee', '0790000003', null, true, true],
            [$missions['tgg_sac_coureur_arrivee_dimanche'], 'Coordination TGG Arrivee', '0790000003', null, true, true],
            [$missions['tgg_remise_podium_dimanche'], 'Coordination Podium TGG', '0790000004', null, true, true],
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
            [$missions['signalisation'], 'missions/signalisation-zone.jpg', 'image/jpeg', 184320, $users['sofian']],
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
            [$users['emma'], $missions['ravitaillement'], 'assigne', false, 'Suivi global superadmin.', now()->subDays(6), null, null, '07:15:00'],
            [$users['emma'], $missions['signalisation'], 'confirme', false, 'Validation planning communication.', now()->subDays(5), now()->subDays(4), null, '07:45:00'],
            [$users['marc'], $missions['ravitaillement'], 'assigne', true, 'Responsable mission.', now()->subDays(8), null, null, '07:00:00'],
            [$users['dayanna'], $missions['ravitaillement'], 'confirme', false, 'Disponible dès 07h15.', now()->subDays(6), now()->subDays(4), null, '07:15:00'],
            [$users['leo'], $missions['ravitaillement'], 'assigne', false, 'Renfort ravitaillement.', now()->subDays(5), null, null, '07:15:00'],
            [$users['sofian'], $missions['signalisation'], 'present', true, 'Coordination parcours.', now()->subDays(7), now()->subDays(3), now()->subDays(1), '07:30:00'],
            [$users['dayanna'], $missions['signalisation'], 'present', false, 'Disponible en zone 1.', now()->subDays(5), now()->subDays(2), now()->subMinutes(10), '07:30:00'],
            [$users['zoe'], $missions['signalisation'], 'confirme', false, 'Disponible en zone 2.', now()->subDays(5), now()->subDays(2), null, '07:30:00'],
            [$users['alexandre'], $missions['vip'], 'assigne', true, 'Pilotage partenaires.', now()->subDays(5), null, null, '08:00:00'],
            [$users['marc'], $missions['dossards'], 'present', true, 'Mission terminée.', now()->subDays(20), now()->subDays(18), now()->subDays(16), '17:30:00'],
            [$users['dayanna'], $missions['dossards'], 'present', false, 'Accueil participants.', now()->subDays(20), now()->subDays(18), now()->subDays(16), '17:30:00'],
            [$users['marc'], $missions['briefing_printemps'], 'assigne', true, 'Responsable briefing.', now()->subDays(4), null, null, '08:30:00'],
            [$users['sofian'], $missions['canoe'], 'annule', true, 'Mission annulée avec l événement.', now()->subDays(12), null, null, '08:00:00'],
            [$users['alexandre'], $missions['tgg_remise_dossard_vendredi_midi'], 'assigne', true, 'Responsable d ouverture du stand dossards.', now()->subDays(2), null, null, '11:00:00'],
            [$users['dayanna'], $missions['tgg_montage_village_samedi'], 'confirme', false, 'Renfort logistique montage village.', now()->subDays(2), now()->subDay(), null, '14:30:00'],
            [$users['emma'], $missions['tgg_depart_vessy_dimanche'], 'present', false, 'Appui coordination départ.', now()->subDays(2), now()->subDay(), now()->subHours(6), '05:30:00'],
            [$users['sofian'], $missions['tgg_ravito_monnetier_dimanche'], 'assigne', true, 'Responsable ravito Monnetier.', now()->subDays(2), null, null, '08:00:00'],
            [$users['leo'], $missions['tgg_douanes_veyrier_dimanche'], 'confirme', false, 'Poste signalisation douanes.', now()->subDays(2), now()->subDay(), null, '07:45:00'],
            [$users['nina'], $missions['tgg_ravito_arrivee_dimanche'], 'assigne', false, 'Aide ravitaillement arrivée.', now()->subDays(2), null, null, '09:00:00'],
            [$users['zoe'], $missions['tgg_sac_coureur_arrivee_dimanche'], 'present', false, 'Distribution sacs coureurs.', now()->subDays(2), now()->subDay(), now()->subHours(4), '08:00:00'],
            [$users['marc'], $missions['tgg_remise_podium_dimanche'], 'assigne', true, 'Coordination des vagues podium.', now()->subDays(2), null, null, '11:30:00'],
        ];

        foreach ($rows as [$userId, $missionId, $status, $isResponsible, $note, $assignedAt, $confirmedAt, $presentAt, $meetingTime]) {
            DB::table('affectations')->insert([
                'id_utilisateur' => $userId,
                'id_mission' => $missionId,
                'statut_affectation' => $status,
                'est_responsable' => $isResponsible,
                'heure_rendez_vous_affectation' => $meetingTime,
                'remarque' => $note,
                'date_affectation' => $assignedAt,
                'date_confirmation' => $confirmedAt,
                'date_presence' => $presentAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedPostulations(array $users, array $events, array $missions): void
    {
        $rows = [
            [$missions['ravitaillement'], $events['geneva10k'] ?? null, $users['zoe'], 'en_attente', 'Disponible toute la matinée.', now()->subDays(2), null, null],
            [$missions['vip'], $events['geneva10k'] ?? null, $users['leo'], 'refuse', 'Préfère une mission publique.', now()->subDays(3), now()->subDays(2), null],
            [$missions['canoe'], $events['tri_rhone'] ?? null, $users['nina'], 'annule', 'Se retire suite à l annulation.', now()->subDays(10), now()->subDays(8), now()->subDays(7)],
            [null, $events['geneva10k'], $users['nina'], 'en_attente', 'Souhaite être dispatché sur l événement principal.', now()->subDays(1), null, null],
            [null, $events['semi_printemps'], $users['leo'], 'en_attente', 'Disponible seulement sur le créneau matin.', now()->subHours(18), null, null],
            [$missions['tgg_remise_dossard_vendredi_midi'], $events['tgg_2026'], $users['alexandre'], 'accepte', 'Créneau validé sur remise dossards.', now()->subDays(2), now()->subDay(), null],
            [$missions['tgg_montage_village_samedi'], $events['tgg_2026'], $users['dayanna'], 'accepte', 'Disponible pour le montage village.', now()->subDays(2), now()->subDay(), null],
            [$missions['tgg_depart_vessy_dimanche'], $events['tgg_2026'], $users['emma'], 'accepte', 'Présence au départ Vessy.', now()->subDays(2), now()->subDay(), null],
            [$missions['tgg_ravito_monnetier_dimanche'], $events['tgg_2026'], $users['sofian'], 'accepte', 'Supervision ravito Monnetier.', now()->subDays(2), now()->subDay(), null],
            [$missions['tgg_douanes_veyrier_dimanche'], $events['tgg_2026'], $users['leo'], 'accepte', 'Signalisation poste douanes.', now()->subDays(2), now()->subDay(), null],
            [$missions['tgg_ravito_arrivee_dimanche'], $events['tgg_2026'], $users['nina'], 'accepte', 'Renfort zone arrivée.', now()->subDays(2), now()->subDay(), null],
            [$missions['tgg_sac_coureur_arrivee_dimanche'], $events['tgg_2026'], $users['zoe'], 'accepte', 'Distribution sacs arrivants.', now()->subDays(2), now()->subDay(), null],
            [$missions['tgg_remise_podium_dimanche'], $events['tgg_2026'], $users['marc'], 'accepte', 'Coordination podium et flux.', now()->subDays(2), now()->subDay(), null],
        ];

        foreach ($rows as [$missionId, $eventId, $userId, $status, $note, $postedAt, $decisionAt, $cancelledAt]) {
            DB::table('postulations')->insert([
                'id_mission' => $missionId,
                'id_evenement' => $eventId,
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
            [$users['dayanna'], $badges['starter'], now()->subDays(10)],
            [$users['leo'], $badges['team'], now()->subDays(30)],
            [$users['dayanna'], $badges['secours'], now()->subDays(2)],
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
                'id_utilisateur' => $users['dayanna'],
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
            [$users['dayanna'], $missions['ravitaillement']],
            [$users['dayanna'], $missions['signalisation']],
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
            'mission_emergency_message_views',
            'mission_emergency_messages',
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
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();
    }

    private function seedEmergencyMessages(array $users, array $events, array $missions): void
    {
        $openEmergencyId = DB::table('mission_emergency_messages')->insertGetId([
            'id_mission' => $missions['signalisation'],
            'id_evenement' => $events['geneva10k'],
            'id_emetteur_utilisateur' => $users['dayanna'],
            'categorie_urgence' => 'security',
            'message_urgence' => 'Un spectateur force le passage à une barrière au carrefour zone 1.',
            'pris_en_charge_par_utilisateur_id' => null,
            'pris_en_charge_le' => null,
            'created_at' => now()->subMinutes(18),
            'updated_at' => now()->subMinutes(18),
        ], 'id_mission_emergency_message');

        $ownedEmergencyId = DB::table('mission_emergency_messages')->insertGetId([
            'id_mission' => $missions['signalisation'],
            'id_evenement' => $events['geneva10k'],
            'id_emetteur_utilisateur' => $users['zoe'],
            'categorie_urgence' => 'medical',
            'message_urgence' => 'Un coureur désorienté a besoin d assistance au point de traversée sud.',
            'pris_en_charge_par_utilisateur_id' => $users['emma'],
            'pris_en_charge_le' => now()->subMinutes(35),
            'created_at' => now()->subMinutes(42),
            'updated_at' => now()->subMinutes(35),
        ], 'id_mission_emergency_message');

        $views = [
            [$openEmergencyId, $users['alexandre'], now()->subMinutes(15)],
            [$ownedEmergencyId, $users['emma'], now()->subMinutes(36)],
            [$ownedEmergencyId, $users['alexandre'], now()->subMinutes(33)],
        ];

        foreach ($views as [$messageId, $userId, $viewedAt]) {
            DB::table('mission_emergency_message_views')->insert([
                'id_mission_emergency_message' => $messageId,
                'id_utilisateur' => $userId,
                'consulte_le' => $viewedAt,
                'created_at' => $viewedAt,
                'updated_at' => $viewedAt,
            ]);
        }
    }
}
