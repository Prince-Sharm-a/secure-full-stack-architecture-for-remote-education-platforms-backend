<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    //
    protected $fillable = [
        'teacher_id','title','description','price','level','status','category','cover_image'
    ];

    public function module(){
        return $this->hasMany(Module::class,'course_id','id');
    }
    public function teacher(){
        return $this->belongsTo(User::class,'teacher_id');
    }
    public function students(){
        return $this->hasMany(Enrollment::class,'course_id','id');
    }
}
