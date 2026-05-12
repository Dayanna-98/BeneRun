<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionUserReward extends Model
{
    protected $table = 'mission_user_rewards';
    protected $primaryKey = 'id_mission_user_reward';

    protected $fillable = [
        'id_mission',
        'id_utilisateur',
        'rewarded_at',
        'details_recompense',
    ];

    protected $casts = [
        'rewarded_at' => 'datetime',
        'details_recompense' => 'array',
    ];
}
