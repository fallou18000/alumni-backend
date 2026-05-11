<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlumniWelcomeMail;
use Carbon\Carbon;
use App\Mail\NewAlumniPendingMail;
class AlumniController extends Controller
{
public function register(Request $request)
{
    $request->validate([
        'first_name' => 'required|string',
        'last_name'  => 'required|string',
        'email'      => 'required|email|unique:users,email',

        'numero_dossier' => 'required|string',
        'photo_diplome'  => 'required|image',

        'ufr_id' => 'required|exists:ufrs,id',
        'departement_id' => 'required|exists:departements,id',
        'filiere_id' => 'required|exists:filieres,id',
    ]);

    if (!$request->hasFile('photo_diplome')) {
        return response()->json([
            'message' => 'Diplôme manquant'
        ], 422);
    }

    $path = $request->file('photo_diplome')->store('diplomes', 'public');

    // 1️⃣ USER
    $user = User::create([
        'first_name' => $request->first_name,
        'last_name'  => $request->last_name,
        'email'      => $request->email,
        'password'   => Hash::make('temp1234'),
        'role_id'    => 2,
        'status'     => 'pending',
        'ufr_id' => $request->ufr_id,
    'departement_id' => $request->departement_id,
    'numero_dossier' => $request->numero_dossier,
    'filiere_id' => $request->filiere_id,
      'photo_diplome' => $path,
    ]);

    // 2️⃣ PROFILE (SOURCE UNIQUE DES DONNÉES)
    $profile = $user->profile()->create([
        'numero_dossier' => $request->numero_dossier,
        // 'photo_diplome'  => $path,
        'ufr_id'         => $request->ufr_id,
        'departement_id' => $request->departement_id,
        'filiere_id'     => $request->filiere_id,

    ]);

    Mail::to('sfallou.thioune@univ-thies.sn')->send(new NewAlumniPendingMail($user));

    return response()->json([
        'message' => 'Demande envoyée. En attente de validation admin.',
        'user' => $user,
        'profile' => $profile
    ]);
}

public function listUsers()
{
    return User::where('role_id', 2)
        ->where('status', 'approved')
        ->where('id', '!=', auth()->id())
        ->select('id', 'first_name', 'last_name', 'email')
        ->get();
}

public function showUser($id)
{
    $user = User::with('profile.filiere')->findOrFail($id);

    return response()->json($user);
}
public function getPublicProfile($id)
{
    $user = \App\Models\User::with(['profile.filiere'])
        ->find($id);

    if (!$user) {
        return response()->json([
            'message' => 'Utilisateur introuvable'
        ], 404);
    }

    // ⚠️ sécurité métier (optionnel)
    if ($user->role_id != 2) {
        return response()->json([
            'message' => 'Ce profil n\'est pas un alumni'
        ], 403);
    }

    return response()->json([
        'id' => $user->id,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'profile' => $user->profile
    ]);
}
}