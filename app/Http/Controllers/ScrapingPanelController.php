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
            'source' => 'required|string|in:komikcast,shinigami'
        ]);

        $page = $request->input('page');
        $limit = $request->input('limit', null);
        $source = $request->input('source');
        
        $commandName = $source === 'komikcast' ? 'comic:bulk-komikcast' : 'comic:scrape-shinigami';

        $params = ['--page' => $page];
        if ($limit) {
            $params['--limit'] = $limit;
        }

        $outputBuffer = new BufferedOutput();
        
        try {
            Artisan::call($commandName, $params, $outputBuffer);
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
