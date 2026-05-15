<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeCompetenceRule extends Model
{
    protected $table = 'badge_competence_rules';

    protected $primaryKey = 'id_badge_competence_rule';

    protected $fillable = [
        'id_badge',
        'id_competence',
        'points_requis',
    ];
}
