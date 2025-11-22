<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogDownload extends Model
{
    //
    protected $fillable=[
        'user_id',
        'material_id',
        'downloaded_at',
        'note'
    ];
    protected $casts=[
        'download_at'=>'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
