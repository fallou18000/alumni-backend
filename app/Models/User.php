<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use App\Models\Entreprise;
use App\Models\Ufr;
use App\Models\Departement;
use App\Models\Filiere;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role_id',
        'status',
        'must_change_password',
        'has_logged_in',
        'password_expires_at',
          'last_seen',
           'ufr_id',
    'departement_id',
    'numero_dossier',
    'filiere_id',
    'photo_diplome', 
           
   
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ✅ IMPORTANT FIX
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',

            // 🔥 AJOUT OBLIGATOIRE
            'password_expires_at' => 'datetime',
            'has_logged_in' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }
    public function profile()
{
    return $this->hasOne(AlumniProfile::class);
}
public function role()
{
    return $this->belongsTo(Role::class);
}
// public function entreprise()
// {
//     return $this->belongsTo(Entreprise::class);
// }

public function ufr()
{
    return $this->belongsTo(Ufr::class);
}

public function departement()
{
    return $this->belongsTo(Departement::class);
}

public function filiere()
{
    return $this->belongsTo(Filiere::class);
}



}