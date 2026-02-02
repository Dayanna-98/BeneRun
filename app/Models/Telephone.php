<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telephone extends Model
{
    protected $primaryKey = 'id_telephone';
    protected $fillable = ['id_mission', 'id_course', 'description_telephone', 'numero_telephone', 'detail_telephone'];

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course');
    }
}
