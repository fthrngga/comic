<?php

namespace App\Drivers;

use App\Contracts\ComicDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GlobalComixDriver implements ComicDriverInterface
{
    public function getMangaDetails(string $slug): array
    {
        $url = "https://globalcomix.com/c/{$slug}";
        
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if ($response->failed()) {
            Log::error("Failed to fetch GlobalComix manga: {$slug}");
            return [];
        }

        $html = $response->body();
        $parts = explode('window.__REACT_QUERY_STATE__=', $html);
        if (count($parts) < 2) {
            Log::error("React state not found in GlobalComix: {$slug}");
            return [];
        }

        $jsonString = explode('</script>', $parts[1])[0];
        $jsonString = rtrim(trim($jsonString), ';'); // Remove trailing semicolon

        $json = json_decode($jsonString, true);
        if (!$json || !isset($json['queries'])) {
            return [];
        }

        $mangaData = null;
        $chaptersData = [];

        foreach ($json['queries'] as $query) {
            $key = $query['queryKey'][0] ?? '';
            
            // Find Comic detail
            if (strpos($key, 'Comic-') === 0 && isset($query['state']['data']['name'])) {
                $mangaData = $query['state']['data'];
            }
            
            // Find Releases/Chapters
            // In GlobalComix, chapters are usually "releases" for a specific comic
            if (is_array($query['queryKey']) && in_array('Releases', $query['queryKey'])) {
                 $chaptersData = $query['state']['data']['data'] ?? [];
            }
        }

        if (!$mangaData) return [];

        $chapters = [];
        foreach ($chaptersData as $ch) {
            $chapters[] = [
                'chapter_id' => $ch['uuid'] ?? $ch['id'],
                'chapter_number' => $ch['order'] ?? 0,
                'chapter_title' => $ch['title'] ?? 'Chapter ' . ($ch['order'] ?? 0),
            ];
        }

        return [
            'title' => $mangaData['name'],
            'synopsis' => $mangaData['description'] ?? '',
            'cover_url' => $mangaData['image_url'] ?? '',
            'chapters' => $chapters
        ];
    }

    public function getChapterImages(string $mangaId, string $chapterId): array
    {
        // $chapterId in GlobalComix is usually the UUID of the release, e.g. "00114620-9ebe-475e-ba37-aacedd89dcc6"
        $url = "https://globalcomix.com/read/{$chapterId}/1";
        
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);

        if ($response->failed()) return [];

        $html = $response->body();
        $parts = explode('window.__REACT_QUERY_STATE__=', $html);
        if (count($parts) < 2) return [];

        $jsonString = explode('</script>', $parts[1])[0];
        $jsonString = rtrim(trim($jsonString), ';');

        $json = json_decode($jsonString, true);
        if (!$json || !isset($json['queries'])) return [];

        $images = [];
        
        foreach ($json['queries'] as $query) {
            $key = $query['queryKey'][0] ?? '';
            if ($key === 'Release' && isset($query['state']['data']['pages'])) {
                foreach ($query['state']['data']['pages'] as $page) {
                    if (isset($page['url'])) {
                        $images[] = $page['url'];
                    }
                }
            }
        }

        return $images;
    }
}
