<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $primaryKey = 'id_document';
    protected $fillable = ['id_mission', 'id_course', 'nom_fichier_document', 'type_mime_document', 'date_document'];

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'id_mission');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

}
