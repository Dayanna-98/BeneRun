<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $table = 'badges';
    protected $primaryKey = 'id_badge';
    protected $fillable = [
        'titre_badge',
        'description_badge',
        'icone_badge',
        'score_badge',
        'regle_auto',
    ];

    public function utilisateurs()
    {
        return $this->belongsToMany(
            User::class,
            'user_badges',
            'id_badge',
            'id_utilisateur'
            )
        ->withPivot('attribue_le')
        ->withTimestamps();
    }

}
