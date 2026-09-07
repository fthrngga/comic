<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class ScrapingPanelController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/ScrapingPanel');
    }

    public function runSingle(Request $request)
    {
        set_time_limit(0); // Prevent PHP timeout

        $request->validate([
            'slug' => 'required|string',
        ]);

        $slug = $request->input('slug');
        $commandName = 'comic:scrape-komikcast'; // Single scrape hanya Komikcast untuk saat ini

        $outputBuffer = new BufferedOutput();
        
        try {
            Artisan::call($commandName, ['slug' => $slug], $outputBuffer);
            $log = $outputBuffer->fetch();
            return response()->json([
                'success' => true,
                'output' => $log,
                'message' => 'Scraping berhasil diselesaikan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'output' => $outputBuffer->fetch() . "\nError: " . $e->getMessage(),
                'message' => 'Terjadi kesalahan saat scraping.'
            ], 500);
        }
    }

    public function runBulk(Request $request)
    {
        set_time_limit(0); // Prevent PHP timeout

        $request->validate([
            'page' => 'required|integer|min:1',
            'limit' => 'nullable|integer|min:1',
            'source' => 'required|string|in:komikcast,shinigami,mangadex,globalcomix,mgread'
        ]);

        $page = $request->input('page');
        $limit = $request->input('limit', null);
        $source = $request->input('source');

        $params = [];
        if ($limit) {
            $params['--limit'] = $limit;
        }

        $outputBuffer = new BufferedOutput();
        
        if ($source === 'komikcast') {
            $command = 'scrape:komikcast:bulk';
            $params['--pages'] = $page;
        } elseif ($source === 'shinigami') {
            $command = 'scrape:shinigami:bulk';
            $params['--pages'] = $page;
        } elseif ($source === 'globalcomix') {
            $command = 'scrape:globalcomix:bulk';
            $params['--pages'] = $page;
        } elseif ($source === 'mgread') {
            $command = 'scrape:mgread:bulk';
            $params['--pages'] = $page;
        } elseif ($source === 'mangadex') {
            $command = 'scrape:mangadex:bulk';
            $params['--limit'] = 10;
        } else {
            return response()->json(['error' => 'Invalid source selected for bulk scrape.'], 400);
        }

        try {
            Artisan::call($command, $params, $outputBuffer);
            $log = $outputBuffer->fetch();
            return response()->json([
                'success' => true,
                'output' => $log,
                'message' => 'Bulk Scraping berhasil diselesaikan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'output' => $outputBuffer->fetch() . "\nError: " . $e->getMessage(),
                'message' => 'Terjadi kesalahan saat bulk scraping.'
            ], 500);
        }
    }
}
