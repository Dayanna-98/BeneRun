<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Evenement; 
use App\Models\User;
use App\Models\MissionMedia;
use App\Models\MissionContact;
use App\Models\Competence;

class Mission extends Model
{
    protected $primaryKey = 'id_mission';
    protected $fillable = [
        'id_evenement',
        'responsable_utilisateur_id',
        'titre_mission',
        'type_mission',
        'description_mission',
        'date_mission',
        'heure_debut_mission',
        'heure_fin_mission',
        'lieu_mission',
        'latitude_mission',
        'longitude_mission',
        'nombre_benevoles_max',
        'nombre_benevoles_backup',
        'statut_mission',
        'consignes_securite',
        'image_mission',
        'publie_le_mission',
    ];

    public function evenement()
    {
        return $this->belongsTo(Evenement::class, 'id_evenement');
    }
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_utilisateur_id', 'id_utilisateur');
    }
    public function contacts()
    {
        return $this->hasMany(MissionContact::class, 'id_mission');
    }
    public function medias()
    {
        return $this->hasMany(MissionMedia::class, 'id_mission');
    }

    public function competences()
    {
        return $this->belongsToMany(
            Competence::class,
            'mission_competences',
            'id_mission',
            'id_competence'
        )->withTimestamps();
    }

    public function utilisateurs_favoris()
    {
        return $this->belongsToMany(
        User::class,
        'favorites',
        'id_mission',
        'id_utilisateur'
        )->withTimestamps();
    }

}




