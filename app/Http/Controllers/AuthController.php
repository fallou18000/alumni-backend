<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use App\Mail\AlumniWelcomeMail;
use App\Mail\ResetPasswordMail;
use App\Services\BrevoService;
class AuthController extends Controller
{
    /**
     * LOGIN
     */
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Identifiants invalides'
        ], 401);
    }

    // 🔥 création token
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login réussi',
        'user' => $user,
        'token' => $token
    ]);
}

    /**
     * CHANGE PASSWORD (1ère connexion ou reset)
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed'
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'password_expires_at' => null
        ]);

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès'
        ]);
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Déconnecté avec succès'
        ]);
    }

    /**
     * FORGOT PASSWORD (reset via email)
     */
    public function forgotPassword(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'Email introuvable'], 404);
    }

    // 🔥 TOUJOURS : lien reset password (même logique pour tous)
    $token = Password::createToken($user);

    $frontendUrl = env('FRONTEND_URL');

    $resetUrl = $frontendUrl . "/reset-password?token=" . $token . "&email=" . urlencode($user->email);

    BrevoService::send(
    $user->email,
    "Réinitialisation de mot de passe",
    "<h1>Réinitialisation</h1>
     <p>Clique sur le lien ci-dessous :</p>
     <a href='{$resetUrl}'>Reset Password</a>"
);

    return response()->json([
        'message' => 'Lien de réinitialisation envoyé'
    ]);
}

    /**
     * RESET PASSWORD
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'must_change_password' => false
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Mot de passe réinitialisé avec succès']);
        }

        return response()->json(['message' => 'Token invalide ou expiré'], 400);
    }

    /**
     * SHOW RESET FORM (pour React)
     */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        return response()->json([
            'token' => $token,
            'email' => $email,
        ]);
    }
}