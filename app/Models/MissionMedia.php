<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionMedia extends Model
{
    protected $table = 'mission_medias';
    protected $primaryKey = 'id_media_mission';
    protected $fillable = [
        'id_mission',
        'chemin_fichier',
        'type_mime',
        'taille_fichier',
        'telecharge_par_utilisateur_id',
    ];

    protected $casts = [
        'taille_fichier' => 'integer',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'telecharge_par_utilisateur_id', 'id_utilisateur');
    }
}
