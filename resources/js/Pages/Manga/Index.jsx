import { Link } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';

export default function Index({ mangas }) {
    const mangaList = Array.isArray(mangas) ? mangas : (mangas?.data || []);

    return (
        <MainLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-slate-100">Latest Updates</h1>
                <p className="text-sm text-slate-400 mt-1">Discover new chapters and series</p>
            </div>

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
                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                            />
                            {manga.status === 'ongoing' && (
                                <div className="absolute top-2 right-2 bg-emerald-500/90 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-1 rounded-sm uppercase tracking-wider">
                                    Ongoing
                                </div>
                            )}
                        </div>
                        <div className="p-3 flex-1 flex flex-col justify-between">
                            <h2 className="text-slate-100 font-semibold text-sm md:text-base truncate" title={manga.title}>
                                {manga.title}
                            </h2>
                            <div className="mt-2 flex items-center justify-between text-xs text-slate-400">
                                <span className="capitalize">{manga.type || 'Manga'}</span>
                            </div>
                        </div>
                    </Link>
                ))}
                
                {mangaList.length === 0 && (
                    <div className="col-span-full py-12 text-center text-slate-500">
                        No manga found.
                    </div>
                )}
            </div>
        </MainLayout>
    );
}
