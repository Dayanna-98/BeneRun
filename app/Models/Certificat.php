<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificat extends Model
{
    protected $table = 'certificats';
    protected $primaryKey = 'id_certificat';
    protected $fillable = [
        'id_utilisateur',
        'titre_certificat',
        'emetteur_certificat',
        'date_emission_certificat',
        'date_expiration_certificat',
        'type_certificat',
        'statut_certificat',
        'chemin_fichier_certificat',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }

}
