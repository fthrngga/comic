<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use App\Models\Chapter;
use App\Models\Genre; // Tambahkan import Model Genre
use App\Services\ComicAggregatorService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    protected ComicAggregatorService $comicService;

    public function __construct(ComicAggregatorService $comicService)
    {
        $this->comicService = $comicService;
    }

    // 1. Halaman Depan: List Semua Komik dengan Search & Filter
    public function index(Request $request)
    {
        $mangas = Manga::query()
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->when($request->genre, function ($query, $genre) {
                $query->whereHas('genres', function ($q) use ($genre) {
                    $q->where('name', $genre);
                });
            })
            ->latest()
            ->get();

        $genres = Genre::orderBy('name', 'asc')->get(); // Ambil semua genre urut abjad

        return Inertia::render('Manga/Index', [
            'mangas' => $mangas,
            'genres' => $genres, // Lempar ke React
            'filters' => $request->only(['search', 'genre']) // Simpan state pencarian
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
        
        $images = $this->comicService->fetchAndCacheChapterImages(
            $chapter->manga->source_manga_id, 
            $chapter->source_chapter_id
        );

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