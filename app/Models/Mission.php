<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model 
{
    protected $primaryKey = 'id_mission';
    protected $fillable = ['id_course', 'id_benevole', 'titre_mission', 'type_mission', 'description_mission', 'date_debut_mission', 'date_fin_mission', 'heure_debut_mission', 'heure_fin_mission', 'lieu_mission', 'nombre_mission', 'statut_mission', 'publie_mission'];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course');
    }
    public function benevole()
    {
        return $this->belongsTo(Benevole::class, 'id_benevole');
    }

}




