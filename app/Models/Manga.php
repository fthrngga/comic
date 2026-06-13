<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Manga extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'synopsis',
        'cover_url',
        'status',
        'type',
        'source_code',
        'source_manga_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (Manga $manga) {
            if (empty($manga->slug)) {
                $manga->slug = Str::slug($manga->title);
            }
        });
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }
}
