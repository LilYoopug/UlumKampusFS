import React, { useState, useEffect, useMemo } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { Course, CourseStatus } from '@/types';
import { Icon } from '@/src/ui/components/Icon';
import { ProdiCourseForm } from '@/src/features/prodi/components/ProdiCourseForm';
import { FACULTIES } from '@/constants';
import { apiService } from '@/services/apiService';

// Mapping function to convert frontend string IDs to backend integer IDs
const facultyIdMap: Record<string, number> = {
    'ushuluddin': 1,
    'syariah': 2,
    'ekonomi': 3,
    'tarbiyah': 4,
    'adab': 5,
    'sains': 6,
    'psikologi': 7,
    'pascasarjana': 8,
};

const majorIdMap: Record<string, number> = {
    // Ushuluddin & Dakwah
    'aqidah': 1,
    'tafsir': 2,
    'hadis': 3,
    'perbandingan-agama': 4,
    'kpi': 5,
    // Syariah & Hukum
    'hes': 6,
    'ahwal-syakhshiyyah': 7,
    'siyasah': 8,
    'peradilan-agama': 9,
    // Ekonomi & Manajemen Syariah
    'ekonomi-islam': 10,
    'perbankan-syariah': 11,
    'akuntansi-syariah': 12,
    'manajemen-syariah': 13,
    'keuangan-investasi-syariah': 14,
    // Tarbiyah & Pendidikan Islam
    'pai': 15,
    'pba': 16,
    'pgmi': 17,
    'mpi': 18,
    'tekpen-islami': 19,
    // Adab, Humaniora & Bahasa
    'spi': 20,
    'bsa': 21,
    'english-islamic': 22,
    'islamic-civ': 23,
    // Sains & Inovasi Islami
    'sains-etika': 24,
    'ti-islami': 25,
    'industri-halal': 26,
    'farmasi-halal': 27,
    'kesehatan-syariah': 28,
    // Psikologi & Sosial
    'psikologi-islam': 29,
    'bk-islami': 30,
    'sosiologi-islam': 31,
    'studi-gender': 32,
    // Pascasarjana
    'kajian-kontemporer': 33,
    'fiqh-aqalliyat': 34,
    'islamic-leadership': 35,
};

const getBackendFacultyId = (frontendId: string): number => {
    return facultyIdMap[frontendId] || 1; // Default to 1 if not found
};

const getBackendMajorId = (frontendId: string): number | null => {
    return majorIdMap[frontendId] || null;
};

const getFrontendFacultyId = (backendId: number): string => {
    const entry = Object.entries(facultyIdMap).find(([_, value]) => value === backendId);
    return entry ? entry[0] : 'syariah';
};

const getFrontendMajorId = (backendId: number): string => {
    const entry = Object.entries(majorIdMap).find(([_, value]) => value === backendId);
    return entry ? entry[0] : '';
};

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

