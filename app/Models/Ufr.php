<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ufr extends Model
{
    use HasFactory;

    protected $table = 'ufrs'; 

    protected $fillable = [
        'nom'
    ];
    
      public static function boot()
    {
        parent::boot();
    }

    public function departements()
    {
        return $this->hasMany(Departement::class);
    }

}