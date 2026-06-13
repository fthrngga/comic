<?php

use App\Services\ComicAggregatorService;
use App\Models\Manga;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

Route::get('/smoke-test', function (ComicAggregatorService $service) {
    
    // Wajib: Hapus memori kosong sebelumnya
    Cache::flush(); 

    $mangaIdTarget = 'c0f1d049-ff7f-474d-8c6a-3a55e4c44147'; 
    $chapterIdTarget = 'f82f1ad6-ced3-47b8-97f0-7a36a1b5aa95'; 
    
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

    $images = $service->fetchAndCacheChapterImages($mangaIdTarget, $chapterIdTarget);
    
    // Tampilkan hasil final
    dd([
        'Status' => count($images) > 0 ? 'MESIN BEKERJA SEMPURNA!' : 'GAGAL MENGAMBIL GAMBAR',
        'Total Gambar Ditarik' => count($images) . ' lembar',
        'Array Gambar' => $images
    ]);
});