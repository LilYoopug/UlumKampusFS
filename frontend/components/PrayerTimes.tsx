import React, { useState, useEffect, useMemo, useCallback } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { Icon } from './Icon';

interface Location {
    id: string;
    lokasi: string;
}

interface PrayerSchedule {
    tanggal: string;
    imsak: string;
    subuh: string;
    terbit: string;
    dhuha: string;
    dzuhur: string;
    ashar: string;
    maghrib: string;
    isya: string;
    date: string;
}

const PRAYER_LOCATION_KEY = 'ulumcampus_prayer_location';

const PrayerTimeCard: React.FC<{ name: string, time: string, isNext: boolean, isPassed: boolean, icon: React.ReactNode }> = ({ name, time, isNext, isPassed, icon }) => (
    <div className={`p-4 rounded-lg flex flex-col items-center justify-center text-center transition-all ${
        isPassed 
            ? 'bg-green-900/30 dark:bg-green-900/50 text-green-700 dark:text-green-300' 
            : isNext 
                ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 shadow-lg scale-105 border border-green-300 dark:border-green-700' 
                : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'
    }`}>
        <div className={`mb-2 ${
            isPassed 
                ? 'text-green-800 dark:text-green-200' 
                : isNext 
                    ? 'text-green-600 dark:text-green-300' 
                    : 'text-brand-emerald-50'
        }`}>{icon}</div>
        <p className={`font-semibold text-sm ${
            isPassed 
                ? 'text-green-800 dark:text-green-200' 
                : isNext 
                    ? 'text-green-800 dark:text-green-200' 
                    : 'text-slate-700 dark:text-slate-200'
        }`}>{name}</p>
        <p className={`font-bold text-xl ${
            isPassed 
                ? 'text-green-900 dark:text-green-100' 
                : isNext 
                    ? 'text-green-800 dark:text-green-100' 
                    : 'text-slate-80 dark:text-white'
        }`}>{time}</p>
    </div>
);

