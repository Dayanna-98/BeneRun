<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'id_course';
    protected $fillable = ['nom_course', 'lieu_course', 'informations_course', 'date_debut_course', 'date_fin_course', 'heure_debut_course', 'heure_fin_course', 'annule_course','date_annulation_course', 'raison_annulation_course', 'publie_course'];
    public $timestamps = false;

}
