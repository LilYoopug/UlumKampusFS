import React, { useState, useMemo } from 'react';
import { LibraryResource } from '../types';
import { useLanguage } from '../contexts/LanguageContext';
import { Icon } from './Icon';
import { TranslationKey } from '../translations';

interface ResourceCardProps {
    resource: LibraryResource;
    isBookmarked: boolean;
    onToggleLibrary: (id: string) => void;
}

const ResourceCard: React.FC<ResourceCardProps> = ({ resource, isBookmarked, onToggleLibrary }) => {
    const { t } = useLanguage();
    const typeLabel = resource.type.charAt(0).toUpperCase() + resource.type.slice(1);
    
    const canRead = !!resource.sourceUrl;

    const readButton = (
        <a 
            href={canRead ? resource.sourceUrl : '#'} 
            target="_blank" 
            rel="noopener noreferrer"
            className={`flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors ${!canRead ? 'opacity-50 cursor-not-allowed' : ''}`}
            onClick={(e) => !canRead && e.preventDefault()}
            aria-disabled={!canRead}
        >
            <Icon className="w-5 h-5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></Icon>
            {t('elibrary_read_now')}
        </a>
    );

    return (
        <div className="flex flex-col sm:flex-row items-start gap-5 p-5 bg-white dark:bg-slate-800/50 rounded-lg shadow-md border border-slate-200 dark:border-slate-700">
            <img src={resource.coverUrl} alt={resource.title} className="w-32 h-44 object-cover rounded-md flex-shrink-0 shadow-lg" />
            <div className="flex flex-col h-full">
                <div>
                    <span className="text-xs font-semibold uppercase tracking-wider text-brand-sand-600 dark:text-brand-sand-400">{typeLabel}</span>
                    <h3 className="text-xl font-bold text-slate-800 dark:text-white mt-1">{resource.title}</h3>
                    <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">{t('detail_by')} {resource.author} ({resource.year})</p>
                    <p className="text-slate-600 dark:text-slate-300 mt-2 text-sm leading-relaxed">{resource.description}</p>
                </div>
                <div className="mt-4 flex flex-wrap gap-3 sm:mt-auto sm:pt-4">
                    {readButton}
                    <button 
                        onClick={() => onToggleLibrary(resource.id)}
                        className={`flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 font-semibold rounded-lg transition-colors ${
                            isBookmarked 
                                ? 'bg-brand-emerald-600 text-white hover:bg-brand-emerald-700' 
                                : 'bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-white hover:bg-slate-300 dark:hover:bg-slate-600'
                        }`}
                    >
                        {isBookmarked 
                            ? <Icon className="w-5 h-5"><path d="M20 6 9 17l-5-5"/></Icon> 
                            : <Icon className="w-5 h-5"><path d="M5 12h14"/><path d="M12 5v14"/></Icon>
                        }
                        {isBookmarked ? t('elibrary_in_library') : t('elibrary_add_to_library')}
                    </button>
                </div>
            </div>
        </div>
    );
};

interface JournalPortal {
    id: string;
    nameKey: TranslationKey;
    descriptionKey: TranslationKey;
    url: string;
}

const journalPortals: JournalPortal[] = [
    {
        id: 'jstor',
        nameKey: 'journal_jstor_name',
        descriptionKey: 'journal_jstor_desc',
        url: 'https://www.jstor.org/subject/islamicstudies',
    },
    {
        id: 'brill',
        nameKey: 'journal_brill_name',
        descriptionKey: 'journal_brill_desc',
        url: 'https://brill.com/subjects/religious-studies/islamic-studies',
    },
    {
        id: 'scholar',
        nameKey: 'journal_scholar_name',
        descriptionKey: 'journal_scholar_desc',
        url: 'https://scholar.google.com/',
    },
    {
        id: 'doaj',
        nameKey: 'journal_doaj_name',
        descriptionKey: 'journal_doaj_desc',
        url: 'https://doaj.org/search/subjects?source=%7B%22query%22%3A%7B%22bool%22%3A%7B%22must%22%3A%5B%7B%22term%22%3A%7B%22_id%22%3A%2220b029232349479685311b51e4c76045%22%7D%7D%5D%7D%7D%2C%22track_total_hits%22%3Atrue%7D',
    },
];

interface ELibraryProps {
    resources: LibraryResource[];
    myLibrary: string[];
    onToggleLibrary: (id: string) => void;
}

