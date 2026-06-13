import { Link } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function Show({ manga, chapter, images, prevChapterUrl, nextChapterUrl }) {
    const [loading, setLoading] = useState(true);

    // Hilangkan loading screen setelah beberapa saat
    useEffect(() => {
        const timer = setTimeout(() => setLoading(false), 500);
        return () => clearTimeout(timer);
    }, []);

    // Fungsi sakti untuk merutekan gambar melewati Proxy Anti-CORS kita
    const getProxyUrl = (rawUrl) => {
        return `/api/proxy/image?url=${encodeURIComponent(rawUrl)}`;
    };

    return (
        <div className="min-h-screen bg-[#0a0a0a] text-slate-200 font-sans selection:bg-violet-500/30">
            {/* Top Navigation Bar */}
            <div className="sticky top-0 z-50 bg-[#0f0f11]/90 backdrop-blur-md border-b border-white/5 px-4 py-3 shadow-2xl">
                <div className="max-w-4xl mx-auto flex items-center justify-between">
                    <Link 
                        href={`/manga/${manga.slug}`}
                        className="flex items-center gap-2 text-slate-400 hover:text-white transition-colors"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span className="hidden sm:inline font-medium text-sm">Back to Manga</span>
                    </Link>

                    <div className="text-center flex-1 px-4 truncate">
                        <h1 className="text-white font-bold truncate text-sm md:text-base">{manga.title}</h1>
                        <p className="text-violet-400 text-xs font-medium mt-0.5">
                            Chapter {chapter.chapter_number}
                        </p>
                    </div>

                    <div className="w-[88px]"></div> {/* Spacer untuk keseimbangan flex */}
                </div>
            </div>

            {/* Area Baca Komik */}
            <div className="w-full bg-[#0a0a0a]">
                <div className="max-w-[720px] mx-auto min-h-screen flex flex-col relative">
                    
                    {loading && (
                        <div className="absolute inset-0 flex items-center justify-center bg-[#0a0a0a] z-10">
                            <div className="animate-pulse flex flex-col items-center">
                                <div className="w-12 h-12 border-4 border-violet-500/30 border-t-violet-500 rounded-full animate-spin"></div>
                                <p className="mt-4 text-slate-500 font-medium text-sm tracking-widest uppercase">Connecting to Proxy...</p>
                            </div>
                        </div>
                    )}

                    {images && images.length > 0 ? (
                        images.map((imgUrl, index) => (
                            <img 
                                key={index}
                                src={getProxyUrl(imgUrl)} 
                                alt={`Page ${index + 1}`}
                                loading="lazy"
                                className="w-full h-auto object-contain block m-0 p-0"
                                onError={(e) => {
                                    // Fallback jika proxy gagal meload satu gambar
                                    e.target.onerror = null; 
                                    e.target.style.display = 'none';
                                }}
                            />
                        ))
                    ) : (
                        <div className="flex flex-col items-center justify-center py-32 px-4 text-center">
                            <div className="bg-red-500/10 text-red-500 p-4 rounded-full mb-4">
                                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-bold text-white mb-2">Gagal Memuat Gambar</h3>
                            <p className="text-slate-400 max-w-md">
                                Server sumber mungkin memblokir akses atau gambar telah dihapus. Silakan coba muat ulang halaman.
                            </p>
                        </div>
                    )}
                </div>
            </div>

            {/* Bottom Navigation */}
            <div className="bg-[#0f0f11] border-t border-white/5 py-6 px-4">
                <div className="max-w-2xl mx-auto flex items-center justify-between gap-4">
                    {prevChapterUrl ? (
                        <Link 
                            href={prevChapterUrl}
                            className="flex-1 bg-slate-800 hover:bg-slate-700 text-white text-center py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7"/></svg>
                            Prev Chapter
                        </Link>
                    ) : (
                        <div className="flex-1 bg-slate-900 text-slate-700 text-center py-3 rounded-lg font-medium cursor-not-allowed border border-slate-800/50">
                            First Chapter
                        </div>
                    )}

                    {nextChapterUrl ? (
                        <Link 
                            href={nextChapterUrl}
                            className="flex-1 bg-violet-600 hover:bg-violet-500 text-white text-center py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2 shadow-lg shadow-violet-900/20"
                        >
                            Next Chapter
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"/></svg>
                        </Link>
                    ) : (
                        <div className="flex-1 bg-slate-900 text-slate-700 text-center py-3 rounded-lg font-medium cursor-not-allowed border border-slate-800/50">
                            Latest Chapter
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}