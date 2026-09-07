<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Manga;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BulkScrapeMangaDexCommand extends Command
{
    protected $signature = 'scrape:mangadex:bulk {--limit=10} {--offset=0}';
    protected $description = 'Bulk scrape English mangas from MangaDex';

    public function handle()
    {
        $this->info("=== Memulai Misi Scraping MangaDex ===");
        $limit = $this->option('limit');
        $offset = $this->option('offset');

        $url = "https://api.mangadex.org/manga?limit={$limit}&offset={$offset}&translatedLanguage[]=en&includes[]=cover_art&order[followedCount]=desc";
        
        $this->info(">> Mengambil data dari MangaDex API...");
        
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        
        $responseJson = @file_get_contents($url, false, $context);

        if (!$responseJson) {
            $this->error("Gagal mengambil data dari MangaDex (SSL atau timeout).");
            return Command::FAILURE;
        }

        $data = json_decode($responseJson, true)['data'] ?? null;

        if (empty($data)) {
            $this->error("Tidak ada daftar komik ditemukan.");
            return Command::FAILURE;
        }

        foreach ($data as $item) {
            $slug = $item['id']; // UUID
            $title = $item['attributes']['title']['en'] ?? current($item['attributes']['title']);
            if (!$title) continue;
            
            $synopsis = $item['attributes']['description']['en'] ?? current($item['attributes']['description']);
            if (is_array($synopsis)) $synopsis = '';
            
            $status = $item['attributes']['status'] ?? 'ongoing';
            $type = $item['attributes']['publicationDemographic'] ?? 'manga'; // fallback to demographic if type not strictly defined, or just use 'manga'

            $coverUrl = '';
            foreach ($item['relationships'] as $rel) {
                if ($rel['type'] === 'cover_art' && isset($rel['attributes']['fileName'])) {
                    $coverUrl = "https://uploads.mangadex.org/covers/{$slug}/" . $rel['attributes']['fileName'];
                    break;
                }
            }

            $this->info("Memproses: $title");

            $manga = Manga::updateOrCreate(
                ['source_manga_id' => $slug, 'source_code' => 'mangadex'],
                [
                    'title' => $title,
                    'slug' => Str::slug($title) . '-' . substr($slug, 0, 5), // Ensure unique slug
                    'synopsis' => $synopsis ?: 'No synopsis available.',
                    'cover_url' => $coverUrl,
                    'status' => strtolower($status),
                    'type' => strtolower($type),
                    'language' => 'en',
                ]
            );

            $this->line("Disimpan: " . $manga->title);
            sleep(1); // API Rate limit friendly
        }

        $this->info("=== Misi MangaDex Selesai ===");
        return Command::SUCCESS;
    }
}
