<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    //
    protected $fillable = [
        'teacher_id','title','description','price','level','status','category'
    ];

    public function module(){
        return $this->belongsTo(Module::class,'id','course_id');
    }
    public function teacher(){
        return $this->belongsTo(User::class,'teacher_id');
    }
}
