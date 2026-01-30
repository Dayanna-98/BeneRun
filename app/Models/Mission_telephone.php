<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission_telephone extends Model
{
    protected $table = 'mission_telephones';
    protected $primaryKey = 'id_mission_telephone';
    protected $fillable = ['id_mission', 'id_telephone'];

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission');
    }

    public function telephone()
    {
        return $this->belongsTo(Telephone::class, 'id_telephone');
    }

}
