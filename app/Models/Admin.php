<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Utilisateur;

class Admin extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'id_admin';
    protected $fillable = ['est_organisateur_admin', 'permission_admin'];

    public $timestamps = false;

        public function utilisateur() {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }
}
