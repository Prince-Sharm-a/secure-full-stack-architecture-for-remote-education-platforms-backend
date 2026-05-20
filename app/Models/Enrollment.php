<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    //
    protected $fillable = [
        'user_id','course_id','progress','enrolled_at'
    ];
    public function courses(){
        return $this->belongsTo(Course::class,'course_id','id');
    }
    public function students(){
        return $this->belongsTo(Enrollment::class,'user_id','id');
    }
}
