<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;

    protected $table = 'departements';

    protected $fillable = [
        'nom',
        'ufr_id'
    ];

    // 🔗 relation avec UFR
    public function ufr()
    {
        return $this->belongsTo(Ufr::class);
    }

    // 🔗 relation avec filières
    public function filieres()
    {
        return $this->hasMany(Filiere::class);
    }
}