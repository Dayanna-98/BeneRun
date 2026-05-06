<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionPosition extends Model
{
    protected $primaryKey = 'id_position';

    protected $fillable = [
        'id_mission',
        'id_utilisateur',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission', 'id_mission');
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
