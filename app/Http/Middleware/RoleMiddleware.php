<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
   public function handle(Request $request, Closure $next, $role)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Non authentifié'], 401);
    }

    if (!$user->role || !$user->role->name) {
        return response()->json(['message' => 'Rôle utilisateur introuvable'], 403);
    }

    if ($user->role->name !== $role) {
        return response()->json(['message' => 'Accès refusé, rôle insuffisant'], 403);
    }

    return $next($request);
}
}