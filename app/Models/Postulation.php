<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postulation extends Model
{
    protected $primaryKey = 'id_postulation';
    protected $fillable = ['id_mission', 'id_benevole', 'date_postulation', 'date_acceptation_refus_postulation', 'date_annulation_postulation', 'remarque_postulation', 'presence_postulation', 'commentaire_postulation', 'statut_postulation'];

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission');
    }
    public function benevole()
    {
        return $this->belongsTo(Benevole::class, 'id_benevole');
    }
}
