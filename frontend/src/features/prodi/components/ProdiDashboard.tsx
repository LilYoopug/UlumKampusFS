import React, { useMemo, useState, useEffect } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { Icon } from '@/src/ui/components/Icon';
import { Course, User, Faculty } from '@/types';
import { facultyAPI } from '@/services/apiService';

const StatCard: React.FC<{value: string, label: string, icon: React.ReactNode}> = ({ value, label, icon }) => (
    <div className="bg-white dark:bg-slate-800/50 p-5 rounded-2xl shadow-md flex items-center space-x-4 rtl:space-x-reverse">
        <div className="p-3 rounded-full bg-slate-100 dark:bg-slate-700">
            {icon}
        </div>
        <div className="text-start">
            <p className="text-2xl font-bold text-slate-800 dark:text-white">{value}</p>
            <p className="text-slate-500 dark:text-slate-400 text-sm font-medium">{label}</p>
        </div>
    </div>
);

const enrollmentData = [
  { name: '2021', 'mahasiswa': 120 },
  { name: '2022', 'mahasiswa': 150 },
  { name: '2023', 'mahasiswa': 145 },
  { name: '2024', 'mahasiswa': 180 },
];

interface ProdiDashboardProps {
    courses: Course[];
    users: User[];
    currentUser: User;
}

export const ProdiDashboard: React.FC<ProdiDashboardProps> = ({ courses, users, currentUser }) => {
    const myFacultyId = currentUser.facultyId;
    const [faculties, setFaculties] = useState<Faculty[]>([]);
    
    // Fetch faculties from API
    useEffect(() => {
        const fetchFaculties = async () => {
            try {
                const response = await facultyAPI.getAll();
                const responseData = response.data as any;
                const data = responseData?.data || responseData || [];
                if (Array.isArray(data)) {
                    setFaculties(data);
                }
            } catch (error) {
                console.error('Failed to fetch faculties:', error);
            }
        };
        fetchFaculties();
    }, []);
    
    const myFaculty = useMemo(() => faculties.find(f => f.id === myFacultyId), [myFacultyId, faculties]);
    
    const prodiStats = useMemo(() => {
        const prodiStudents = users.filter(u => (u.role === 'Mahasiswa' || u.role === 'student') && u.facultyId === myFacultyId);
        const totalStudents = prodiStudents.length;
        const totalCourses = courses.length;

        const studentsWithGpa = prodiStudents.filter(s => s.gpa !== undefined && s.gpa > 0);
        const avgGPA = studentsWithGpa.length > 0 
            ? (studentsWithGpa.reduce((sum, s) => sum + s.gpa!, 0) / studentsWithGpa.length).toFixed(2)
            : '0.00';

        return { totalStudents, totalCourses, avgGPA };
    }, [users, courses, myFacultyId]);
    
    const gradRate = "92%"; // Mock data

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">Dashboard Prodi {myFaculty?.name || 'Admin'}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">Selamat datang, {currentUser.name}.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard icon={<Icon className="w-8 h-8 text-blue-500"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></Icon>} value={prodiStats.totalStudents.toString()} label="Total Mahasiswa Prodi" />
                <StatCard icon={<Icon className="w-8 h-8 text-green-500"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></Icon>} value={prodiStats.totalCourses.toString()} label="Total Mata Kuliah Kampus" />
                <StatCard icon={<Icon className="w-8 h-8 text-amber-500"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></Icon>} value={prodiStats.avgGPA} label="IPK Rata-Rata Prodi" />
                <StatCard icon={<Icon className="w-8 h-8 text-red-500"><path d="M15.5 2H8.6c-.4 0-.8.2-1.1.5-.3.3-.5.7-.5 1.1V21c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V8l-6-6z"/><path d="M15 2v5h5"/></Icon>} value={gradRate} label="Tingkat Kelulusan" />
            </div>

            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">Tren Pendaftaran Mahasiswa</h2>
                <div className="h-80" dir="ltr">
                    <ResponsiveContainer width="100%" height="100%">
                        <LineChart data={enrollmentData} margin={{ top: 5, right: 20, left: -10, bottom: 5 }}>
                            <CartesianGrid strokeDasharray="3 3" stroke="rgba(128,128,128,0.2)" />
                            <XAxis dataKey="name" tick={{ fill: 'rgb(100 116 139)', fontSize: 12 }} />
                            <YAxis tick={{ fill: 'rgb(100 116 139)', fontSize: 12 }} />
                            <Tooltip contentStyle={{ backgroundColor: 'rgba(30, 41, 59, 0.9)', borderColor: '#475569', color: '#f1f5f9' }} />
                            <Legend />
                            <Line type="monotone" dataKey="mahasiswa" stroke="#10b981" strokeWidth={2} activeDot={{ r: 8 }} />
                        </LineChart>
                    </ResponsiveContainer>
                </div>
            </div>
        </div>
    );
};
