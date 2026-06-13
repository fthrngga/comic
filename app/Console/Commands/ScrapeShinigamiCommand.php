<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Manga;
use App\Models\Chapter;
use Illuminate\Support\Str;

class ScrapeShinigamiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comic:scrape-shinigami {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape latest mangas and chapters from Shinigami API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $this->info("Memulai proses scraping dari Shinigami API (Limit: {$limit} komik)...");

        // Konfigurasi endpoint
        // Anda bisa mengganti URL ini sesuai dengan endpoint asli yang tersedia
        $listUrl = 'https://api.shngm.io/v1/manga/latest?page=1'; 
        
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept'     => 'application/json',
            'Referer'    => 'https://shinigami.to/',
            'Origin'     => 'https://shinigami.to',
        ];

        try {
            $response = Http::withHeaders($headers)->timeout(15)->get($listUrl);

            if ($response->failed()) {
                $this->error('Gagal mengambil daftar komik dari Shinigami API. Cek log untuk detail.');
                Log::error('ScrapeShinigamiCommand Failed: List API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return Command::FAILURE;
            }

            $data = $response->json();
            
            // Asumsi response array komik berada di $data['data']
            // Sesuaikan key array ini jika struktur response dari API berbeda
            $mangas = $data['data'] ?? [];
            
            if (empty($mangas)) {
                $this->warn('Tidak ada data komik yang ditemukan.');
                return Command::SUCCESS;
            }

            // Batasi jumlah yang discrape sesuai argumen
            $mangasToScrape = array_slice($mangas, 0, $limit);
            
            $progressBar = $this->output->createProgressBar(count($mangasToScrape));
            $progressBar->start();

            foreach ($mangasToScrape as $mangaData) {
                // Ekstrak ID asli dari API
                $sourceMangaId = $mangaData['id'] ?? null;
                
                if (!$sourceMangaId) {
                    $progressBar->advance();
                    continue;
                }

                // 1. SIMPAN MANGA
                $manga = Manga::updateOrCreate(
                    ['source_manga_id' => $sourceMangaId],
                    [
                        'title'       => $mangaData['title'] ?? 'Unknown Title',
                        'slug'        => Str::slug($mangaData['title'] ?? 'Unknown Title') . '-' . Str::random(5),
                        'cover_url'   => $mangaData['cover_url'] ?? '',
                        'status'      => strtolower($mangaData['status'] ?? 'ongoing'),
                        'type'        => strtolower($mangaData['type'] ?? 'manga'),
                        'source_code' => 'shinigami',
                        'synopsis'    => $mangaData['synopsis'] ?? null,
                    ]
                );

                // 2. AMBIL DAFTAR CHAPTER DARI ENDPOINT DETAIL
                // Ganti URL sesuai endpoint aslinya
                $detailUrl = "https://api.shngm.io/v1/manga/detail/{$sourceMangaId}";
                
                $detailResponse = Http::withHeaders($headers)->timeout(15)->get($detailUrl);

                if ($detailResponse->successful()) {
                    $detailData = $detailResponse->json();
                    
                    // Asumsi daftar chapter berada di $detailData['data']['chapters']
                    $chapters = $detailData['data']['chapters'] ?? [];

                    foreach ($chapters as $chapterData) {
                        $sourceChapterId = $chapterData['id'] ?? null;
                        
                        if (!$sourceChapterId) continue;

                        // 3. SIMPAN CHAPTER
                        Chapter::updateOrCreate(
                            [
                                'manga_id' => $manga->id,
                                'source_chapter_id' => $sourceChapterId,
                            ],
                            [
                                'chapter_number' => $chapterData['chapter_number'] ?? 0,
                                'chapter_title'  => $chapterData['chapter_title'] ?? null,
                                // Kosongkan pages_data untuk On-Demand Fetching (Fase Aggregator)
                                'pages_data'     => [], 
                            ]
                        );
                    }
                } else {
                    Log::warning("Gagal menarik chapter untuk manga ID: {$sourceMangaId}");
                }

                $progressBar->advance();
                
                // 4. ETIKA SCRAPING (ANTI-BAN)
                // Beri jeda 1 sampai 3 detik per iterasi
                sleep(rand(1, 3));
            }

            $progressBar->finish();
            $this->newLine(2);
            $this->info("Berhasil melakukan scraping dan sinkronisasi komik!");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('Terjadi kesalahan yang tidak terduga: ' . $e->getMessage());
            Log::error('ScrapeShinigamiCommand Exception: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
