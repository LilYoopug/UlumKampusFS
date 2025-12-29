import React, { useState, useEffect, useMemo, useCallback, useRef } from 'react';
import { Icon } from '@/src/ui/components/Icon';
import { EQuranSurah, EQuranAyah, MyQuranRandomAyahData, EQuranSurahDetail, TafsirAyah } from '@/types';

const LoadingSpinner: React.FC = () => (
    <div className="flex justify-center items-center h-64">
        <Icon className="w-12 h-12 text-brand-emerald-500 animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></Icon>
    </div>
);

const API_BASE_MYQURAN = 'https://api.myquran.com/v2';
const API_BASE_EQURAN = 'https://equran.id/api/v2';

export const QuranReader: React.FC = () => {
    const [view, setView] = useState<'random' | 'list' | 'detail'>('random');
    const [randomAyah, setRandomAyah] = useState<MyQuranRandomAyahData | null>(null);
    const [surahs, setSurahs] = useState<EQuranSurah[]>([]);
    const [selectedSurah, setSelectedSurah] = useState<EQuranSurahDetail | null>(null);
    const [tafsirData, setTafsirData] = useState<TafsirAyah[] | null>(null);
    const [activeTafsirAyah, setActiveTafsirAyah] = useState<number | null>(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [playingAudio, setPlayingAudio] = useState<string | null>(null);
    const [ayahToScrollTo, setAyahToScrollTo] = useState<string | null>(null);
    const ayahRefs = useRef<Record<string, HTMLLIElement | null>>({});

    const fetchRandomAyah = useCallback(async () => {
        setLoading(true);
        setError(null);
        try {
            const res = await fetch(`${API_BASE_MYQURAN}/quran/ayat/acak`);
            const data = await res.json();
            if (data.status) {
                setRandomAyah(data.data);
            } else {
                throw new Error('Gagal mengambil data ayat acak.');
            }
        } catch (e) {
            setError('Tidak dapat memuat ayat hari ini. Silakan coba lagi nanti.');
        } finally {
            setLoading(false);
        }
    }, []);

    const fetchAllSurahs = useCallback(async () => {
        if (surahs.length > 0) return surahs;
        setLoading(true);
        setError(null);
        try {
            const res = await fetch(`${API_BASE_EQURAN}/surat`);
            const data = await res.json();
            if (data.code === 200) {
                setSurahs(data.data);
                return data.data;
            } else {
                throw new Error('Gagal mengambil daftar surah.');
            }
        } catch (e) {
            setError('Tidak dapat memuat daftar surah.');
            return [];
        } finally {
            setLoading(false);
        }
    }, [surahs]);

    const handleSelectSurah = useCallback(async (surahNumber: number) => {
        setLoading(true);
        setError(null);
        setView('detail');
        setActiveTafsirAyah(null); // Reset open tafsir
        try {
            const [surahRes, tafsirRes] = await Promise.all([
                fetch(`${API_BASE_EQURAN}/surat/${surahNumber}`),
                fetch(`${API_BASE_EQURAN}/tafsir/${surahNumber}`)
            ]);
            
            const surahData = await surahRes.json();
            const tafsirData = await tafsirRes.json();

            if (surahData.code === 200 && tafsirData.code === 200) {
                setSelectedSurah(surahData.data);
                setTafsirData(tafsirData.data.tafsir);
            } else {
                throw new Error(`Gagal mengambil data untuk surah nomor ${surahNumber}.`);
            }
        } catch (e) {
            setError('Tidak dapat memuat detail surah.');
            setView('list'); // Revert to list on error
        } finally {
            setLoading(false);
        }
    }, []);

    const handleShowFullSurah = async () => {
        if (!randomAyah) return;
        setAyahToScrollTo(randomAyah.ayat.ayah);
        await handleSelectSurah(randomAyah.info.surat.id);
    };
    
    useEffect(() => {
        if (view === 'random') {
            fetchRandomAyah();
        } else if (view === 'list' && surahs.length === 0) {
            fetchAllSurahs();
        }
    }, [view, fetchRandomAyah, fetchAllSurahs, surahs.length]);
    
    useEffect(() => {
        if (view === 'detail' && ayahToScrollTo && ayahRefs.current[ayahToScrollTo]) {
            setTimeout(() => {
                const element = ayahRefs.current[ayahToScrollTo];
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    element.classList.add('bg-brand-sand-100', 'dark:bg-brand-sand-900/50', 'transition-colors', 'duration-1000', 'rounded-lg');
                    setTimeout(() => {
                        element.classList.remove('bg-brand-sand-100', 'dark:bg-brand-sand-900/50');
                    }, 2500);
                    setAyahToScrollTo(null);
                }
            }, 100);
        }
    }, [selectedSurah, ayahToScrollTo, view]);


    const filteredSurahs = useMemo(() => 
        surahs.filter(s => 
            s.namaLatin.toLowerCase().includes(searchTerm.toLowerCase()) || 
            s.nomor.toString().includes(searchTerm)
        ), [surahs, searchTerm]);

    const playAudio = (audioUrl: string) => {
        if (playingAudio === audioUrl) {
            setPlayingAudio(null); // Pause if it's already playing
        } else {
            setPlayingAudio(audioUrl);
        }
    };
     
    if (loading && view === 'random' && !randomAyah) return <LoadingSpinner />;
    if (error && view === 'random') return <p className="text-center text-red-500">{error}</p>;

    if (view === 'random' && randomAyah) {
        return (
            <div className="bg-gradient-to-br from-brand-emerald-50 to-brand-sand-50 dark:from-brand-emerald-900/50 dark:to-brand-sand-900/50 p-6 rounded-lg text-center flex flex-col items-center">
                <h3 className="text-lg font-semibold text-brand-emerald-700 dark:text-brand-emerald-300">Ayat Hari Ini</h3>
                <p className="mt-4 text-2xl font-serif text-slate-800 dark:text-slate-100 text-right leading-loose rtl:text-right">{randomAyah.ayat.arab}</p>
                <p className="mt-2 text-sm text-slate-500 dark:text-slate-400 italic">"{randomAyah.ayat.text}"</p>
                <p className="mt-2 text-sm font-semibold text-slate-600 dark:text-slate-300">(QS. {randomAyah.info.surat.nama.id}: {randomAyah.ayat.ayah})</p>
                <div className="mt-6 flex flex-col sm:flex-row items-center gap-4">
                    <button onClick={handleShowFullSurah} className="font-semibold text-brand-emerald-600 dark:text-brand-emerald-400 hover:underline">
                        Lihat Selengkapnya
                    </button>
                    <button onClick={() => setView('list')} className="px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">
                        Baca Al-Qur'an
                    </button>
                </div>
            </div>
        );
    }

    if (view === 'list') {
        return (
            <div>
                <div className="relative mb-4">
                    <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></Icon>
                    <input type="text" placeholder="Cari surah (e.g. Al-Fatihah atau 1)" value={searchTerm} onChange={e => setSearchTerm(e.target.value)} className="w-full ps-10 pe-4 py-2 rounded-lg bg-slate-10 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"/>
                </div>
                {loading ? <LoadingSpinner /> : error ? <p className="text-center text-red-500">{error}</p> : (
                    <ul className="space-y-2 max-h-[60vh] overflow-y-auto">
                        {filteredSurahs.map(surah => (
                            <li key={surah.nomor}>
                                <a href="#" onClick={(e) => { e.preventDefault(); handleSelectSurah(surah.nomor); }} className="flex items-center p-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                                    <span className="flex items-center justify-center w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 font-bold text-brand-emerald-600 dark:text-brand-emerald-400">{surah.nomor}</span>
                                    <div className="ms-4 flex-1">
                                        <p className="font-semibold text-slate-800 dark:text-white">{surah.namaLatin}</p>
                                        <p className="text-sm text-slate-500 dark:text-slate-400">{surah.arti} • {surah.jumlahAyat} ayat</p>
                                    </div>
                                    <p className="font-serif text-xl text-brand-emerald-700 dark:text-brand-emerald-300">{surah.nama}</p>
                                </a>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        );
    }
    
    if (view === 'detail' && selectedSurah) {
        return (
            <div>
                 <button onClick={() => setView('list')} className="flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors font-medium mb-4">
                    <Icon className="w-5 h-5"><path d="m15 18-6-6 6-6"/></Icon>
                    <span>Kembali ke Daftar Surah</span>
                </button>
                <div className="bg-slate-50 dark:bg-slate-900/50 p-6 rounded-lg mb-4">
                    <h2 className="text-3xl font-bold text-slate-800 dark:text-white">{selectedSurah.namaLatin} ({selectedSurah.nama})</h2>
                    <p className="text-slate-500 dark:text-slate-400 mt-1">{selectedSurah.arti} • {selectedSurah.tempatTurun} • {selectedSurah.jumlahAyat} ayat</p>
                    <details className="mt-2 text-sm">
                        <summary className="cursor-pointer text-brand-emerald-600">Deskripsi Singkat</summary>
                        <div className="mt-1 text-slate-600 dark:text-slate-300" dangerouslySetInnerHTML={{ __html: selectedSurah.deskripsi }}></div>
                    </details>
                </div>
                {loading ? <LoadingSpinner /> : (
                    <ul className="space-y-2 max-h-[60vh] overflow-y-auto p-1 -m-1">
                        {selectedSurah.ayat.map(ayah => (
                            // FIX: Wrapped ref callback body in curly braces to ensure void return type.
                            <li key={ayah.nomorAyat} ref={el => { ayahRefs.current[String(ayah.nomorAyat)] = el; }} className="p-4 border-b border-slate-200 dark:border-slate-700">
                                <div className="flex justify-between items-center mb-4">
                                    <span className="font-bold text-brand-emerald-600 dark:text-brand-emerald-400">{selectedSurah.nomor}:{ayah.nomorAyat}</span>
                                    <div className="flex items-center gap-2">
                                        <button onClick={() => setActiveTafsirAyah(activeTafsirAyah === ayah.nomorAyat ? null : ayah.nomorAyat)} className={`px-2 py-1 text-xs font-semibold rounded-md transition-colors ${activeTafsirAyah === ayah.nomorAyat ? 'bg-brand-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-200 hover:bg-slate-300'}`}>
                                            Tafsir
                                        </button>
                                        <button onClick={() => playAudio(ayah.audio['05'])} aria-label="Play audio">
                                            {playingAudio === ayah.audio['05'] ? (
                                                 <Icon className="w-6 h-6 text-brand-emerald-500"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></Icon>
                                            ) : (
                                                <Icon className="w-6 h-6 text-slate-500 hover:text-brand-emerald-500"><circle cx="12" cy="12" r="10"/><path d="m10 8 6 4-6 4Z"/></Icon>
                                            )}
                                        </button>
                                    </div>
                                </div>
                                <p className="text-3xl font-serif text-slate-800 dark:text-slate-100 text-right leading-loose rtl:text-right">{ayah.teksArab}</p>
                                <p className="mt-4 text-sm text-slate-500 dark:text-slate-400 italic text-right rtl:text-right">{ayah.teksLatin}</p>
                                <p className="mt-3 text-slate-600 dark:text-slate-300">"{ayah.teksIndonesia}"</p>
                                {activeTafsirAyah === ayah.nomorAyat && (
                                    <div className="mt-4 p-4 bg-slate-100 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-700">
                                        <h5 className="font-semibold text-brand-emerald-700 dark:text-brand-emerald-300">Tafsir Kemenag:</h5>
                                        <p className="mt-2 text-slate-600 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">
                                            {tafsirData?.find(t => t.ayat === ayah.nomorAyat)?.teks || 'Tafsir tidak tersedia.'}
                                        </p>
                                    </div>
                                )}
                            </li>
                        ))}
                    </ul>
                )}
                 {playingAudio && <audio src={playingAudio} autoPlay onEnded={() => setPlayingAudio(null)} className="hidden" />}
            </div>
        )
    }

    return null;
}
