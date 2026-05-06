<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionEmergencyMessageView extends Model
{
    protected $table = 'mission_emergency_message_views';
    protected $primaryKey = 'id_mission_emergency_message_view';

    protected $fillable = [
        'id_mission_emergency_message',
        'id_utilisateur',
        'consulte_le',
    ];

    protected $casts = [
        'consulte_le' => 'datetime',
    ];

    public function urgence()
    {
        return $this->belongsTo(MissionEmergencyMessage::class, 'id_mission_emergency_message', 'id_mission_emergency_message');
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
