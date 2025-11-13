<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    //
    protected $fillable=[
        'course_id',
        'title',
        'description',
        'deadline',
        'created_by',
        'status',
    ];

    protected $casts=[
      'deadline'=>'datetime',
    ];

    public function course()
    {
     return $this->belongsTo(Course::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }
}
