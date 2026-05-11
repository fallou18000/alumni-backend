<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AlumniProfile;
use Carbon\Carbon;

class ResponsableController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📊 DASHBOARD
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $user = auth()->user();

        $query = User::where('role_id', 2)
            ->where('filiere_id', $user->filiere_id);

        $total = $query->count();

        $withJob = (clone $query)
            ->whereHas('profile', fn($q) => $q->whereNotNull('job_title'))
            ->count();

        $withoutJob = $total - $withJob;

        return response()->json([
            'total' => $total,
            'with_job' => $withJob,
            'without_job' => $withoutJob,
            'insertion_rate' => $total > 0
                ? round(($withJob / $total) * 100, 2)
                : 0
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 👥 LIST ALUMNI + FILTRES
    |--------------------------------------------------------------------------
    */
    public function alumni(Request $request)
    {
        $user = auth()->user();

        $query = User::with('profile.entreprise')
            ->where('role_id', 2)
            ->where('filiere_id', $user->filiere_id);

            if ($request->search) {
    $search = $request->search;

    $query->where(function ($q) use ($search) {
        $q->where('first_name', 'like', "%$search%")
          ->orWhere('last_name', 'like', "%$search%")
          ->orWhere('email', 'like', "%$search%")
          ->orWhereHas('profile', function ($p) use ($search) {
              $p->where('job_title', 'like', "%$search%")
                ->orWhere('promotion', 'like', "%$search%");
          });
    });
}

        // 🔍 STATUS
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
         // 🔍 ENTREPRISE
if ($request->entreprise_id) {
    $query->whereHas('profile', function ($q) use ($request) {
        $q->where('entreprise_id', $request->entreprise_id);
    });
}

        // 🔍 PROMOTION
        if ($request->promotion) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('promotion', $request->promotion);
            });
        }

        // 🔍 JOB
        if ($request->job_title) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('job_title', 'like', "%{$request->job_title}%");
            });
        }

        // 🔍 ANNÉE
        if ($request->graduation_year) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('graduation_year', $request->graduation_year);
            });
        }

        return response()->json(
            $query->latest()->paginate(10)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 DETAIL ALUMNI
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $user = auth()->user();

        $alumni = User::with('profile.entreprise')
            ->where('role_id', 2)
            ->where('filiere_id', $user->filiere_id)
            ->findOrFail($id);

        return response()->json($alumni);
    }

    /*
    |--------------------------------------------------------------------------
    | 📈 STATS PAR PROMOTION
    |--------------------------------------------------------------------------
    */
    public function statsPromotion()
    {
        $user = auth()->user();

        return AlumniProfile::whereHas('user', function ($q) use ($user) {
            $q->where('filiere_id', $user->filiere_id);
        })
        ->selectRaw('promotion, COUNT(*) as total')
        ->groupBy('promotion')
        ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 📈 USER GROWTH (COURBE)
    |--------------------------------------------------------------------------
    */
    public function growth()
    {
        $user = auth()->user();

        $data = User::where('role_id', 2)
            ->where('filiere_id', $user->filiere_id)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /*
    |--------------------------------------------------------------------------
    | 📤 EXPORT FILTRÉ
    |--------------------------------------------------------------------------
    */
    public function export(Request $request)
    {
        $user = auth()->user();

        $query = User::with('profile.entreprise')
            ->where('role_id', 2)
            ->where('filiere_id', $user->filiere_id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->promotion) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('promotion', $request->promotion);
            });
        }

        return response()->json($query->get());
    }
}