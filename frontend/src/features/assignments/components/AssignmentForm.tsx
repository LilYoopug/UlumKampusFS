import React, { useState } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { Assignment } from '@/types';
import { Icon } from '@/src/ui/components/Icon';

interface AssignmentFormProps {
    courseId: string;
    onSave: (assignment: Omit<Assignment, 'id' | 'submissions'>) => void;
    onClose: () => void;
    initialData?: Assignment;
}

export const AssignmentForm: React.FC<AssignmentFormProps> = ({ courseId, onSave, onClose, initialData }) => {
    const { t } = useLanguage();
    const [title, setTitle] = useState(initialData?.title || '');
    const [description, setDescription] = useState(initialData?.description || '');
    const [dueDate, setDueDate] = useState(initialData?.dueDate ? new Date(initialData.dueDate).toISOString().split('T')[0] + 'T23:59' : '');
    const [category, setCategory] = useState<'Tugas' | 'Ujian'>(initialData?.category || 'Tugas');
    const [files, setFiles] = useState<File[]>([]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!title || !dueDate) return;
        // The dueDate from datetime-local input is in local timezone, so we use it directly
        onSave({
            courseId,
            title,
            description,
            dueDate: new Date(dueDate).toISOString(),
            files: files.map(f => ({ name: f.name, url: '#' })), // Placeholder URL
            category,
            type: 'file', // Default to file type
        });
        onClose();
    };
    
    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            setFiles(prev => [...prev, ...Array.from(e.target.files)]);
        }
    };
    
     const removeFile = (fileName: string) => {
        setFiles(prev => prev.filter(f => f.name !== fileName));
    };
    
    const handleDateChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const dateValue = e.target.value;
        if (dateValue) {
            setDueDate(`${dateValue}T23:59`); // Always set time to 23:59
        }
    };

    return (
        <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] lg:max-h-none overflow-hidden" onClick={e => e.stopPropagation()}>
            <form onSubmit={handleSubmit} className="flex flex-col h-full">
                <div className="p-4 lg:p-6 border-b border-slate-200 dark:border-slate-700 flex-shrink-0">
                    <h3 className="text-lg lg:text-xl font-semibold text-slate-800 dark:text-white">
                        {t('assignment_form_title')}
                    </h3>
                </div>
                <div className="p-4 lg:p-6 space-y-4 lg:space-y-4 flex-1 overflow-y-auto">
                    <div>
                        <label htmlFor="assignment-title" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1.5">{t('assignment_form_label_title')}</label>
                        <input type="text" id="assignment-title" value={title} onChange={e => setTitle(e.target.value)} placeholder={t('assignment_form_label_title')} className="w-full px-4 py-3 lg:py-2 rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 text-base lg:text-sm" required />
                    </div>
                    <div>
                        <label htmlFor="assignment-desc" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1.5">{t('assignment_form_label_desc')}</label>
                        <textarea id="assignment-desc" value={description} onChange={e => setDescription(e.target.value)} rows={4} placeholder={t('assignment_form_label_desc')} className="w-full px-4 py-3 lg:py-2 rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-brand-emerald-50 focus:border-brand-emerald-50 dark:focus:ring-brand-emerald-500 dark:focus:border-brand-emerald-500 text-base lg:text-sm"></textarea>
                    </div>
                     <div>
                         <label htmlFor="assignment-due-date" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1.5">Tanggal Jatuh Tempo</label>
                         <input type="date" id="assignment-due-date" value={dueDate ? dueDate.split('T')[0] : ''} onChange={handleDateChange} className="w-full px-4 py-3 lg:py-2 rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:focus:ring-brand-emerald-500 dark:focus:border-brand-emerald-500 text-base lg:text-sm" required />
                         <p className="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Waktu jatuh tempo otomatis diatur ke pukul 23:59 (11:59 PM)</p>
                     </div>
                     <div>
                         <label htmlFor="assignment-category" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1.5">{t('assignment_form_label_category')}</label>
                         <select id="assignment-category" value={category} onChange={e => setCategory(e.target.value as any)} className="w-full px-4 py-3 lg:py-2 rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:focus:ring-brand-emerald-500 dark:focus:border-brand-emerald-500 text-base lg:text-sm">
                             <option value="Tugas" className="dark:bg-slate-700 dark:text-white">{t('assignment_form_category_assignment')}</option>
                             <option value="Ujian" className="dark:bg-slate-700 dark:text-white">{t('assignment_form_category_exam')}</option>
                         </select>
                     </div>
                     <div>
                        <label className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1.5">{t('assignment_form_label_attachments')}</label>
                        <div className="mt-2 flex justify-center px-4 lg:px-6 pt-4 lg:pt-5 pb-4 lg:pb-6 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-md">
                            <div className="space-y-2 text-center">
                                <Icon className="mx-auto h-10 w-10 lg:h-12 lg:w-12 text-slate-400"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.59a2 2 0 0 1-2.83-2.83l.79-.79"/></Icon>
                                <div className="flex flex-col sm:flex-row items-center justify-center gap-1 text-sm text-slate-600 dark:text-slate-400">
                                    <label htmlFor="file-upload" className="relative cursor-pointer bg-white dark:bg-slate-800 rounded-md font-medium text-brand-emerald-600 hover:text-brand-emerald-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-brand-emerald-500 px-2 py-1">
                                        <span>{t('assignment_form_upload_cta')}</span>
                                        <input id="file-upload" name="file-upload" type="file" className="sr-only" onChange={handleFileChange} multiple />
                                    </label>
                                    <p className="hidden sm:block">{t('assignment_form_drag_cta')}</p>
                                </div>
                                <p className="text-xs text-slate-500 dark:text-slate-500">{t('assignment_form_file_size_limit')}</p>
                            </div>
                        </div>
                        {files.length > 0 && (
                            <ul className="mt-3 lg:mt-4 space-y-2">
                                {files.map(file => (
                                    <li key={file.name} className="flex items-center justify-between p-3 lg:p-2 bg-slate-100 dark:bg-slate-700 rounded-md text-sm">
                                        <span className="truncate flex-1 mr-2">{file.name}</span>
                                        <button type="button" onClick={() => removeFile(file.name)} className="text-red-500 hover:text-red-700 p-2 lg:p-1 rounded-full active:scale-[0.95] transition-transform">
                                            <Icon className="w-5 h-5 lg:w-4 lg:h-4"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></Icon>
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
                <div className="flex flex-col-reverse sm:flex-row justify-end items-stretch sm:items-center gap-2 sm:gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 rounded-b-lg flex-shrink-0">
                    <button type="button" onClick={onClose} className="px-4 py-3 lg:py-2 rounded-lg bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold hover:bg-slate-300 dark:hover:bg-slate-500 active:scale-[0.98] transition-all">{t('button_cancel')}</button>
                    <button type="submit" className="px-4 py-3 lg:py-2 rounded-lg bg-brand-emerald-600 text-white font-semibold hover:bg-brand-emerald-700 active:scale-[0.98] transition-all">{t('button_save')}</button>
                </div>
            </form>
        </div>
    );
};
