<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificat extends Model
{
    protected $table = 'certificats';
    protected $primaryKey = 'id_certificat';
    protected $fillable = ['id_benevole', 'titre_certificat'];

    public function benevole()
    {
        return $this->belongsTo(Benevole::class, 'id_benevole');
    }
}
