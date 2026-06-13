<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use App\Models\Chapter;
use App\Services\ComicAggregatorService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    protected ComicAggregatorService $comicService;

    // Inject Aggregator Service ke Controller
    public function __construct(ComicAggregatorService $comicService)
    {
        $this->comicService = $comicService;
    }

    // 1. Halaman Depan: List Semua Komik
    public function index()
    {
        $mangas = Manga::latest()->get();
        
        return Inertia::render('Manga/Index', [
            'mangas' => $mangas
        ]);
    }

    // 2. Halaman Detail Komik & List Chapter
    public function show($slug)
    {
        $manga = Manga::where('slug', $slug)->with(['chapters' => function($query) {
            $query->orderBy('chapter_number', 'asc');
        }])->firstOrFail();

        return Inertia::render('Manga/Show', [
            'manga' => $manga
        ]);
    }

    // 3. Halaman Reader (Tempat Baca Komik)
    public function read($chapterId)
    {
        $chapter = Chapter::with('manga')->findOrFail($chapterId);
        
        // Panggil engine universal kita untuk ambil/cache gambar dari CDN target
        $images = $this->comicService->fetchAndCacheChapterImages(
            $chapter->manga->source_manga_id, 
            $chapter->source_chapter_id
        );


        // Ambil navigasi prev/next chapter (opsional, bisa dikembangkan dari metadata API jika ada)
        $prevChapter = Chapter::where('manga_id', $chapter->manga_id)
            ->where('chapter_number', '<', $chapter->chapter_number)
            ->orderBy('chapter_number', 'desc')
            ->first();

        $nextChapter = Chapter::where('manga_id', $chapter->manga_id)
            ->where('chapter_number', '>', $chapter->chapter_number)
            ->orderBy('chapter_number', 'asc')
            ->first();

        return Inertia::render('Read/Show', [
            'manga' => $chapter->manga,
            'chapter' => $chapter,
            'images' => $images,
            'prevChapterUrl' => $prevChapter ? "/chapter/{$prevChapter->id}" : null,
            'nextChapterUrl' => $nextChapter ? "/chapter/{$nextChapter->id}" : null,
        ]);
    }
}