const formatCurrentTime = (date: Date) => {
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${hours}:${minutes}:${seconds}`;
};


export const PrayerTimes: React.FC = () => {
    const { t } = useLanguage();
    const [location, setLocation] = useState<{ id: string; name: string } | null>(null);
    const [allLocations, setAllLocations] = useState<Location[]>([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [schedule, setSchedule] = useState<PrayerSchedule | null>(null);
    const [loading, setLoading] = useState(false);
    const [locationsLoading, setLocationsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [nextPrayer, setNextPrayer] = useState<string | null>(null);
    const [currentTime, setCurrentTime] = useState(new Date());

    const prayerTimesList = useMemo(() => {
        if (!schedule) return [];
        return [
            { name: 'Imsak', time: schedule.imsak, icon: <Icon className="w-6 h-6"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></Icon> },
            { name: 'Subuh', time: schedule.subuh, icon: <Icon className="w-6 h-6"><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></Icon> },
            { name: 'Terbit', time: schedule.terbit, icon: <Icon className="w-6 h-6"><path d="M12 17a5 5 0 0 0 0-10V2"/><path d="M17 8H7"/></Icon> },
            { name: 'Dhuha', time: schedule.dhuha, icon: <Icon className="w-6 h-6"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></Icon> },
            { name: 'Dzuhur', time: schedule.dzuhur, icon: <Icon className="w-6 h-6"><circle cx="12" cy="12" r="5"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></Icon> },
            { name: 'Ashar', time: schedule.ashar, icon: <Icon className="w-6 h-6"><circle cx="12" cy="12" r="5"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></Icon> },
            { name: 'Maghrib', time: schedule.maghrib, icon: <Icon className="w-6 h-6"><path d="M12 17a5 5 0 0 1 0-10v10"/><path d="M17 8H7"/></Icon> },
            { name: 'Isya', time: schedule.isya, icon: <Icon className="w-6 h-6"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></Icon> },
        ]
    }, [schedule]);

    // Timer to update current time every second
    useEffect(() => {
        const timer = setInterval(() => setCurrentTime(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    // Check if all prayers for today have passed
    const allPrayersPassed = useMemo(() => {
        if (!schedule || !prayerTimesList.length) return false;
        
        const now = new Date();
        const todayStr = now.toISOString().split('T')[0];
        
        // Check if the last prayer of the day (isya) has passed
        const lastPrayerTime = schedule.isya;
        const lastPrayerDate = new Date(`${todayStr}T${lastPrayerTime}:00`);
        
        return lastPrayerDate < now;
    }, [schedule, prayerTimesList]);

    const isPrayerPassed = useCallback((prayerTime: string) => {
        if (!schedule) return false;
        
        const now = new Date();
        const todayStr = now.toISOString().split('T')[0];
        
        // If all prayers for today have passed, we're showing tomorrow's schedule
        // In this case, no prayers from tomorrow's schedule should be marked as "passed" yet
        // because tomorrow hasn't started
        if (allPrayersPassed) {
            return false;
        }
        
        // For today's schedule, check if the prayer time has passed
        const prayerDate = new Date(`${todayStr}T${prayerTime}:00`);
        
        return prayerDate < now;
    }, [schedule, allPrayersPassed]);

    // Determine the next prayer time and which prayers have passed
    useEffect(() => {
        if (!schedule) return;

        const now = new Date();
        const todayStr = now.toISOString().split('T')[0];

        // Use all prayer times for next prayer logic (including Imsak and Terbit)
        const scheduleCheckList = prayerTimesList;

        // If all prayers for today have passed, the next prayer is the first prayer of tomorrow (imsak)
        if (allPrayersPassed) {
            setNextPrayer('Imsak');
        } else {
            // Otherwise, find the next prayer in today's schedule
            for (const prayer of scheduleCheckList) {
                const prayerTimeStr = prayer.time;
                const prayerDate = new Date(`${todayStr}T${prayerTimeStr}:00`);
                if (prayerDate > now) {
                    setNextPrayer(prayer.name);
                    return;
                }
            }
            // If all prayers for today have passed, the next is Subuh tomorrow
            setNextPrayer('Subuh');
        }
    }, [schedule, prayerTimesList, currentTime, allPrayersPassed]);


    useEffect(() => {
        const savedLocation = localStorage.getItem(PRAYER_LOCATION_KEY);
        if (savedLocation) {
            try {
                const parsedLocation = JSON.parse(savedLocation);
                setLocation(parsedLocation);
            } catch (e) {
                console.error('Error parsing saved location:', e);
            }
        }
        
        // Always load locations for search functionality
        setLocationsLoading(true);
        fetch('https://api.myquran.com/v2/sholat/kota/semua')
            .then(res => res.json())
            .then(data => {
                if (data && data.status && data.data) {
                    setAllLocations(data.data);
                } else {
                    setError('Gagal memuat daftar kota.');
                }
            })
            .catch((error) => {
                console.error('Error fetching locations:', error);
                setError('Gagal memuat daftar kota.');
            })
            .finally(() => {
                setLocationsLoading(false);
            });
    }, []);

    useEffect(() => {
        if (location) {
            setLoading(true);
            setError(null);
            const date = new Date();
            const todayStr = date.toISOString().split('T')[0];
            
            // Check if all prayers for today have passed
            const fetchDate = allPrayersPassed ? 
                new Date(date.getTime() + 24 * 60 * 60 * 1000).toISOString().split('T')[0] : 
                todayStr;
            
            fetch(`https://api.myquran.com/v2/sholat/jadwal/${location.id}/${fetchDate}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        setSchedule(data.data.jadwal);
                    } else {
                        setError('Gagal memuat jadwal shalat.');
                    }
                })
                .catch(() => setError('Gagal memuat jadwal shalat.'))
                .finally(() => setLoading(false));
        }
    }, [location, allPrayersPassed]);

    const handleSelectLocation = (loc: Location) => {
        const newLocation = { id: loc.id, name: loc.lokasi };
        localStorage.setItem(PRAYER_LOCATION_KEY, JSON.stringify(newLocation));
        setLocation(newLocation);
    };

    const handleClearLocation = () => {
        localStorage.removeItem(PRAYER_LOCATION_KEY);
        setLocation(null);
        setSchedule(null);
        setSearchTerm('');
    };

    const filteredLocations = useMemo(() => {
        if (!searchTerm) return [];
        return allLocations
            .filter(loc => loc.lokasi.toLowerCase().includes(searchTerm.toLowerCase()))
            .slice(0, 100); // Limit results for performance
    }, [searchTerm, allLocations]);

    if ((loading && !schedule) || (locationsLoading && !location)) {
        return <div className="text-center p-8">Memuat...</div>;
    }

    if (error) {
        return <div className="text-center p-8 text-red-500">{error}</div>;
    }

    if (!location) {
        return (
            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md max-w-2xl mx-auto">
                <h3 className="text-xl font-bold text-center mb-4 text-slate-800 dark:text-white">Pilih Lokasi Anda</h3>
                <div className="relative">
                     <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></Icon>
                <input
                    type="text"
                    value={searchTerm}
                    onChange={e => setSearchTerm(e.target.value)}
                    placeholder="Cari nama kota atau kabupaten..."
                    className="w-full ps-10 pe-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-60 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                />
                </div>
                {searchTerm && (
                    <ul className="mt-2 border border-slate-200 dark:border-slate-700 rounded-lg max-h-60 overflow-y-auto">
                        {filteredLocations.map(loc => (
                            <li key={loc.id} onClick={() => handleSelectLocation(loc)} className="px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer text-slate-800 dark:text-slate-200">
                                {loc.lokasi}
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        );
    }
    
    if (schedule) {
        return (
             <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                    <div>
                        <h3 className="text-xl font-bold text-slate-800 dark:text-white">{location.name}</h3>
                        <p className="text-slate-500 dark:text-white">{schedule.tanggal}</p>
                    </div>
                <div className="flex items-center gap-4 w-full sm:w-auto">
                    <p className="text-2xl font-mono font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-lg flex-grow sm:flex-grow-0 text-center">
                        {formatCurrentTime(currentTime)}
                    </p>
                    <button onClick={handleClearLocation} className="text-sm font-semibold text-brand-emerald-60 dark:text-brand-emerald-400 hover:underline flex-shrink-0">Ganti Lokasi</button>
                </div>
                </div>
                
                {allPrayersPassed && (
                    <div className="mb-4 p-3 bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 rounded-lg text-center">
                        Jadwal shalat hari ini sudah habis/terlewat
                    </div>
                )}
                
                <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
                   {prayerTimesList.map(prayer => (
                       <PrayerTimeCard 
                           key={prayer.name}
                           name={prayer.name}
                           time={prayer.time}
                           icon={prayer.icon}
                           isNext={nextPrayer === prayer.name}
                           isPassed={isPrayerPassed(prayer.time)}
                       />
                   ))}
                </div>
            </div>
        )
    }

    return null;
}
