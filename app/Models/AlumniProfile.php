<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniProfile extends Model
{
    protected $fillable = [
        'user_id',
        'graduation_year',
        'degree_level',
        'status',
        'job_title',
        'photo',
        'cv_path',
        'ufr_id',
        'entreprise_id',
    'departement_id',
        'filiere_id',
        'promotion',
        'documents',
    ];

    protected $casts = [
        'documents' => 'array',
    ];

    // 🔥 AJOUT CRITIQUE
    protected $hidden = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function ufr()
{
    return $this->belongsTo(Ufr::class);
}

public function departement()
{
    return $this->belongsTo(Departement::class);
}
public function entreprise()
{
    return $this->belongsTo(Entreprise::class);
}


}