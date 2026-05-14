<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    //
    protected $fillable = [
        'assignment_id','student_id','file_url','grade'
    ];
    
    public function student(){
        return $this->belongsTo(User::class,'student_id','id');
    }
}
