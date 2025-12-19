import React, { useState, Suspense } from 'react';
import { Icon } from './Icon';
import { QuranReader } from './QuranReader';
import { HadithReader } from './HadithReader';
import { DoaReader } from './DoaReader';

type Tab = 'quran' | 'hadith' | 'doa';

const LoadingSpinner: React.FC = () => (
    <div className="flex justify-center items-center h-64">
        <Icon className="w-12 h-12 text-brand-emerald-500 animate-spin">
            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
        </Icon>
    </div>
);

export const IslamicResources: React.FC = () => {
    const [activeTab, setActiveTab] = useState<Tab>('quran');

    const tabs: { id: Tab; label: string; icon: React.ReactNode }[] = [
        { id: 'quran', label: 'Al-Qur\'an', icon: <Icon className="w-5 h-5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></Icon> },
        { id: 'hadith', label: 'Al-Hadits', icon: <Icon className="w-5 h-5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></Icon> },
        { id: 'doa', label: 'Kumpulan Doa', icon: <Icon className="w-5 h-5"><path d="M12 2a5 5 0 0 0-5 5v2H5a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h2v2a5 5 0 0 0 5 5 5 5 0 0 0 5-5v-2h2a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2h-2V7a5 5 0 0 0-5-5z"/></Icon> },
    ];

    const renderContent = () => {
        switch (activeTab) {
            case 'quran':
                return <QuranReader />;
            case 'hadith':
                return <HadithReader />;
            case 'doa':
                return <DoaReader />;
            default:
                return null;
        }
    };

    return (
        <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
            <div className="border-b border-slate-200 dark:border-slate-700">
                <nav className="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                    {tabs.map(tab => (
                        <button
                            key={tab.id}
                            onClick={() => setActiveTab(tab.id)}
                            className={`${
                                activeTab === tab.id
                                    ? 'border-brand-emerald-500 text-brand-emerald-600 dark:text-brand-emerald-400'
                                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:border-slate-600'
                            } whitespace-nowrap flex items-center gap-2 py-4 px-1 border-b-2 font-medium text-sm transition-colors`}
                        >
                            {tab.icon}
                            {tab.label}
                        </button>
                    ))}
                </nav>
            </div>
            <div className="mt-6 min-h-[50vh]">
                <Suspense fallback={<LoadingSpinner />}>
                    {renderContent()}
                </Suspense>
            </div>
        </div>
    );
};
