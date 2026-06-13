<?php
use App\Services\ComicAggregatorService;
use App\Models\Manga;
use Illuminate\Support\Facades\Route;

Route::get('/smoke-test', function (ComicAggregatorService $service) {
    // 1. Eksekusi Adapter untuk mengambil 1 Chapter spesifik dari Shinigami
    // (Ini menggunakan UUID yang kita intip di awal diskusi)
    $mangaIdTarget = 'c0f1d049-ff7f-474d-8c6a-3a55e4c44147'; // ID Kaisar Demonic
    $chapterIdTarget = 'f82f1ad6-ced3-47b8-97f0-7a36a1b5aa95'; // ID Chapter 868
    
    // Simulasikan pembuatan Manga jika belum ada di database lokal
    $manga = Manga::firstOrCreate(
        ['source_manga_id' => $mangaIdTarget],
        [
            'title' => 'Kaisar Demonic (Test)',
            'slug' => 'kaisar-demonic-test',
            'cover_url' => 'https://assets.shngm.id/thumbnail/image/banner_1781309537826_df036w.jpg',
            'status' => 'ongoing',
            'type' => 'manhwa',
            'source_code' => 'shinigami'
        ]
    );

    // 2. Gunakan Mesin Fase 3 untuk menarik URL Gambar Chapter
    $images = $service->fetchAndCacheChapterImages($mangaIdTarget, $chapterIdTarget);
    
    // Tampilkan hasilnya langsung di layar (Dump & Die)
    dd([
        'Status' => 'MESIN BEKERJA SEMPURNA!',
        'Data Manga' => $manga->toArray(),
        'Total Gambar Ditarik' => count($images) . ' lembar',
        'Array Gambar' => $images
    ]);
});

require __DIR__.'/auth.php';
