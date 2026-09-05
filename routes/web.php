<?php

use App\Http\Controllers\MangaController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\DebugController;
use Illuminate\Support\Facades\Route;

// Rute Aplikasi Komik Universal
Route::get('/', [MangaController::class, 'index'])->name('manga.index');
Route::get('/manga/{slug}', [MangaController::class, 'show'])->name('manga.show');
Route::get('/chapter/{chapterId}', [MangaController::class, 'read'])->name('manga.read');

// Rute Sakti Image Proxy (Anti-CORS) dari Fase 5
Route::get('/api/proxy/image', [ImageProxyController::class, 'stream'])->name('image.proxy');

use App\Http\Controllers\ScrapingPanelController;

// Rute Halaman Debug Rahasia
Route::get('/proses', [DebugController::class, 'index'])->name('debug.index');
Route::post('/proses/test', [DebugController::class, 'testScrape'])->name('debug.test');

// Rute Control Center (Admin Scraping Panel)
Route::get('/scraping', [ScrapingPanelController::class, 'index'])->name('scraping.index');
Route::post('/scraping/run-single', [ScrapingPanelController::class, 'runSingle'])->name('scraping.runSingle');
Route::post('/scraping/run-bulk', [ScrapingPanelController::class, 'runBulk'])->name('scraping.runBulk');