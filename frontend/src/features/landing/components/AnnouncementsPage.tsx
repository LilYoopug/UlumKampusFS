import React, { useEffect, useRef, useState, FormEvent } from 'react';
import { Icon } from '@/src/ui/components/Icon';
import { useLanguage } from '@/contexts/LanguageContext';
import { Announcement, AnnouncementCategory, TranslationKey, User, UserRole } from '@/types';
import { timeAgo } from '@/utils/time';
import { announcementAPI } from '@/services/apiService';

interface AnnouncementsPageProps {
    initialAnnouncementId?: string;
    currentUser: User;
    announcements: Announcement[];
}

const CategoryBadge: React.FC<{ category: AnnouncementCategory }> = ({ category }) => {
    const { t } = useLanguage();
    
    const categoryStyles: Record<AnnouncementCategory, string> = {
        'Kampus': 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
        'Akademik': 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
        'Mata Kuliah': 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
    };

    const translationKey: TranslationKey = `announcement_category_${category.toLowerCase().replace(' ', '')}` as TranslationKey;

    return (
        <span className={`text-xs font-semibold px-2.5 py-0.5 rounded-full ${categoryStyles[category]}`}>
            {t(translationKey)}
        </span>
    );
};


export const AnnouncementsPage: React.FC<AnnouncementsPageProps> = ({ initialAnnouncementId, currentUser, announcements: propAnnouncements }) => {
    const { t } = useLanguage();
    const announcementRefs = useRef<Record<string, HTMLDivElement | null>>({});
    
    const [announcements, setAnnouncements] = useState<Announcement[]>([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [newTitle, setNewTitle] = useState('');
    const [newContent, setNewContent] = useState('');
    const [newCategory, setNewCategory] = useState<AnnouncementCategory>('Akademik');
    const [submitting, setSubmitting] = useState(false);

    const allowedRoles: UserRole[] = ['Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin'];
    const canCreate = allowedRoles.includes(currentUser.role);

    useEffect(() => {
        fetchAnnouncements();
    }, []);

    // Separate effect for highlighting - runs after announcements are loaded
    useEffect(() => {
        if (initialAnnouncementId && !loading && announcements.length > 0) {
            // Use a small delay to ensure DOM is updated with refs
            const timer = setTimeout(() => {
                const element = announcementRefs.current[initialAnnouncementId];
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    element.classList.add('bg-brand-sand-100', 'dark:bg-brand-sand-900/50', 'ring-2', 'ring-brand-sand-300');
                    setTimeout(() => {
                        element.classList.remove('bg-brand-sand-100', 'dark:bg-brand-sand-900/50', 'ring-2', 'ring-brand-sand-300');
                    }, 3000);
                }
            }, 100);
            return () => clearTimeout(timer);
        }
    }, [initialAnnouncementId, loading, announcements]);

    const fetchAnnouncements = async () => {
        try {
            setLoading(true);
            const response = await announcementAPI.getAll();
            // Handle paginated response
            const responseData = response.data as any;
            const data = Array.isArray(responseData) 
                ? responseData 
                : (responseData?.data || []);
            setAnnouncements(data);
        } catch (error) {
            console.error('Error fetching announcements:', error);
            // Fallback to prop announcements if API fails
            if (propAnnouncements && propAnnouncements.length > 0) {
                setAnnouncements(propAnnouncements);
            }
        } finally {
            setLoading(false);
        }
    };
    
    const handleFormSubmit = async (e: FormEvent) => {
        e.preventDefault();
        if (!newTitle.trim() || !newContent.trim()) return;
        
        setSubmitting(true);
        try {
            const announcementData = {
                title: newTitle,
                content: newContent,
                category: newCategory,
                target_audience: 'all',
                priority: 'normal',
                is_published: true,
            };
            
            const response = await announcementAPI.create(announcementData);
            
            // Add new announcement to the list
            setAnnouncements(prev => [response.data, ...prev]);
            setShowForm(false);
            setNewTitle('');
            setNewContent('');
            setNewCategory('Akademik');
            
            alert('Pengumuman berhasil dibuat!');
        } catch (error) {
            console.error('Error creating announcement:', error);
            alert('Gagal membuat pengumuman. Silakan coba lagi.');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="max-w-4xl mx-auto">
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('announcements_title')}</h1>
                    <p className="text-slate-500 dark:text-slate-400 mt-1 mb-6">{t('announcements_subtitle')}</p>
                </div>
                {canCreate && (
                    <div className="mb-6">
                        <button 
                            onClick={() => setShowForm(!showForm)} 
                            className="flex items-center gap-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors"
                        >
                            {showForm ? (
                                <>
                                    <Icon className="w-5 h-5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></Icon>
                                    Batal
                                </>
                            ) : (
                                 <>
                                    <Icon className="w-5 h-5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></Icon>
                                    Buat Pengumuman Baru
                                </>
                            )}
                        </button>
                    </div>
                )}
            </div>

            {showForm && canCreate && (
                 <div className="bg-white dark:bg-slate-800/50 p-6 rounded-lg shadow-md border border-slate-200 dark:border-slate-700 mb-6">
                    <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">Buat Pengumuman Baru</h2>
                    <form onSubmit={handleFormSubmit} className="space-y-4">
                        <div>
                            <label htmlFor="ann-title" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Judul</label>
                                <input 
                                    id="ann-title"
                                    type="text"
                                    value={newTitle}
                                    onChange={e => setNewTitle(e.target.value)}
                                    placeholder={t('announcement_form_title_placeholder')}
                                    className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                                    required
                                />
                        </div>
                         <div>
                            <label htmlFor="ann-category" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Kategori</label>
                            <select
                                id="ann-category"
                                value={newCategory}
                                onChange={e => setNewCategory(e.target.value as AnnouncementCategory)}
                                className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                            >
                                <option value="Akademik">Akademik</option>
                                <option value="Kampus">Kampus</option>
                                <option value="Mata Kuliah">Mata Kuliah</option>
                            </select>
                        </div>
                        <div>
                            <label htmlFor="ann-content" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Isi Pengumuman</label>
                            <textarea 
                                id="ann-content"
                                value={newContent}
                                onChange={e => setNewContent(e.target.value)}
                                placeholder={t('announcement_form_content_placeholder')}
                                rows={6}
                                className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                                required
                            ></textarea>
                        </div>
                        <div className="text-end">
                            <button type="submit" className="px-5 py-2.5 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">
                                Kirim Pengumuman
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {loading ? (
                <div className="flex justify-center items-center py-12">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-emerald-600"></div>
                </div>
            ) : (
            <div className="space-y-6">
                {announcements.length === 0 ? (
                    <div className="text-center py-12 text-slate-500 dark:text-slate-400">
                        <Icon className="w-16 h-16 mx-auto mb-4 opacity-50">
                            <path d="M9 7H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h4l5 5V2L9 7z"/><path d="M15.5 12a4.5 4.5 0 0 0-4.5-4.5v9a4.5 4.5 0 0 0 4.5-4.5z"/>
                        </Icon>
                        <p className="text-lg">{t('no_announcements')}</p>
                        <p className="text-sm mt-1">Belum ada pengumuman yang tersedia</p>
                    </div>
                ) : (
                    announcements.map(announcement => (
                    <div
                        key={announcement.id}
                        ref={el => (announcementRefs.current[announcement.id] = el)}
                        className="bg-white dark:bg-slate-800/50 p-6 rounded-lg shadow-md border border-slate-200 dark:border-slate-700 transition-all duration-500"
                    >
                        <div className="flex justify-between items-start gap-4">
                            <h2 className="text-xl font-bold text-slate-800 dark:text-white">{announcement.title}</h2>
                            <Icon className="w-6 h-6 text-brand-emerald-500 flex-shrink-0">
                                <path d="M9 7H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h4l5 5V2L9 7z"/><path d="M15.5 12a4.5 4.5 0 0 0-4.5-4.5v9a4.5 4.5 0 0 0 4.5-4.5z"/>
                            </Icon>
                        </div>
                        <div className="flex items-center gap-3 text-sm text-slate-500 dark:text-slate-400 mt-2">
                           <CategoryBadge category={announcement.category} />
                            <span className="mx-1">•</span>
                            <span>{t('announcements_by')} <span className="font-semibold text-slate-700 dark:text-slate-200">{announcement.authorName}</span></span>
                            <span className="mx-1">•</span>
                            <span>{timeAgo(announcement.timestamp)}</span>
                        </div>
                        <p className="mt-4 text-slate-600 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">
                            {announcement.content}
                        </p>
                    </div>
                )))}
            </div>
            )}
        </div>
    );
};
