<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Manga;
use App\Models\Chapter;
use Illuminate\Support\Str;
use App\Drivers\MgreadDriver;
use App\Services\ComicAggregatorService;

class BulkScrapeMgreadCommand extends Command
{
    protected $signature = 'scrape:mgread:bulk {--pages=1 : Jumlah halaman homepage yang akan discrape}';
    protected $description = 'Melakukan bulk scraping komik bahasa Inggris dari Mgread.io';

    protected $driver;
    protected $service;

    public function __construct(MgreadDriver $driver, ComicAggregatorService $service)
    {
        parent::__construct();
        $this->driver = $driver;
        $this->service = $service;
    }

    public function handle()
    {
        $pages = (int) $this->option('pages');
        $this->info("Memulai bulk scrape dari Mgread.io sebanyak $pages halaman...");

        for ($p = 1; $p <= $pages; $p++) {
            $this->info("Mengekstrak halaman $p...");
            
            // Endpoint untuk genre/semua manga
            $url = $p === 1 ? "https://mgread.io/genre/action/" : "https://mgread.io/genre/action/page/$p/";
            
            $html = shell_exec("curl -s -L \"$url\"");

            if (!$html || trim($html) === '') {
                $this->error("Gagal mengambil data dari Mgread.io pada halaman $p");
                continue;
            }

            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);

            // Ekstrak semua link yang menuju ke /manga/
            $mangaNodes = $xpath->query("//a[contains(@href, '/manga/')]");
            
            $mangaLinks = [];
            foreach ($mangaNodes as $node) {
                $href = $node->getAttribute('href');
                if ($href && strpos($href, '/manga/') !== false && strpos($href, '/chapter-') === false && !in_array($href, $mangaLinks)) {
                    $mangaLinks[] = $href;
                }
            }

            if (empty($mangaLinks)) {
                $this->warn("Tidak ada komik ditemukan di halaman $p.");
                continue;
            }

            $this->info("Menemukan " . count($mangaLinks) . " komik di halaman $p.");

            foreach ($mangaLinks as $mangaUrl) {
                try {
                    $this->info("Memproses komik: $mangaUrl");
                    
                    // Ambil detail
                    $details = $this->driver->getMangaDetails($mangaUrl);
                    
                    // Simpan komik (Cek duplikasi berdasarkan judul dan language)
                    $manga = Manga::firstOrCreate(
                        ['title' => $details['title'], 'language' => $details['language']],
                        [
                            'slug' => Str::slug($details['title']),
                            'synopsis' => $details['synopsis'],
                            'type' => $details['type'],
                            'status' => $details['status'],
                            'cover_url' => $details['cover_url'],
                            'source_code' => 'mgread',
                            'source_manga_id' => Str::slug($details['title'])
                        ]
                    );

                    $this->info("Berhasil memproses manga: {$manga->title}");

                    // Cek jika chapter kosong
                    if (empty($details['chapters'])) {
                        continue;
                    }

                    // Ambil maksimal 5 chapter terbaru untuk optimasi scrape pertama
                    $recentChapters = array_slice($details['chapters'], -5);

                    foreach ($recentChapters as $chapterData) {
                        $chapterExists = Chapter::where('manga_id', $manga->id)
                            ->where('chapter_number', $chapterData['chapter_number'])
                            ->exists();

                        if (!$chapterExists) {
                            $this->line("Mengambil gambar untuk Chapter {$chapterData['chapter_number']}...");
                            
                            try {
                                $images = $this->driver->getChapterImages($chapterData['url'], '');
                                
                                Chapter::create([
                                    'manga_id' => $manga->id,
                                    'chapter_number' => $chapterData['chapter_number'],
                                    'chapter_title' => $chapterData['title'],
                                    'pages_data' => $images,
                                    'source_chapter_id' => $chapterData['url']
                                ]);
                                
                                $this->info("✓ Chapter {$chapterData['chapter_number']} tersimpan.");
                            } catch (\Exception $e) {
                                $this->error("Gagal mengambil gambar chapter {$chapterData['chapter_number']}: " . $e->getMessage());
                            }
                            
                            // Delay random agar tidak diblokir
                            sleep(rand(1, 2));
                        } else {
                            $this->line("- Chapter {$chapterData['chapter_number']} sudah ada. Melewati...");
                        }
                    }

                } catch (\Exception $e) {
                    $this->error("Gagal memproses $mangaUrl: " . $e->getMessage());
                }
                
                sleep(rand(1, 2));
            }
        }

        $this->info("Bulk scraping selesai!");
    }
}
