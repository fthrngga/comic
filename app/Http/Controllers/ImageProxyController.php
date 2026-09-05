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

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid URL'], 400);
        }

        $referer = 'https://shinigami.to/';
        $origin = 'https://shinigami.to';

        if (str_contains($url, 'komiku.to') || str_contains($url, 'komiku.org')) {
            $referer = 'https://v1.komikcast.ac/';
            $origin = 'https://v1.komikcast.ac';
        }

        // Spoof headers to bypass hotlink protection (CORB/CORS)
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Referer' => $referer,
            'Origin' => $origin,
            'Accept' => 'image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        ];

        try {
            $response = Http::withHeaders($headers)->timeout(15)->get($url);

            if ($response->failed()) {
                Log::error("ImageProxyController failed to fetch image: {$url}, Status: " . $response->status());
                return response()->json(['error' => 'Failed to fetch image'], $response->status());
            }

            $contentType = $response->header('Content-Type') ?? 'image/jpeg';
            $body = $response->body();

            // Stream response to browser with public caching for 24 hours
            return response($body)
                    ->header('Content-Type', $contentType)
                    ->header('Cache-Control', 'public, max-age=86400');

        } catch (\Exception $e) {
            Log::error("ImageProxyController Exception: " . $e->getMessage() . " for URL: {$url}");
            return response()->json(['error' => 'Proxy error: ' . $e->getMessage()], 500);
        }
    }
}
