@include('emails.partials.layout', [
    'title' => 'Alerte urgence mission',
    'preheader' => 'Un message d\'urgence vient d\'etre envoye a destination des superadmins.',
    'subtitle' => 'Une urgence necessite votre attention immediate.',
    'greeting' => 'Bonjour '.trim(($recipient->prenom_utilisateur ?? '').' '.($recipient->nom_utilisateur ?? '')),
    'introLines' => [
        $senderName.' a signale une urgence sur la mission "'.$missionName.'" ('.$eventName.').',
        'Categorie: '.($category ?: 'general'),
        'Message: "'.$messagePreview.'"',
    ],
    'actionText' => 'Ouvrir le dashboard superadmin',
    'actionUrl' => $actionUrl,
    'noticeTitle' => 'Traitement recommande',
    'noticeLines' => [
        'Consultez rapidement la section Urgence du dashboard pour marquer la lecture.',
        'Prenez en charge l\'urgence pour eviter les doublons de traitement.',
    ],
    'salutation' => 'Merci, equipe Béné\'Run',
])
