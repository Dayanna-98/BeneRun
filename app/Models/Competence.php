<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    protected $table = 'competences';
    protected $primaryKey = 'id_competence';

    protected $fillable = ['nom_competence',];

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

}
