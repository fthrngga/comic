namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Manga;
use App\Models\Chapter;
use Illuminate\Support\Str;

class ScrapeShinigamiCommand extends Command
{
    protected $signature = 'comic:scrape-shinigami {--limit=10} {--page=1}';
    protected $description = 'Scrape latest mangas and chapters from Shinigami API';
    protected string $apiUrl = 'https://api.shngm.io';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $page = (int) $this->option('page');

        $this->info("Memulai proses scraping dari Shinigami API (Page: {$page}, Limit: {$limit} komik)...");

        // 1. Ambil List Manga (Hanya untuk mendapat ID dan Metadata Dasar)
        $listEndpoint = "{$this->apiUrl}/v1/manga/list?type=project&page={$page}&page_size={$limit}&is_update=true&sort=latest&sort_order=desc";
        
        $response = Http::withHeaders($this->getHeaders())->timeout(15)->get($listEndpoint);

        if ($response->failed()) {
            $this->error("Gagal mengambil daftar komik. HTTP Status: " . $response->status());
            return;
        }

        $data = $response->json();
        $mangasData = $data['data']['data'] ?? ($data['data'] ?? []);

        if (empty($mangasData)) {
            $this->warn("Tidak ada data komik yang ditemukan.");
            return;
        }

        $this->output->progressStart(count($mangasData));

        foreach ($mangasData as $mangaItem) {
            try {
                $sourceMangaId = $mangaItem['manga_id'] ?? null;
                $title = $mangaItem['title'] ?? 'Unknown Title';
                $coverUrl = $mangaItem['cover_portrait_url'] ?? ($mangaItem['cover_image_url'] ?? null);
                
                if (!$sourceMangaId) continue;

                // 2. Simpan Metadata Manga
                $manga = Manga::updateOrCreate(
                    ['source_manga_id' => $sourceMangaId],
                    [
                        'title' => $title,
                        'slug' => Str::slug($title) . '-' . substr($sourceMangaId, 0, 5),
                        'synopsis' => $mangaItem['description'] ?? null, 
                        'cover_url' => $coverUrl,
                        'status' => ($mangaItem['status'] ?? 0) == 1 ? 'ongoing' : 'completed', 
                        'type' => strtolower($mangaItem['taxonomy']['Format'][0]['name'] ?? 'manga'), 
                        'source_code' => 'shinigami'
                    ]
                );

                // 3. SEDOT FULL CHAPTERS (Misteri Terpecahkan!)
                // Menggunakan endpoint detail pagination yang baru kita temukan
                $this->scrapeFullChapters($manga->id, $sourceMangaId);

            } catch (\Exception $e) {
                Log::error("Error processing manga ID {$sourceMangaId}: " . $e->getMessage());
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("Scraping selesai! Data berhasil dimasukkan ke database.");
    }

    /**
     * Fungsi khusus untuk menyedot RATUSAN chapter menggunakan pagination
     */
    private function scrapeFullChapters($mangaLocalId, $sourceMangaId)
    {
        $chapterPage = 1;
        $chapterPageSize = 100; // Tarik 100 chapter sekaligus per halaman biar cepat
        $hasMoreChapters = true;

        while ($hasMoreChapters) {
            // Endpoint sakti yang ditemukan Bos Fathur: list?page=X&page_size=Y (dengan Referer spesifik)
            $chapterEndpoint = "{$this->apiUrl}/v1/manga/{$sourceMangaId}/chapter/list?page={$chapterPage}&page_size={$chapterPageSize}&sort_by=chapter_number&sort_order=desc";
            
            // Shinigami sangat ketat di endpoint ini, Referer wajib mengarah ke halaman komik tersebut
            $customHeaders = array_merge($this->getHeaders(), [
                'Referer' => "https://shinigami.to/series/{$sourceMangaId}"
            ]);

            $response = Http::withHeaders($customHeaders)->timeout(15)->get($chapterEndpoint);

            if ($response->successful()) {
                $chapterData = $response->json();
                $chaptersArray = $chapterData['data']['data'] ?? ($chapterData['data'] ?? []);

                if (empty($chaptersArray)) {
                    $hasMoreChapters = false; // Stop loop jika sudah habis
                    break;
                }

                foreach ($chaptersArray as $chapterItem) {
                    $sourceChapterId = $chapterItem['chapter_id'] ?? ($chapterItem['id'] ?? null);
                    if (!$sourceChapterId) continue;

                    Chapter::updateOrCreate(
                        [
                            'manga_id' => $mangaLocalId,
                            'source_chapter_id' => $sourceChapterId,
                        ],
                        [
                            'chapter_number' => (float) ($chapterItem['chapter_number'] ?? 0),
                            'chapter_title' => $chapterItem['title'] ?? ("Chapter " . ($chapterItem['chapter_number'] ?? 0)),
                            'pages_data' => null
                        ]
                    );
                }

                // Cek apakah masih ada halaman chapter berikutnya
                $totalChapters = $chapterData['data']['total'] ?? 0;
                $fetchedSoFar = $chapterPage * $chapterPageSize;
                
                if ($fetchedSoFar >= $totalChapters) {
                    $hasMoreChapters = false;
                } else {
                    $chapterPage++;
                    sleep(1); // Jeda sopan antar halaman chapter
                }
            } else {
                Log::warning("Failed to fetch chapters for manga {$sourceMangaId} on page {$chapterPage}");
                $hasMoreChapters = false; // Stop loop jika error agar tidak infinite loop
            }
        }
    }

    private function getHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'application/json',
            'Referer' => 'https://shinigami.to/',
            'Origin' => 'https://shinigami.to',
        ];
    }
}