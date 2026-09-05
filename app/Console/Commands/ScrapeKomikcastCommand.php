<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Manga;
use App\Models\Chapter;
use Illuminate\Support\Str;
use App\Drivers\KomikcastDriver;

class ScrapeKomikcastCommand extends Command
{
    protected $signature = 'comic:scrape-komikcast {slug}';
    protected $description = 'Scrape a specific manga from Komikcast by slug';

    public function handle()
    {
        $slug = $this->argument('slug');
        $this->info("Scraping Komikcast untuk manga: {$slug}...");

        $driver = new KomikcastDriver();
        $details = $driver->getMangaDetails($slug);

        if (empty($details)) {
            $this->error("Gagal mendapatkan detail komik. Pastikan slug benar (contoh: hiiragi-chan-to-tomikawa-chan).");
            return;
        }

        $manga = Manga::updateOrCreate(
            ['source_manga_id' => $slug, 'source_code' => 'komikcast'],
            [
                'title' => $details['title'],
                'slug' => Str::slug($details['title']),
                'synopsis' => $details['synopsis'], 
                'cover_url' => $details['cover_url'],
                'status' => 'ongoing', // default
                'type' => 'manga', 
            ]
        );

        $this->info("Manga berhasil disimpan: {$manga->title}");

        $chapters = $details['chapters'] ?? [];
        if (empty($chapters)) {
            $this->warn("Tidak ada chapter yang ditemukan untuk komik ini.");
            return;
        }

        $this->output->progressStart(count($chapters));

        foreach ($chapters as $chapterItem) {
            Chapter::updateOrCreate(
                [
                    'manga_id' => $manga->id,
                    'source_chapter_id' => $chapterItem['chapter_id'],
                ],
                [
                    'chapter_number' => (float) $chapterItem['chapter_number'],
                    'chapter_title' => "Chapter " . $chapterItem['chapter_number'],
                    'pages_data' => [] 
                ]
            );
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("Scraping selesai! {$manga->title} dengan " . count($chapters) . " chapter berhasil dimasukkan ke database.");
    }
}
