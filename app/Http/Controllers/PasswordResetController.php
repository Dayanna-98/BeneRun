<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
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

        if (!$user) {
            return response()->json([
                'message' => 'Email non trouvé dans notre système'
            ], 404);
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

        // Envoyer le mail avec le lien de réinitialisation
        Mail::send('emails.reset-password', [
            'user' => $user,
            'resetLink' => env('FRONTEND_URL') . '/reset-password?token=' . $token . '&email=' . urlencode($validated['email']),
            'token' => $token,
        ], function ($mail) use ($user) {
            $mail->to($user->email)
                ->subject('Réinitialisation de votre mot de passe - Béné\'Run');
        });

        return response()->json([
            'message' => 'Un lien de réinitialisation a été envoyé à ' . $validated['email']
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

        if (!$resetRecord) {
            return response()->json([
                'valid' => false,
                'message' => 'Token invalide ou expiré'
            ], 401);
        }

        // Vérifier le token
        if (!Hash::check($validated['token'], $resetRecord->token)) {
            return response()->json([
                'valid' => false,
                'message' => 'Token invalide'
            ], 401);
        }

        // Vérifier l'expiration (30 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 30) {
            DB::table('password_resets')->where('email', $validated['email'])->delete();
            return response()->json([
                'valid' => false,
                'message' => 'Token expiré. Veuillez demander une nouvelle réinitialisation'
            ], 401);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Token valide'
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
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email non trouvé'
            ], 404);
        }

        $resetRecord = DB::table('password_resets')
            ->where('email', $validated['email'])
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'message' => 'Token invalide ou expiré'
            ], 401);
        }

        // Vérifier le token
        if (!Hash::check($validated['token'], $resetRecord->token)) {
            return response()->json([
                'message' => 'Token invalide'
            ], 401);
        }

        // Vérifier l'expiration
        if (now()->diffInMinutes($resetRecord->created_at) > 30) {
            DB::table('password_resets')->where('email', $validated['email'])->delete();
            return response()->json([
                'message' => 'Token expiré'
            ], 401);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        // Supprimer le token utilisé
        DB::table('password_resets')->where('email', $validated['email'])->delete();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès'
        ], 200);
    }
}
