<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Material extends Model implements HasMedia
{
    //
    use InteractsWithMedia;

    protected $fillable=['course_id', 'title', 'description', 'uploader_id'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('files')->useDisk('public');
    }
}
