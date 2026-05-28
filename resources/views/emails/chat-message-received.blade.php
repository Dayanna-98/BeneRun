@include('emails.partials.layout', [
    'title' => 'Nouveau message reçu',
    'preheader' => 'Un nouveau message vient d\'arriver dans votre messagerie Béné\'Run.',
    'subtitle' => 'Votre messagerie Béné\'Run a reçu une nouvelle activité.',
    'greeting' => 'Bonjour '.trim(($recipient->prenom_utilisateur ?? '').' '.($recipient->nom_utilisateur ?? '')),
    'introLines' => array_values(array_filter([
        $senderName.' vous a envoyé un nouveau message'.(!empty($conversationName) ? ' dans "'.$conversationName.'".' : '.'),
        'Aperçu du message : "'.$messagePreview.'"',
    ])),
    'actionText' => 'Ouvrir la messagerie',
    'actionUrl' => $actionUrl,
    'noticeTitle' => 'Préférence de notification',
    'noticeLines' => [
        'Vous recevez cet email car les notifications de messagerie sont activées dans votre profil.',
        'Vous pouvez les désactiver à tout moment depuis votre profil utilisateur.',
    ],
    'salutation' => 'À bientôt, l\'équipe Béné\'Run',
])