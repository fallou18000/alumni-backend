<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use Illuminate\Http\Request;

class FiliereController extends Controller
{
    //  Récupérer toutes les filières
    public function index()
    {
        try {
            $filieres = Filiere::all(); // récupère toutes les filières
            return response()->json($filieres);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des filières',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
{
    $request->validate([
        'nom' => 'required',
        'departement_id' => 'required|exists:departements,id'
    ]);

    $fil = Filiere::create($request->all());

    return response()->json($fil, 201);
}
}