import React, { useState, useMemo } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { FaqItem, UserRole } from '../types';
import { Icon } from './Icon';

const ALL_FAQS: FaqItem[] = [
    // General FAQs (for all roles)
    {
        q: 'faq_q_general_theme',
        a: 'faq_a_general_theme',
        roles: ['Mahasiswa', 'Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin'],
    },
    {
        q: 'faq_q_general_profile',
        a: 'faq_a_general_profile',
        roles: ['Mahasiswa', 'Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin'],
    },
    {
        q: 'faq_q_general_login',
        a: 'faq_a_general_login',
        roles: ['Mahasiswa', 'Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin'],
    },
    {
        q: 'faq_q_general_password',
        a: 'faq_a_general_password',
        roles: ['Mahasiswa', 'Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin'],
    },
    {
        q: 'faq_q_general_language',
        a: 'faq_a_general_language',
        roles: ['Mahasiswa', 'Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin'],
    },
    {
        q: 'faq_q_general_notifications',
        a: 'faq_a_general_notifications',
        roles: ['Mahasiswa', 'Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin'],
    },
    {
        q: 'faq_q_help_support',
        a: 'faq_a_help_support',
        roles: ['Mahasiswa', 'Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin'],
    },
    
    // Student-specific FAQs
    {
        q: 'faq_q_student_grades',
        a: 'faq_a_student_grades',
        roles: ['Mahasiswa'],
    },
    {
        q: 'faq_q_student_assignment',
        a: 'faq_a_student_assignment',
        roles: ['Mahasiswa'],
    },
    {
        q: 'faq_q_student_courses',
        a: 'faq_a_student_courses',
        roles: ['Mahasiswa'],
    },
    {
        q: 'faq_q_student_certificates',
        a: 'faq_a_student_certificates',
        roles: ['Mahasiswa'],
    },
    {
        q: 'faq_q_student_library',
        a: 'faq_a_student_library',
        roles: ['Mahasiswa'],
    },
    
    // Lecturer-specific FAQs
    {
        q: 'faq_q_dosen_course',
        a: 'faq_a_dosen_course',
        roles: ['Dosen'],
    },
    {
        q: 'faq_q_dosen_module',
        a: 'faq_a_dosen_module',
        roles: ['Dosen'],
    },
    {
        q: 'faq_q_dosen_gradebook',
        a: 'faq_a_dosen_gradebook',
        roles: ['Dosen'],
    },
    {
        q: 'faq_q_dosen_students',
        a: 'faq_a_dosen_students',
        roles: ['Dosen'],
    },
    
    // Prodi Admin-specific FAQs
    {
        q: 'faq_q_prodi_courses',
        a: 'faq_a_prodi_courses',
        roles: ['Prodi Admin'],
    },
    {
        q: 'faq_q_prodi_students',
        a: 'faq_a_prodi_students',
        roles: ['Prodi Admin'],
    },
    {
        q: 'faq_q_prodi_lecturers',
        a: 'faq_a_prodi_lecturers',
        roles: ['Prodi Admin'],
    },
    
    // Admin-specific FAQs
    {
        q: 'faq_q_admin_user',
        a: 'faq_a_admin_user',
        roles: ['Super Admin'],
    },
    {
        q: 'faq_q_admin_dashboard',
        a: 'faq_a_admin_dashboard',
        roles: ['Super Admin'],
    },
    {
        q: 'faq_q_admin_reports',
        a: 'faq_a_admin_reports',
        roles: ['Manajemen Kampus', 'Super Admin'],
    },
];

const AccordionItem: React.FC<{ title: string; children: React.ReactNode }> = ({ title, children }) => {
    const [isOpen, setIsOpen] = useState(false);
    return (
        <div className="border-b border-slate-200 dark:border-slate-700">
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="w-full flex justify-between items-center text-start py-5 font-semibold text-slate-800 dark:text-white"
            >
                <span>{title}</span>
                <Icon className={`w-6 h-6 transition-transform transform ${isOpen ? 'rotate-180' : ''} text-slate-500 dark:text-slate-400 flex-shrink-0`}>
                    <path d="m6 9 6 6 6-6"/>
                </Icon>
            </button>
            <div className={`grid transition-all duration-300 ease-in-out ${isOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'}`}>
                <div className="overflow-hidden">
                    <div className="pb-5 text-slate-500 dark:text-slate-400 leading-relaxed">
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
};


export const FaqContent: React.FC<{ currentUserRole: UserRole }> = ({ currentUserRole }) => {
    const { t } = useLanguage();
    const [searchTerm, setSearchTerm] = useState('');

    const filteredFaqs = useMemo(() => {
        return ALL_FAQS.filter(faq => {
            const matchesRole = faq.roles.includes(currentUserRole);
            const matchesSearch = searchTerm === '' || 
                t(faq.q).toLowerCase().includes(searchTerm.toLowerCase()) || 
                t(faq.a).toLowerCase().includes(searchTerm.toLowerCase());
            return matchesRole && matchesSearch;
        });
    }, [searchTerm, currentUserRole, t]);

    return (
        <div className="space-y-6">
            <div className="relative">
                <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></Icon>
                <input
                    type="text"
                    placeholder={t('help_search_faq')}
                    value={searchTerm}
                    onChange={e => setSearchTerm(e.target.value)}
                    className="w-full h-full ps-10 pe-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-60 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                />
            </div>
            
            <div className="mt-6">
                {filteredFaqs.length > 0 ? (
                    filteredFaqs.map(item => (
                        <AccordionItem key={item.q} title={t(item.q)}>
                            <p>{t(item.a)}</p>
                        </AccordionItem>
                    ))
                ) : (
                    <div className="text-center py-16 text-slate-500">
                        <Icon className="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></Icon>
                        <p className="mt-4 font-semibold">{t('help_no_faq')}</p>
                    </div>
                )}
            </div>
        </div>
    );
};
