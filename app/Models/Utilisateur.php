<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Benevole;
use App\Models\Admin;

class Utilisateur extends Model
{
    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';
    protected $fillable = ['nom_utilisateur', 'prenom_utilisateur', 'adresse_email_utilisateur', 'mot_de_passe_utilisateur', 'date_inscription_utilisateur' ];


    public $timestamps = false;

        public function benevole() {
        return $this->hasOne(Benevole::class, 'id_utilisateur');
    }

    public function admin() {
        return $this->hasOne(Admin::class, 'id_utilisateur');
    }
}
