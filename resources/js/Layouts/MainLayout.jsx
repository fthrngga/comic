import { Link } from '@inertiajs/react';

export default function MainLayout({ children }) {
    return (
        <div className="min-h-screen bg-slate-900 text-slate-100 font-sans">
            <header className="sticky top-0 z-50 bg-slate-900/80 backdrop-blur-md border-b border-slate-800">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center h-16">
                        <div className="flex-shrink-0 flex items-center">
                            <Link href="/" className="text-xl font-bold tracking-tight text-violet-500">
                                Anti-Gravity
                            </Link>
                        </div>
                        <nav className="flex space-x-4 text-sm font-medium">
                            <Link href="/" className="text-slate-300 hover:text-white transition-colors">Home</Link>
                            <Link href="#" className="text-slate-300 hover:text-white transition-colors">Bookmarks</Link>
                        </nav>
                    </div>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {children}
            </main>
        </div>
    );
}
