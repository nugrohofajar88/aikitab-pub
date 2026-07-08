<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookImport extends Model
{
    protected $fillable = [
        'source_local_id',
        'filename',
        'request_uuid',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
