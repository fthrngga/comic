<?php

namespace App\Drivers;

use App\Contracts\ComicDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShinigamiDriver implements ComicDriverInterface
{
    // Menggunakan domain API asli hasil temuan KDV & Bos Fathur
    protected string $apiUrl = 'https://api.shngm.io'; 

    public function getMangaDetails(string $sourceMangaId): array
    {
        // Akan diimplementasikan nanti untuk auto-fetch manga
        return [];
    }

    public function getChapterImages(string $mangaId, string $chapterId): array
    {
        try {
            // Menggunakan PATH ASLI: /v1/chapter/detail/{id}
            $endpoint = "{$this->apiUrl}/v1/chapter/detail/{$chapterId}";

            // Header standar untuk menghindari blokir
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

            // Membaca dari struktur JSON yang benar ($data['data'])
            $baseCdnUrl = $data['data']['base_url'] ?? '';
            $path = $data['data']['path'] ?? '';
            $chapterData = $data['data']['chapter']['data'] ?? [];

            foreach ($chapterData as $imageName) {
                // Rakit URL gambar secara penuh
                $images[] = rtrim($baseCdnUrl, '/') . '/' . trim($path, '/') . '/' . ltrim($imageName, '/');
            }

            return $images;

        } catch (\Exception $e) {
            Log::error("Exception in ShinigamiDriver getChapterImages: " . $e->getMessage());
            return [];
        }
    }
}