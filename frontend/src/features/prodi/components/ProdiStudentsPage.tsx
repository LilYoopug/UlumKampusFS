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

export const ProdiStudentsPage: React.FC = () => {
    const { t } = useLanguage();
    const [students, setStudents] = useState<User[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('All');

    // Fetch students from API
    useEffect(() => {
        fetchStudents();
    }, []);

    const fetchStudents = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await apiService.getProdiStudents();
            
            console.log('Prodi students API response:', response);
            
            // Handle different response structures
            let studentsList: any[] = [];
            if (Array.isArray(response)) {
                studentsList = response;
            } else if (response && typeof response === 'object') {
                const resp = response as any;
                // Handle Axios response with .data property
                if (resp.data) {
                    const responseData = resp.data;
                    if (Array.isArray(responseData)) {
                        studentsList = responseData;
                    } else if (responseData.success && Array.isArray(responseData.data)) {
                        studentsList = responseData.data;
                    } else if (Array.isArray(responseData.data)) {
                        studentsList = responseData.data;
                    } else if (responseData.students && Array.isArray(responseData.students)) {
                        studentsList = responseData.students;
                    }
                }
                // Handle direct response object
                else if (resp.success && Array.isArray(resp.data)) {
                    studentsList = resp.data;
                } else if (Array.isArray(resp.data)) {
                    studentsList = resp.data;
                } else if (resp.students && Array.isArray(resp.students)) {
                    studentsList = resp.students;
                }
            }
            
            console.log('Extracted students list:', studentsList);
            
            // Transform backend data to match frontend User type
            const transformedStudents: User[] = studentsList.map((student: any) => {
                return {
                    id: student.id || student.student_id || '',
                    name: student.name || '',
                    email: student.email || '',
                    role: 'Mahasiswa', // Prodi students are always students
                    studentId: student.studentId || student.student_id || student.id || '',
                    studentStatus: student.studentStatus || student.student_status || student.status || 'Aktif',
                    avatarUrl: student.avatarUrl || student.avatar_url || student.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(student.name || 'User') + '&background=random',
                    gpa: student.gpa || null,
                    totalSks: student.totalSks || student.total_sks || 0,
                    facultyId: student.facultyId || student.faculty_id || '',
                    majorId: student.majorId || student.major_id || '',
                    joinDate: student.joinDate || student.created_at || student.join_date || null,
                    badges: student.badges || [],
                };
            });
            
            console.log('Transformed students:', transformedStudents);
            setStudents(transformedStudents);
        } catch (err) {
            console.error('Error fetching prodi students:', err);
            setError('Failed to load students. Please try again.');
            setStudents([]);
        } finally {
            setLoading(false);
        }
    };

    const filteredStudents = useMemo(() => {
        return students.filter(student => {
            const searchMatch = student.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                                student.studentId.toLowerCase().includes(searchTerm.toLowerCase());
            const statusMatch = statusFilter === 'All' || student.studentStatus === statusFilter;
            return searchMatch && statusMatch;
        });
    }, [students, searchTerm, statusFilter]);
    
    const handleExportPDF = () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Daftar Mahasiswa", 14, 16);
        (doc as any).autoTable({
            startY: 22,
            head: [['Nama Mahasiswa', 'NIM', 'Status', 'IPK', 'SKS']],
            body: filteredStudents.map(s => [s.name, s.studentId, s.studentStatus, s.gpa?.toFixed(2) ?? '-', s.totalSks ?? '-']),
        });
        doc.save('daftar-mahasiswa.pdf');
    };

    const handleExportXLSX = () => {
        const worksheet = window.XLSX.utils.json_to_sheet(
            filteredStudents.map(s => ({
                'Nama Mahasiswa': s.name,
                'NIM': s.studentId,
                'Status': s.studentStatus,
                'IPK': s.gpa?.toFixed(2) ?? '-',
                'SKS': s.totalSks ?? '-',
            }))
        );
        const workbook = window.XLSX.utils.book_new();
        window.XLSX.utils.book_append_sheet(workbook, worksheet, 'Mahasiswa');
        window.XLSX.writeFile(workbook, 'daftar-mahasiswa.xlsx');
    };

    const statusOptions = [
        { value: 'All', label: t('prodi_student_status_all')},
        { value: 'Aktif', label: t('prodi_student_status_active')},
        { value: 'Cuti', label: t('prodi_student_status_leave')},
        { value: 'Lulus', label: t('prodi_student_status_graduated')},
        { value: 'DO', label: t('prodi_student_status_do')},
    ];

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
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('prodi_students_title')}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">{t('prodi_students_subtitle')}</p>
            </div>

            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4">
                    <div className="relative flex-grow w-full sm:w-auto">
                        <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                        </Icon>
                        <input type="text" placeholder="Cari mahasiswa (nama atau NIM)..." value={searchTerm} onChange={e => setSearchTerm(e.target.value)} className="w-full ps-10 pe-4 py-2 rounded-full bg-slate-10 dark:bg-slate-700 border border-transparent focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white" />
                    </div>
                     <Dropdown 
                         options={statusOptions} 
                         value={statusFilter} 
                         onChange={setStatusFilter} 
                         className="w-full sm:w-auto"
                     />
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
                                <th scope="col" className="px-6 py-3">{t('prodi_table_student_name')}</th>
                                <th scope="col" className="px-6 py-3">{t('prodi_table_student_id')}</th>
                                <th scope="col" className="px-6 py-3">{t('prodi_table_student_status')}</th>
                                <th scope="col" className="px-6 py-3">{t('prodi_table_student_gpa')}</th>
                                <th scope="col" className="px-6 py-3">{t('prodi_table_student_sks')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredStudents.map(student => (
                                <tr key={student.studentId} className="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600/50">
                                    <td className="px-6 py-4 font-medium text-slate-900 dark:text-white flex items-center gap-3">
                                        <img src={student.avatarUrl} alt={student.name} className="w-8 h-8 rounded-full" />
                                        {student.name}
                                    </td>
                                    <td className="px-6 py-4 font-mono">{student.studentId}</td>
                                    <td className="px-6 py-4">{student.studentStatus}</td>
                                    <td className="px-6 py-4">{student.gpa?.toFixed(2) ?? '-'}</td>
                                    <td className="px-6 py-4">{student.totalSks ?? '-'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};
