<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course_telephone extends Model
{
    protected $table = 'course_telephones';
    protected $primaryKey = 'id_course_telephone';
    protected $fillable = ['id_course', 'id_telephone'];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    public function telephone()
    {
        return $this->belongsTo(Telephone::class, 'id_telephone');
    }
}
