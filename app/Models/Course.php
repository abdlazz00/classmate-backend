<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    //
    protected $fillable = [
        'name',
        'code',
        'lecturer',
        'class_name',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
    public function materials()
    {
        return $this->hasMany(Material::class);
    }
}
