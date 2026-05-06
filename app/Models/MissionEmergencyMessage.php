<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionEmergencyMessage extends Model
{
    protected $table = 'mission_emergency_messages';
    protected $primaryKey = 'id_mission_emergency_message';

    protected $fillable = [
        'id_mission',
        'id_evenement',
        'id_emetteur_utilisateur',
        'categorie_urgence',
        'message_urgence',
        'pris_en_charge_par_utilisateur_id',
        'pris_en_charge_le',
    ];

    protected $casts = [
        'pris_en_charge_le' => 'datetime',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission', 'id_mission');
    }

    public function evenement()
    {
        return $this->belongsTo(Evenement::class, 'id_evenement', 'id_evenement');
    }

    public function emetteur()
    {
        return $this->belongsTo(User::class, 'id_emetteur_utilisateur', 'id_utilisateur');
    }

    public function prisEnChargePar()
    {
        return $this->belongsTo(User::class, 'pris_en_charge_par_utilisateur_id', 'id_utilisateur');
    }

    public function consultations()
    {
        return $this->hasMany(MissionEmergencyMessageView::class, 'id_mission_emergency_message', 'id_mission_emergency_message');
    }
}
