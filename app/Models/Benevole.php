<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Benevole extends Model
{
    protected $table = 'benevoles';
    protected $primaryKey = 'id_benevole';
    use HasFactory;
    protected $fillable = ['nb_missions_benevole'];

    public $timestamps = false;
}
