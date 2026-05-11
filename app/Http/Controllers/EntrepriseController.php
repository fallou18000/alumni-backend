<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EntrepriseController extends Controller
{
    //

    public function index()
{
    return \App\Models\Entreprise::all();
}

public function store(Request $request)
{
    $request->validate([
        'nom' => 'required|string|unique:entreprises,nom',
        'ville' => 'nullable|string',
        'secteur' => 'nullable|string',
         
    ]);

    $entreprise = \App\Models\Entreprise::create([
        'nom' => $request->nom,
        'ville' => $request->ville,
        'secteur' => $request->secteur,
        
    ]);

    return response()->json($entreprise);
}
}
