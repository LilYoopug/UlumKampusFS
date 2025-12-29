import React, { useEffect, useMemo, useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { Icon } from '@/src/ui/components/Icon';
import { CourseCard } from '@/src/features/courses/components/CourseCard';
import { Badge, Faculty } from '@/types';
import { Course, Page, Announcement, User } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { timeAgo } from '@/utils/time';
import { ANNOUNCEMENTS_DATA, FACULTIES } from '@/constants';

const gradeToPoint: Record<string, number> = {
  'A': 4.0, 'A-': 3.7, 'B+': 3.3, 'B': 3.0, 'B-': 2.7,
  'C+': 2.3, 'C': 2.0, 'D': 1.0, 'E': 0.0,
};

const StatCard: React.FC<{value: string, label: string, icon: React.ReactNode}> = ({ value, label, icon }) => (
    <div className="bg-white dark:bg-slate-800/50 p-5 rounded-2xl shadow-md flex items-center space-x-4 rtl:space-x-reverse">
        <div className="p-3 rounded-full bg-slate-10 dark:bg-slate-700">
            {icon}
        </div>
        <div className="text-start">
            <p className="text-2xl font-bold text-slate-800 dark:text-white">{value}</p>
            <p className="text-slate-500 dark:text-slate-400 text-sm font-medium">{label}</p>
        </div>
    </div>
);


export const Dashboard: React.FC<{ 
  currentUser: User;
  onSelectCourse: (course: Course) => void, 
  courses: Course[], 
  navigateTo: (page: Page, params?: any) => void,
  announcements: Announcement[] 
}> = ({ currentUser, onSelectCourse, courses, navigateTo, announcements }) => {
  const { t, language } = useLanguage();
 const [announcementsState, setAnnouncements] = useState<Announcement[]>(announcements);
  const [faculties, setFaculties] = useState<Faculty[]>(FACULTIES);
  const coursesInProgress = courses.filter(c => c.progress > 0 && c.progress < 100);
  
  useEffect(() => {
    // Use mock data directly instead of API calls
    setAnnouncements(ANNOUNCEMENTS_DATA);
    setFaculties(FACULTIES);
  }, []);
  
   const academicStats = useMemo(() => {
    const completedCourses = courses.filter(c => c.progress === 100 && c.gradeLetter);
    const totalSks = completedCourses.reduce((sum, course) => sum + course.sks, 0);
    const totalPoints = completedCourses.reduce((sum, course) => {
        const point = gradeToPoint[course.gradeLetter!] || 0;
        return sum + (point * course.sks);
    }, 0);

    const gpa = totalSks > 0 ? (totalPoints / totalSks).toFixed(2) : '0.00';
    
    return {
        coursesCompleted: completedCourses.length,
        totalSks,
        gpa,
    };
  }, [courses]);

  const pastSemesterStats = useMemo(() => {
    const semesterStart = new Date('2024-01-01');
    const semesterEnd = new Date('2024-06-30');

    const completedCourses = courses.filter(c => {
        if (!c.completionDate || !c.gradeLetter) return false;
        const completion = new Date(c.completionDate);
        return completion >= semesterStart && completion <= semesterEnd;
    });

    if (completedCourses.length === 0) {
        return null;
    }

    const totalSks = completedCourses.reduce((sum, course) => sum + course.sks, 0);
    const totalPoints = completedCourses.reduce((sum, course) => {
        const point = gradeToPoint[course.gradeLetter!] || 0;
        return sum + (point * course.sks);
    }, 0);

    const gpa = totalSks > 0 ? (totalPoints / totalSks).toFixed(2) : '0.00';

    return {
        courses: completedCourses,
        coursesCompleted: completedCourses.length,
        totalSks,
        gpa,
    };
  }, [courses]);

  const earnedBadges = useMemo(() => {
    const completedCourses = courses.filter(c => c.progress === 100);
    const completedCourseIds = new Set(completedCourses.map(c => c.id));
    const completedFacultyIds = new Set(completedCourses.map(c => c.facultyId));

    // Define badges dynamically
    const badgeDefinitions: Badge[] = [
      {
        id: 'learner',
        icon: <Icon className="w-8 h-8"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></Icon>,
        titleKey: 'badge_learner_title',
        descriptionKey: 'badge_learner_desc',
      },
      {
        id: 'fiqh',
        icon: <Icon className="w-8 h-8"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></Icon>,
        titleKey: 'badge_fiqh_title',
        descriptionKey: 'badge_fiqh_desc',
      },
      {
        id: 'historian',
        icon: <Icon className="w-8 h-8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></Icon>,
        titleKey: 'badge_historian_title',
        descriptionKey: 'badge_historian_desc',
      },
      {
        id: 'aqidah_foundations',
        icon: <Icon className="w-8 h-8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 2a9 9 0 0 0-9 9c0 4.28 2.5 8 9 12 6.5-4 9-7.72 9-12a9 9 0 0-9-9z"/></Icon>,
        titleKey: 'badge_aqidah_title',
        descriptionKey: 'badge_aqidah_desc',
      },
      {
        id: 'muamalat_expert',
        icon: <Icon className="w-8 h-8"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 7h5a3.5 3.5 0 1 0 7H6"/></Icon>,
        titleKey: 'badge_muamalat_title',
        descriptionKey: 'badge_muamalat_desc',
      },
    ];

    const badges = [];
    if (completedCourses.length > 0) badges.push(badgeDefinitions.find(b => b.id === 'learner'));
    if (completedFacultyIds.has('syariah')) badges.push(badgeDefinitions.find(b => b.id === 'fiqh'));
    if (completedCourseIds.has('AD501')) badges.push(badgeDefinitions.find(b => b.id === 'historian'));

    return badges.filter(Boolean) as Badge[];
  }, [courses]);

  const ibadahData = [
    { name: t('dashboard_chart_senin'), Tilawah: 4, Hafalan: 1 },
    { name: t('dashboard_chart_selasa'), Tilawah: 3, Hafalan: 2 },
    { name: t('dashboard_chart_rabu'), Tilawah: 5, Hafalan: 1 },
    { name: t('dashboard_chart_kamis'), Tilawah: 4, Hafalan: 1 },
    { name: t('dashboard_chart_jumat'), Tilawah: 8, Hafalan: 3 },
    { name: t('dashboard_chart_sabtu'), Tilawah: 2, Hafalan: 0 },
    { name: t('dashboard_chart_ahad'), Tilawah: 6, Hafalan: 2 },
  ];

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('dashboard_greeting')} {currentUser.name.split(' ')[0]}!</h1>
        <p className="text-slate-500 dark:text-slate-400 mt-1">{t('dashboard_welcome')}</p>
      </div>

      <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
        <div className="flex justify-between items-center mb-4">
            <h2 className="text-xl font-bold text-slate-800 dark:text-white">{t('dashboard_recent_announcements')}</h2>
            <button onClick={() => navigateTo('announcements')} className="text-sm font-semibold text-brand-emerald-600 hover:underline dark:text-brand-emerald-40">{t('notifications_view_all')}</button>
        </div>
        <div className="space-y-4">
            {announcementsState.slice(0, 2).map(announcement => (
                <div key={announcement.id} className="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800" onClick={() => navigateTo('announcements', { announcementId: announcement.id })}>
                    <p className="font-semibold text-slate-700 dark:text-slate-200">{announcement.title}</p>
                    <div className="flex items-center gap-2 mt-1">
                        <span className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                            {announcement.category}
                        </span>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Oleh {announcement.authorName} • {timeAgo(announcement.timestamp)}</p>
                    </div>
                </div>
            ))}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <StatCard 
            icon={<Icon className="w-8 h-8 text-green-500"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></Icon>} 
            value={academicStats.coursesCompleted.toString()}
            label={t('profile_courses_completed')}
        />
         <StatCard 
            icon={<Icon className="w-8 h-8 text-blue-500"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></Icon>} 
            value={academicStats.totalSks.toString()} 
            label={t('profile_sks_earned')}
        />
        <StatCard 
            icon={<Icon className="w-8 h-8 text-amber-50"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></Icon>} 
            value={academicStats.gpa}
            label={t('profile_gpa')}
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
          <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">{t('dashboard_continue_learning')}</h2>
          <div className="space-y-4">
            {coursesInProgress.slice(0, 2).map(course => (
              <CourseCard key={course.id} course={course} onSelectCourse={onSelectCourse} layout='horizontal' faculties={faculties}/>
            ))}
             {coursesInProgress.length === 0 && (
                <div className="text-center py-10 text-slate-500 dark:text-slate-400">
                    <p>{t('dashboard_no_active_courses')}</p>
                </div>
            )}
          </div>
        </div>
        
        <div className="space-y-8">
            {pastSemesterStats ? (
                <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                    <h2 className="text-xl font-bold mb-4 flex items-center gap-3 text-slate-800 dark:text-white">
                        <Icon className="w-6 h-6 text-brand-emerald-500">
                           <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0-6.74 2.74L3 8"/>
                           <path d="M3 3v5h5"/>
                        </Icon>
                        {t('dashboard_past_semester_summary')}
                    </h2>
                    <div className="grid grid-cols-3 gap-4 text-center border-b border-slate-200 dark:border-slate-700 pb-4 mb-4">
                        <div>
                            <p className="text-2xl font-bold text-slate-800 dark:text-white">{pastSemesterStats.coursesCompleted}</p>
                            <p className="text-xs text-slate-500 dark:text-slate-400 truncate">{t('profile_courses_completed')}</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-slate-800 dark:text-white">{pastSemesterStats.totalSks}</p>
                            <p className="text-xs text-slate-500 dark:text-slate-400 truncate">{t('profile_sks_earned')}</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-slate-800 dark:text-white">{pastSemesterStats.gpa}</p>
                            <p className="text-xs text-slate-500 dark:text-slate-400 truncate">{t('dashboard_semester_gpa')}</p>
                        </div>
                    </div>
                    <ul className="space-y-2 max-h-32 overflow-y-auto">
                        {pastSemesterStats.courses.map(course => (
                            <li key={course.id} className="flex justify-between items-center text-sm">
                                <span className="text-slate-600 dark:text-slate-300 truncate pe-4" title={course.title}>{course.title}</span>
                                <span className="font-bold text-brand-emerald-600 dark:text-brand-emerald-40 flex-shrink-0">{course.gradeLetter}</span>
                            </li>
                        ))}
                    </ul>
                </div>
            ) : (
                <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                    <h2 className="text-xl font-bold mb-4 text-slate-80 dark:text-white">{t('dashboard_worship_progress')}</h2>
                    <div className="h-64" dir="ltr">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart data={ibadahData} layout={language === 'ar' ? 'vertical' : 'horizontal'} margin={{ top: 5, right: 20, left: -10, bottom: 5 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="rgba(128,128,128,0.2)" />
                                {language === 'ar' ? (
                                    <>
                                        <XAxis type="number" hide />
                                        <YAxis type="category" dataKey="name" tick={{ fill: 'rgb(100 116 139)', fontSize: 12 }} width={40} />
                                    </>
                                ) : (
                                    <>
                                        <XAxis dataKey="name" tick={{ fill: 'rgb(100 116 139)', fontSize: 12 }} />
                                        <YAxis tick={{ fill: 'rgb(100 116 139)', fontSize: 12 }} />
                                    </>
                                )}
                                <Tooltip contentStyle={{
                                    backgroundColor: 'rgba(30, 41, 59, 0.9)',
                                    borderColor: '#475569',
                                    color: '#f1f5f9'
                                }}/>
                                <Legend wrapperStyle={{fontSize: "14px"}}/>
                                <Bar dataKey="Tilawah" fill="#10b981" name={t('dashboard_chart_tilawah')} />
                                <Bar dataKey="Hafalan" fill="#eab97d" name={t('dashboard_chart_murojaah')} />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            )}
             <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">{t('profile_badges')}</h2>
                {earnedBadges.length > 0 ? (
                    <div className="grid grid-cols-3 gap-4">
                        {earnedBadges.map(badge => (
                            <div key={badge.id} className="text-center" title={t(badge.descriptionKey)}>
                                <div className="mx-auto w-12 h-12 flex items-center justify-center rounded-full bg-brand-sand-100 dark:bg-brand-sand-900/50 text-brand-sand-600 dark:text-brand-sand-300">
                                    <div className="w-6 h-6 flex items-center justify-center">
                                        {badge.icon}
                                    </div>
                                </div>
                                <p className="mt-2 text-xs font-semibold text-slate-800 dark:text-white truncate">{t(badge.titleKey)}</p>
                            </div>
                        ))}
                    </div>
                ) : (
                    <p className="text-center text-slate-500 dark:text-slate-400 py-4">{t('profile_no_badges')}</p>
                )}
            </div>
        </div>
      </div>
    </div>
  );
};
