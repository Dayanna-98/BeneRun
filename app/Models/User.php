<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Badge;
use App\Models\Competence;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id_utilisateur';

    protected $fillable = [
        'nom_utilisateur',
        'prenom_utilisateur',
        'email',
        'password',
        'role_utilisateur',
        'telephone_utilisateur',
        'adresse_utilisateur',
        'date_naissance_utilisateur',
        'allergies_utilisateur',
        'problemes_sante_utilisateur',
        'possede_permis_utilisateur',
        'est_motorise_utilisateur',
        'possede_vehicule_utilisateur',
        'taille_tshirt_utilisateur',
        'est_anonyme_utilisateur',
        'est_suspendu_utilisateur',
        'raison_suspension_utilisateur',
        'permissions_utilisateur',
        'nombre_missions_utilisateur',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'possede_permis_utilisateur' => 'boolean',
            'est_motorise_utilisateur' => 'boolean',
            'possede_vehicule_utilisateur' => 'boolean',
            'est_anonyme_utilisateur' => 'boolean',
            'est_suspendu_utilisateur' => 'boolean',
            'nombre_missions_utilisateur' => 'integer',
            'date_naissance_utilisateur' => 'date',
        ];
    }

    public function competences()
    {
        return $this->belongsToMany(
            Competence::class,
            'user_competences',
            'id_utilisateur',
            'id_competence'
            )->withPivot('niveau')
            ->withTimestamps();
    }

    public function badges()
    {
        return $this->belongsToMany(
            Badge::class,
            'user_badges',
            'id_utilisateur',
            'id_badge'
        )
        ->withPivot('attribue_le')
        ->withTimestamps();
    }

    public function certificats()
    {
        return $this->hasMany(Certificat::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function missions_favorites()
    {
        return $this->belongsToMany(
            Mission::class,
            'favorites',
            'id_utilisateur',
            'id_mission'
        )->withTimestamps();
    }

    public function missions_creees()
    {
        return $this->hasMany(Mission::class, 'responsable_utilisateur_id', 'id_utilisateur');
    }

    public function postulations()
    {
        return $this->hasMany(Postulation::class, 'id_utilisateur');
    }
}
