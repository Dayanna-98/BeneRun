<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCompetencePoint extends Model
{
    protected $table = 'user_competence_points';

    public $incrementing = false;

    protected $fillable = [
        'id_utilisateur',
        'id_competence',
        'points_total',
    ];
}
