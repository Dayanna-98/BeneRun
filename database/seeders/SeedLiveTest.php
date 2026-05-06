<?php

use Illuminate\Support\Facades\DB;

$emma    = DB::table('users')->where('email', 'emma.rougeron@benerun.test')->value('id_utilisateur');
$dayanna = DB::table('users')->where('email', 'dayanna.tenecela@benerun.test')->value('id_utilisateur');
$marc    = DB::table('users')->where('email', 'marc.manager@benerun.test')->value('id_utilisateur');
$sofian  = DB::table('users')->where('email', 'sofian.madani@benerun.test')->value('id_utilisateur');
$leo     = DB::table('users')->where('email', 'leo.benevole@benerun.test')->value('id_utilisateur');

if (!$emma || !$dayanna || !$marc || !$sofian || !$leo) {
    echo "ERREUR: utilisateurs introuvables (emma=$emma, dayanna=$dayanna, marc=$marc, sofian=$sofian, leo=$leo)\n";
    return;
}

$eventId = DB::table('evenements')->insertGetId([
    'nom_evenement'               => 'Course du Lac - Live test',
    'description_evenement'       => 'Evenement de test pour valider les messages urgence en temps reel.',
    'date_debut_evenement'        => now()->toDateString(),
    'date_fin_evenement'          => now()->toDateString(),
    'heure_debut_evenement'       => now()->subHour()->format('H:i:s'),
    'heure_fin_evenement'         => now()->addHours(3)->format('H:i:s'),
    'lieu_evenement'              => 'Quai du Mont-Blanc, Geneve',
    'google_maps_url_evenement'   => 'https://www.google.com/maps?q=46.2094471,6.1475688',
    'rayon_localisation_evenement'=> 2000,
    'latitude_evenement'          => 46.2094471,
    'longitude_evenement'         => 6.1475688,
    'organisateur_evenement'      => 'BeneRun Test',
    'image_evenement'             => 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=1200',
    'nombre_benevoles_requis'     => 6,
    'est_annule_evenement'        => false,
    'est_publie_evenement'        => true,
    'cree_par_utilisateur_id'     => $emma,
    'created_at' => now(),
    'updated_at' => now(),
], 'id_evenement');

$m1 = DB::table('missions')->insertGetId([
    'id_evenement'                => $eventId,
    'responsable_utilisateur_id'  => $marc,
    'titre_mission'               => 'Securite depart - Quai',
    'type_mission'                => 'secours',
    'description_mission'         => 'Gestion des barrieres et orientation au depart de la course.',
    'date_mission'                => now()->toDateString(),
    'heure_debut_mission'         => now()->subHour()->format('H:i:s'),
    'heure_fin_mission'           => now()->addHours(2)->format('H:i:s'),
    'lieu_mission'                => 'Quai du Mont-Blanc - ligne de depart',
    'google_maps_url_mission'     => 'https://www.google.com/maps?q=46.2094471,6.1475688',
    'latitude_mission'            => 46.2094471,
    'longitude_mission'           => 6.1475688,
    'nombre_benevoles_max'        => 4,
    'nombre_benevoles_backup'     => 1,
    'statut_mission'              => 'En cours',
    'inscription_requise'         => true,
    'visibilite_mission'          => 'publique',
    'consignes_securite'          => 'Gilet haute visibilite obligatoire, radio canal 3.',
    'image_mission'               => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=1200',
    'publie_le_mission'           => now()->subDays(2),
    'created_at' => now(),
    'updated_at' => now(),
], 'id_mission');

$m2 = DB::table('missions')->insertGetId([
    'id_evenement'                => $eventId,
    'responsable_utilisateur_id'  => $marc,
    'titre_mission'               => 'Ravitaillement km 3 - Live',
    'type_mission'                => 'logistique',
    'description_mission'         => 'Distribution eau et barres energetiques au poste km 3.',
    'date_mission'                => now()->toDateString(),
    'heure_debut_mission'         => now()->subMinutes(30)->format('H:i:s'),
    'heure_fin_mission'           => now()->addHours(2)->format('H:i:s'),
    'lieu_mission'                => 'Promenade du Lac - km 3',
    'google_maps_url_mission'     => 'https://www.google.com/maps?q=46.2060000,6.1515000',
    'latitude_mission'            => 46.2060000,
    'longitude_mission'           => 6.1515000,
    'nombre_benevoles_max'        => 3,
    'nombre_benevoles_backup'     => 1,
    'statut_mission'              => 'En cours',
    'inscription_requise'         => true,
    'visibilite_mission'          => 'publique',
    'consignes_securite'          => 'Prevoir gants et tablier.',
    'image_mission'               => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=1200',
    'publie_le_mission'           => now()->subDays(2),
    'created_at' => now(),
    'updated_at' => now(),
], 'id_mission');

// Affectations Emma + Dayanna sur mission 1
foreach ([$emma, $dayanna] as $uid) {
    DB::table('postulations')->insert([
        'id_mission'          => $m1,
        'id_evenement'        => $eventId,
        'id_utilisateur'      => $uid,
        'statut_postulation'  => 'accepte',
        'date_postulation'    => now()->subDays(2),
        'date_decision'       => now()->subDay(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('affectations')->insert([
        'id_mission'          => $m1,
        'id_utilisateur'      => $uid,
        'statut_affectation'  => 'present',
        'est_responsable'     => false,
        'date_affectation'    => now()->subDays(2),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

// Sofian + Leo sur mission 2
foreach ([$sofian, $leo] as $uid) {
    DB::table('postulations')->insert([
        'id_mission'          => $m2,
        'id_evenement'        => $eventId,
        'id_utilisateur'      => $uid,
        'statut_postulation'  => 'accepte',
        'date_postulation'    => now()->subDays(2),
        'date_decision'       => now()->subDay(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('affectations')->insert([
        'id_mission'          => $m2,
        'id_utilisateur'      => $uid,
        'statut_affectation'  => 'present',
        'est_responsable'     => false,
        'date_affectation'    => now()->subDays(2),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

echo "OK – event=$eventId  m1=$m1  m2=$m2\n";
