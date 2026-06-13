<?php

namespace App\Drivers;

use App\Contracts\ComicDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShinigamiDriver implements ComicDriverInterface
{
    // Menggunakan shinigami.to sesuai dengan URL yang ada di browser Bos sebelumnya
    protected string $baseUrl = 'https://shinigami.to'; 

    public function getMangaDetails(string $sourceMangaId): array
    {
        // Implementasi detail manga menyusul
        return [];
    }

    public function getChapterImages(string $mangaId, string $chapterId): array
    {
        try {
            // URL target API
            $endpoint = "{$this->baseUrl}/api/chapter/{$chapterId}";

            // 1. Serangan menembus Cloudflare dengan Header palsu (menyamar jadi browser)
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
                'Referer' => "{$this->baseUrl}/",
                'Origin' => $this->baseUrl,
            ])->timeout(15)->get($endpoint);

            // ==========================================
            // JEBAKAN DEBUGGING (KDV TRAP)
            // ==========================================
            dd([
                '1_TARGET_URL' => $endpoint,
                '2_HTTP_STATUS' => $response->status(),
                '3_RAW_BODY' => $response->json() ?? $response->body(),
            ]);
            // ==========================================

            if ($response->failed()) {
                Log::error("Shinigami API failed to fetch chapter images for ID: {$chapterId}", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [];
            }

            $data = $response->json();
            $images = [];

            // 2. Perbaikan Struktur JSON (harus masuk ke dalam index ['data'] dulu)
            $baseCdnUrl = $data['data']['base_url'] ?? '';
            $path = $data['data']['path'] ?? '';
            $chapterData = $data['data']['chapter']['data'] ?? [];

            foreach ($chapterData as $imageName) {
                // Menggabungkan URL CDN, Path, dan Nama File Gambar
                $images[] = rtrim($baseCdnUrl, '/') . '/' . trim($path, '/') . '/' . ltrim($imageName, '/');
            }

            return $images;

        } catch (\Exception $e) {
            Log::error("Exception in ShinigamiDriver getChapterImages: " . $e->getMessage());
            return [];
        }
    }
}