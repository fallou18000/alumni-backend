<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // Modifier un document existant
    public function update(Request $request, $index)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Non authentifié'], 401);

        $profile = $user->profile;
        if (!$profile || !isset($profile->documents[$index])) {
            return response()->json(['message' => 'Document introuvable'], 404);
        }

        $request->validate([
            'document' => 'required|file|max:2048'
        ]);

        // Supprimer ancien fichier si existe
        if (isset($profile->documents[$index]['path'])) {
            Storage::disk('public')->delete($profile->documents[$index]['path']);
        }

        $file = $request->file('document');
        $path = $file->store('documents', 'public');

        // Remplacer le document
        $profile->documents[$index] = [
            'name' => $file->getClientOriginalName(),
            'path' => $path
        ];

        $profile->save();

        return response()->json(['message' => 'Document modifié', 'documents' => $profile->documents]);
    }

    // Supprimer un document
    public function destroy($index)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Non authentifié'], 401);

        $profile = $user->profile;
        if (!$profile || !isset($profile->documents[$index])) {
            return response()->json(['message' => 'Document introuvable'], 404);
        }

        // Supprimer le fichier
        Storage::disk('public')->delete($profile->documents[$index]['path']);

        // Supprimer du tableau
        array_splice($profile->documents, $index, 1);

        $profile->save();

        return response()->json(['message' => 'Document supprimé', 'documents' => $profile->documents]);
    }
}