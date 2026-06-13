<?php

namespace App\Contracts;

interface ComicDriverInterface
{
    /**
     * Get details of a specific manga.
     *
     * @param string $sourceMangaId
     * @return array
     */
    public function getMangaDetails(string $sourceMangaId): array;

    /**
     * Get list of image URLs for a specific chapter.
     *
     * @param string $mangaId
     * @param string $chapterId
     * @return array
     */
    public function getChapterImages(string $mangaId, string $chapterId): array;
}
