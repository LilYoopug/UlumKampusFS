import React, { useState, useEffect, useMemo } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { Course, CourseStatus, Faculty, User } from '../types';
import { Icon } from './Icon';
import { FACULTIES, ALL_USERS } from '../constants';

interface ProdiCourseFormProps {
    onSave: (courseData: Course) => void;
    onCancel: () => void;
    initialData?: Course | null;
}

export const ProdiCourseForm: React.FC<ProdiCourseFormProps> = ({ onSave, onCancel, initialData }) => {
    const { t } = useLanguage();
    const isEditMode = !!initialData;

    const [faculties, setFaculties] = useState<Faculty[]>([]);
    const [lecturers, setLecturers] = useState<User[]>([]);
    const [formData, setFormData] = useState({
       id: '',
       title: '',
       instructor: '',
       sks: 3,
       facultyId: '', // Will be set after fetching faculties
       majorId: '',
       status: 'Draft' as CourseStatus
   });

   useEffect(() => {
       // Set initial data if provided
       if (initialData) {
           setFormData({
               id: initialData.id,
               title: initialData.title,
               instructor: initialData.instructor,
               sks: initialData.sks,
               facultyId: initialData.facultyId,
               majorId: initialData.majorId || '',
               status: initialData.status,
           });
       } else {
           // Set default facultyId if available
           if (FACULTIES.length > 0) {
               setFormData(prev => ({
                   ...prev,
                   facultyId: FACULTIES[0].id
               }));
           }
       }
   }, [initialData]);
   
   // Set faculties and lecturers from constants
   useEffect(() => {
       setFaculties(FACULTIES);
       // Filter users to get only lecturers (dosen)
       const dosenUsers = ALL_USERS.filter(user => user.role === 'Dosen');
       setLecturers(dosenUsers);
   }, []);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        // Prevent changing the ID field (for security)
        if (name === 'id') return;
        setFormData(prev => ({ ...prev, [name]: name === 'sks' ? parseInt(value) : value }));
        if (name === 'facultyId') {
            setFormData(prev => ({ ...prev, majorId: '' }));
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Generate ID automatically if creating a new course
        let courseData = { ...formData };
        if (!isEditMode && !courseData.id) {
            // Generate a course ID based on the first 3 letters of the title and a random number
            const titlePrefix = courseData.title.substring(0, 3).toUpperCase().replace(/\s+/g, '');
            const randomNum = Math.floor(100 + Math.random() * 900); // 3-digit number
            courseData.id = `${titlePrefix}${randomNum}`;
        }
        
        onSave(courseData as Course);
    };

    const availableMajors = useMemo(() => {
       if (!formData.facultyId) return [];
       const selectedFaculty = faculties.find(f => f.id === formData.facultyId);
       return selectedFaculty ? selectedFaculty.majors : [];
     }, [formData.facultyId, faculties]);
    

    return (
        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div className="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h1 className="text-2xl font-bold text-slate-800 dark:text-white">
                    {/* FIX: Use translation keys for consistency. */}
                    {isEditMode ? t('edit_course_title') : t('create_course_title')}
                </h1>
                <button onClick={onCancel} className="p-2 rounded-full text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <Icon className="w-6 h-6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></Icon>
                </button>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-6">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div className="sm:col-span-2">
                        <label htmlFor="title" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Nama Mata Kuliah</label>
                        <input
                            type="text"
                            name="title"
                            id="title"
                            value={formData.title}
                            onChange={handleChange}
                            className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                            placeholder="Masukkan Nama Mata Kuliah"
                            required
                        />
                    </div>
                </div>
                 <div>
                    <label htmlFor="instructor" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Dosen Pengampu</label>
                    <select
                        name="instructor"
                        id="instructor"
                        value={formData.instructor}
                        onChange={handleChange}
                        className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                        required
                    >
                        <option value="">Pilih Dosen Pengampu</option>
                        {lecturers.map(lecturer => (
                            <option key={lecturer.studentId} value={lecturer.name}>
                                {lecturer.name}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
                     <div>
                        <label htmlFor="sks" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">SKS</label>
                        <input
                            type="number"
                            name="sks"
                            id="sks"
                            value={formData.sks}
                            onChange={handleChange}
                            min="1"
                            max="6"
                            className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                            placeholder="Masukkan Jumlah SKS"
                            required
                        />
                    </div>
                    <div className="sm:col-span-2">
                         <label htmlFor="status" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Status</label>
                        <select
                            name="status"
                            id="status"
                            value={formData.status}
                            onChange={handleChange}
                            className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                        >
                            <option value="Draft">Draft</option>
                            <option value="Published">Published</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                </div>
                 <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label htmlFor="facultyId" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Fakultas</label>
                        <select name="facultyId" id="facultyId" value={formData.facultyId} onChange={handleChange} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white" required>
                            <option value="">Pilih Fakultas</option>
                            {faculties.map(faculty => <option key={faculty.id} value={faculty.id}>{faculty.name}</option>)}
                        </select>
                    </div>
                     <div>
                        <label htmlFor="majorId" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Jurusan</label>
                        <select name="majorId" id="majorId" value={formData.majorId} onChange={handleChange} disabled={availableMajors.length === 0} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 disabled:bg-slate-200 dark:disabled:bg-slate-800 disabled:cursor-not-allowed text-slate-800 dark:text-white">
                            <option value="">Pilih Jurusan (Opsional)</option>
                            {availableMajors.map(major => <option key={major.id} value={major.id}>{major.name}</option>)}
                        </select>
                    </div>
                </div>

                <div className="flex justify-end items-center gap-4 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <button
                        type="button"
                        onClick={onCancel}
                        className="px-6 py-2 rounded-lg bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors"
                    >
                        {t('button_cancel')}
                    </button>
                    <button
                        type="submit"
                        className="px-6 py-2 rounded-lg bg-brand-emerald-600 text-white font-semibold hover:bg-brand-emerald-700 transition-colors"
                    >
                        {/* FIX: Corrected translation key and added key for create button. */}
                        {isEditMode ? t('create_course_button_save_changes') : t('create_course_button_create')}
                    </button>
                </div>
            </form>
        </div>
    );
};