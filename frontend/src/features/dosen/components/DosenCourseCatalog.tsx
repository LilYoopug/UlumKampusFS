import React, { useState, useEffect, useMemo } from 'react';
import { Course, Faculty, Major, Page, User } from '@/types';
import { Icon } from '@/src/ui/components/Icon';
import { useLanguage } from '@/contexts/LanguageContext';
import { courseAPI, assignmentAPI, enrollmentAPI } from '@/services/apiService';

interface DosenCourseCatalogProps {
  onSelectCourse: (course: Course, params?: any) => void;
  onEditCourse: (course: Course) => void;
  currentUser: User;
  navigateTo: (page: Page) => void;
}

interface CourseStats {
  courseId: string;
  enrolledCount: number;
  pendingAssignments: number;
  pendingGrading: number;
  averageProgress: number;
}

const DosenCourseCatalog: React.FC<DosenCourseCatalogProps> = ({ 
  onSelectCourse, 
  onEditCourse, 
  currentUser, 
  navigateTo 
}) => {
  const { t } = useLanguage();
  const [courses, setCourses] = useState<Course[]>([]);
  const [courseStats, setCourseStats] = useState<Record<string, CourseStats>>({});
  const [selectedFaculties, setSelectedFaculties] = useState<string[]>([]);
  const [selectedMajors, setSelectedMajors] = useState<string[]>([]);
  const [selectedSemester, setSelectedSemester] = useState<string>('');
  const [selectedYear, setSelectedYear] = useState<string>('');
  const [selectedStatus, setSelectedStatus] = useState<string>('');
  const [searchTerm, setSearchTerm] = useState('');
  const [sortBy, setSortBy] = useState('name-asc');
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [loading, setLoading] = useState(true);
  const [statsLoading, setStatsLoading] = useState(false);
  const [faculties, setFaculties] = useState<Faculty[]>([]);
  
  const semesterOptions = [
    { value: '', label: 'Semua Semester' },
    { value: 'Ganjil', label: 'Semester Ganjil' },
    { value: 'Genap', label: 'Semester Genap' },
    { value: 'Pendek', label: 'Semester Pendek' },
  ];

  const currentYear = new Date().getFullYear();
  const yearOptions = [
    { value: '', label: 'Semua Tahun' },
    { value: (currentYear).toString(), label: `${currentYear}` },
    { value: (currentYear - 1).toString(), label: `${currentYear - 1}` },
    { value: (currentYear + 1).toString(), label: `${currentYear + 1}` },
  ];

  const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Non-Aktif' },
    { value: 'archived', label: 'Diarsipkan' },
  ];

  const sortOptions = [
    { value: 'name-asc', label: 'Nama (A-Z)' },
    { value: 'name-desc', label: 'Nama (Z-A)' },
    { value: 'code-asc', label: 'Kode (A-Z)' },
    { value: 'students-desc', label: 'Mahasiswa Terbanyak' },
    { value: 'students-asc', label: 'Mahasiswa Terendah' },
    { value: 'created-desc', label: 'Terbaru' },
  ];

  // Fetch courses for the current dosen
  useEffect(() => {
    fetchCourses();
  }, [currentUser.id]);

  // Fetch course statistics
  useEffect(() => {
    if (courses.length > 0) {
      fetchCourseStats();
    }
  }, [courses]);

  const fetchCourses = async () => {
    try {
      setLoading(true);
      const response = await courseAPI.getMyCourses();
      const responseData = response.data as any;
      const coursesData: Course[] = Array.isArray(responseData?.data)
        ? responseData.data
        : Array.isArray(responseData)
          ? responseData
          : [];
      setCourses(coursesData);
    } catch (error) {
      console.error('Error fetching courses:', error);
      setCourses([]);
    } finally {
      setLoading(false);
    }
  };

  const fetchCourseStats = async () => {
    try {
      setStatsLoading(true);
      const stats: Record<string, CourseStats> = {};

      for (const course of courses) {
        try {
          // Get enrollments count
          const enrollmentsResponse = await enrollmentAPI.getByCourse(course.id);
          const enrollmentsData = enrollmentsResponse.data as any;
          const enrollments = Array.isArray(enrollmentsData?.data)
            ? enrollmentsData.data
            : Array.isArray(enrollmentsData)
              ? enrollmentsData
              : [];
          
          const enrolledCount = enrollments.filter((e: any) => e.status === 'enrolled').length;

          // Get assignments
          const assignmentsResponse = await assignmentAPI.getAll({ course_id: course.id });
          const assignmentsData = assignmentsResponse.data as any;
          const assignments = Array.isArray(assignmentsData?.data)
            ? assignmentsData.data
            : Array.isArray(assignmentsData)
              ? assignmentsData
              : [];

          const pendingAssignments = assignments.filter((a: any) => 
            new Date(a.due_date) > new Date() && a.is_published
          ).length;

          // Calculate average progress
          const progressSum = enrollments.reduce((sum: number, e: any) => sum + (e.progress || 0), 0);
          const averageProgress = enrollments.length > 0 ? Math.round(progressSum / enrollments.length) : 0;

          stats[course.id] = {
            courseId: course.id,
            enrolledCount,
            pendingAssignments,
            pendingGrading: 0, // Would need separate endpoint for grading stats
            averageProgress,
          };
        } catch (error) {
          console.error(`Error fetching stats for course ${course.id}:`, error);
          stats[course.id] = {
            courseId: course.id,
            enrolledCount: 0,
            pendingAssignments: 0,
            pendingGrading: 0,
            averageProgress: 0,
          };
        }
      }

      setCourseStats(stats);
    } catch (error) {
      console.error('Error fetching course stats:', error);
    } finally {
      setStatsLoading(false);
    }
  };

  // Filter and sort courses
  const filteredCourses = useMemo(() => {
    let result = [...courses];

    // Apply search
    if (searchTerm) {
      const search = searchTerm.toLowerCase();
      result = result.filter(course =>
        course.title.toLowerCase().includes(search) ||
        course.code.toLowerCase().includes(search) ||
        course.instructor.toLowerCase().includes(search)
      );
    }

    // Apply faculty filter
    if (selectedFaculties.length > 0) {
      result = result.filter(course => selectedFaculties.includes(course.facultyId));
    }

    // Apply major filter
    if (selectedMajors.length > 0) {
      result = result.filter(course => course.majorId && selectedMajors.includes(course.majorId));
    }

    // Apply semester filter
    if (selectedSemester) {
      result = result.filter(course => course.semester === selectedSemester);
    }

    // Apply year filter
    if (selectedYear) {
      result = result.filter(course => course.year.toString() === selectedYear);
    }

    // Apply status filter
    if (selectedStatus) {
      result = result.filter(course => {
        if (selectedStatus === 'active') return course.is_active;
        if (selectedStatus === 'inactive') return !course.is_active;
        if (selectedStatus === 'archived') return false; // Would need actual archived field
        return true;
      });
    }

    // Apply sorting
    result.sort((a, b) => {
      const [key, order] = sortBy.split('-');
      let valA: any, valB: any;

      switch (key) {
        case 'name':
          valA = a.title.toLowerCase();
          valB = b.title.toLowerCase();
          break;
        case 'code':
          valA = a.code.toLowerCase();
          valB = b.code.toLowerCase();
          break;
        case 'students':
          valA = courseStats[a.id]?.enrolledCount || 0;
          valB = courseStats[b.id]?.enrolledCount || 0;
          break;
        case 'created':
          valA = new Date(a.createdAt || 0).getTime();
          valB = new Date(b.createdAt || 0).getTime();
          break;
        default:
          valA = a.title.toLowerCase();
          valB = b.title.toLowerCase();
      }

      if (valA < valB) return order === 'asc' ? -1 : 1;
      if (valA > valB) return order === 'asc' ? 1 : -1;
      return 0;
    });

    return result;
  }, [courses, searchTerm, selectedFaculties, selectedMajors, selectedSemester, selectedYear, selectedStatus, sortBy, courseStats]);

  const totalStats = useMemo(() => {
    return {
      totalCourses: courses.length,
      totalStudents: Object.values(courseStats).reduce((sum, stat) => sum + stat.enrolledCount, 0),
      totalAssignments: Object.values(courseStats).reduce((sum, stat) => sum + stat.pendingAssignments, 0),
      activeCourses: courses.filter(c => c.is_active).length,
    };
  }, [courses, courseStats]);

  const handleToggleStatus = async (course: Course) => {
    try {
      await courseAPI.toggleStatus(course.id);
      // Refresh courses
      fetchCourses();
    } catch (error) {
      console.error('Error toggling course status:', error);
      alert('Gagal mengubah status mata kuliah');
    }
  };

  const renderGridView = () => (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      {filteredCourses.map(course => {
        const stats = courseStats[course.id];
        return (
          <div
            key={course.id}
            className="bg-white dark:bg-slate-800/50 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow cursor-pointer group"
          >
            <div className="relative">
              <img 
                src={course.imageUrl || 'https://via.placeholder.com/400x200'} 
                alt={course.title}
                className="w-full h-40 object-cover"
              />
              <div className="absolute top-2 right-2 flex gap-2">
                <span className={`px-2 py-1 text-xs font-semibold rounded-full ${
                  course.is_active 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                    : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300'
                }`}>
                  {course.is_active ? 'Aktif' : 'Non-Aktif'}
                </span>
              </div>
              <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-3">
                <p className="text-white font-bold text-lg truncate">{course.code}</p>
              </div>
            </div>
            
            <div className="p-4">
              <h3 className="font-bold text-slate-800 dark:text-white text-lg mb-1 line-clamp-2">
                {course.title}
              </h3>
              <p className="text-sm text-slate-500 dark:text-slate-400 mb-3 line-clamp-2">
                {course.description}
              </p>
              
              <div className="flex items-center gap-2 mb-3">
                <img 
                  src={course.instructorAvatarUrl || 'https://via.placeholder.com/32'} 
                  alt={course.instructor}
                  className="w-6 h-6 rounded-full"
                />
                <span className="text-xs text-slate-600 dark:text-slate-400">{course.instructor}</span>
              </div>
              
              {stats && (
                <div className="grid grid-cols-2 gap-2 mb-3">
                  <div className="flex items-center gap-1 text-xs text-slate-600 dark:text-slate-400">
                    <Icon className="w-4 h-4 text-blue-500">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                      <circle cx="12" cy="7" r="4"/>
                    </Icon>
                    {stats.enrolledCount} Mahasiswa
                  </div>
                  <div className="flex items-center gap-1 text-xs text-slate-600 dark:text-slate-400">
                    <Icon className="w-4 h-4 text-amber-500">
                      <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                    </Icon>
                    {stats.pendingAssignments} Tugas
                  </div>
                </div>
              )}
              
              <div className="flex gap-2">
                <button
                  onClick={() => onSelectCourse(course)}
                  className="flex-1 px-3 py-2 bg-brand-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-brand-emerald-700 transition-colors"
                >
                  Lihat Detail
                </button>
                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    onEditCourse(course);
                  }}
                  className="px-3 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors"
                >
                  <Icon className="w-4 h-4">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </Icon>
                </button>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );

  const renderListView = () => (
    <div className="space-y-4">
      {filteredCourses.map(course => {
        const stats = courseStats[course.id];
        return (
          <div
            key={course.id}
            className="bg-white dark:bg-slate-800/50 rounded-xl shadow-md p-4 hover:shadow-lg transition-shadow cursor-pointer group"
          >
            <div className="flex items-start gap-4">
              <img 
                src={course.imageUrl || 'https://via.placeholder.com/100x100'} 
                alt={course.title}
                className="w-24 h-24 object-cover rounded-lg flex-shrink-0"
              />
              
              <div className="flex-1 min-w-0">
                <div className="flex items-start justify-between gap-4 mb-2">
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 mb-1">
                      <span className="text-sm font-semibold text-brand-emerald-600 dark:text-brand-emerald-400">
                        {course.code}
                      </span>
                      <span className={`px-2 py-0.5 text-xs font-semibold rounded-full ${
                        course.is_active 
                          ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                          : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300'
                      }`}>
                        {course.is_active ? 'Aktif' : 'Non-Aktif'}
                      </span>
                    </div>
                    <h3 className="font-bold text-slate-800 dark:text-white text-lg">
                      {course.title}
                    </h3>
                  </div>
                  
                  <div className="flex gap-2 flex-shrink-0">
                    <button
                      onClick={() => onSelectCourse(course)}
                      className="px-4 py-2 bg-brand-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-brand-emerald-700 transition-colors"
                    >
                      Lihat Detail
                    </button>
                    <button
                      onClick={(e) => {
                        e.stopPropagation();
                        onEditCourse(course);
                      }}
                      className="px-3 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors"
                    >
                      <Icon className="w-4 h-4">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </Icon>
                    </button>
                  </div>
                </div>
                
                <p className="text-sm text-slate-500 dark:text-slate-400 mb-3 line-clamp-2">
                  {course.description}
                </p>
                
                <div className="flex flex-wrap items-center gap-4 text-sm">
                  <div className="flex items-center gap-2">
                    <img 
                      src={course.instructorAvatarUrl || 'https://via.placeholder.com/24'} 
                      alt={course.instructor}
                      className="w-6 h-6 rounded-full"
                    />
                    <span className="text-slate-600 dark:text-slate-400">{course.instructor}</span>
                  </div>
                  
                  {stats && (
                    <>
                      <div className="flex items-center gap-1 text-slate-600 dark:text-slate-400">
                        <Icon className="w-4 h-4 text-blue-500">
                          <path d="M17 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                          <circle cx="12" cy="7" r="4"/>
                        </Icon>
                        {stats.enrolledCount} Mahasiswa
                      </div>
                      <div className="flex items-center gap-1 text-slate-600 dark:text-slate-400">
                        <Icon className="w-4 h-4 text-amber-500">
                          <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                        </Icon>
                        {stats.pendingAssignments} Tugas Aktif
                      </div>
                      <div className="flex items-center gap-1 text-slate-600 dark:text-slate-400">
                        <Icon className="w-4 h-4 text-green-500">
                          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                          <polyline points="22 4 12 14.01 9 11.01"/>
                        </Icon>
                        {stats.averageProgress}% Progress Rata-rata
                      </div>
                    </>
                  )}
                  
                  <div className="flex items-center gap-1 text-slate-600 dark:text-slate-400">
                    <Icon className="w-4 h-4 text-purple-500">
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                      <line x1="16" y1="2" x2="16" y2="6"/>
                      <line x1="8" y1="2" x2="8" y2="6"/>
                      <line x1="3" y1="10" x2="21" y2="10"/>
                    </Icon>
                    {course.semester} {course.year}
                  </div>
                  
                  <div className="flex items-center gap-1 text-slate-600 dark:text-slate-400">
                    <Icon className="w-4 h-4 text-brand-emerald-500">
                      <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                      <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </Icon>
                    {course.sks} SKS
                  </div>
                </div>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-brand-emerald-500"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-3xl font-bold text-slate-800 dark:text-white">Katalog Mata Kuliah</h1>
          <p className="text-slate-500 dark:text-slate-400 mt-1">Kelola semua mata kuliah yang Anda ampu</p>
        </div>
        <button
          onClick={() => navigateTo('create-course')}
          className="flex items-center gap-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors flex-shrink-0"
        >
          <Icon className="w-5 h-5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></Icon>
          Buat Mata Kuliah Baru
        </button>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white dark:bg-slate-800/50 p-4 rounded-xl shadow-md">
          <div className="flex items-center gap-3">
            <div className="p-3 rounded-full bg-blue-100 dark:bg-blue-900/50">
              <Icon className="w-6 h-6 text-blue-500">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
              </Icon>
            </div>
            <div>
              <p className="text-2xl font-bold text-slate-800 dark:text-white">{totalStats.totalCourses}</p>
              <p className="text-sm text-slate-500 dark:text-slate-400">Total Mata Kuliah</p>
            </div>
          </div>
        </div>
        
        <div className="bg-white dark:bg-slate-800/50 p-4 rounded-xl shadow-md">
          <div className="flex items-center gap-3">
            <div className="p-3 rounded-full bg-green-100 dark:bg-green-900/50">
              <Icon className="w-6 h-6 text-green-500">
                <path d="M17 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </Icon>
            </div>
            <div>
              <p className="text-2xl font-bold text-slate-800 dark:text-white">{totalStats.totalStudents}</p>
              <p className="text-sm text-slate-500 dark:text-slate-400">Total Mahasiswa</p>
            </div>
          </div>
        </div>
        
        <div className="bg-white dark:bg-slate-800/50 p-4 rounded-xl shadow-md">
          <div className="flex items-center gap-3">
            <div className="p-3 rounded-full bg-amber-100 dark:bg-amber-900/50">
              <Icon className="w-6 h-6 text-amber-500">
                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
              </Icon>
            </div>
            <div>
              <p className="text-2xl font-bold text-slate-800 dark:text-white">{totalStats.totalAssignments}</p>
              <p className="text-sm text-slate-500 dark:text-slate-400">Tugas Aktif</p>
            </div>
          </div>
        </div>
        
        <div className="bg-white dark:bg-slate-800/50 p-4 rounded-xl shadow-md">
          <div className="flex items-center gap-3">
            <div className="p-3 rounded-full bg-purple-100 dark:bg-purple-900/50">
              <Icon className="w-6 h-6 text-purple-500">
