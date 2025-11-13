<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    //
    protected $fillable=[
        'title',
        'message',
        'class_name',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }
}
