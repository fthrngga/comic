import { useState } from 'react';
import { Head } from '@inertiajs/react';
import axios from 'axios';

export default function ScraperProcess() {
    const [loading, setLoading] = useState(false);
    const [logs, setLogs] = useState([]);
    const [error, setError] = useState(null);

    const startDebug = async () => {
        setLoading(true);
        setLogs([{ step: 'Inisialisasi', status: 'pending', details: 'Memulai proses diagnosa dari server lokal ke Shinigami API...' }]);
        setError(null);

        try {
            const response = await axios.post('/proses/test');
            setLogs(response.data.logs);
        } catch (err) {
            setError(err.message || 'Terjadi kesalahan sistem saat menghubungi backend.');
            setLogs(prev => [...prev, { step: 'Request Failed', status: 'error', details: 'Network/Server Error' }]);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-gray-950 text-green-400 p-8 font-mono">
            <Head title="Diagnosa Scraper Shinigami" />
            
            <div className="max-w-4xl mx-auto">
                <div className="flex items-center justify-between mb-8 border-b border-green-800 pb-4">
                    <div>
                        <h1 className="text-2xl font-bold text-green-500">Terminal Diagnosa Scraper</h1>
                        <p className="text-sm text-green-700 mt-1">Menguji koneksi dan respon API pihak ketiga secara langsung</p>
                    </div>
                    <button 
                        onClick={startDebug} 
                        disabled={loading}
                        className="bg-green-900 hover:bg-green-700 text-green-100 px-6 py-2 rounded border border-green-500 disabled:opacity-50 transition-colors"
                    >
                        {loading ? 'Mendiagnosa...' : 'Mulai Diagnosa'}
                    </button>
                </div>

                {error && (
                    <div className="bg-red-950 border border-red-500 text-red-400 p-4 rounded mb-6">
                        <strong>Error Internal:</strong> {error}
                    </div>
                )}

                <div className="bg-black rounded-lg border border-gray-800 shadow-2xl p-6 min-h-[400px]">
                    <div className="flex gap-2 mb-6 border-b border-gray-800 pb-4">
                        <div className="w-3 h-3 rounded-full bg-red-500"></div>
                        <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div className="w-3 h-3 rounded-full bg-green-500"></div>
                        <div className="ml-4 text-xs text-gray-600">bash - scraper_diagnostics</div>
                    </div>

                    <div className="space-y-6">
                        {logs.length === 0 && !loading && (
                            <div className="text-gray-500 text-center py-10">
                                <p>Menunggu eksekusi...</p>
                                <p className="text-sm mt-2">Tekan "Mulai Diagnosa" untuk menjalankan tes scraper.</p>
                            </div>
                        )}

                        {logs.map((log, index) => (
                            <div key={index} className="border-l-2 pl-4 py-1" style={{ borderColor: log.status === 'error' ? '#ef4444' : log.status === 'success' ? '#22c55e' : '#eab308' }}>
                                <div className="flex items-center gap-2 mb-1">
                                    <span className="font-bold text-blue-400">[{log.step}]</span>
                                    <span className={`text-xs px-2 py-0.5 rounded ${
                                        log.status === 'error' ? 'bg-red-900 text-red-200' : 
                                        log.status === 'success' ? 'bg-green-900 text-green-200' : 
                                        'bg-yellow-900 text-yellow-200'
                                    }`}>
                                        {log.status.toUpperCase()}
                                    </span>
                                </div>
                                <div className="text-gray-300 text-sm mb-2">{log.details}</div>
                                
                                {log.error_message && (
                                    <div className="mt-2 p-3 bg-red-950 border border-red-800 rounded text-red-300 text-xs overflow-x-auto whitespace-pre-wrap font-sans">
                                        {log.error_message}
                                    </div>
                                )}
                                
                                {log.response_body && (
                                    <div className="mt-2 p-3 bg-gray-900 border border-gray-700 rounded text-gray-400 text-xs overflow-x-auto">
                                        <pre>{JSON.stringify(log.response_body, null, 2)}</pre>
                                    </div>
                                )}
                            </div>
                        ))}
                        
                        {loading && (
                            <div className="text-green-500 animate-pulse mt-4">
                                root@anti-gravity:~# Mengeksekusi request jaringan...
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
