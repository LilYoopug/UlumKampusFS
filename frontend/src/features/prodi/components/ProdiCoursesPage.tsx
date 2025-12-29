import React, { useState, useMemo } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { Course } from '@/types';
import { Icon } from '@/src/ui/components/Icon';
import { ProdiCourseForm } from '@/src/features/prodi/components/ProdiCourseForm';
import { FACULTIES } from '@/constants';

// Add declarations for CDN-loaded libraries to the global window object
declare global {
    interface Window {
        jspdf: any;
        XLSX: any;
    }
}

const ConfirmationModal: React.FC<{
    isOpen: boolean;
    onClose: () => void;
    onConfirm: () => void;
    title: string;
    message: string;
}> = ({ isOpen, onClose, onConfirm, title, message }) => {
    const { t } = useLanguage();
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 p-4" onClick={onClose} role="dialog" aria-modal="true">
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-md" onClick={e => e.stopPropagation()}>
                <div className="p-6">
                    <div className="flex items-start gap-4">
                        <div className="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/50 sm:mx-0">
                             <Icon className="h-6 w-6 text-red-600 dark:text-red-400">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </Icon>
                        </div>
                        <div className="mt-0 text-start">
                            <h3 className="text-lg leading-6 font-bold text-slate-900 dark:text-white" id="modal-title">
                                {title}
                            </h3>
                            <div className="mt-2">
                                <p className="text-sm text-slate-500 dark:text-slate-400">
                                    {message}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="flex justify-end items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 rounded-b-lg">
                    <button type="button" onClick={onClose} className="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">{t('button_cancel')}</button>
                    <button type="button" onClick={onConfirm} className="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors">{t('button_confirm_delete')}</button>
                </div>
            </div>
        </div>
    );
};

interface ProdiCoursesPageProps {
    courses: Course[];
    onCreateCourse: (course: Course) => void;
    onUpdateCourse: (course: Course) => void;
    onDeleteCourse: (courseId: string) => void;
}

export const ProdiCoursesPage: React.FC<ProdiCoursesPageProps> = ({ courses, onCreateCourse, onUpdateCourse, onDeleteCourse }) => {
    const { t } = useLanguage();
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editingCourse, setEditingCourse] = useState<Course | null>(null);
    const [deletingCourse, setDeletingCourse] = useState<Course | null>(null);
    const [searchTerm, setSearchTerm] = useState('');

    const filteredCourses = useMemo(() => {
        return courses.filter(course =>
            course.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
            course.id.toLowerCase().includes(searchTerm.toLowerCase()) ||
            course.instructor.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [courses, searchTerm]);
    
    const handleAddNew = () => {
        setEditingCourse(null);
        setIsFormOpen(true);
    };

    const handleEdit = (course: Course) => {
        setEditingCourse(course);
        setIsFormOpen(true);
    };

    const handleDelete = (course: Course) => {
        setDeletingCourse(course);
    };

    const handleConfirmDelete = () => {
        if (deletingCourse) {
            onDeleteCourse(deletingCourse.id);
            setDeletingCourse(null);
        }
    };

    const handleSaveCourse = (courseData: Course) => {
        if (editingCourse) {
            onUpdateCourse({ ...editingCourse, ...courseData });
        } else {
            onCreateCourse(courseData);
        }
        setIsFormOpen(false);
        setEditingCourse(null);
    };

    const handleExportPDF = () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Daftar Mata Kuliah", 14, 16);
        (doc as any).autoTable({
            startY: 22,
            head: [['Kode MK', 'Nama Mata Kuliah', 'Dosen Pengampu', 'SKS', 'Status']],
            body: filteredCourses.map(c => [c.id, c.title, c.instructor, c.sks, c.status]),
        });
        doc.save('daftar-mata-kuliah.pdf');
    };

    const handleExportXLSX = () => {
        const worksheet = window.XLSX.utils.json_to_sheet(
            filteredCourses.map(c => ({
                'Kode MK': c.id,
                'Nama Mata Kuliah': c.title,
                'Dosen Pengampu': c.instructor,
                'SKS': c.sks,
                'Status': c.status,
            }))
        );
        const workbook = window.XLSX.utils.book_new();
        window.XLSX.utils.book_append_sheet(workbook, worksheet, 'Mata Kuliah');
        window.XLSX.writeFile(workbook, 'daftar-mata-kuliah.xlsx');
    };

    return (
        <>
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
                            <button onClick={handleExportPDF} className="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/50 rounded-full transition-colors" title="Export PDF">
                                <Icon className="w-5 h-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12v-1h3v1"/><path d="M10 15h3"/><path d="M10 18h3"/></Icon>
                            </button>
                            <button onClick={handleExportXLSX} className="p-2 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/50 rounded-full transition-colors" title="Export XLSX">
                                <Icon className="w-5 h-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-4_M15 14h-3_M9 14h3"/><path d="M10.5 10.5 13.5 7.5_M13.5 10.5 10.5 7.5"/></Icon>
                            </button>
                            <button onClick={handleAddNew} className="flex-shrink-0 flex items-center gap-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">
                                <Icon className="w-5 h-5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></Icon>
                                Tambah MK Baru
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
                                    <th scope="col" className="px-6 py-3 text-end">Aksi</th>
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
                                            <td className="px-6 py-4">
                                                <div className="flex items-center justify-end gap-4">
                                                    <button onClick={() => handleEdit(course)} className="font-medium text-brand-emerald-600 dark:text-brand-emerald-500 hover:underline">Edit</button>
                                                    <button onClick={() => handleDelete(course)} className="font-medium text-red-600 dark:text-red-500 hover:underline">Hapus</button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={6} className="text-center py-12 text-slate-500 dark:text-slate-400">
                                            Tidak ada mata kuliah yang cocok dengan pencarian Anda.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            {isFormOpen && (
                <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={() => setIsFormOpen(false)} role="dialog" aria-modal="true">
                    <div className="w-full max-w-2xl" onClick={e => e.stopPropagation()}>
                        <ProdiCourseForm
                            onSave={handleSaveCourse}
                            onCancel={() => { setIsFormOpen(false); setEditingCourse(null); }}
                            initialData={editingCourse}
                        />
                    </div>
                </div>
            )}

            <ConfirmationModal
                isOpen={!!deletingCourse}
                onClose={() => setDeletingCourse(null)}
                onConfirm={handleConfirmDelete}
                title="Hapus Mata Kuliah?"
                message={`Apakah Anda yakin ingin menghapus mata kuliah "${deletingCourse?.title}"? Tindakan ini tidak dapat diurungkan.`}
            />
        </>
    );
};