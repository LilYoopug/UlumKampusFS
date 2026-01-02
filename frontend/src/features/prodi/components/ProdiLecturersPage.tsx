import React, { useState, useMemo, useEffect } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { User } from '@/types';
import { Icon } from '@/src/ui/components/Icon';
import { Dropdown } from '@/src/features/shared/components/Dropdown';
import { apiService } from '@/services/apiService';

// Add declarations for CDN-loaded libraries to the global window object
declare global {
    interface Window {
        jspdf: any;
        XLSX: any;
    }
}

interface LecturerWithCourseCount extends User {
    courseCount: number;
}

export const ProdiLecturersPage: React.FC = () => {
    const { t } = useLanguage();
    const [lecturers, setLecturers] = useState<LecturerWithCourseCount[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [searchTerm, setSearchTerm] = useState('');

    // Fetch lecturers from API
    useEffect(() => {
        fetchLecturers();
    }, []);

    const fetchLecturers = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await apiService.getProdiLecturers();
            
            
            // Handle different response structures
            let lecturersList: any[] = [];
            if (Array.isArray(response)) {
                lecturersList = response;
            } else if (response && typeof response === 'object') {
                const resp = response as any;
                // Handle Axios response with .data property
                if (resp.data) {
                    const responseData = resp.data;
                    if (Array.isArray(responseData)) {
                        lecturersList = responseData;
                    } else if (responseData.success && Array.isArray(responseData.data)) {
                        lecturersList = responseData.data;
                    } else if (Array.isArray(responseData.data)) {
                        lecturersList = responseData.data;
                    }
                }
                // Handle direct response object
                else if (resp.success && Array.isArray(resp.data)) {
                    lecturersList = resp.data;
                } else if (Array.isArray(resp.data)) {
                    lecturersList = resp.data;
                }
            }
            
            
            // Transform backend data to match frontend User type
            const transformedLecturers: LecturerWithCourseCount[] = lecturersList.map((lecturer: any) => {
                return {
                    id: lecturer.id || '',
                    name: lecturer.name || '',
                    email: lecturer.email || '',
                    role: 'Dosen',
                    avatarUrl: lecturer.avatarUrl || lecturer.avatar_url || lecturer.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(lecturer.name || 'User') + '&background=random',
                    facultyId: lecturer.facultyId || lecturer.faculty_id || '',
                    majorId: lecturer.majorId || lecturer.major_id || '',
                    courseCount: lecturer.courseCount || 0,
                };
            });
            
            setLecturers(transformedLecturers);
        } catch (err) {
            console.error('Error fetching prodi lecturers:', err);
            setError('Failed to load lecturers. Please try again.');
            setLecturers([]);
        } finally {
            setLoading(false);
        }
    };

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

    if (loading) {
        return (
            <div className="flex justify-center items-center py-12">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-emerald-600"></div>
            </div>
        );
    }

    return (
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