export const ProdiCoursesPage: React.FC = () => {
    const { t } = useLanguage();
    const [courses, setCourses] = useState<Course[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editingCourse, setEditingCourse] = useState<Course | null>(null);
    const [deletingCourse, setDeletingCourse] = useState<Course | null>(null);
    const [searchTerm, setSearchTerm] = useState('');

    // Fetch courses from backend API
    useEffect(() => {
        fetchCourses();
    }, []);

    const fetchCourses = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await apiService.getCourses();
            
            console.log('Raw API response:', response);
            
            // Handle different response structures
            let coursesList: any[] = [];
            if (Array.isArray(response)) {
                coursesList = response;
            } else if (response && typeof response === 'object') {
                const resp = response as any;
                if (resp.success && resp.data && Array.isArray(resp.data)) {
                    coursesList = resp.data;
                } else if (resp.data && Array.isArray(resp.data)) {
                    coursesList = resp.data;
                } else if (resp.courses && Array.isArray(resp.courses)) {
                    coursesList = resp.courses;
                }
            }
            
            console.log('Extracted courses list:', coursesList);
            console.log('Number of courses:', coursesList.length);
            
            // Transform backend data to match frontend Course type
            const transformedCourses: Course[] = coursesList.map((course: any) => {
                const transformed: Course = {
                    id: course.id || course.kode_mk || course.code || '',
                    title: course.title || course.nama_mk || course.name || '',
                    instructor: course.instructor || course.dosen_pengampu || course.instructor_name || '',
                    instructorId: course.instructor_id || course.dosen_id,
                    // faculty_id and major_id are already strings in the database
                    facultyId: course.faculty_id || '',
                    majorId: course.major_id || '',
                    sks: course.sks || course.sks_credit || 0,
                    description: course.description || course.deskripsi || '',
                    status: (course.status as CourseStatus) || 'Draft',
                    imageUrl: course.image_url || course.thumbnail || course.imageUrl,
                    mode: course.mode,
                    learningObjectives: course.learning_objectives || course.learningObjectives,
                    syllabus: course.syllabus,
                    modules: course.modules,
                    created_at: course.created_at,
                    updated_at: course.updated_at,
                    instructorAvatarUrl: course.instructor_avatar_url || course.instructorAvatarUrl,
                    instructorBioKey: course.instructor_bio_key || course.instructorBioKey,
                };
                console.log('Transformed course:', transformed);
                return transformed;
            });
            
            console.log('Final transformed courses:', transformedCourses);
            console.log('Setting courses state...');
            setCourses(transformedCourses);
        } catch (err) {
            console.error('Error fetching courses:', err);
            setError('Failed to load courses. Please try again.');
            setCourses([]); // Set empty array on error
        } finally {
            setLoading(false);
        }
    };

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

    const handleConfirmDelete = async () => {
        if (deletingCourse) {
            try {
                setLoading(true);
                setError(null);
                await apiService.deleteCourse(deletingCourse.id);
                await fetchCourses();
                setDeletingCourse(null);
            } catch (err) {
                console.error('Error deleting course:', err);
                setError('Failed to delete course. Please try again.');
            } finally {
                setLoading(false);
            }
        }
    };

    const handleSaveCourse = async (courseData: Course) => {
        try {
            setLoading(true);
            setError(null);
            
            // Transform frontend data to backend format
            // IMPORTANT: faculty_id and major_id must be strings (not integers) to match database schema
            const backendCourseData: any = {
                id: courseData.id, // Primary key
                code: courseData.id, // Course code (same as ID in this implementation)
                name: courseData.title,
                faculty_id: courseData.facultyId || 'syariah', // Use string directly
                major_id: courseData.majorId || null, // Use string directly
                instructor_id: courseData.instructorId ? (typeof courseData.instructorId === 'string' ? parseInt(courseData.instructorId) : courseData.instructorId) : null,
                description: courseData.description || '',
                credit_hours: courseData.sks,
                is_active: courseData.status === 'Published',
                capacity: 50,
                current_enrollment: 0,
                semester: 'Fall',
                year: 2024,
                schedule: '',
                room: '',
            };
            
            console.log('Sending to backend:', backendCourseData);
            
            if (editingCourse) {
                await apiService.updateCourse(editingCourse.id, backendCourseData);
            } else {
                await apiService.createCourse(backendCourseData);
            }
            
            await fetchCourses();
            setIsFormOpen(false);
            setEditingCourse(null);
        } catch (err) {
            console.error('Error saving course:', err);
            setError('Failed to save course. Please try again.');
        } finally {
            setLoading(false);
        }
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
                {error && (
                    <div className="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-200 px-4 py-3 rounded-lg">
                        <div className="flex items-center">
                            <Icon className="w-5 h-5 mr-2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </Icon>
                            {error}
                        </div>
                    </div>
                )}

                <div>
                    <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('prodi_courses_title')}</h1>
                    <p className="text-slate-500 dark:text-slate-400 mt-1">{t('prodi_courses_subtitle')}</p>
                </div>

                <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                    {loading && courses.length === 0 ? (
                        <div className="flex justify-center items-center py-12">
                            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-emerald-600"></div>
                        </div>
                    ) : (
                        <>
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
                                                    {courses.length === 0 
                                                        ? 'Belum ada mata kuliah. Klik "Tambah MK Baru" untuk membuat mata kuliah pertama Anda.'
                                                        : 'Tidak ada mata kuliah yang cocok dengan pencarian Anda.'
                                                    }
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </>
                    )}
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
