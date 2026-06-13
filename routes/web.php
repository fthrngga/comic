<?php

use App\Drivers\ShinigamiDriver;
use Illuminate\Support\Facades\Route;

Route::get('/smoke-test', function () {
    // KDV BYPASS: Kita tembak langsung Driver-nya tanpa basa-basi!
    $driver = new ShinigamiDriver();
    
    $mangaIdTarget = 'c0f1d049-ff7f-474d-8c6a-3a55e4c44147'; 
    $chapterIdTarget = 'f82f1ad6-ced3-47b8-97f0-7a36a1b5aa95'; 
    
    // Jika file Driver di VPS sudah ter-update, baris ini PASTI akan memicu layar error bawaan dd() KDV Trap.
    return $driver->getChapterImages($mangaIdTarget, $chapterIdTarget);
});

require __DIR__.'/auth.php';
