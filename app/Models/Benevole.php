<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Benevole extends Model
{
    protected $table = 'benevoles';
    protected $primaryKey = 'id_benevole';
    protected $fillable = ['nb_missions_benevole'];

    public $timestamps = false;


     public function user() {
        return $this->belongsTo(User::class, 'id_utilisateur');
    }

}
