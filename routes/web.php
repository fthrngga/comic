<?php

use App\Http\Controllers\MangaController;
use App\Http\Controllers\ImageProxyController;
use Illuminate\Support\Facades\Route;

// Rute Aplikasi Komik Universal
Route::get('/', [MangaController::class, 'index'])->name('manga.index');
Route::get('/manga/{slug}', [MangaController::class, 'show'])->name('manga.show');
Route::get('/chapter/{chapterId}', [MangaController::class, 'read'])->name('manga.read');

// Rute Sakti Image Proxy (Anti-CORS) dari Fase 5
Route::get('/api/proxy/image', [ImageProxyController::class, 'stream'])->name('image.proxy');