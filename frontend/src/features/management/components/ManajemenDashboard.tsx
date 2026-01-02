import React, { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { Icon } from '@/src/ui/components/Icon';
import { User, Faculty } from '@/types';
import { apiService } from '@/services/apiService';

interface ManajemenDashboardProps {
    currentUser: User;
    navigateTo?: (page: any) => void;
}

const StatCard: React.FC<{value: string, label: string, icon: React.ReactNode, hasBorder?: boolean}> = ({ value, label, icon, hasBorder = true }) => (
    <div className={`flex items-center gap-4 p-4 ${hasBorder ? 'sm:border-e sm:dark:border-slate-700' : ''}`}>
        <div className="p-4 rounded-full bg-slate-10 dark:bg-slate-700 text-brand-emerald-500">
            {icon}
        </div>
        <div>
            <p className="text-3xl font-bold text-slate-800 dark:text-white">{value}</p>
            <p className="text-slate-500 dark:text-slate-400 font-medium">{label}</p>
        </div>
    </div>
);

export const ManajemenDashboard: React.FC<ManajemenDashboardProps> = ({ currentUser }) => {
    const [facultyEnrollmentData, setFacultyEnrollmentData] = useState<any[]>([]);
    const [managementStats, setManagementStats] = useState({
        totalStudents: '0',
        totalLecturers: '0',
        totalFaculties: '0',
        totalBudget: 'Rp 2.1 M'
    });
    const [campusActivities, setCampusActivities] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchDashboardData = async () => {
            try {
                const response = await apiService.getManagementDashboardData();
                // Handle different response formats
                const data = response.data?.data || response.data;
                
                
                // Ensure data exists before setting it
                // Map snake_case keys to camelCase for frontend compatibility
                const stats = data?.stats || {};
                setManagementStats({
                    totalStudents: stats.total_students?.toString() || '0',
                    totalLecturers: stats.total_lecturers?.toString() || '0',
                    totalFaculties: stats.total_faculties?.toString() || '0',
                    totalBudget: stats.total_budget || 'Rp 2.1 M'
                });
                setFacultyEnrollmentData(data?.faculty_enrollment_data || []);
                setCampusActivities(data?.recent_activities || []);
            } catch (error) {
                console.error('Failed to fetch management dashboard data:', error);
                // Fallback to empty state
                setManagementStats({
                    totalStudents: '0',
                    totalLecturers: '0',
                    totalFaculties: '0',
                    totalBudget: 'Rp 2.1 M'
                });
                setFacultyEnrollmentData([]);
                setCampusActivities([]);
            } finally {
                setLoading(false);
            }
        };

        fetchDashboardData();
    }, []);

    if (loading || !managementStats) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-emerald-600"></div>
            </div>
        );
    }

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">Dashboard Manajemen Kampus</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">Selamat datang, {currentUser?.name || 'User'}.</p>
            </div>

             <div className="bg-white dark:bg-slate-800/50 rounded-2xl shadow-md p-6">
<div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div className="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                          <div className="p-4 rounded-full bg-slate-100 dark:bg-slate-700 text-brand-emerald-500">
                               <Icon className="w-8 h-8">
                                   <path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z"/>
                               </Icon>
                          </div>
                          <div>
                              <p className="text-3xl font-bold text-slate-800 dark:text-white">{managementStats.totalStudents}</p>
                              <p className="text-slate-500 dark:text-slate-400 font-medium">Total Mahasiswa</p>
                          </div>
                      </div>
                      
                      <div className="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                          <div className="p-4 rounded-full bg-slate-100 dark:bg-slate-700 text-brand-emerald-500">
                               <Icon className="w-8 h-8">
                                   <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                   <circle cx="12" cy="7" r="4"/>
                               </Icon>
                          </div>
                          <div>
                              <p className="text-3xl font-bold text-slate-800 dark:text-white">{managementStats.totalLecturers}</p>
                              <p className="text-slate-500 dark:text-slate-400 font-medium">Total Dosen</p>
                          </div>
                      </div>
                      
                       <div className="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                           <div className="p-4 rounded-full bg-slate-100 dark:bg-slate-700 text-brand-emerald-500">
                                <Icon className="w-8 h-8">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </Icon>
                           </div>
                           <div>
                               <p className="text-3xl font-bold text-slate-800 dark:text-white">{managementStats.totalFaculties}</p>
                               <p className="text-slate-500 dark:text-slate-400 font-medium">Total Fakultas</p>
                           </div>
                       </div>
                      
                      <div className="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                          <div className="p-4 rounded-full bg-slate-100 dark:bg-slate-700 text-brand-emerald-500">
                               <Icon className="w-8 h-8">
                                   <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                               </Icon>
                          </div>
                          <div>
                              <p className="text-3xl font-bold text-slate-800 dark:text-white">{managementStats.totalBudget}</p>
                              <p className="text-slate-500 dark:text-slate-400 font-medium">Anggaran Semester</p>
                          </div>
                      </div>
                  </div>
             </div>

