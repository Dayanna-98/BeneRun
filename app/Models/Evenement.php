<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evenement extends Model
{
    protected $table = 'evenements';
    protected $primaryKey = 'id_evenement';
    protected $fillable = [
        'nom_evenement',
        'description_evenement',
        'date_debut_evenement',
        'date_fin_evenement',
        'heure_debut_evenement',
        'heure_fin_evenement',
        'lieu_evenement',
        'latitude_evenement',
        'longitude_evenement',
        'organisateur_evenement',
        'image_evenement',
        'nombre_benevoles_requis',
        'est_annule_evenement',
        'date_annulation_evenement',
        'raison_annulation_evenement',
        'est_publie_evenement',
        'cree_par_utilisateur_id',
    ];

    protected $casts = [
        'date_debut_evenement' => 'date',
        'date_fin_evenement' => 'date',
        'heure_debut_evenement' => 'time',
        'heure_fin_evenement' => 'time',
        'latitude_evenement' => 'decimal:7',
        'longitude_evenement' => 'decimal:7',
        'nombre_benevoles_requis' => 'integer',
        'est_annule_evenement' => 'boolean',
        'date_annulation_evenement' => 'date',
        'est_publie_evenement' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'cree_par_utilisateur_id', 'id_utilisateur');
    }

    public function missions()
    {
        return $this->hasMany(Mission::class, 'id_evenement');
    }
}
