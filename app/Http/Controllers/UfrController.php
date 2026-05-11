<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UFR; 

class UfrController extends Controller
{

public function store(Request $request)
{
    $request->validate([
        'nom' => 'required|string|max:255'
    ]);

    $ufr = Ufr::create([
        'nom' => $request->nom
    ]);

    return response()->json($ufr, 201);
}
    //
}
