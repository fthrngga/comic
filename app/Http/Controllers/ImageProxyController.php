<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageProxyController extends Controller
{
    public function stream(Request $request)
    {
        $url = $request->query('url');

        if (!$url) {
            return response()->json(['error' => 'URL is required'], 400);
        }

        try {
            // Kita menembak CDN target dengan menyamar sebagai browser yang sedang membuka web Shinigami
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Referer' => 'https://shinigami.to/',
                'Origin' => 'https://shinigami.to',
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ])->timeout(15)->get($url);

            if ($response->successful()) {
                // Kembalikan gambar utuh ke frontend dengan header Cache agar load berikutnya super cepat
                return response($response->body(), 200)
                        ->header('Content-Type', $response->header('Content-Type') ?? 'image/jpeg')
                        ->header('Cache-Control', 'max-age=86400, public');
            }

            Log::warning("Proxy gagal memuat gambar: {$url}. Status: " . $response->status());
            return response()->json(['error' => 'Failed to fetch image'], $response->status());

        } catch (\Exception $e) {
            Log::error("Proxy exception pada URL {$url}: " . $e->getMessage());
            return response()->json(['error' => 'Proxy error'], 500);
        }
    }
}