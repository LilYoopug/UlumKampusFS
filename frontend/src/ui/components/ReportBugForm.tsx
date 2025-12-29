import React, { useState } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { Icon } from '@/src/ui/components/Icon';

export const ReportBugForm: React.FC = () => {
    const { t } = useLanguage();
    const [subject, setSubject] = useState('');
    const [description, setDescription] = useState('');
    const [steps, setSteps] = useState('');

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const body = `
Deskripsi Masalah:
${description}

Langkah-langkah untuk Mereproduksi:
${steps || '(tidak disediakan)'}
        `;
        const mailtoLink = `mailto:support@ulumcampus.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        window.location.href = mailtoLink;
    };

    return (
        <div className="space-y-6">
            <div>
                <h3 className="text-xl font-bold text-slate-800 dark:text-white">{t('help_bug_report_title')}</h3>
                <p className="mt-2 text-slate-500 dark:text-slate-400">{t('help_bug_report_desc')}</p>
            </div>
            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label htmlFor="bug-subject" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('help_bug_subject')}</label>
                    <input
                        id="bug-subject"
                        type="text"
                        value={subject}
                        onChange={e => setSubject(e.target.value)}
                        placeholder={t('help_bug_subject_placeholder')}
                        className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                        required
                    />
                </div>
                <div>
                    <label htmlFor="bug-description" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('help_bug_description')}</label>
                    <textarea
                        id="bug-description"
                        value={description}
                        onChange={e => setDescription(e.target.value)}
                        placeholder={t('help_bug_description_placeholder')}
                        rows={5}
                        className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                        required
                    ></textarea>
                </div>
                <div>
                    <label htmlFor="bug-steps" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('help_bug_steps')}</label>
                    <textarea
                        id="bug-steps"
                        value={steps}
                        onChange={e => setSteps(e.target.value)}
                        placeholder={t('help_bug_steps_placeholder')}
                        rows={4}
                        className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                    ></textarea>
                </div>
                <div className="text-end">
                    <button
                        type="submit"
                        className="flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors"
                    >
                        <Icon className="w-5 h-5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></Icon>
                        {t('help_bug_submit')}
                    </button>
                </div>
            </form>
        </div>
    );
};