import { Head, Link } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function Show({ manga, chapter, images, prevChapterUrl, nextChapterUrl }) {
    const [headerVisible, setHeaderVisible] = useState(true);
    const [lastScrollY, setLastScrollY] = useState(0);

    // Auto-hide header on scroll down for distraction-free reading
    useEffect(() => {
        const handleScroll = () => {
            const currentScrollY = window.scrollY;
            if (currentScrollY > lastScrollY && currentScrollY > 100) {
                setHeaderVisible(false);
            } else if (currentScrollY < lastScrollY) {
                setHeaderVisible(true);
            }
            setLastScrollY(currentScrollY);
        };

        window.addEventListener('scroll', handleScroll, { passive: true });
        return () => window.removeEventListener('scroll', handleScroll);
    }, [lastScrollY]);

    return (
        <div className="min-h-screen bg-[#0F172A] text-slate-200">
            <Head title={`Chapter ${Number(chapter.chapter_number)} - ${manga.title}`} />
            
            {/* Top Navigation Bar */}
            <div className={`fixed top-0 left-0 right-0 z-50 bg-[#1E293B]/95 backdrop-blur-sm border-b border-slate-700/50 transition-transform duration-300 ${headerVisible ? 'translate-y-0' : '-translate-y-full'}`}>
                <div className="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
                    <Link 
                        href={`/manga/${manga.slug}`}
                        className="flex items-center gap-2 text-slate-300 hover:text-white transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fillRule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clipRule="evenodd" />
                        </svg>
                        <span className="font-medium text-sm hidden sm:inline truncate max-w-[200px] md:max-w-xs">{manga.title}</span>
                    </Link>
                    
                    <div className="text-center font-bold text-sm tracking-wide flex-1 mx-4 truncate">
                        Chapter {Number(chapter.chapter_number)}
                    </div>

                    <div className="w-6 hidden sm:block"></div>
                </div>
            </div>

            {/* Reader Container */}
            <div className="w-full max-w-3xl mx-auto flex flex-col pt-14 pb-20 sm:pb-24">
                {images && images.length > 0 ? (
                    images.map((imgUrl, index) => (
                        <div key={index} className="w-full bg-[#1E293B] min-h-[300px] sm:min-h-[500px] flex items-center justify-center animate-pulse">
                            <img 
                                src={`/api/proxy/image?url=${encodeURIComponent(imgUrl)}`} 
                                alt={`Page ${index + 1}`} 
                                loading={index < 3 ? "eager" : "lazy"}
                                className="w-full h-auto object-contain block mx-auto opacity-0 transition-opacity duration-300"
                                onLoad={(e) => {
                                    e.target.parentElement.classList.remove('animate-pulse');
                                    e.target.classList.remove('opacity-0');
                                }}
                                onError={(e) => {
                                    e.target.parentElement.classList.remove('animate-pulse');
                                    e.target.style.display = 'none';
                                }}
                            />
                        </div>
                    ))
                ) : (
                    <div className="py-20 px-4 text-center">
                        <div className="text-red-400 font-medium mb-2">Belum ada gambar yang termuat.</div>
                        <p className="text-slate-500 text-sm">Kemungkinan gambar tidak ditemukan dari sumber atau proses scraping tertunda.</p>
                    </div>
                )}
            </div>

            {/* Bottom Navigation Overlay */}
            <div className="fixed bottom-0 left-0 right-0 z-50 bg-[#1E293B]/95 backdrop-blur-sm border-t border-slate-700/50">
                <div className="max-w-3xl mx-auto px-4 h-16 sm:h-20 flex items-center justify-between gap-4">
                    {prevChapterUrl ? (
                        <Link 
                            href={prevChapterUrl}
                            className="flex-1 max-w-[150px] h-10 sm:h-12 bg-slate-700/50 hover:bg-slate-700 rounded-md flex items-center justify-center gap-2 text-sm font-medium text-slate-200 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clipRule="evenodd" />
                            </svg>
                            Prev
                        </Link>
                    ) : (
                        <div className="flex-1 max-w-[150px] h-10 sm:h-12 bg-slate-800/50 rounded-md flex items-center justify-center gap-2 text-sm font-medium text-slate-600 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clipRule="evenodd" />
                            </svg>
                            Prev
                        </div>
                    )}
                    
                    <Link 
                        href={`/manga/${manga.slug}`}
                        className="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-violet-600/20 text-violet-400 flex items-center justify-center hover:bg-violet-600/30 hover:text-violet-300 transition-colors"
                        title="Daftar Chapter"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </Link>

                    {nextChapterUrl ? (
                        <Link 
                            href={nextChapterUrl}
                            className="flex-1 max-w-[150px] h-10 sm:h-12 bg-violet-600 hover:bg-violet-500 rounded-md flex items-center justify-center gap-2 text-sm font-medium text-white transition-colors shadow-lg shadow-violet-900/20"
                        >
                            Next
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                            </svg>
                        </Link>
                    ) : (
                        <div className="flex-1 max-w-[150px] h-10 sm:h-12 bg-slate-800/50 rounded-md flex items-center justify-center gap-2 text-sm font-medium text-slate-600 cursor-not-allowed">
                            Next
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                            </svg>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
