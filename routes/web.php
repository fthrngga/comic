<?php

use App\Drivers\ShinigamiDriver;
use Illuminate\Support\Facades\Route;

// Rute bawaan Laravel/Breeze biarkan saja di atas...

Route::get('/smoke-test', function () {
    // ========================================================
    // KDV NUCLEAR BYPASS: Tembak Driver Langsung Tanpa Cache!
    // ========================================================
    $driver = new ShinigamiDriver();
    
    $mangaIdTarget = 'c0f1d049-ff7f-474d-8c6a-3a55e4c44147'; // ID Kaisar Demonic
    $chapterIdTarget = 'f82f1ad6-ced3-47b8-97f0-7a36a1b5aa95'; // ID Chapter 868
    
    // Baris ini PASTI akan memicu ledakan KDV Trap di dalam Driver
    // Jika tidak meledak, berarti Driver-nya masih versi lama!
    return $driver->getChapterImages($mangaIdTarget, $chapterIdTarget);
});