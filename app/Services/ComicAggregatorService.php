<?php

namespace App\Services;

use App\Contracts\ComicDriverInterface;
use Illuminate\Support\Facades\Cache;

class ComicAggregatorService
{
    public function __construct(
        protected ComicDriverInterface $driver
    ) {}

    /**
     * Fetch chapter images, checking cache first.
     * Cache duration is set to 24 hours (86400 seconds).
     *
     * @param string $mangaId
     * @param string $chapterId
     * @return array
     */
    public function fetchAndCacheChapterImages(string $mangaId, string $chapterId): array
    {
        $cacheKey = "chapter_images:{$mangaId}:{$chapterId}";

        return Cache::remember($cacheKey, 86400, function () use ($mangaId, $chapterId) {
            return $this->driver->getChapterImages($mangaId, $chapterId);
        });
    }
}
