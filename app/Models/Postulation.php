<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postulation extends Model
{
    protected $primaryKey = 'id_postulation';
    protected $fillable = [
        'id_mission',
        'id_utilisateur',
        'statut_postulation',
        'remarque',
        'date_decision',
        'date_annulation',
    ];

    protected $casts = [
        'date_postulation' => 'datetime',
        'date_decision' => 'datetime',
        'date_annulation' => 'datetime',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission');
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
