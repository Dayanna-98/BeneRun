<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model 
{
    protected $table = 'badges';
    protected $primaryKey = 'id_badge';
    protected $fillable = ['titre_badge', 'description_badge', 'score_badge', 'regle_auto'];
}
