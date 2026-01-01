import React from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { GuideSection, UserRole } from '@/types';

const ALL_GUIDES: GuideSection[] = [
    {
        title: 'guide_title_maba_start',
        content: 'guide_content_maba_start',
        roles: ['MABA'],
    },
    {
        title: 'guide_title_maba_registrasi',
        content: 'guide_content_maba_registrasi',
        roles: ['MABA'],
    },
    {
        title: 'guide_title_maba_administrasi',
        content: 'guide_content_maba_administrasi',
        roles: ['MABA'],
    },
    {
        title: 'guide_title_maba_pengaturan',
        content: 'guide_content_maba_pengaturan',
        roles: ['MABA'],
    },
    {
        title: 'guide_title_maba_bantuan',
        content: 'guide_content_maba_bantuan',
        roles: ['MABA'],
    },
    {
        title: 'guide_title_start',
        content: 'guide_content_start',
        roles: ['Mahasiswa', 'Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin'],
    },
    {
        title: 'guide_title_assignment_student',
        content: 'guide_content_assignment_student',
        roles: ['Mahasiswa'],
    },
    {
        title: 'guide_title_course_dosen',
        content: 'guide_content_course_dosen',
        roles: ['Dosen', 'Prodi Admin', 'Super Admin'],
    },
];

export const UserGuideContent: React.FC<{ currentUserRole: UserRole }> = ({ currentUserRole }) => {
    const { t } = useLanguage();

    const relevantGuides = ALL_GUIDES.filter(guide => guide.roles.includes(currentUserRole));

    return (
        <div className="space-y-8">
            {relevantGuides.map(guide => (
                <div key={guide.title} className="bg-slate-50 dark:bg-slate-900/50 p-6 rounded-lg border border-slate-200 dark:border-slate-700">
                    <h3 className="text-xl font-bold text-slate-800 dark:text-white">{t(guide.title)}</h3>
                    <div className="mt-4 text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                        {t(guide.content)}
                    </div>
                </div>
            ))}
        </div>
    );
};
