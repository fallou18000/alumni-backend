<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'departement_id'
    
    ];

    // Une filière a plusieurs alumni profiles
    public function alumniProfiles()
    {
        return $this->hasMany(AlumniProfile::class);
    }

    public function departement() {
    return $this->belongsTo(Departement::class);
}
}