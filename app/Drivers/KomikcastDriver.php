<?php

namespace App\Drivers;

use App\Contracts\ComicDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KomikcastDriver implements ComicDriverInterface
{
    protected string $baseUrl = 'https://v1.komikcast.ac';

    public function getMangaDetails(string $sourceMangaId): array
    {
        try {
            $url = "{$this->baseUrl}/manga/{$sourceMangaId}";
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])->timeout(15)->get($url);

            if ($response->failed()) {
                Log::error("Komikcast failed to fetch manga details for: {$sourceMangaId}");
                return [];
            }

            $html = $response->body();
            
            // Extract Title
            preg_match('/<h1 class="text-2xl font-black leading-tight text-white sm:text-3xl">(.*?)<\/h1>/s', $html, $titleMatches);
            $title = $titleMatches[1] ?? 'Unknown Title';

            // Extract Synopsis
            preg_match('/<p class="whitespace-pre-line text-sm leading-relaxed text-slate-300 line-clamp-4">(.*?)<\/p>/s', $html, $synopsisMatches);
            $synopsis = $synopsisMatches[1] ?? '';

            // Extract Cover
            preg_match('/<meta property="og:image" content="(.*?)"/s', $html, $coverMatches);
            $coverUrl = $coverMatches[1] ?? '';

            // Extract Chapters from Next.js payload (might be escaped)
            preg_match('/chapters\\\\?":(\[.*?\}\])/s', $html, $chaptersMatches);
            $chapters = [];
            
            if (!empty($chaptersMatches[1])) {
                $jsonStr = stripslashes($chaptersMatches[1]);
                $chaptersData = json_decode($jsonStr, true);
                if (is_array($chaptersData)) {
                    foreach ($chaptersData as $chapter) {
                        $chapters[] = [
                            'chapter_id' => $chapter['slug'],
                            'chapter_number' => $chapter['num'],
                            'created_at' => date('Y-m-d H:i:s', strtotime($chapter['date'] ?? 'now')),
                        ];
                    }
                }
            }

            return [
                'title' => $title,
                'synopsis' => $synopsis,
                'cover_url' => $coverUrl,
                'chapters' => $chapters
            ];

        } catch (\Exception $e) {
            Log::error("Exception in KomikcastDriver getMangaDetails: " . $e->getMessage());
            return [];
        }
    }

    public function getChapterImages(string $mangaId, string $chapterId): array
    {
        try {
            $url = "{$this->baseUrl}/manga/{$mangaId}/{$chapterId}";
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])->timeout(15)->get($url);

            if ($response->failed()) {
                Log::error("Komikcast failed to fetch chapter images for ID: {$chapterId}");
                return [];
            }

            $html = $response->body();
            
            // Extract images from Next.js payload
            preg_match('/images\\\\?":(\[.*?\])/s', $html, $imageMatches);
            $images = [];

            if (!empty($imageMatches[1])) {
                $jsonStr = stripslashes($imageMatches[1]);
                $decodedImages = json_decode($jsonStr, true);
                if (is_array($decodedImages)) {
                    $images = $decodedImages;
                }
            }

            return $images;

        } catch (\Exception $e) {
            Log::error("Exception in KomikcastDriver getChapterImages: " . $e->getMessage());
            return [];
        }
    }
}
