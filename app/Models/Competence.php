<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    protected $table = 'competences';
    protected $primaryKey = 'id_competence';
    protected $fillable = ['nom_competence'];

    public $timestamps = false;
}