<div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                 <div className="lg:col-span-3 bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                       <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">Pendaftaran per Fakultas</h2>
                       <div className="h-96" dir="ltr">
                           <ResponsiveContainer width="100%" height="100%">
                               <BarChart data={facultyEnrollmentData} margin={{ top: 5, right: 20, left: -20, bottom: 5 }}>
                                   <CartesianGrid strokeDasharray="3 3" stroke="rgba(128,0.2)" />
                                   <XAxis dataKey="name" tick={{ fill: 'rgb(100 116 139)', fontSize: 11 }} angle={-25} textAnchor="end" height={50} />
                                   <YAxis tick={{ fill: 'rgb(100 116 139)', fontSize: 12 }} />
                                   <Tooltip contentStyle={{ backgroundColor: 'rgba(30, 41, 59, 0.9)', borderColor: '#475569', color: '#f1f5f9' }}/>
                                   <Bar dataKey="mahasiswa" fill="#10b981" name="Jumlah Mahasiswa" />
                               </BarChart>
                           </ResponsiveContainer>
                       </div>
                   </div>
                  
 

                 <div className="lg:col-span-3 bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                     <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">Aktivitas Terbaru Kampus</h2>
                     <ul className="space-y-4">
                        <li className="flex gap-4">
                            <div className="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-full h-fit">
                                 <Icon className="w-5 h-5 text-blue-500">
                                     <path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z"/>
                                 </Icon>
                            </div>
                            <div>
                                <p className="font-semibold text-slate-800 dark:text-white">Pendaftaran Mahasiswa Baru 2024/2025 dibuka.</p>
                                <p className="text-xs text-slate-500 dark:text-slate-400">1 jam lalu</p>
                            </div>
                        </li>
                         <li className="flex gap-4">
                            <div className="p-2 bg-green-100 dark:bg-green-900/50 rounded-full h-fit">
                                 <Icon className="w-5 h-5 text-green-500">
                                     <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                     <path d="M9.5 9.5 12 12l4.5-4.5"/>
                                 </Icon>
                            </div>
                            <div>
                                <p className="font-semibold text-slate-800 dark:text-white">Fakultas Syariah memenangkan Lomba Debat Nasional.</p>
                                <p className="text-xs text-slate-500 dark:text-slate-400">3 jam lalu</p>
                            </div>
                        </li>
                         <li className="flex gap-4">
                            <div className="p-2 bg-amber-100 dark:bg-amber-900/50 rounded-full h-fit">
                                 <Icon className="w-5 h-5 text-amber-500">
                                     <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                     <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1 2 2h12a2 2 0 0 1 2-2V7Z"/>
                                 </Icon>
                            </div>
                            <div>
                                <p className="font-semibold text-slate-800 dark:text-white">Prof. Dr. Tariq An-Nawawi menerbitkan jurnal baru.</p>
                                <p className="text-xs text-slate-500 dark:text-slate-400">1 hari lalu</p>
                            </div>
                        </li>
                     </ul>
</div>
             </div>
         </div>
     );
 };
