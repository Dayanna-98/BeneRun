@include('emails.partials.layout', [
    'title' => 'Réinitialisation de mot de passe',
    'preheader' => 'Réinitialisez votre mot de passe Béné\'Run en toute sécurité.',
    'subtitle' => 'Un accès simple, avec une vérification de sécurité adaptée à votre compte.',
    'greeting' => 'Bonjour '.($user->prenom_utilisateur ?: 'à vous').',',
    'introLines' => [
        'Vous avez demandé une réinitialisation de votre mot de passe Béné\'Run.',
        'Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe.',
    ],
    'actionUrl' => $resetLink,
    'actionText' => 'Réinitialiser mon mot de passe',
    'outroLines' => [
        'Ce lien de réinitialisation expirera dans 30 minutes.',
        'Si vous n\'avez pas demandé cette opération, vous pouvez ignorer cet email en toute sécurité.',
    ],
    'noticeTitle' => 'Rappels de sécurité',
    'noticeLines' => [
        'Ce lien est personnel et unique.',
        'Ne le partagez avec personne.',
        'Béné\'Run ne vous demandera jamais votre mot de passe par email.',
    ],
])
