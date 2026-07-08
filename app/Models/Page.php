<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'book_id',
        'page_number',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function paragraphs(): HasMany
    {
        return $this->hasMany(Paragraph::class)->orderBy('paragraph_number');
    }
}
