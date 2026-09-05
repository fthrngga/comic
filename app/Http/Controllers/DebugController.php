<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class DebugController extends Controller
{
    public function index()
    {
        return Inertia::render('Debug/ScraperProcess');
    }

    public function testScrape(Request $request)
    {
        $logs = [];
        $apiUrl = 'https://api.shngm.io';

        // Step 1: Check DNS and connection
        $logs[] = [
            'step' => 'Persiapan Target API',
            'status' => 'success',
            'details' => 'Target URL diatur ke: ' . $apiUrl
        ];

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'application/json',
            'Referer' => 'https://shinigami.to/',
            'Origin' => 'https://shinigami.to',
        ];

        try {
            $listEndpoint = "{$apiUrl}/v1/manga/list?type=project&page=1&page_size=1&is_update=true&sort=latest&sort_order=desc";
            
            $start = microtime(true);
            $response = Http::withHeaders($headers)->timeout(10)->get($listEndpoint);
            $time = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $logs[] = [
                    'step' => 'Mendapatkan List Manga',
                    'status' => 'success',
                    'details' => "Berhasil mengambil data dalam {$time}ms. HTTP Code: " . $response->status(),
                    'response_body' => $response->json()
                ];
            } else {
                $logs[] = [
                    'step' => 'Mendapatkan List Manga',
                    'status' => 'error',
                    'details' => "Gagal mengambil data. HTTP Code: " . $response->status(),
                    'response_body' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            $logs[] = [
                'step' => 'Koneksi API / DNS Resolve',
                'status' => 'error',
                'details' => "Gagal terhubung ke API. Terjadi Exception: " . get_class($e),
                'error_message' => $e->getMessage()
            ];
            
            return response()->json(['success' => false, 'logs' => $logs]);
        }

        return response()->json(['success' => true, 'logs' => $logs]);
    }
}
