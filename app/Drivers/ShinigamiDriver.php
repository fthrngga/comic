<?php

namespace App\Drivers;

use App\Contracts\ComicDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShinigamiDriver implements ComicDriverInterface
{
    protected string $baseUrl = 'https://shinigami.id'; // Adjust according to real endpoint if different

    public function getMangaDetails(string $sourceMangaId): array
    {
        // Implementation for getting manga details would go here
        return [];
    }

    public function getChapterImages(string $mangaId, string $chapterId): array
    {
        try {
            // This endpoint is just an example based on the prompt instructions.
            $response = Http::timeout(15)->get("{$this->baseUrl}/api/chapter/{$chapterId}");

            if ($response->failed()) {
                Log::error("Shinigami API failed to fetch chapter images for ID: {$chapterId}", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [];
            }

            $data = $response->json();
            $images = [];

            // Example response structure assumption based on instructions:
            // { "base_url": "https://cdn...", "path": "/chapter-1/", "chapter": { "data": ["01.jpg", "02.jpg"] } }
            $baseUrl = $data['base_url'] ?? '';
            $path = $data['path'] ?? '';
            $chapterData = $data['chapter']['data'] ?? [];

            foreach ($chapterData as $imageName) {
                $images[] = rtrim($baseUrl, '/') . '/' . trim($path, '/') . '/' . ltrim($imageName, '/');
            }

            return $images;

        } catch (\Exception $e) {
            Log::error("Exception in ShinigamiDriver getChapterImages: " . $e->getMessage());
            return [];
        }
    }
}
