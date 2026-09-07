import { Link, router } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';

export default function Index({ mangas, activeLanguage }) {
    const mangaList = Array.isArray(mangas) ? mangas : (mangas?.data || []);

    const changeLanguage = (lang) => {
        router.get('/', { lang }, { preserveState: true, replace: true });
    };

    return (
        <MainLayout>
            <div className="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-slate-100">Latest Updates</h1>
                    <p className="text-sm text-slate-400 mt-1">Discover new chapters and series</p>
                </div>
                
                {/* Language Tabs - Elegan & Ramah UX */}
                <div className="flex bg-slate-800/50 p-1.5 rounded-xl border border-white/5 backdrop-blur-sm self-start">
                    <button
                        onClick={() => changeLanguage('id')}
                        className={`relative flex items-center gap-2 px-5 py-2.5 rounded-lg font-semibold text-sm transition-all duration-300 ${
                            activeLanguage === 'id' 
                            ? 'bg-gradient-to-r from-brand-600 to-brand-500 text-white shadow-lg shadow-brand-500/20' 
                            : 'text-slate-400 hover:text-slate-200 hover:bg-white/5'
                        }`}
                    >
                        <span>🇮🇩</span>
                        Indonesia
                    </button>
                    <button
                        onClick={() => changeLanguage('en')}
                        className={`relative flex items-center gap-2 px-5 py-2.5 rounded-lg font-semibold text-sm transition-all duration-300 ${
                            activeLanguage === 'en' 
                            ? 'bg-gradient-to-r from-emerald-600 to-emerald-500 text-white shadow-lg shadow-emerald-500/20' 
                            : 'text-slate-400 hover:text-slate-200 hover:bg-white/5'
                        }`}
                    >
                        <span>🇬🇧</span>
                        English
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 md:gap-6">
                {mangaList.map((manga) => (
                    <Link
                        key={manga.id}
                        href={`/manga/${manga.slug}`}
                        className="bg-slate-800 rounded-lg overflow-hidden hover:ring-2 hover:ring-brand-500 transition-all flex flex-col group"
                    >
                        <div className="w-full aspect-[3/4] relative overflow-hidden bg-slate-700">
                            <img
                                src={manga.cover_url || '/images/default-cover.png'}
                                alt={manga.title}
                                onError={(e) => { e.target.onerror = null; e.target.src = '/images/default-cover.png'; }}
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
                                <span className="uppercase font-bold text-[10px] bg-slate-700 px-1.5 py-0.5 rounded text-slate-300">
                                    {manga.language}
                                </span>
                            </div>
                        </div>
                    </Link>
                ))}
                
                {mangaList.length === 0 && (
                    <div className="col-span-full py-16 flex flex-col items-center justify-center bg-slate-800/20 rounded-xl border border-dashed border-white/10">
                        <div className="text-4xl mb-3">🛸</div>
                        <h3 className="text-lg font-bold text-slate-200">Tidak ada data</h3>
                        <p className="text-slate-500 text-sm mt-1">Belum ada komik untuk kategori bahasa ini.</p>
                    </div>
                )}
            </div>
        </MainLayout>
    );
}
