import React, { useMemo } from 'react';
import { BADGES } from '../../constants';
import { useLanguage } from '../../contexts/LanguageContext';
import { Icon } from '../../src/ui/components/Icon';
import { Course, User } from '../../types';

const StatCard: React.FC<{value: string, label: string, icon: React.ReactNode}> = ({ value, label, icon }) => (
    <div className="bg-white dark:bg-slate-800/50 p-5 rounded-2xl shadow-md flex flex-col items-center text-center min-w-0 max-w-full">
        <div className="p-3 rounded-full bg-slate-10 dark:bg-slate-700 mb-3 flex-shrink-0">
            {icon}
        </div>
        <div className="min-w-0 max-w-full">
            <p className="text-2xl font-bold text-slate-800 dark:text-white truncate max-w-full">{value}</p>
            <p className="text-slate-500 dark:text-slate-400 text-sm font-medium truncate max-w-full">{label}</p>
        </div>
    </div>
);

const gradeToPoint: Record<string, number> = {
  'A': 4.0, 'A-': 3.7, 'B+': 3.3, 'B': 3.0, 'B-': 2.7,
  'C+': 2.3, 'C': 2.0, 'D': 1.0, 'E': 0.0,
};

export const Profile: React.FC<{ courses: Course[], currentUser: User, onEditProfile?: () => void, navigateTo?: (page: string, params?: any) => void }> = ({ courses, currentUser, onEditProfile, navigateTo }) => {
  const { t } = useLanguage();

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

   const earnedBadges = useMemo(() => {
    const completedCourses = courses.filter(c => c.progress === 100);
    const completedCourseIds = new Set(completedCourses.map(c => c.id));
    const completedFacultyIds = new Set(completedCourses.map(c => c.facultyId));

    const badges = [];
    if (completedCourses.length > 0) badges.push(BADGES.find(b => b.id === 'learner'));
    if (completedFacultyIds.has('syariah')) badges.push(BADGES.find(b => b.id === 'fiqh'));
    if (completedCourseIds.has('AD501')) badges.push(BADGES.find(b => b.id === 'historian'));

    return badges.filter(Boolean) as (typeof BADGES)[0][];
  }, [courses]);

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('profile_title')}</h1>
        <p className="text-slate-500 dark:text-slate-400 mt-1">{t('profile_subtitle')}</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column: Profile Card */}
        <div className="lg:col-span-1 space-y-8">
            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md text-center">
                <img src={currentUser.avatarUrl} alt={currentUser.name} className="w-32 h-32 rounded-full mx-auto border-4 border-brand-emerald-500 shadow-lg"/>
                <h2 className="mt-4 text-2xl font-bold text-slate-800 dark:text-white">{currentUser.name}</h2>
                <p className="text-brand-emerald-600 dark:text-brand-emerald-40 font-medium">{t(currentUser.role.toLowerCase().replace(' ', '_') as 'mahasiswa')}</p>
                <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">{t('profile_student_id')}: {currentUser.studentId}</p>
                
                <ul className="mt-6 text-start space-y-3 text-slate-600 dark:text-slate-300">
                    <li className="flex items-center gap-3">
                        <Icon className="w-5 h-5 text-slate-400 dark:text-slate-500"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></Icon>
                        <a href={`mailto:${currentUser.email}`} className="truncate hover:underline text-slate-600 dark:text-slate-300">{currentUser.email}</a>
                    </li>
                    <li className="flex items-center gap-3">
                        <Icon className="w-5 h-5 text-slate-40 dark:text-slate-500"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y2="2"/><line x1="8" x2="8" y2="2"/><line x1="3" x2="21" y1="10" y2="10"/></Icon>
                        <span className="text-slate-600 dark:text-slate-300">{t('profile_join_date')}: {new Date(currentUser.joinDate).toLocaleDateString('id-ID', { year: 'numeric', month: 'long' })}</span>
                    </li>
                </ul>

                <button 
                    onClick={() => {
                        if (onEditProfile) {
                            onEditProfile();
                        } else if (navigateTo) {
                            navigateTo('settings');
                        }
                    }} 
                    className="mt-6 w-full bg-brand-emerald-600 text-white font-semibold py-2.5 px-4 rounded-lg hover:bg-brand-emerald-700 transition-colors flex items-center justify-center gap-2"
                >
                    <Icon className="w-5 h-5"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 22l1.5-5.5Z"/></Icon>
                    {t('profile_edit')}
                </button>
            </div>
        </div>

        {/* Right Column: Details */}
        <div className="lg:col-span-2 space-y-8">
            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <h3 className="text-xl font-bold text-slate-800 dark:text-white mb-4">{t('profile_about_me')}</h3>
                <p className="text-slate-600 dark:text-slate-300 leading-relaxed">{currentUser.bio}</p>
            </div>
            
            {currentUser.role === 'Mahasiswa' && (
              <>
                <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                    <h3 className="text-xl font-bold text-slate-800 dark:text-white mb-4">{t('profile_academic_stats')}</h3>
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <StatCard value={academicStats.coursesCompleted.toString()} label={t('profile_courses_completed')} icon={<Icon className="w-8 h-8 text-green-500"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></Icon>}/>
                        <StatCard value={academicStats.totalSks.toString()} label={t('profile_sks_earned')} icon={<Icon className="w-8 h-8 text-blue-500"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></Icon>}/>
                        <StatCard value={academicStats.gpa} label={t('profile_gpa')} icon={<Icon className="w-8 h-8 text-amber-50"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></Icon>}/>
                    </div>
                </div>

                <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                    <h3 className="text-xl font-bold text-slate-800 dark:text-white mb-4">{t('profile_badges')}</h3>
                    {earnedBadges.length > 0 ? (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            {earnedBadges.map(badge => (
                                <div key={badge.id} className="text-center p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                                    <div className="mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-brand-sand-100 dark:bg-brand-sand-900/50 text-brand-sand-600 dark:text-brand-sand-300">
                                        {badge.icon}
                                    </div>
                                    <p className="mt-3 font-semibold text-slate-800 dark:text-white">{t(badge.titleKey)}</p>
                                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">{t(badge.descriptionKey)}</p>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-center text-slate-500 dark:text-slate-400 py-4">{t('profile_no_badges')}</p>
                    )}
                </div>
              </>
            )}
        </div>
      </div>
    </div>
  );
};
