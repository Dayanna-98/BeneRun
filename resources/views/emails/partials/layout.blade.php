<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background-color:#eef2f7;font-family:Arial,Helvetica,sans-serif;color:#223042;">
    <span style="display:none!important;visibility:hidden;opacity:0;color:transparent;height:0;width:0;overflow:hidden;">
        {{ $preheader ?? $title }}
    </span>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#eef2f7;margin:0;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background-color:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 18px 45px rgba(26,34,48,0.12);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%);padding:32px 36px;text-align:center;">
                            <div style="display:inline-block;padding:12px 18px;border-radius:16px;background-color:rgba(255,255,255,0.12);color:#ffffff;font-size:12px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;">
                                B&eacute;n&eacute;'Run
                            </div>
                            <h1 style="margin:18px 0 10px;color:#ffffff;font-size:28px;line-height:1.2;font-weight:700;">{{ $title }}</h1>
                            @if (!empty($subtitle))
                                <p style="margin:0;color:rgba(255,255,255,0.78);font-size:15px;line-height:1.6;">{{ $subtitle }}</p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:36px;">
                            @if (!empty($greeting))
                                <p style="margin:0 0 18px;font-size:18px;line-height:1.5;color:#1f2937;font-weight:700;">{{ $greeting }}</p>
                            @endif

                            @foreach (($introLines ?? []) as $line)
                                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#4b5563;">{{ $line }}</p>
                            @endforeach

                            @if (!empty($actionUrl) && !empty($actionText))
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:28px 0 24px;">
                                    <tr>
                                        <td align="center" bgcolor="#1f4fd6" style="border-radius:999px;">
                                            <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 28px;color:#ffffff;background-color:#1f4fd6;border-radius:999px;font-size:15px;font-weight:700;text-decoration:none;">
                                                {{ $actionText }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @foreach (($outroLines ?? []) as $line)
                                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#4b5563;">{{ $line }}</p>
                            @endforeach

                            @if (!empty($noticeTitle) || !empty($noticeLines))
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:28px 0 24px;background-color:#f4f7fb;border:1px solid #d8e1f0;border-radius:18px;">
                                    <tr>
                                        <td style="padding:20px 22px;">
                                            @if (!empty($noticeTitle))
                                                <p style="margin:0 0 12px;font-size:14px;font-weight:700;line-height:1.5;color:#1a2230;">{{ $noticeTitle }}</p>
                                            @endif

                                            @if (!empty($noticeLines))
                                                <ul style="margin:0;padding-left:18px;color:#4b5563;font-size:14px;line-height:1.7;">
                                                    @foreach ($noticeLines as $line)
                                                        <li style="margin-bottom:8px;">{{ $line }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if (!empty($actionUrl) && !empty($actionText))
                                <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#6b7280;">
                                    Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :
                                </p>
                                <p style="margin:0 0 20px;font-size:13px;line-height:1.6;word-break:break-all;">
                                    <a href="{{ $actionUrl }}" style="color:#1f4fd6;text-decoration:none;">{{ $actionUrl }}</a>
                                </p>
                            @endif

                            <p style="margin:0;font-size:15px;line-height:1.7;color:#4b5563;">
                                {{ $salutation ?? 'L\'équipe Béné\'Run' }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 36px 28px;background-color:#f8fafc;border-top:1px solid #e5e7eb;text-align:center;">
                            <p style="margin:0 0 6px;font-size:12px;line-height:1.6;color:#6b7280;">B&eacute;n&eacute;'Run • Plateforme de b&eacute;n&eacute;volat</p>
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#94a3b8;">Message automatique, merci de ne pas y répondre directement.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>