import React, { useState, useEffect, useCallback } from 'react';
import { Icon } from './Icon';
import { Hadith, Perawi } from '../types';

const LoadingSpinner: React.FC = () => (
    <div className="flex justify-center items-center h-64">
        <Icon className="w-12 h-12 text-brand-emerald-500 animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></Icon>
    </div>
);

const API_BASE = 'https://api.myquran.com/v2';

export const HadithReader: React.FC = () => {
    const [view, setView] = useState<'random' | 'list'>('random');
    const [randomHadith, setRandomHadith] = useState<Hadith | null>(null);
    const [hadithList, setHadithList] = useState<Hadith[]>([]);
    const [perawiList, setPerawiList] = useState<Perawi[]>([]);

    const [activeCollection, setActiveCollection] = useState('arbain');
    const [selectedPerawi, setSelectedPerawi] = useState<string | null>(null);
    const [hadithNumber, setHadithNumber] = useState('');
    const [searchedHadith, setSearchedHadith] = useState<Hadith | null>(null);

    const [isModalOpen, setIsModalOpen] = useState(false);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchRandomHadith = useCallback(async () => {
        setLoading(true); setError(null);
        try {
            const res = await fetch(`${API_BASE}/hadits/arbain/acak`);
            const data = await res.json();
            if (data.status) setRandomHadith(data.data);
            else throw new Error('Gagal mengambil data hadits acak.');
        } catch (e) {
            setError('Tidak dapat memuat hadits hari ini.');
        } finally {
            setLoading(false);
        }
    }, []);

    const fetchCollection = useCallback(async (collection: string) => {
        setLoading(true); setError(null); setHadithList([]); setSearchedHadith(null);
        try {
            if (collection === 'arbain') {
                const res = await fetch(`${API_BASE}/hadits/arbain/semua`);
                const data = await res.json();
                if (data.status) setHadithList(data.data);
                else throw new Error('Gagal memuat hadits Arba\'in.');
            } else if (collection === 'perawi') {
                const res = await fetch(`${API_BASE}/hadits/perawi`);
                const data = await res.json();
                if (data.status) setPerawiList(data.data);
                else throw new Error('Gagal memuat daftar perawi.');
            }
        } catch (e: any) {
            setError(e.message);
        } finally {
            setLoading(false);
        }
    }, []);
    
    const fetchSingleHadith = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!hadithNumber) return;
        setLoading(true); setError(null); setSearchedHadith(null);
        let url = '';
        if (activeCollection === 'bm') {
            url = `${API_BASE}/hadits/bm/${hadithNumber}`;
        } else if (activeCollection === 'perawi' && selectedPerawi) {
            url = `${API_BASE}/hadits/${selectedPerawi}/${hadithNumber}`;
        }
        if (!url) { setLoading(false); return; }

        try {
            const res = await fetch(url);
            const data = await res.json();
            if (data.status) setSearchedHadith(data.data);
            else throw new Error('Hadits tidak ditemukan.');
        } catch(e: any) {
            setError(e.message);
        } finally {
            setLoading(false);
        }
    };


    useEffect(() => {
        if (view === 'random') {
            fetchRandomHadith();
        } else {
            fetchCollection(activeCollection);
        }
    }, [view, activeCollection, fetchRandomHadith, fetchCollection]);

    const renderHadith = (h: Hadith, index: number) => (
         <li key={index} className="border-b border-slate-200 dark:border-slate-700 pb-6">
            <h4 className="font-bold text-brand-emerald-600 dark:text-brand-emerald-400">Hadits no. {h.no || h.number}</h4>
            {h.judul && <p className="text-lg font-semibold text-slate-800 dark:text-white mt-1">{h.judul}</p>}
            <p className="mt-4 font-serif text-slate-800 dark:text-slate-100 text-right leading-loose rtl:text-right">{h.arab}</p>
            <p className="mt-4 text-slate-600 dark:text-slate-300 leading-relaxed" dangerouslySetInnerHTML={{ __html: h.indo || h.id || '' }}></p>
        </li>
    );

    if (view === 'random') {
        return (
            <>
                <div className="bg-gradient-to-br from-brand-emerald-50 to-brand-sand-50 dark:from-brand-emerald-900/50 dark:to-brand-sand-900/50 p-6 rounded-lg text-center flex flex-col items-center">
                    <h3 className="text-lg font-semibold text-brand-emerald-700 dark:text-brand-emerald-300">Hadits Hari Ini</h3>
                    {loading ? <LoadingSpinner /> : error ? <p className="text-red-500">{error}</p> : randomHadith && (
                        <div className="mt-4">
                            <p className="font-semibold text-slate-800 dark:text-white">{randomHadith.judul}</p>
                            <p className="mt-2 text-slate-600 dark:text-slate-300 text-sm">"{randomHadith.indo.substring(0, 150)}..."</p>
                            <button onClick={() => setIsModalOpen(true)} className="mt-2 font-semibold text-sm text-brand-emerald-600 hover:underline">
                                Lihat Selengkapnya
                            </button>
                        </div>
                    )}
                    <button onClick={() => setView('list')} className="mt-6 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">
                        Jelajahi Hadits
                    </button>
                </div>

                {isModalOpen && randomHadith && (
                    <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={() => setIsModalOpen(false)} role="dialog" aria-modal="true">
                        <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col" onClick={e => e.stopPropagation()}>
                            <div className="p-6 overflow-y-auto">
                                <h3 className="text-xl font-bold text-slate-800 dark:text-white">{randomHadith.judul}</h3>
                                <p className="mt-4 font-serif text-lg text-slate-800 dark:text-slate-100 text-right leading-loose rtl:text-right">{randomHadith.arab}</p>
                                <p className="mt-4 text-slate-600 dark:text-slate-300 leading-relaxed" dangerouslySetInnerHTML={{ __html: randomHadith.indo }}></p>
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

    const collectionTabs = [
        { id: 'arbain', label: "Arba'in An-Nawawi" },
        { id: 'bm', label: "Bulughul Maram" },
        { id: 'perawi', label: "9 Perawi" },
    ];

    return (
        <div>
            <div className="flex items-center border-b border-slate-200 dark:border-slate-700 mb-4">
                {collectionTabs.map(tab => (
                    <button key={tab.id} onClick={() => setActiveCollection(tab.id)} className={`px-4 py-2 text-sm font-semibold transition-colors ${activeCollection === tab.id ? 'border-b-2 border-brand-emerald-500 text-brand-emerald-600' : 'text-slate-500 hover:text-slate-800'}`}>
                        {tab.label}
                    </button>
                ))}
            </div>
            
            {loading && <LoadingSpinner />}
            {error && <p className="text-center text-red-500 p-4">{error}</p>}

            {activeCollection === 'arbain' && !loading && (
                <ul className="space-y-6 max-h-[60vh] overflow-y-auto p-2">{hadithList.map(renderHadith)}</ul>
            )}

            {(activeCollection === 'bm' || activeCollection === 'perawi') && (
                <form onSubmit={fetchSingleHadith} className="space-y-4 p-2">
                    {activeCollection === 'perawi' && (
                        <div>
                             <label htmlFor="perawi-select" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pilih Perawi:</label>
                             <select id="perawi-select" value={selectedPerawi || ''} onChange={e => setSelectedPerawi(e.target.value)} className="w-full px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-60 text-slate-800 dark:text-white" required>
                                <option value="" disabled>--Pilih seorang perawi--</option>
                                {perawiList.map(p => <option key={p.slug} value={p.slug}>{p.name} (1-{p.total})</option>)}
                             </select>
                        </div>
                    )}
                    <div>
                        <label htmlFor="hadith-number" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Masukkan Nomor Hadits {activeCollection === 'bm' && '(1-1597)'}:
                        </label>
                        <div className="flex gap-2">
                            <input id="hadith-number" type="number" value={hadithNumber} onChange={e => setHadithNumber(e.target.value)} className="w-full px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-60 text-slate-800 dark:text-white" required />
                            <button type="submit" className="px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700">Cari</button>
                        </div>
                    </div>
                </form>
            )}

            {searchedHadith && (
                <div className="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <ul className="space-y-6 p-2">{renderHadith(searchedHadith, 0)}</ul>
                </div>
            )}
        </div>
    );
};
