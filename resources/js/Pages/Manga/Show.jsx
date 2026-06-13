import { Link } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';

export default function Show({ manga }) {
    const chapters = manga?.chapters || [];

    return (
        <MainLayout>
            <div className="flex flex-col md:flex-row gap-6 md:gap-8 mb-10">
                <div className="w-full md:w-64 lg:w-72 shrink-0">
                    <div className="aspect-[3/4] w-full rounded-lg overflow-hidden shadow-xl shadow-black/50 bg-slate-800">
                        <img 
                            src={manga?.cover_url || '/placeholder-cover.jpg'} 
                            alt={manga?.title} 
                            className="w-full h-full object-cover"
                        />
                    </div>
                </div>

                <div className="flex-1 flex flex-col">
                    <div className="mb-4">
                        <h1 className="text-2xl md:text-4xl font-bold text-slate-100 tracking-tight mb-2">
                            {manga?.title}
                        </h1>
                        <div className="flex flex-wrap items-center gap-3 text-sm">
                            <span className="text-slate-300 font-medium">Author Name</span>
                            <span className="text-slate-600">•</span>
                            {manga?.status === 'ongoing' ? (
                                <span className="bg-emerald-400/10 text-emerald-400 px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide">
                                    Ongoing
                                </span>
                            ) : (
                                <span className="bg-violet-400/10 text-violet-400 px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide">
                                    {manga?.status || 'Completed'}
                                </span>
                            )}
                            <span className="text-slate-600">•</span>
                            <span className="text-slate-400 capitalize">{manga?.type || 'Manga'}</span>
                        </div>
                    </div>

                    <div className="prose prose-invert prose-sm max-w-none">
                        <h3 className="text-slate-200 font-semibold mb-2">Synopsis</h3>
                        <p className="text-slate-400 text-sm leading-relaxed">
                            {manga?.synopsis || 'No synopsis available for this title.'}
                        </p>
                    </div>

                    <div className="mt-6 flex gap-3">
                        {/* PERBAIKAN 1: Tombol Read First Chapter menggunakan Link dan Rute yang benar */}
                        {chapters.length > 0 ? (
                            <Link 
                                href={`/chapter/${chapters[0].id}`}
                                className="bg-violet-600 hover:bg-violet-500 text-white px-6 py-2.5 rounded-md font-medium transition-colors text-sm"
                            >
                                Read First Chapter
                            </Link>
                        ) : (
                            <button disabled className="bg-slate-700 text-slate-500 px-6 py-2.5 rounded-md font-medium text-sm cursor-not-allowed border border-slate-600">
                                No Chapters Yet
                            </button>
                        )}
                        <button className="bg-slate-800 hover:bg-slate-700 text-slate-200 px-6 py-2.5 rounded-md font-medium transition-colors text-sm border border-slate-700">
                            Bookmark
                        </button>
                    </div>
                </div>
            </div>

            <div className="max-w-4xl">
                <h3 className="text-xl font-bold text-slate-100 mb-4 flex items-center gap-2">
                    Chapters
                    <span className="text-sm font-normal text-slate-500 bg-slate-800 px-2 py-0.5 rounded-full">
                        {chapters.length}
                    </span>
                </h3>
                
                <div className="flex flex-col">
                    {chapters.length > 0 ? (
                        chapters.map((chapter) => (
                            // PERBAIKAN 2: Tautan daftar chapter menggunakan Rute yang benar (/chapter/{id})
                            <Link 
                                key={chapter.id} 
                                href={`/chapter/${chapter.id}`}
                                className="bg-slate-800/50 hover:bg-slate-800 p-4 rounded-md mb-2 flex justify-between items-center transition-colors border border-transparent hover:border-slate-700 group"
                            >
                                <div className="flex flex-col">
                                    <span className="text-slate-200 font-medium group-hover:text-violet-400 transition-colors">
                                        Chapter {chapter.chapter_number} {chapter.chapter_title && chapter.chapter_title !== `Chapter ${chapter.chapter_number}` ? `- ${chapter.chapter_title}` : ''}
                                    </span>
                                </div>
                                <div className="text-slate-500 text-xs font-medium">
                                    {chapter.created_at ? new Date(chapter.created_at).toLocaleDateString() : 'Recently'}
                                </div>
                            </Link>
                        ))
                    ) : (
                        <div className="bg-slate-800/30 border border-slate-800 rounded-md p-8 text-center text-slate-500">
                            No chapters available yet.
                        </div>
                    )}
                </div>
            </div>
        </MainLayout>
    );
}