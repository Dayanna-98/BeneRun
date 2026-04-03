<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionContact extends Model
{
    protected $table = 'mission_contacts';
    protected $primaryKey = 'id_contact_mission';

    protected $fillable = [
        'id_mission',
        'nom_contact',
        'telephone_contact',
        'email_contact',
        'est_contact_principal',
        'est_contact_jour_j',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission');
    }

}
