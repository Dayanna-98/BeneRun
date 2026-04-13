<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
            'possede_permis_utilisateur' => 'boolean',
            'est_motorise_utilisateur' => 'boolean',
            'possede_vehicule_utilisateur' => 'boolean',
            'est_anonyme_utilisateur' => 'boolean',
            'est_suspendu_utilisateur' => 'boolean',
            'nombre_missions_utilisateur' => 'integer',
        ];
    }

    public function benevole()
    {
        return $this->hasOne(Benevole::class, 'id_benevole');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'id_admin');
    }
}
