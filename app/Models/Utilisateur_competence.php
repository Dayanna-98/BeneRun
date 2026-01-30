<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utilisateur_competence extends Model
{
    protected $table = 'utilisateur_competences';
    protected $primaryKey = 'id_utilisateur_competence';
    protected $fillable = ['id_utilisateur', 'id_competence'];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function competence()
    {
        return $this->belongsTo(Competence::class, foreignKey: 'id_competence');
    }

}
