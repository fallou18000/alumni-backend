<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'ville',
        'secteur',
        
    ];

  

    public function profiles()
{
    return $this->hasMany(AlumniProfile::class);
}
}