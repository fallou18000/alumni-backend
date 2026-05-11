<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departement; 

class DepartementController extends Controller
{
    //

    public function store(Request $request)
{
    $request->validate([
        'nom' => 'required',
        'ufr_id' => 'required|exists:ufrs,id'
    ]);

    $dep = Departement::create($request->all());

    return response()->json($dep, 201);
}
}
