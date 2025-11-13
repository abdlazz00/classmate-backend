<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogDownload extends Model
{
    //
    protected $fillable=[
        'user_id',
        'material_id',
        'download_at',
    ];
    protected $casts=[
        'download_at'=>'datetime',
    ];

    public function user()
    {
        $this->belongsTo(User::class);
    }
    public function material()
    {
        $this->belongsTo(Material::class);
    }
}
