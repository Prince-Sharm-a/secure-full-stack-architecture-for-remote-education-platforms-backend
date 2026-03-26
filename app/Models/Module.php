<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    //
    protected $fillable = [
        'course_id','title'
    ];
    
    public function lesson(){
        return $this->belongsTo(Lesson::class,'id','module_id');
    }
}
