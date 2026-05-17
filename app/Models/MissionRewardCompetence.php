<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionRewardCompetence extends Model
{
    protected $table = 'mission_reward_competences';

    public $incrementing = false;

    protected $fillable = [
        'id_mission',
        'id_competence',
        'points_gagnes',
    ];
}
