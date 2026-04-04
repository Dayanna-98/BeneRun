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

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par_utilisateur_id', 'id_utilisateur');
    }

}
