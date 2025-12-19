import React, { useState, useMemo } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { User, Course } from '../types';
import { Icon } from './Icon';
import { Dropdown } from './Dropdown';

// Add declarations for CDN-loaded libraries to the global window object
declare global {
    interface Window {
        jspdf: any;
        XLSX: any;
    }
}

interface ProdiLecturersPageProps {
    users: User[];
    courses: Course[];
}

export const ProdiLecturersPage: React.FC<ProdiLecturersPageProps> = ({ users, courses }) => {
    const { t } = useLanguage();
    const [searchTerm, setSearchTerm] = useState('');

    const lecturers = useMemo(() => {
        return users.filter(u => u.role === 'Dosen').map(lecturer => {
            const courseCount = courses.filter(c => c.instructor === lecturer.name).length;
            return { ...lecturer, courseCount };
        });
    }, [users, courses]);

    const filteredLecturers = useMemo(() => {
        return lecturers.filter(lecturer =>
            lecturer.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            lecturer.email.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [lecturers, searchTerm]);
    
    const handleExportPDF = () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Daftar Dosen", 14, 16);
        (doc as any).autoTable({
            startY: 22,
            head: [['Nama Dosen', 'Email', 'Jumlah MK']],
            body: filteredLecturers.map(l => [l.name, l.email, l.courseCount]),
        });
        doc.save('daftar-dosen.pdf');
    };

    const handleExportXLSX = () => {
        const worksheet = window.XLSX.utils.json_to_sheet(
            filteredLecturers.map(l => ({
                'Nama Dosen': l.name,
                'Email': l.email,
                'Jumlah MK': l.courseCount,
            }))
        );
        const workbook = window.XLSX.utils.book_new();
        window.XLSX.utils.book_append_sheet(workbook, worksheet, 'Dosen');
        window.XLSX.writeFile(workbook, 'daftar-dosen.xlsx');
    };

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('prodi_lecturers_title')}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">{t('prodi_lecturers_subtitle')}</p>
            </div>

            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                 <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4">
                    <div className="relative flex-grow w-full sm:w-auto">
                        <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                        </Icon>
                        <input type="text" placeholder="Cari nama atau email dosen..." value={searchTerm} onChange={e => setSearchTerm(e.target.value)} className="w-full ps-10 pe-4 py-2 rounded-full bg-slate-100 dark:bg-slate-700 border border-transparent focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white" />
                    </div>
                     <div className="flex-grow flex justify-start sm:justify-end items-center gap-2 w-full sm:w-auto">
                         <button onClick={handleExportPDF} className="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/50 rounded-full transition-colors" title={t('admin_export_pdf')}>
                            <Icon className="w-5 h-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12v-1h3v1"/><path d="M10 15h3"/><path d="M10 18h3"/></Icon>
                        </button>
                        <button onClick={handleExportXLSX} className="p-2 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/50 rounded-full transition-colors" title={t('admin_export_xlsx')}>
                             <Icon className="w-5 h-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-4_M15 14h-3_M9 14h3"/><path d="M10.5 10.5 13.5 7.5_M13.5 10.5 10.5 7.5"/></Icon>
                        </button>
                    </div>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                        <thead className="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                            <tr>
                                <th scope="col" className="px-6 py-3">{t('prodi_table_lecturer_name')}</th>
                                <th scope="col" className="px-6 py-3">{t('prodi_table_lecturer_email')}</th>
                                <th scope="col" className="px-6 py-3">{t('prodi_table_lecturer_courses')}</th>
                            </tr>
                        </thead>
                         <tbody>
                             {filteredLecturers.map(lecturer => (
                                 <tr key={lecturer.id || lecturer.email} className="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600/50">
                                     <td className="px-6 py-4 font-medium text-slate-900 dark:text-white flex items-center gap-3">
                                         <img src={lecturer.avatarUrl} alt={lecturer.name} className="w-8 h-8 rounded-full" />
                                         {lecturer.name}
                                     </td>
                                     <td className="px-6 py-4">{lecturer.email}</td>
                                     <td className="px-6 py-4">{lecturer.courseCount}</td>
                                 </tr>
                             ))}
                         </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};
