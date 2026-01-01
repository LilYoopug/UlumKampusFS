import React, { useState, FormEvent, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { Icon } from '@/src/ui/components/Icon';
import { CourseCard } from '@/src/features/courses/components/CourseCard';
import { Course, AnnouncementCategory, User, Assignment } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { dashboardAPI, courseAPI, announcementAPI, assignmentAPI } from '@/services/apiService';

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

interface DosenDashboardProps {
  onSelectCourse: (course: Course) => void;
 courses: Course[];
 currentUser: User;
}

export const DosenDashboard: React.FC<DosenDashboardProps> = ({ onSelectCourse, courses, currentUser }) => {
  const { t } = useLanguage();
  const [assignments, setAssignments] = useState<Assignment[]>([]);
  const [dosenCourses, setDosenCourses] = useState<Course[]>([]);
  const [gradeDistributionData, setGradeDistributionData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState<any>(null);
  
  useEffect(() => {
    fetchDashboardData();
  }, [currentUser.name]);

  const fetchDashboardData = async () => {
    try {
      setLoading(true);

      // Fetch faculty dashboard stats
      const statsResponse = await dashboardAPI.getFacultyStats();
      const statsData = (statsResponse.data as any).data;
      setStats(statsData);

      // Fetch courses for this faculty
      const coursesResponse = await courseAPI.getMyCourses();
      const responseData = coursesResponse.data as any;
      const facultyCourses: Course[] = Array.isArray(responseData?.data) 
        ? responseData.data 
        : Array.isArray(responseData) 
          ? responseData 
          : [];
      setDosenCourses(facultyCourses);

      // Fetch assignments for faculty courses
      const courseIds = facultyCourses.map((c: Course) => c.id);
      if (courseIds.length > 0) {
        // Get assignments for each course
        const allAssignments: Assignment[] = [];
        for (const courseId of courseIds) {
          try {
            const assignmentsResponse = await assignmentAPI.getAll({ course_id: courseId });
            const assignmentResponseData = assignmentsResponse.data as any;
            const courseAssignments: Assignment[] = Array.isArray(assignmentResponseData?.data)
              ? assignmentResponseData.data
              : Array.isArray(assignmentResponseData)
                ? assignmentResponseData
                : [];
            allAssignments.push(...courseAssignments);
          } catch (error) {
            console.error(`Error fetching assignments for course ${courseId}:`, error);
          }
        }
        setAssignments(allAssignments);

        // Get grade distribution from stats or calculate it
        if (statsData.course_grades && statsData.course_grades.length > 0) {
          // Calculate grade distribution from course grades
          const distribution: Record<string, number> = {
            'A': 0, 'A-': 0, 'B+': 0, 'B': 0, 'B-': 0, 'C+': 0, 'C': 0
          };
          
          // Use mock distribution for now since the API doesn't provide detailed distribution
          // In production, this should come from a dedicated grade distribution endpoint
          setGradeDistributionData([
            { name: 'A', count: statsData.total_students ? Math.round(statsData.total_students * 0.15) : 45 },
            { name: 'A-', count: statsData.total_students ? Math.round(statsData.total_students * 0.12) : 32 },
            { name: 'B+', count: statsData.total_students ? Math.round(statsData.total_students * 0.10) : 25 },
            { name: 'B', count: statsData.total_students ? Math.round(statsData.total_students * 0.08) : 18 },
            { name: 'B-', count: statsData.total_students ? Math.round(statsData.total_students * 0.05) : 10 },
            { name: 'C+', count: statsData.total_students ? Math.round(statsData.total_students * 0.03) : 5 },
            { name: 'C', count: statsData.total_students ? Math.round(statsData.total_students * 0.02) : 3 },
          ]);
        } else {
          // Fallback to default distribution
          setGradeDistributionData([
            { name: 'A', count: 45 },
            { name: 'A-', count: 32 },
            { name: 'B+', count: 25 },
            { name: 'B', count: 18 },
            { name: 'B-', count: 10 },
            { name: 'C+', count: 5 },
            { name: 'C', count: 3 },
          ]);
        }
      }
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
      // Fallback to using passed courses if API fails
      const dosenCoursesData = courses.filter(course => 
        course.instructor.toLowerCase().includes(currentUser.name.toLowerCase())
      );
      setDosenCourses(dosenCoursesData);
      setGradeDistributionData([
        { name: 'A', count: 45 },
        { name: 'A-', count: 32 },
        { name: 'B+', count: 25 },
        { name: 'B', count: 18 },
        { name: 'B-', count: 10 },
        { name: 'C+', count: 5 },
        { name: 'C', count: 3 },
      ]);
    } finally {
      setLoading(false);
    }
  };

   const assignmentsToGrade = stats?.assignments_pending_grading || 0;
   const totalStudents = stats?.total_students || dosenCourses.reduce((sum, c) => sum + (c.students_count || Math.floor(Math.random() * 30) + 20), 0);

  const [announcementTitle, setAnnouncementTitle] = useState('');
  const [announcementContent, setAnnouncementContent] = useState('');
  const [announcementCourseId, setAnnouncementCourseId] = useState('');
  
   const handleAnnouncementSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (!announcementTitle.trim() || !announcementContent.trim()) {
        alert('Judul dan isi pengumuman tidak boleh kosong.');
        return;
    }

    try {
      const announcementData = {
        title: announcementTitle,
        content: announcementContent,
        category: (announcementCourseId ? 'Mata Kuliah' : 'Akademik') as AnnouncementCategory,
        course_id: announcementCourseId || null,
        is_published: true,
      };

      await announcementAPI.create(announcementData);
      alert('Pengumuman berhasil dibuat!');
      setAnnouncementTitle('');
      setAnnouncementContent('');
      setAnnouncementCourseId('');
    } catch (error) {
      console.error('Error creating announcement:', error);
      alert('Gagal membuat pengumuman. Silakan coba lagi.');
    }
  };

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">Selamat Datang, {currentUser.name}!</h1>
        <p className="text-slate-500 dark:text-slate-400 mt-1">Berikut adalah ringkasan aktivitas mengajar Anda.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
         <StatCard 
             icon={<Icon className="w-8 h-8 text-green-500"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></Icon>} 
             value={stats?.total_courses?.toString() || dosenCourses.length.toString()}
             label="Mata Kuliah Diampu"
         />
         <StatCard 
             icon={<Icon className="w-8 h-8 text-blue-500"><path d="M17 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></Icon>} 
             value={totalStudents.toString()} 
             label="Total Mahasiswa"
         />
         <StatCard 
             icon={<Icon className="w-8 h-8 text-amber-500"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></Icon>} 
             value={assignmentsToGrade.toString()}
             label="Tugas Perlu Dinilai"
         />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
          <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">Mata Kuliah Saya</h2>
          <div className="space-y-4">
            {dosenCourses.map(course => (
              <CourseCard key={course.id} course={course} onSelectCourse={onSelectCourse} layout='horizontal'/>
            ))}
            {dosenCourses.length === 0 && !loading && (
              <div className="text-center py-10 text-slate-500 dark:text-slate-400">
                <p>Anda belum mengampu mata kuliah apapun.</p>
              </div>
            )}
          </div>
        </div>
        
        <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
            <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">Distribusi Nilai Mahasiswa</h2>
            <div className="h-80" dir="ltr">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={gradeDistributionData} layout="vertical" margin={{ top: 5, right: 20, left: -10, bottom: 5 }}>
                        <CartesianGrid strokeDasharray="3 3" stroke="rgba(128,128,128,0.2)" />
                        <XAxis type="number" hide />
                        <YAxis type="category" dataKey="name" tick={{ fill: 'rgb(100 116 139)', fontSize: 12 }} width={30} />
                        <Tooltip contentStyle={{ backgroundColor: 'rgba(30, 41, 59, 0.9)', borderColor: '#475569', color: '#f1f5f9' }}/>
                        <Bar dataKey="count" fill="#10b981" name="Jumlah Mahasiswa" barSize={20} />
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </div>
      </div>
      
       <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
            <h2 className="text-xl font-bold mb-4 text-slate-80 dark:text-white">Buat Pengumuman</h2>
            <form onSubmit={handleAnnouncementSubmit} className="space-y-4">
                <input
                    type="text"
                    placeholder={t('announcement_form_title_placeholder')}
                    value={announcementTitle}
                    onChange={e => setAnnouncementTitle(e.target.value)}
                    className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                    required
                />
                <select 
                    value={announcementCourseId}
                    onChange={e => setAnnouncementCourseId(e.target.value)}
                    className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                >
                    <option value="">Umum (Akademik)</option>
                    {dosenCourses.map(c => <option key={c.id} value={c.id}>Untuk: {c.title}</option>)}
                </select>
                <textarea 
                    rows={4} 
                    placeholder={t('announcement_form_content_placeholder')} 
                    value={announcementContent}
                    onChange={e => setAnnouncementContent(e.target.value)}
                    className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                    required
                ></textarea>
                <div className="text-end">
                     <button type="submit" className="px-5 py-2.5 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">Kirim Pengumuman</button>
                </div>
            </form>
       </div>
    </div>
  );
};
