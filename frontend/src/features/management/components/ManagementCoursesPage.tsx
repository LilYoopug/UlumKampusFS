import React, { useState, useMemo } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { Course } from '@/types';
import { Icon } from '@/src/ui/components/Icon';

// Add declarations for CDN-loaded libraries to the global window object
declare global {
    interface Window {
        jspdf: {
            jsPDF: unknown;
        };
        XLSX: {
            utils: {
                json_to_sheet: (data: unknown) => unknown;
                book_new: () => unknown;
                book_append_sheet: (workbook: unknown, worksheet: unknown, name: string) => void;
            };
            writeFile: (workbook: unknown, filename: string) => void;
        };
    }
}

interface ManagementCoursesPageProps {
    courses: Course[];
}

export const ManagementCoursesPage: React.FC<ManagementCoursesPageProps> = ({ courses }) => {
    const { t } = useLanguage();
    const [searchTerm, setSearchTerm] = useState('');

    const filteredCourses = useMemo(() => {
        return courses.filter(course =>
            course.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
            course.id.toLowerCase().includes(searchTerm.toLowerCase()) ||
            course.instructor.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [courses, searchTerm]);
    
    const handleExportPDF = () => {
        const { jsPDF } = window.jspdf as { jsPDF: unknown };
        const PDFClass = jsPDF as new () => unknown;
        const doc = new PDFClass();
        doc.text("Daftar Mata Kuliah", 14, 16);
        (doc as { autoTable: (options: unknown) => void }).autoTable({
            startY: 22,
            head: [['Kode MK', 'Nama Mata Kuliah', 'Dosen Pengampu', 'SKS', 'Status']],
            body: filteredCourses.map(c => [c.id, c.title, c.instructor, c.sks, c.status]),
        });
        doc.save('daftar-mata-kuliah.pdf');
    };

    const handleExportXLSX = () => {
        const XLSX = window.XLSX as {
            utils: {
                json_to_sheet: (data: unknown) => unknown;
                book_new: () => unknown;
                book_append_sheet: (workbook: unknown, worksheet: unknown, name: string) => void;
            };
            writeFile: (workbook: unknown, filename: string) => void;
        };
        
        const worksheet = XLSX.utils.json_to_sheet(
            filteredCourses.map(c => ({
                'Kode MK': c.id,
                'Nama Mata Kuliah': c.title,
                'Dosen Pengampu': c.instructor,
                'SKS': c.sks,
                'Status': c.status,
            }))
        );
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Mata Kuliah');
        XLSX.writeFile(workbook, 'daftar-mata-kuliah.xlsx');
    };

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('prodi_courses_title')}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">{t('prodi_courses_subtitle')}</p>
            </div>

            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                    <div className="relative flex-grow w-full sm:w-auto">
                        <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3" />
                        </Icon>
                        <input
                            type="text"
                            placeholder="Cari Kode MK, Nama, atau Dosen..."
                            value={searchTerm}
                            onChange={e => setSearchTerm(e.target.value)}
                            className="w-full sm:w-80 ps-10 pe-4 py-2 rounded-full bg-slate-100 dark:bg-slate-700 border border-transparent focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                        />
                    </div>
                    <div className="flex flex-grow sm:flex-grow-0 gap-2">
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
                                <th scope="col" className="px-6 py-3">Kode MK</th>
                                <th scope="col" className="px-6 py-3">Nama Mata Kuliah</th>
                                <th scope="col" className="px-6 py-3">Dosen Pengampu</th>
                                <th scope="col" className="px-6 py-3">SKS</th>
                                <th scope="col" className="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredCourses.length > 0 ? (
                                filteredCourses.map(course => (
                                    <tr key={course.id} className="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600/50">
                                        <td className="px-6 py-4 font-mono">{course.id}</td>
                                        <td className="px-6 py-4 font-medium text-slate-900 dark:text-white">{course.title}</td>
                                        <td className="px-6 py-4">{course.instructor}</td>
                                        <td className="px-6 py-4">{course.sks}</td>
                                        <td className="px-6 py-4">{course.status}</td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={5} className="text-center py-12 text-slate-500 dark:text-slate-400">
                                        Tidak ada mata kuliah yang cocok dengan pencarian Anda.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};