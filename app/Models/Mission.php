<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'inscription_requise',
        'visibilite_mission',
        'consignes_securite',
        'image_mission',
        'publie_le_mission',
    ];

    protected $casts = [
        'date_mission' => 'date',
        'heure_debut_mission' => 'string',
        'heure_fin_mission' => 'string',
        'latitude_mission' => 'decimal:7',
        'longitude_mission' => 'decimal:7',
        'nombre_benevoles_max' => 'integer',
        'nombre_benevoles_backup' => 'integer',
        'inscription_requise' => 'boolean',
        'publie_le_mission' => 'datetime',
    ];

    public function evenement()
    {
        return $this->belongsTo(Evenement::class, 'id_evenement');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_utilisateur_id', 'id_utilisateur');
    }

    public function medias()
    {
        return $this->hasMany(MissionMedia::class, 'id_mission');
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'id_mission', 'id_mission');
    }

    public function competences()
    {
        return $this->belongsToMany(
            Competence::class,
            'mission_competences',
            'id_mission',
            'id_competence',
            'id_mission',
            'id_competence'
        )->withTimestamps();
    }
}




