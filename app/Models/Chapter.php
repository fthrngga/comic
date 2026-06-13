<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
{
    use HasUuids;

    protected $fillable = [
        'manga_id',
        'chapter_number',
        'chapter_title',
        'source_chapter_id',
        'pages_data',
        'base_url_override',
        'path_override',
    ];

    protected function casts(): array
    {
        return [
            'chapter_number' => 'decimal:2',
            'pages_data' => 'array',
        ];
    }

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
    }
}
