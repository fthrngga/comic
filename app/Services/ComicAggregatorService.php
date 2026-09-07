<?php

namespace App\Services;

use App\Contracts\ComicDriverInterface;
use Illuminate\Support\Facades\Cache;

class ComicAggregatorService
{
    public function getDriver(string $sourceCode): ComicDriverInterface
    {
        return match($sourceCode) {
            'shinigami' => app(\App\Drivers\ShinigamiDriver::class),
            'komikcast' => app(\App\Drivers\KomikcastDriver::class),
            'mangadex' => app(\App\Drivers\MangaDexDriver::class),
            default => throw new \Exception("Unsupported driver: {$sourceCode}")
        };
    }

    /**
     * Fetch chapter images, checking cache first.
     * Cache duration is set to 24 hours (86400 seconds).
     *
     * @param string $mangaId
     * @param string $chapterId
     * @param string $sourceCode
     * @return array
     */
    public function fetchAndCacheChapterImages(string $mangaId, string $chapterId, string $sourceCode = 'shinigami'): array
    {
        $cacheKey = "chapter_images:{$sourceCode}:{$mangaId}:{$chapterId}";

        return Cache::remember($cacheKey, 86400, function () use ($mangaId, $chapterId, $sourceCode) {
            $driver = $this->getDriver($sourceCode);
            return $driver->getChapterImages($mangaId, $chapterId);
        });
    }
}
