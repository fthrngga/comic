import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import MainLayout from '@/Layouts/MainLayout';

export default function Index({ mangas, genres = [], filters = {} }) {
    const mangaList = Array.isArray(mangas) ? mangas : (mangas?.data || []);
    
    // State untuk menyimpan inputan user
    const [search, setSearch] = useState(filters.search || '');

    // Fungsi sakti untuk mengirim request ke backend tanpa loading layar putih
    const handleFilter = (selectedGenre = filters.genre) => {
        router.get('/', { search, genre: selectedGenre }, { 
            preserveState: true,
            replace: true 
        });
    };

    return (
        <MainLayout>
            <div className="mb-8">
                <div className="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold text-slate-100 tracking-tight">Library</h1>
                        <p className="text-sm text-slate-400 mt-1">Discover {mangaList.length} titles in our collection</p>
                    </div>

                    {/* SEARCH & FILTER BAR */}
                    <div className="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <div className="relative flex-1 md:w-64">
                            <input 
                                type="text" 
                                placeholder="Search comics..." 
                                className="w-full bg-slate-800/50 border border-slate-700 text-slate-200 rounded-lg pl-4 pr-10 py-2.5 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 outline-none transition-all placeholder:text-slate-500 text-sm"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleFilter()}
                            />
                            {/* Icon Kaca Pembesar */}
                            <button 
                                onClick={() => handleFilter()}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-violet-400 transition-colors"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>

                        <select 
                            onChange={(e) => handleFilter(e.target.value)} 
                            value={filters.genre || ''}
                            className="bg-slate-800/50 border border-slate-700 text-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 outline-none transition-all text-sm appearance-none sm:w-40 cursor-pointer"
                        >
                            <option value="">All Genres</option>
                            {genres.map(g => (
                                <option key={g.id} value={g.name} className="bg-slate-800 text-slate-200">
                                    {g.name}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
            </div>

            {/* DAFTAR KOMIK */}
            <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 md:gap-6">
                {mangaList.map((manga) => (
                    <Link
                        key={manga.id}
                        href={`/manga/${manga.slug}`}
                        className="bg-slate-800 rounded-lg overflow-hidden hover:ring-2 hover:ring-violet-500 transition-all flex flex-col group"
                    >
                        <div className="w-full aspect-[3/4] relative overflow-hidden bg-slate-700">
                            <img
                                src={manga.cover_url || '/placeholder-cover.jpg'}
                                alt={manga.title}
                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                loading="lazy"
                            />
                            {manga.status === 'ongoing' && (
                                <div className="absolute top-2 right-2 bg-emerald-500/90 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-1 rounded-sm uppercase tracking-wider shadow-lg">
                                    Ongoing
                                </div>
                            )}
                        </div>
                        <div className="p-3 flex-1 flex flex-col justify-between bg-gradient-to-b from-slate-800 to-slate-900/50">
                            <h2 className="text-slate-100 font-medium text-sm md:text-base line-clamp-2 leading-snug group-hover:text-violet-400 transition-colors" title={manga.title}>
                                {manga.title}
                            </h2>
                            <div className="mt-3 flex items-center justify-between text-xs text-slate-400 font-medium">
                                <span className="capitalize px-2 py-0.5 bg-slate-800 rounded-md border border-slate-700">{manga.type || 'Manga'}</span>
                            </div>
                        </div>
                    </Link>
                ))}
                
                {mangaList.length === 0 && (
                    <div className="col-span-full py-20 flex flex-col items-center justify-center text-center">
                        <div className="bg-slate-800/50 p-4 rounded-full mb-4">
                            <svg className="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 className="text-lg font-medium text-slate-300 mb-1">No Comics Found</h3>
                        <p className="text-sm text-slate-500">Try adjusting your search or genre filter.</p>
                        {/* Tombol Reset Filter */}
                        {(filters.search || filters.genre) && (
                            <button 
                                onClick={() => { setSearch(''); handleFilter(''); }}
                                className="mt-4 text-violet-400 hover:text-violet-300 text-sm font-medium transition-colors"
                            >
                                Clear all filters
                            </button>
                        )}
                    </div>
                )}
            </div>
        </MainLayout>
    );
}