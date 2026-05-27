<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    private function passwordRules(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            PasswordRule::min(10)
                ->letters()
                ->numbers(),
        ];
    }

    /**
     * Demander une réinitialisation de mot de passe
     * Vérifie que l'email existe et envoie un lien de réinitialisation
     */
    public function requestReset(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Vérifier que l'email existe en BDD
        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'Si un compte existe pour cet email, un lien de réinitialisation a été envoyé.',
            ], 200);
        }

        // Générer un token unique
        $token = Str::random(60);

        // Stocker le token avec expiration (30 minutes)
        DB::table('password_resets')->updateOrInsert(
            ['email' => $validated['email']],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $frontendBaseUrl = rtrim((string) (
            env('FRONTEND_URL')
            ?: config('app.url')
        ), '/');

        $resetPath = '/reset-password';
        $resetLink = $frontendBaseUrl.$resetPath.'?token='.$token.'&email='.urlencode($validated['email']);

        $resetTemplate = env('FRONTEND_RESET_PASSWORD_URL_TEMPLATE');
        if (is_string($resetTemplate) && trim($resetTemplate) !== '') {
            $resetLink = str_replace(
                ['{token}', '{email}'],
                [$token, urlencode($validated['email'])],
                $resetTemplate
            );
        }

        try {
            Mail::send('emails.reset-password', [
                'user' => $user,
                'resetLink' => $resetLink,
                'token' => $token,
            ], function ($mail) use ($user) {
                $mail->to($user->email)
                    ->subject('Réinitialisation de votre mot de passe - Béné\'Run');
            });
        } catch (\Throwable $exception) {
            Log::error('Password reset email send failed', [
                'email' => $validated['email'],
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Impossible d\'envoyer l\'email de réinitialisation pour le moment. Vérifiez la configuration SMTP.',
            ], 500);
        }

        return response()->json([
            'message' => 'Si un compte existe pour cet email, un lien de réinitialisation a été envoyé.',
        ], 200);
    }

    /**
     * Vérifier la validité du token
     */
    public function verifyToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $resetRecord = DB::table('password_resets')
            ->where('email', $validated['email'])
            ->first();

        if (! $resetRecord) {
            return response()->json([
                'valid' => false,
                'message' => 'Token invalide ou expiré',
            ], 401);
        }

        // Vérifier le token
        if (! Hash::check($validated['token'], $resetRecord->token)) {
            return response()->json([
                'valid' => false,
                'message' => 'Token invalide',
            ], 401);
        }

        // Vérifier l'expiration (30 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 30) {
            DB::table('password_resets')->where('email', $validated['email'])->delete();

            return response()->json([
                'valid' => false,
                'message' => 'Token expiré. Veuillez demander une nouvelle réinitialisation',
            ], 401);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Token valide',
        ], 200);
    }

    /**
     * Réinitialiser le mot de passe avec le token
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => $this->passwordRules(),
        ], [
            'password.min' => 'Le mot de passe doit contenir au moins 10 caractères.',
            'password.letters' => 'Le mot de passe doit contenir au moins une lettre.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'Email non trouvé',
            ], 404);
        }

        $resetRecord = DB::table('password_resets')
            ->where('email', $validated['email'])
            ->first();

        if (! $resetRecord) {
            return response()->json([
                'message' => 'Token invalide ou expiré',
            ], 401);
        }

        // Vérifier le token
        if (! Hash::check($validated['token'], $resetRecord->token)) {
            return response()->json([
                'message' => 'Token invalide',
            ], 401);
        }

        // Vérifier l'expiration
        if (now()->diffInMinutes($resetRecord->created_at) > 30) {
            DB::table('password_resets')->where('email', $validated['email'])->delete();

            return response()->json([
                'message' => 'Token expiré',
            ], 401);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Supprimer le token utilisé
        DB::table('password_resets')->where('email', $validated['email'])->delete();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès',
        ], 200);
    }
}
