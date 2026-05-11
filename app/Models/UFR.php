<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UFR extends Model
{
    use HasFactory;

    protected $table = 'ufrs'; 

    protected $fillable = [
        'nom'
    ];

    public function departements()
    {
        return $this->hasMany(Departement::class);
    }
}