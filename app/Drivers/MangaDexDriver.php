<?php

namespace App\Drivers;

use App\Contracts\ComicDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MangaDexDriver implements ComicDriverInterface
{
    public function getMangaDetails(string $slug): array
    {
        // For MangaDex, $slug is actually the manga ID (UUID)
        $url = "https://api.mangadex.org/manga/{$slug}?includes[]=cover_art";
        
        $response = Http::withOptions(['verify' => false])->get($url);
        if ($response->failed()) {
            Log::error("Failed to fetch MangaDex manga: {$slug}");
            return [];
        }
        
        $data = $response->json('data');
        if (!$data) return [];

        $title = $data['attributes']['title']['en'] ?? current($data['attributes']['title']);
        $synopsis = $data['attributes']['description']['en'] ?? current($data['attributes']['description']);
        
        $coverUrl = '';
        foreach ($data['relationships'] as $rel) {
            if ($rel['type'] === 'cover_art' && isset($rel['attributes']['fileName'])) {
                $coverUrl = "https://uploads.mangadex.org/covers/{$slug}/" . $rel['attributes']['fileName'];
                break;
            }
        }

        // Fetch chapters
        $chaptersUrl = "https://api.mangadex.org/manga/{$slug}/feed?translatedLanguage[]=en&order[chapter]=asc&limit=100";
        $chResponse = Http::withOptions(['verify' => false])->get($chaptersUrl);
        $chapters = [];
        
        if ($chResponse->successful()) {
            $chData = $chResponse->json('data');
            if ($chData) {
                foreach ($chData as $ch) {
                    $chNum = $ch['attributes']['chapter'] ?? '0';
                    $chTitle = $ch['attributes']['title'] ?? '';
                    $chapters[] = [
                        'chapter_id' => $ch['id'],
                        'chapter_number' => $chNum,
                        'chapter_title' => $chTitle ? "Chapter $chNum - $chTitle" : "Chapter $chNum",
                    ];
                }
            }
        }

        return [
            'title' => $title,
            'synopsis' => is_array($synopsis) ? '' : $synopsis,
            'cover_url' => $coverUrl,
            'chapters' => $chapters
        ];
    }

    public function getChapterImages(string $mangaId, string $chapterId): array
    {
        $url = "https://api.mangadex.org/at-home/server/{$chapterId}";
        $response = Http::withOptions(['verify' => false])->get($url);
        
        if ($response->failed()) return [];

        $data = $response->json();
        if (!isset($data['baseUrl'])) return [];

        $hash = $data['chapter']['hash'];
        $images = [];

        foreach ($data['chapter']['data'] as $filename) {
            $images[] = "{$data['baseUrl']}/data/{$hash}/{$filename}";
        }

        return $images;
    }
}
