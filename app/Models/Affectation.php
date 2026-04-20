<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    protected $table = 'affectations';
    protected $primaryKey = 'id_affectation';
    protected $fillable = [
        'id_mission',
        'id_utilisateur',
        'statut_affectation',
        'est_responsable',
        'remarque',
        'date_affectation',
        'date_confirmation',
        'date_presence',
    ];

    protected $casts = [
        'est_responsable' => 'boolean',
        'date_affectation' => 'datetime',
        'date_confirmation' => 'datetime',
        'date_presence' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission');
    }
}
