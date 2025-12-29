import React, { useState, useEffect } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { LibraryResource } from '@/types';
import { Icon } from '@/src/ui/components/Icon';

interface ResourceFormProps {
    onSave: (data: LibraryResource | Omit<LibraryResource, 'id'>) => void;
    onClose: () => void;
    initialData?: LibraryResource | null;
}

export const ResourceForm: React.FC<ResourceFormProps> = ({ onSave, onClose, initialData }) => {
    const { t } = useLanguage();
    const isEditMode = !!initialData;

    const [formData, setFormData] = useState({
        title: '',
        author: '',
        year: new Date().getFullYear(),
        type: 'book' as 'book' | 'journal',
        description: '',
        coverUrl: '',
    });

    const [sourceType, setSourceType] = useState<'link' | 'upload'>('link');
    const [sourceUrl, setSourceUrl] = useState('');
    const [sourceFile, setSourceFile] = useState<File | null>(null);
    const [fileName, setFileName] = useState('');

    useEffect(() => {
        if (initialData) {
            setFormData({
                title: initialData.title,
                author: initialData.author,
                year: initialData.year,
                type: initialData.type,
                description: initialData.description,
                coverUrl: initialData.coverUrl,
            });
            setSourceType(initialData.sourceType || 'link');
            setSourceUrl(initialData.sourceUrl || '');
        }
    }, [initialData]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: name === 'year' ? parseInt(value, 10) : value
        }));
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            setSourceFile(e.target.files[0]);
            setFileName(e.target.files[0].name);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        let finalSourceUrl = '';

        if (sourceType === 'link') {
            if (sourceUrl.trim()) {
                finalSourceUrl = sourceUrl.trim();
            }
        } else { // sourceType === 'upload'
            if (sourceFile) {
                finalSourceUrl = URL.createObjectURL(sourceFile);
            } else if (isEditMode && initialData?.sourceType === 'upload' && initialData.sourceUrl) {
                finalSourceUrl = initialData.sourceUrl;
            }
        }

        if (!finalSourceUrl) {
            alert("Sumber digital (link atau file) wajib diisi.");
            return;
        }
        
        const resourceData = {
            ...formData,
            sourceType: sourceType,
            sourceUrl: finalSourceUrl,
        };

        if (isEditMode && initialData) {
            onSave({ ...initialData, ...resourceData });
        } else {
            onSave(resourceData);
        }
    };

    return (
        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div className="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h1 className="text-2xl font-bold text-slate-800 dark:text-white">
                    {isEditMode ? t('manage_elibrary_form_edit_title') : t('manage_elibrary_form_add_title')}
                </h1>
                <button onClick={onClose} className="p-2 rounded-full text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <Icon className="w-6 h-6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></Icon>
                </button>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label htmlFor="title" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('manage_elibrary_form_title')}</label>
                        <input type="text" name="title" id="title" value={formData.title} onChange={handleChange} placeholder={t('manage_elibrary_form_title_placeholder')} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white" required />
                    </div>
                    <div>
                        <label htmlFor="author" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('manage_elibrary_form_author')}</label>
                        <input type="text" name="author" id="author" value={formData.author} onChange={handleChange} placeholder={t('manage_elibrary_form_author_placeholder')} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white" required />
                    </div>
                </div>
                 <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label htmlFor="year" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('manage_elibrary_form_year')}</label>
                        <input type="number" name="year" id="year" value={formData.year} onChange={handleChange} placeholder={t('manage_elibrary_form_year_placeholder')} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white" required />
                    </div>
                     <div>
                        <label htmlFor="type" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('manage_elibrary_form_type')}</label>
                        <select name="type" id="type" value={formData.type} onChange={handleChange} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white" required>
                            <option value="book">{t('manage_elibrary_form_type_book')}</option>
                            <option value="journal">{t('manage_elibrary_form_type_journal')}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label htmlFor="description" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('manage_elibrary_form_description')}</label>
                    <textarea name="description" id="description" value={formData.description} onChange={handleChange} rows={3} placeholder={t('manage_elibrary_form_description_placeholder')} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white" required />
                </div>
                <div>
                    <label htmlFor="coverUrl" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('manage_elibrary_form_cover_url')}</label>
                    <input type="url" name="coverUrl" id="coverUrl" value={formData.coverUrl} onChange={handleChange} placeholder={t('manage_elibrary_form_cover_url_placeholder')} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white" required />
                </div>
                
                <div className="p-4 border-t border-slate-200 dark:border-slate-700 space-y-4">
                    <label className="block text-sm font-medium text-slate-600 dark:text-slate-300">{t('manage_elibrary_form_digital_source_label')}</label>
                    <div className="flex items-center gap-4">
                        <label className="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="sourceType" value="link" checked={sourceType === 'link'} onChange={() => setSourceType('link')} className="w-4 h-4 text-brand-emerald-600 bg-slate-100 border-slate-300 focus:ring-brand-emerald-500" />
                            <span className="text-slate-700 dark:text-slate-300">{t('manage_elibrary_form_radio_link')}</span>
                        </label>
                        <label className="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="sourceType" value="upload" checked={sourceType === 'upload'} onChange={() => setSourceType('upload')} className="w-4 h-4 text-brand-emerald-600 bg-slate-100 border-slate-300 focus:ring-brand-emerald-500" />
                            <span className="text-slate-700 dark:text-slate-300">{t('manage_elibrary_form_radio_upload')}</span>
                        </label>
                    </div>
                    {sourceType === 'link' ? (
                        <div>
                            <label htmlFor="sourceUrl" className="sr-only">URL Sumber</label>
                            <input type="url" id="sourceUrl" value={sourceUrl} onChange={e => setSourceUrl(e.target.value)} placeholder={t('manage_elibrary_form_source_url_placeholder')} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white" />
                        </div>
                    ) : (
                        <div>
                            <label className="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg cursor-pointer border border-slate-300 dark:border-slate-600">
                                <Icon className="w-5 h-5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></Icon>
                                <span className="text-slate-700 dark:text-slate-200">{fileName || t('manage_elibrary_form_file_upload_placeholder')}</span>
                                <input type="file" onChange={handleFileChange} accept=".pdf" className="hidden" />
                            </label>
                        </div>
                    )}
                </div>

                <div className="flex justify-end items-center gap-4 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" onClick={onClose} className="px-6 py-2 rounded-lg bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold">{t('button_cancel')}</button>
                    <button type="submit" className="px-6 py-2 rounded-lg bg-brand-emerald-600 text-white font-semibold">{t('button_save')}</button>
                </div>
            </form>
        </div>
    );
};
