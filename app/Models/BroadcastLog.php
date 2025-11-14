<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastLog extends Model
{
    protected $fillable = [
        'type',
        'target_group',
        'title',
        'message',
        'status',
        'note',
        'triggered_by',

    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
