<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Utilisateur;

class Benevole extends Model
{
    protected $table = 'benevoles';
    protected $primaryKey = 'id_benevole';
    protected $fillable = ['nb_missions_benevole'];

    public $timestamps = false;


     public function utilisateur() {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

}
