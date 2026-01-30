<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model 
{
    protected $table = 'badges';
    protected $primaryKey = 'id_badge';
    protected $fillable = ['id_benevole', 'titre_badge', 'valeur_badge', 'regle_auto_badge'];

    public function benevole()
    {
        return $this->belongsTo(Benevole::class, 'id_benevole');
    }
}
