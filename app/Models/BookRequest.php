<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BookRequest extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'author',
        'requester_name',
        'requester_note',
        'original_filename',
        'file_path',
        'status',
        'book_id',
        'claimed_at',
        'completed_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (BookRequest $request) {
            $request->uuid ??= (string) Str::uuid();
        });
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
