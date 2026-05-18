<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    //
    protected $fillable = [
        'course_id','title','description','due_date'
    ];

    public function submission(){
        return $this->hasMany(Submission::class,'assignment_id','id');
    }
    public function courses(){
        return $this->belongsTo(Course::class,'course_id');
    }
}
