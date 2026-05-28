@include('emails.partials.layout', [
    'title' => 'Vérifiez votre adresse email',
    'preheader' => 'Confirmez votre adresse email pour finaliser votre accès Béné\'Run.',
    'subtitle' => 'Confirmez votre adresse pour sécuriser votre compte et faciliter vos futures démarches.',
    'greeting' => 'Bonjour '.($user->prenom_utilisateur ?: 'à vous').',',
    'introLines' => [
        'Votre compte Béné\'Run est déjà actif. Nous vous invitons maintenant à vérifier votre adresse email.',
        'Cette vérification sera notamment requise si vous oubliez votre mot de passe et devez lancer une réinitialisation.',
    ],
    'actionUrl' => $verificationUrl,
    'actionText' => 'Vérifier mon adresse email',
    'outroLines' => [
        'Le lien de vérification est valable pendant une durée limitée pour protéger votre compte.',
        'Si vous n\'êtes pas à l\'origine de cette inscription, aucune action supplémentaire n\'est nécessaire.',
    ],
    'noticeTitle' => 'Bon à savoir',
    'noticeLines' => [
        'Vous pouvez utiliser votre compte sans attendre cette vérification.',
        'En revanche, la procédure “mot de passe oublié” restera bloquée tant que votre email n\'est pas vérifié.',
    ],
])