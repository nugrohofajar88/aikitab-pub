<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'source_local_id',
        'title',
        'author',
        'total_pages',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class)->orderBy('page_number');
    }

    public function paragraphs(): HasMany
    {
        return $this->hasMany(Paragraph::class);
    }
}
