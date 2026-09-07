import React, { useState, useRef, useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';

export default function ScrapingPanel() {
    const [output, setOutput] = useState("Sistem Scraping V1.0 diinisiasi...\nMenunggu perintah eksekusi...\n");
    const [isRunning, setIsRunning] = useState(false);
    const terminalRef = useRef(null);

    // Auto-scroll terminal to bottom
    useEffect(() => {
        if (terminalRef.current) {
            terminalRef.current.scrollTop = terminalRef.current.scrollHeight;
        }
    }, [output]);

    const { data: singleData, setData: setSingleData } = useForm({
        slug: '',
        source: 'komikcast'
    });

    const { data: bulkData, setData: setBulkData } = useForm({
        page: 1,
        limit: 10
    });

    const handleSingleScrape = async (e) => {
        e.preventDefault();
        if (isRunning) return;
        
        setIsRunning(true);
        setOutput(prev => prev + `\n> Menjalankan Single Scrape untuk [${singleData.slug}] di [${singleData.source}]...\n`);
        
        try {
            const response = await axios.post('/scraping/run-single', singleData);
            setOutput(prev => prev + response.data.output + "\n");
        } catch (error) {
            const errorOutput = error.response?.data?.output || error.message;
            setOutput(prev => prev + "[ERROR] " + errorOutput + "\n");
        } finally {
            setIsRunning(false);
        }
    };

    const handleBulkScrape = async (e) => {
        e.preventDefault();
        if (isRunning) return;
        
        setIsRunning(true);
        setOutput(prev => prev + `\n> Menjalankan Bulk Scrape pada halaman [${bulkData.page}] dengan limit [${bulkData.limit || 'ALL'}]...\n`);
        
        try {
            const response = await axios.post('/scraping/run-bulk', bulkData);
            setOutput(prev => prev + response.data.output + "\n");
        } catch (error) {
            const errorOutput = error.response?.data?.output || error.message;
            setOutput(prev => prev + "[ERROR] " + errorOutput + "\n");
        } finally {
            setIsRunning(false);
        }
    };

    return (
        <div className="min-h-screen bg-slate-950 p-4 md:p-8 font-sans">
            <Head title="Control Center | Scraping" />
            
            <div className="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                {/* Bagian Kiri: Panel Kontrol */}
                <div className="lg:col-span-4 space-y-6">
                    <div>
                        <h1 className="text-3xl font-black text-brand-400 mb-2">Control Center</h1>
                        <p className="text-sm text-slate-400">Panel admin rahasia untuk mengeksekusi script otomatis.</p>
                    </div>

                    {/* Form Single Scrape */}
                    <div className="bg-slate-900 rounded-xl border border-white/10 p-5">
                        <h2 className="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Single Target
                        </h2>
                        <form onSubmit={handleSingleScrape} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-400 mb-1">Slug Komik</label>
                                <input 
                                    type="text" 
                                    className="w-full bg-slate-950 border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-200 outline-none focus:border-brand-500"
                                    placeholder="contoh: hiiragi-chan-to-tomikawa-chan"
                                    value={singleData.slug}
                                    onChange={e => setSingleData('slug', e.target.value)}
                                    required
                                />
                            </div>
                            <button 
                                type="submit" 
                                disabled={isRunning}
                                className="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-lg text-sm transition disabled:opacity-50"
                            >
                                {isRunning ? 'MENGEKSEKUSI...' : 'EKSEKUSI SINGLE (KOMIKCAST)'}
                            </button>
                        </form>
                    </div>

                    {/* Form Bulk Scrape */}
                    <div className="bg-slate-900 rounded-xl border border-white/10 p-5">
                        <h2 className="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span className="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            Mass Destruction (Bulk)
                        </h2>
                        <form onSubmit={handleBulkScrape} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-400 mb-1">Sumber Target</label>
                                <select 
                                    className="w-full bg-slate-950 border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-200 outline-none focus:border-rose-500"
                                    value={bulkData.source}
                                    onChange={e => setBulkData('source', e.target.value)}
                                >
                                    <option value="komikcast">Komikcast</option>
                                    <option value="shinigami">Shinigami</option>
                                    <option value="mangadex">MangaDex (EN)</option>
                                    <option value="globalcomix">GlobalComix (EN)</option>
                                    <option value="mgread">Mgread.io (EN)</option>
                                </select>
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-400 mb-1">Halaman</label>
                                    <input 
                                        type="number" 
                                        min="1"
                                        className="w-full bg-slate-950 border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-200 outline-none focus:border-rose-500"
                                        value={bulkData.page}
                                        onChange={e => setBulkData('page', e.target.value)}
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-slate-400 mb-1">Limit Target</label>
                                    <input 
                                        type="number" 
                                        min="1"
                                        className="w-full bg-slate-950 border border-white/10 rounded-lg px-3 py-2 text-sm text-slate-200 outline-none focus:border-rose-500"
                                        value={bulkData.limit}
                                        onChange={e => setBulkData('limit', e.target.value)}
                                    />
                                </div>
                            </div>
                            <button 
                                type="submit" 
                                disabled={isRunning}
                                className="w-full bg-rose-600 hover:bg-rose-500 text-white font-bold py-2.5 rounded-lg text-sm transition disabled:opacity-50"
                            >
                                {isRunning ? 'MENGEKSEKUSI...' : 'EKSEKUSI MASSAL'}
                            </button>
                        </form>
                    </div>
                </div>

                {/* Bagian Kanan: Terminal Output */}
                <div className="lg:col-span-8 flex flex-col h-[85vh]">
                    <div className="bg-zinc-900 rounded-t-xl border border-white/10 border-b-0 px-4 py-2 flex items-center gap-2">
                        <div className="w-3 h-3 rounded-full bg-rose-500"></div>
                        <div className="w-3 h-3 rounded-full bg-amber-500"></div>
                        <div className="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <span className="ml-4 text-xs font-mono text-zinc-500">root@server:~</span>
                    </div>
                    <div 
                        ref={terminalRef}
                        className="flex-1 bg-black rounded-b-xl border border-white/10 p-5 overflow-y-auto font-mono text-sm leading-relaxed"
                    >
                        {output.split('\n').map((line, i) => (
                            <div key={i} className="min-h-[1.5rem]">
                                {line.startsWith('>') ? (
                                    <span className="text-cyan-400">{line}</span>
                                ) : line.startsWith('[ERROR]') ? (
                                    <span className="text-rose-500">{line}</span>
                                ) : line.includes('Berhasil') || line.includes('selesai!') || line.includes('✅') ? (
                                    <span className="text-emerald-400">{line}</span>
                                ) : line.includes('Mengakses') || line.includes('Menunggu') ? (
                                    <span className="text-amber-300">{line}</span>
                                ) : (
                                    <span className="text-zinc-300">{line}</span>
                                )}
                            </div>
                        ))}
                        {isRunning && (
                            <div className="text-zinc-500 animate-pulse mt-2">Menunggu respon server... █</div>
                        )}
                    </div>
                </div>

            </div>
        </div>
    );
}
