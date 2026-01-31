<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    protected $table = 'affectations';
    protected $primaryKey = 'id_affectation';
    protected $fillable = ['id_benevole', 'id_mission', 'statut_affectation', 'remarque_affectation', 'est_responsable_affectation'];

    public function benevole()
    {
        return $this->belongsTo(Benevole::class, 'id_benevole');
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission');
    }
}
