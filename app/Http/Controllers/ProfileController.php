<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Récupérer le profil + infos utilisateur
public function getProfile(Request $request)
{
    $user = $request->user();

    $user->load([
        'ufr',
        'departement',
        'filiere',
        'profile',
        'profile.entreprise'
    ]);

    return response()->json($user);
}

    // Mettre à jour le profil
public function updateProfile(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Non authentifié'], 401);
    }

    $request->validate([
        'graduation_year' => 'nullable|integer',
        'degree_level' => 'nullable|string',
        'status' => 'nullable|string',
        'job_title' => 'nullable|string',
        'filiere_id' => 'required|exists:filieres,id',
        'promotion' => 'nullable|string',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // ✅ 1 seul profil garanti
    $profile = \App\Models\AlumniProfile::firstOrNew([
        'user_id' => $user->id
    ]);

    $profile->graduation_year = $request->graduation_year;
    $profile->degree_level = $request->degree_level;
    $profile->status = $request->status;
    $profile->job_title = $request->job_title;
    $profile->promotion = $request->promotion;
    $profile->filiere_id = $request->filiere_id;
    $profile->entreprise_id = $request->entreprise_id;

    // photo
    if ($request->hasFile('photo')) {
        $profile->photo = $request->file('photo')->store('photos', 'public');
    }

    $profile->save();

    // 🔥 reload propre
    $user = $user->fresh([
    'ufr',
    'departement',
    'filiere',
    'profile.entreprise',
    'profile.ufr',
    'profile.departement',
    'profile.filiere'
]);

   $user->load([
    'ufr',
    'departement',
    'filiere',
    'profile.entreprise',
    'profile.ufr',
    'profile.departement',
    'profile.filiere'
]);

    return response()->json($user);
}
  // ADMIN: voir profil d'un utilisateur
public function adminGetProfile($id)
{
    $user = \App\Models\User::with([
          'ufr',               
            'departement',       
            'filiere', 
            'profile.entreprise', 
        'profile.ufr',
        'profile.departement',
        'profile.filiere'
        
    ])->find($id);

    if (!$user) {
        return response()->json(['message' => 'Utilisateur introuvable'], 404);
    }

    return response()->json($user);
}
public function getPublicProfile($id)
{
    $user = \App\Models\User::with('profile')->findOrFail($id);

    return response()->json([
        'id' => $user->id,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'profile' => $user->profile
    ]);
}

public function show()
{
    try {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        $profile = $user->profile; // relation

        return response()->json([
            'user' => $user,
            'profile' => $profile
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error',
            'error' => $e->getMessage()
        ], 500);
    }
}
}