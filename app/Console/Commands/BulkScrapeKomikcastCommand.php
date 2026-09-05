<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use App\Models\Manga;

class BulkScrapeKomikcastCommand extends Command
{
    protected $signature = 'comic:bulk-komikcast {--page=1 : Halaman explore yang akan di-scrape} {--limit=20 : Batas maksimal komik yang diambil}';
    protected $description = 'Melakukan bulk scraping komik dari halaman Explore Komikcast';

    public function handle()
    {
        $page = $this->option('page');
        $limit = $this->option('limit');
        $url = "https://v1.komikcast.ac/explore?page={$page}";

        $this->info("Mengakses halaman Explore: {$url}");

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])->timeout(15)->get($url);

            if ($response->failed()) {
                $this->error("Gagal membuka halaman Explore (HTTP " . $response->status() . ").");
                return;
            }

            $html = $response->body();

            // Ekstrak semua link komik dengan pola /manga/slug
            preg_match_all('/href="\/manga\/([^\/"]+)"/', $html, $matches);
            
            if (empty($matches[1])) {
                $this->warn("Tidak ada slug komik yang ditemukan pada halaman {$page}.");
                return;
            }

            // Hapus duplikasi URL pada halaman yang sama
            $slugs = array_values(array_unique($matches[1]));
            
            // Terapkan batasan limit
            $slugs = array_slice($slugs, 0, $limit);
            
            $total = count($slugs);
            $this->info("Berhasil menemukan {$total} komik unik di halaman {$page}. Memulai ekstraksi...");

            foreach ($slugs as $index => $slug) {
                $num = $index + 1;
                $this->line("\n[{$num}/{$total}] Memproses: {$slug}");

                // Cek apakah komik sudah ada di database
                $exists = Manga::where('source_manga_id', $slug)->where('source_code', 'komikcast')->exists();
                if ($exists) {
                    $this->info("  -> Komik sudah ada di database. Memeriksa update chapter terbaru...");
                } else {
                    $this->info("  -> Komik baru! Mengunduh data lengkap...");
                }

                // Panggil command scraper tunggal
                Artisan::call('comic:scrape-komikcast', ['slug' => $slug]);
                
                // Ambil dan tampilkan output dari command scraper tunggal
                $output = Artisan::output();
                $this->line(trim($output));

                if ($num < $total) {
                    $delay = rand(1, 3); // Jeda acak 1 - 3 detik
                    $this->line("  -> Menunggu {$delay} detik sebelum komik berikutnya (Anti-Blokir)...");
                    sleep($delay);
                }
            }

            $this->info("\n✅ Bulk scraping halaman {$page} selesai!");

        } catch (\Exception $e) {
            $this->error("Terjadi kesalahan: " . $e->getMessage());
        }
    }
}
