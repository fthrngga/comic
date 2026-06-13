<?php

namespace App\Drivers;

use App\Contracts\ComicDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShinigamiDriver implements ComicDriverInterface
{
    protected string $apiUrl = 'https://api.shngm.io';

    public function getMangaDetails(string $sourceMangaId): array
    {
        return [];
    }

    public function getChapterImages(string $mangaId, string $chapterId): array
    {
        try {
            $endpoint = "{$this->apiUrl}/v1/chapter/detail/{$chapterId}";

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
                'Referer' => 'https://shinigami.to/',
                'Origin' => 'https://shinigami.to',
            ])->timeout(15)->get($endpoint);

            if ($response->failed()) {
                Log::error("Shinigami API failed to fetch chapter images for ID: {$chapterId}");
                return [];
            }

            $data = $response->json();
            $images = [];

            $baseCdnUrl = rtrim($data['data']['base_url'] ?? 'https://assets.shngm.id', '/'); 
            
            // Kita abaikan variabel 'path' dari JSON mereka yang menyesatkan itu
            $chapterData = $data['data']['chapter']['data'] ?? [];

            // ==========================================================
            // KDV ABSOLUTE BYPASS: MERAKIT PATH BERDASARKAN BUKTI FORENSIK
            // Pola: base_url / chapter / manga_{mangaId} / chapter_{chapterId} / nama_file.jpg
            // ==========================================================
            $forcedPath = "chapter/manga_{$mangaId}/chapter_{$chapterId}";

            foreach ($chapterData as $imageName) {
                if (is_string($imageName)) {
                    $cleanImage = ltrim($imageName, '/');
                    
                    // Susun URL mutlak
                    $images[] = $baseCdnUrl . '/' . $forcedPath . '/' . $cleanImage;
                }
            }

            return $images;

        } catch (\Exception $e) {
            Log::error("Exception in ShinigamiDriver getChapterImages: " . $e->getMessage());
            return [];
        }
    }
}