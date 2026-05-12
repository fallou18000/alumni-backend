<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AlumniProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlumniWelcomeMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
class AdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📊 DASHBOARD
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $totalUsers = User::count();

        // ✅ ALUMNI = role + status approved
        $totalAlumni = User::where('role_id', 2)
            ->where('status', 'approved')
            ->count();

        $totalResponsables = User::where('role_id', 3)->count();
        $totalAdmins = User::where('role_id', 1)->count();

        $pending = User::where('status', 'pending')->count();
        $approved = User::where('status', 'approved')->count();
        $blocked = User::where('status', 'blocked')->count();

        $recentUsers = User::where('created_at', '>=', now()->subDays(7))->count();

        // ✅ insertion rate fiable
        $withJob = User::where('role_id', 2)
            ->where('status', 'approved')
           //->whereNotNull('entreprise_id')
            ->count();

        $insertionRate = $totalAlumni > 0
            ? ($withJob / $totalAlumni) * 100
            : 0;

        return response()->json([
            'total_users' => $totalUsers,
            'total_alumni' => $totalAlumni,
            'total_responsables' => $totalResponsables,
            'total_admins' => $totalAdmins,

            'pending_users' => $pending,
            'approved_users' => $approved,
            'blocked_users' => $blocked,

            'recent_users' => $recentUsers,
            'insertion_rate' => round($insertionRate, 2),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 📊 STATS PAR FILIÈRE (basé sur profile)
    |--------------------------------------------------------------------------
    */
    public function statsByFiliere()
    {
        $total = AlumniProfile::whereHas('user', function ($q) {
            $q->where('role_id', 2)
              ->where('status', 'approved');
        })->count();

        return AlumniProfile::with('filiere')
            ->whereHas('user', function ($q) {
                $q->where('role_id', 2)
                  ->where('status', 'approved');
            })
            ->selectRaw('filiere_id, COUNT(*) as total')
            ->groupBy('filiere_id')
            ->get()
            ->map(function ($item) use ($total) {
                return [
                    'filiere' => $item->filiere?->name ?? 'Sans filière',
                    'total' => $item->total,
                    'percentage' => $total > 0
                        ? round(($item->total / $total) * 100, 1)
                        : 0,
                ];
            });
    }

    public function alumniWithoutDiploma()
{
    return User::with('profile')
        ->where('role_id', 2)
        ->where(function ($q) {
            $q->whereDoesntHave('profile')
              ->orWhereHas('profile', function ($q2) {
                  $q2->whereNull('photo_diplome');
              });
        })
        ->get();
}



public function uploadDiplome(Request $request, $id)
{
    $request->validate([
        'photo_diplome' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048'
    ]);

    $user = User::findOrFail($id);

    $profile = $user->profile;

    if (!$profile) {
        $profile = $user->profile()->create([]);
    }

    $path = $request->file('photo_diplome')->store('diplomes', 'public');

    $profile->photo_diplome = $path;
    $profile->save();

    return response()->json([
        'message' => 'Diplôme ajouté avec succès'
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | 📈 USER GROWTH
    |--------------------------------------------------------------------------
    */
 public function userGrowth(Request $request)
{
    $days = 365; // on force large pour éviter "0 data"

    $data = User::selectRaw("DATE(created_at) as date, COUNT(*) as total")
        ->whereNotNull('created_at')
        ->where('created_at', '>=', Carbon::now()->subDays($days))
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

    $result = [];

    for ($i = $days; $i >= 0; $i--) {
        $date = Carbon::now()->subDays($i)->format('Y-m-d');

        $result[] = [
            "date" => $date,
            "total" => isset($data[$date]) ? $data[$date]->total : 0
        ];
    }

    return response()->json($result);
}

public function importAlumni(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:csv,txt'
    ]);

    $file = fopen($request->file('file')->getPathname(), 'r');
    fgetcsv($file);

    $created = 0;

    while (($row = fgetcsv($file)) !== false) {

        if (count($row) < 3) continue;

        $user = User::create([
            'first_name' => $row[0],
            'last_name'  => $row[1],
            'email'      => $row[2],

            'numero_dossier' => $row[3] ?? null,
            'ufr_id'         => $row[4] ?? null,
            'departement_id' => $row[5] ?? null,
            'filiere_id'     => $row[6] ?? null,

            'role_id' => 2,
            'status'  => 'approved',

            'password' => bcrypt(Str::random(60)),
        ]);

        $token = Password::createToken($user);

        $url = config('app.frontend_url')
            . "/set-password?token={$token}&email=" . urlencode($user->email);

        Mail::to($user->email)->send(
            new \App\Mail\AlumniWelcomeMail($user, $url)
        );

        $created++;
    }

    fclose($file);

    return response()->json([
        'message' => 'Import terminé',
        'created' => $created
    ]);
}
    /*
    |--------------------------------------------------------------------------
    | 👥 LIST USERS
    |--------------------------------------------------------------------------
    */
public function index(Request $request)
{
    $users = User::select(
        'id',
        'first_name',
        'last_name',
        'email',
        'role_id',
        'status',
        'created_at'
    )
    ->latest()
    ->get();

    return response()->json($users);
}


public function exportUsers(Request $request)
{
    $query = User::with(['profile']);

    if ($request->job_title) {
        $query->whereHas('profile', function ($q) use ($request) {
            $q->where('job_title', 'like', '%' . $request->job_title . '%');
        });
    }

    if ($request->filiere_id) {
        $query->where('filiere_id', $request->filiere_id);
    }

    if ($request->status) {
        $query->where('status', $request->status);
    }

    return response()->json(
        $query->get()
    );
}

    /*
    |--------------------------------------------------------------------------
    | 🗑 DELETE USER
    |--------------------------------------------------------------------------
    */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔁 TOGGLE STATUS
    |--------------------------------------------------------------------------
    */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        $user->status = $user->status === 'blocked'
            ? 'approved'
            : 'blocked';

        $user->save();

        return response()->json([
            'message' => 'Statut mis à jour',
            'user' => $user
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ✏️ UPDATE USER
    |--------------------------------------------------------------------------
    */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'role_id' => 'sometimes|integer',
        ]);

        $user->update($request->only([
            'first_name',
            'last_name',
            'role_id'
        ]));

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => $user
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ⏳ PENDING USERS
    |--------------------------------------------------------------------------
    */
public function pending()
{
    $users = User::with([
        'profile.ufr',
        'profile.departement',
        'profile.filiere'
    ])
    ->where('role_id', 2)
    ->where('status', 'pending')
    ->get()
    ->map(function ($user) {

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,

            // 🎓 diplôme
            'photo_diplome' => $user->photo_diplome,

            // 📚 profil complet
            'profile' => $user->profile,
        ];
    });

    return response()->json([
        'pending_alumni' => $users
    ]);
}

    public function pendingCount()
{
    return response()->json([
        'pending_alumni_count' => User::where('status', 'pending')
            ->where('role_id', 2)
            ->count()
    ]);
}
public function adminGetProfileAdmin($id)
{
    $user = User::with([
        'profile.ufr',
        'profile.departement',
        'profile.filiere'
    ])->findOrFail($id);

    return response()->json([
        'id' => $user->id,

        // 🧑 USER
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'email'      => $user->email,
        'status'     => $user->status,

        // 🎓 diplôme
        'photo_diplome' => $user->photo_diplome,

        // 📂 PROFILE COMPLET
        'profile' => $user->profile,
    ]);
}
public function adminUpdateProfile(Request $request, $id)
{
    $user = \App\Models\User::find($id);

    if (!$user) {
        return response()->json([
            'message' => 'Utilisateur introuvable'
        ], 404);
    }

    // ✅ VALIDATION
    $request->validate([
        'graduation_year' => 'nullable|integer',
        'degree_level' => 'nullable|string',
        'status' => 'nullable|string',
        'job_title' => 'nullable|string',
        'promotion' => 'nullable|string',
        'filiere_id' => 'nullable|exists:filieres,id',
        'photo' => 'nullable|image'
    ]);

    // ✅ DATA CLEAN (important)
    $data = $request->only([
        'graduation_year',
        'degree_level',
        'status',
        'job_title',
        'promotion',
        'filiere_id'
    ]);

    $data = array_filter($data, fn($v) => $v !== null && $v !== "");

    // ✅ PROFILE SAFE (créé si absent)
    $profile = $user->profile()->updateOrCreate(
        ['user_id' => $user->id],
        $data
    );

    // ✅ PHOTO
    if ($request->hasFile('photo')) {
        $profile->photo = $request->file('photo')->store('photos', 'public');
    }

    $profile->save();

    // ✅ RELOAD DATA
    $user->load([
        'ufr',
        'departement',
        'filiere',
        'profile'
    ]);

    return response()->json($user);
}
    public function showUser($id)
    {
        $user = User::with('profile')->findOrFail($id);

        return response()->json($user);
    }
    public function activity()
    {
        $data = \DB::table('users')
            ->whereNotNull('last_login')
            ->selectRaw('DATE(last_login) as date, COUNT(*) as logins')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ APPROVE USER
    |--------------------------------------------------------------------------
    */
  

public function approve($id)
{
    $user = User::findOrFail($id);

    if ($user->status === 'approved') {
        return response()->json([
            'message' => 'Utilisateur déjà validé'
        ], 400);
    }

    // 1. Activer le compte
    $user->update([
        'status' => 'approved',
    ]);

    // 2. Générer token reset password (Laravel standard)
    $token = Password::createToken($user);

    // 3. Construire lien frontend
    $resetUrl = config('app.frontend_url') . "/reset-password?token=" . $token . "&email=" . urlencode($user->email);

    // 4. Envoyer email avec lien
    Mail::to($user->email)->send(
        new \App\Mail\AlumniWelcomeMail($user, $resetUrl)
    );

    return response()->json([
        'message' => 'Utilisateur validé, lien envoyé par email'
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | ➕ CREATE USER(S)
    |--------------------------------------------------------------------------
    */

public function createUser(Request $request)
{
    $request->validate([
        'first_name'     => 'required|string|max:255',
        'last_name'      => 'required|string|max:255',
        'email'          => 'required|email|unique:users,email',
        'role_id'        => 'required|exists:roles,id',

        'numero_dossier' => 'nullable|string',
        'ufr_id'         => 'nullable|exists:ufrs,id',
        'departement_id' => 'nullable|exists:departements,id',
        'filiere_id'     => 'nullable|exists:filieres,id',
    ]);

    // 1. CREATE USER (sans vrai password)
    $user = User::create([
        'first_name' => $request->first_name,
        'last_name'  => $request->last_name,
        'email'      => $request->email,
        'role_id'    => $request->role_id,

        'numero_dossier' => $request->numero_dossier,
        'ufr_id'         => $request->ufr_id,
        'departement_id' => $request->departement_id,
        'filiere_id'     => $request->filiere_id,

        'password' => bcrypt(Str::random(60)),
        'status'   => 'approved',
    ]);

    // 2. TOKEN RESET PASSWORD
    $token = Password::createToken($user);

    $url = config('app.frontend_url')
        . "/set-password?token={$token}&email=" . urlencode($user->email);

    // 3. EMAIL UNIQUE SYSTEM
    Mail::to($user->email)->send(
        new \App\Mail\AlumniWelcomeMail($user, $url)
    );

    return response()->json([
        'message' => 'Utilisateur créé + lien envoyé',
        'user' => $user
    ]);
}
}