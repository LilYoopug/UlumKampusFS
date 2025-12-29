import React, { useState, useEffect, useMemo, useCallback } from 'react';
import { Icon } from '@/src/ui/components/Icon';
import { Doa } from '@/types';

const LoadingSpinner: React.FC = () => (
    <div className="flex justify-center items-center h-64">
        <Icon className="w-12 h-12 text-brand-emerald-500 animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></Icon>
    </div>
);

const API_BASE = 'https://api.myquran.com/v2';

export const DoaReader: React.FC = () => {
    const [view, setView] = useState<'random' | 'list'>('random');
    const [randomDoa, setRandomDoa] = useState<Doa | null>(null);
    const [doaList, setDoaList] = useState<Doa[]>([]);
    const [categories, setCategories] = useState<string[]>([]);
    const [selectedCategory, setSelectedCategory] = useState<string | null>(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const fetchRandomDoa = useCallback(async () => {
        setLoading(true); setError(null);
        try {
            const res = await fetch(`${API_BASE}/doa/acak`);
            const data = await res.json();
            if (data.status) setRandomDoa(data.data);
            else throw new Error('Gagal mengambil data doa acak.');
        } catch (e) {
            setError('Tidak dapat memuat doa hari ini.');
        } finally {
            setLoading(false);
        }
    }, []);
    
    const fetchCategories = useCallback(async () => {
        setLoading(true); setError(null);
        try {
            const res = await fetch(`${API_BASE}/doa/sumber`);
            const data = await res.json();
            if (data.status) {
                setCategories(data.data);
                setSelectedCategory(data.data[0]); // Select the first category by default
            } else {
                throw new Error('Gagal memuat kategori doa.');
            }
        } catch (e: any) {
            setError(e.message);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (view === 'random') {
            fetchRandomDoa();
        } else if (view === 'list' && categories.length === 0) {
            fetchCategories();
        }
    }, [view, fetchRandomDoa, fetchCategories, categories.length]);
    
    useEffect(() => {
        if (selectedCategory) {
            setLoading(true); setError(null);
            fetch(`${API_BASE}/doa/sumber/${selectedCategory}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status) setDoaList(data.data);
                    else throw new Error(`Gagal memuat doa dari sumber ${selectedCategory}.`);
                })
                .catch((e: any) => setError(e.message))
                .finally(() => setLoading(false));
        }
    }, [selectedCategory]);

    const filteredDoa = useMemo(() =>
        doaList.filter(doa =>
            doa.judul.toLowerCase().includes(searchTerm.toLowerCase()) ||
            doa.indo.toLowerCase().includes(searchTerm.toLowerCase())
        ),
        [doaList, searchTerm]
    );

    if (view === 'random') {
        return (
            <>
                <div className="bg-gradient-to-br from-brand-emerald-50 to-brand-sand-50 dark:from-brand-emerald-900/50 dark:to-brand-sand-900/50 p-6 rounded-lg text-center flex flex-col items-center">
                    <h3 className="text-lg font-semibold text-brand-emerald-700 dark:text-brand-emerald-300">Doa Hari Ini</h3>
                    {loading ? <LoadingSpinner /> : error ? <p className="text-red-500">{error}</p> : randomDoa && (
                        <div className="mt-4">
                            <p className="font-semibold text-slate-800 dark:text-white">{randomDoa.judul}</p>
                            <p className="mt-2 text-slate-600 dark:text-slate-300 text-sm">"{randomDoa.indo}"</p>
                            <button onClick={() => setIsModalOpen(true)} className="mt-2 font-semibold text-sm text-brand-emerald-600 hover:underline">
                                Lihat Selengkapnya
                            </button>
                        </div>
                    )}
                    <button onClick={() => setView('list')} className="mt-6 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">
                        Lihat Semua Doa
                    </button>
                </div>
                {isModalOpen && randomDoa && (
                    <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={() => setIsModalOpen(false)} role="dialog" aria-modal="true">
                        <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col" onClick={e => e.stopPropagation()}>
                            <div className="p-6 overflow-y-auto">
                                <h3 className="text-xl font-bold text-slate-800 dark:text-white">{randomDoa.judul}</h3>
                                <p className="mt-4 font-serif text-lg text-slate-800 dark:text-slate-100 text-right leading-loose rtl:text-right">{randomDoa.arab}</p>
                                <p className="mt-4 text-slate-600 dark:text-slate-300 leading-relaxed italic">"{randomDoa.indo}"</p>
                            </div>
                            <div className="p-4 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-700 text-end rounded-b-lg">
                                <button onClick={() => setIsModalOpen(false)} className="px-4 py-2 bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </>
        );
    }
    
    return (
        <div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                 <div className="relative">
                    <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></Icon>
                    <input type="text" placeholder="Cari doa..." value={searchTerm} onChange={e => setSearchTerm(e.target.value)} className="w-full ps-10 pe-4 py-2 rounded-lg bg-slate-10 dark:bg-slate-700 border border-slate-300 dark:border-slate-60 text-slate-800 dark:text-white"/>
                </div>
                <select value={selectedCategory || ''} onChange={e => setSelectedCategory(e.target.value)} className="w-full px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-60 text-slate-800 dark:text-white">
                    {categories.map(cat => <option key={cat} value={cat}>{cat.charAt(0).toUpperCase() + cat.slice(1)}</option>)}
                </select>
            </div>
            {loading ? <LoadingSpinner /> : error ? <p className="text-center text-red-500">{error}</p> : (
                <ul className="space-y-6 max-h-[60vh] overflow-y-auto p-2">
                    {filteredDoa.map((doa, index) => (
                         <li key={doa.id || index} className="border-b border-slate-200 dark:border-slate-700 pb-6">
                            <h4 className="font-semibold text-slate-800 dark:text-white">{doa.judul}</h4>
                            <p className="mt-3 font-serif text-slate-800 dark:text-slate-100 text-right leading-loose rtl:text-right">{doa.arab}</p>
                            <p className="mt-3 text-sm text-slate-500 dark:text-slate-400 italic">"{doa.indo}"</p>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
};
