<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Mission;
use App\Models\User;
use App\Models\UserCompetencePoint;
use App\Models\BadgeCompetenceRule;

class Competence extends Model
{
    protected $table = 'competences';
    protected $primaryKey = 'id_competence';

    protected $fillable = [
        'nom_competence',
        'types_mission_suggeres',
    ];

    protected $casts = [
        'types_mission_suggeres' => 'array',
    ];

    public function missions()
    {
        return $this->belongsToMany(
            Mission::class,
            'mission_competence',
            'id_competence',
            'id_mission'
        )->withTimestamps();
    }

    public function utilisateurs()
    {
        return $this->belongsToMany(
            User::class,
            'user_competences',
            'id_competence',
            'id_utilisateur'
        )->withPivot('niveau_competence')
        ->withTimestamps();
    }

    public function rewardedInMissions()
    {
        return $this->belongsToMany(
            Mission::class,
            'mission_reward_competences',
            'id_competence',
            'id_mission'
        )
            ->withPivot('points_gagnes')
            ->withTimestamps();
    }

    public function userPoints()
    {
        return $this->hasMany(UserCompetencePoint::class, 'id_competence', 'id_competence');
    }

    public function badgeRules()
    {
        return $this->hasMany(BadgeCompetenceRule::class, 'id_competence', 'id_competence');
    }

}