export const ELibrary: React.FC<ELibraryProps> = ({ resources, myLibrary, onToggleLibrary }) => {
    const { t } = useLanguage();
    const [keyword, setKeyword] = useState('');
    const [author, setAuthor] = useState('');
    const [year, setYear] = useState('');
    const [showMyLibraryOnly, setShowMyLibraryOnly] = useState(false);

    const filteredResources = useMemo(() => {
        const resourcesToFilter = showMyLibraryOnly
            ? resources.filter(r => myLibrary.includes(r.id))
            : resources;

        return resourcesToFilter.filter(resource => {
            const keywordMatch = keyword === '' ||
                resource.title.toLowerCase().includes(keyword.toLowerCase()) ||
                resource.description.toLowerCase().includes(keyword.toLowerCase());

            const authorMatch = author === '' ||
                resource.author.toLowerCase().includes(author.toLowerCase());

            const yearMatch = year === '' || !/^\d{4}$/.test(year) ||
                resource.year.toString() === year;

            return keywordMatch && authorMatch && yearMatch;
        });
    }, [keyword, author, year, resources, showMyLibraryOnly, myLibrary]);

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('elibrary_title')}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">{t('elibrary_subtitle')}</p>
            </div>

            <div className="p-4 bg-white dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700 space-y-4">
                <div className="flex flex-col sm:flex-row gap-4 items-center">
                    <div className="relative flex-grow w-full">
                        <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </Icon>
                        <input
                            type="text"
                            placeholder={t('elibrary_search_placeholder')}
                            value={keyword}
                            onChange={e => setKeyword(e.target.value)}
                            className="w-full h-full ps-10 pe-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                        />
                    </div>
                     <button
                        onClick={() => setShowMyLibraryOnly(prev => !prev)}
                        className={`w-full sm:w-auto flex-shrink-0 flex items-center justify-center gap-2 px-4 py-2 font-semibold rounded-lg transition-colors ${
                            showMyLibraryOnly
                                ? 'bg-brand-emerald-600 text-white hover:bg-brand-emerald-700'
                                : 'bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700'
                        }`}
                    >
                        <Icon className="w-5 h-5"><path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.77-.77-.77a5.4 5.4 0 0 0-7.65 0C1.46 6.7 1.33 10.28 4 13l8 8 8-8c2.67-2.72 2.54-6.3.42-8.42z"/></Icon>
                        {t('elibrary_my_library')}
                    </button>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="relative">
                        <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                        </Icon>
                        <input
                            type="text"
                            placeholder={t('elibrary_filter_author')}
                            value={author}
                            onChange={e => setAuthor(e.target.value)}
                            className="w-full h-full ps-10 pe-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-60 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                        />
                    </div>
                    <div className="relative">
                        <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y2="2"/><line x1="8" x2="8" y2="2"/><line x1="3" x2="21" y1="10" y2="10"/>
                        </Icon>
                        <input
                            type="number"
                            placeholder={t('elibrary_filter_year')}
                            value={year}
                            onChange={e => setYear(e.target.value)}
                            className="w-full h-full ps-10 pe-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-50 text-slate-800 dark:text-white"
                        />
                    </div>
                </div>
            </div>

            <div className="space-y-4">
                <h2 className="text-2xl font-bold text-slate-800 dark:text-white">{t('elibrary_credible_journals_title')}</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {journalPortals.map(portal => (
                        <a 
                            key={portal.id} 
                            href={portal.url} 
                            target="_blank" 
                            rel="noopener noreferrer"
                            className="group block p-4 bg-slate-50 dark:bg-slate-800 hover:bg-white dark:hover:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all"
                        >
                            <div className="flex items-center justify-between">
                                <h4 className="font-bold text-brand-emerald-700 dark:text-brand-emerald-400">{t(portal.nameKey)}</h4>
                                <Icon className="w-5 h-5 text-slate-400 group-hover:text-brand-emerald-500 transition-colors">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                    <polyline points="15 3 21 3 21 9" />
                                    <line x1="10" y1="14" x2="21" y2="3" />
                                </Icon>
                            </div>
                            <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">{t(portal.descriptionKey)}</p>
                        </a>
                    ))}
                </div>
            </div>

            <div className="space-y-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                {filteredResources.length > 0 ? (
                    filteredResources.map(resource => (
                        <ResourceCard 
                            key={resource.id} 
                            resource={resource} 
                            isBookmarked={myLibrary.includes(resource.id)}
                            onToggleLibrary={onToggleLibrary}
                        />
                    ))
                ) : (
                    <div className="text-center py-16">
                        <Icon className="mx-auto w-16 h-16 text-slate-400"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.59a2 2 0 0 1-2.83-2.83l.79-.79"/></Icon>
                        <h3 className="mt-4 text-lg font-semibold">{t('elibrary_no_results')}</h3>
                        <p className="mt-1 text-slate-500">{t('elibrary_no_results_subtitle')}</p>
                    </div>
                )}
            </div>
        </div>
    );
};