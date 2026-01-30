<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission_competence extends Model
{
    protected $table = 'mission_competences';
    protected $primaryKey = 'id_mission_competence';
    protected $fillable = ['id_mission', 'id_competence'];

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission');
    }

    public function competence()
    {
        return $this->belongsTo(Competence::class, foreignKey: 'id_competence');
    }
}
