<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #FF6B35 0%, #2C3E50 100%); color: white; padding: 20px; border-radius: 5px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .button { display: inline-block; background: #FF6B35; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
        .security-note { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 20px 0; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Réinitialisation de mot de passe</h1>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $user->prenom_utilisateur }}</strong>,</p>

            <p>Vous avez demandé une réinitialisation de votre mot de passe Béné'Run. Cliquez sur le bouton ci-dessous pour procéder :</p>

            <center>
                <a href="{{ $resetLink }}" class="button">Réinitialiser mon mot de passe</a>
            </center>

            <p><strong>Ce lien expirera dans 30 minutes.</strong></p>

            <div class="security-note">
                <strong>⚠️ Information de sécurité :</strong>
                <ul>
                    <li>Ce lien est personnel et unique</li>
                    <li>Ne le partagez avec personne</li>
                    <li>Béné'Run ne vous demandera jamais votre mot de passe par email</li>
                    <li>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email</li>
                </ul>
            </div>

            <p>Cordialement,<br><strong>L'équipe Béné'Run</strong></p>
        </div>

        <div class="footer">
            <p>&copy; 2025 Béné'Run • Plateforme de bénévolat</p>
        </div>
    </div>
</body>
</html>
