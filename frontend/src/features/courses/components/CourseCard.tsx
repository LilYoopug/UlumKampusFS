import React from 'react';
import { Course, CourseStatus } from '../types';
import { Faculty } from '../types';
import { Icon } from './Icon';
import { useLanguage } from '../contexts/LanguageContext';

interface CourseCardProps {
  course: Course;
  onSelectCourse?: (course: Course) => void;
 onEditCourse?: (course: Course) => void;
  layout?: 'vertical' | 'horizontal';
  isPublic?: boolean;
  faculties?: Faculty[];
}

const StatusBadge: React.FC<{status: CourseStatus}> = ({ status }) => {
    const statusStyles: Record<CourseStatus, string> = {
        Published: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
        Draft: 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300',
        Archived: 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
    };
    return (
        <span className={`absolute top-3 end-3 text-xs font-semibold px-2.5 py-0.5 rounded-full ${statusStyles[status]}`}>
            {status}
        </span>
    )
};


export const CourseCard: React.FC<CourseCardProps> = ({ course, onSelectCourse, onEditCourse, layout = 'vertical', isPublic = false, faculties }) => {
  const { t } = useLanguage();
  const faculty = faculties?.find(f => f.id === course.facultyId);

  const handleCardClick = () => {
    onSelectCourse?.(course);
  }

  const handleEditClick = (e: React.MouseEvent) => {
    e.stopPropagation(); // Prevent card click when editing
    onEditCourse?.(course);
  }

  const ModeBadge: React.FC<{mode: 'Live' | 'VOD'}> = ({ mode }) => (
    <span className={`flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold ${mode === 'VOD' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'}`}>
        {mode === 'Live' ? (
            <span className="relative flex h-2 w-2">
                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span className="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
            </span>
        ) : (
            <Icon className="w-3 h-3" strokeWidth="2" fill="currentColor"><path d="m5 3 10 6-10 6V3z"/></Icon>
        )}
        {mode}
    </span>
  );

  if (layout === 'horizontal') {
      return (
         <div 
          onClick={handleCardClick}
          className={`flex items-center bg-white dark:bg-slate-800/50 p-3 rounded-lg shadow-sm transition-all duration-300 relative ${onSelectCourse ? 'hover:shadow-lg hover:ring-2 hover:ring-brand-emerald-500 cursor-pointer' : ''}`}
        >
          {onEditCourse && <StatusBadge status={course.status}/>}
          <img src={course.imageUrl} alt={course.title} className="w-24 h-24 object-cover rounded-md flex-shrink-0" />
          <div className="flex-1 ms-4">
              <h3 className="font-bold text-lg leading-tight text-slate-800 dark:text-white">{course.title}</h3>
              <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">{course.instructor}</p>
              <div className="flex items-center justify-between mt-2 text-xs text-slate-500 dark:text-slate-400">
                  <span className="flex items-center gap-1.5"><Icon className="w-4 h-4"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 0 0 1 0-5H20"/></Icon> {course.sks} {t('sks')}</span>
                  <ModeBadge mode={course.mode} />
              </div>
              {!isPublic && (
                <div className="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5 mt-3">
                    <div 
                      className="bg-brand-emerald-50 h-2.5 rounded-full" 
                      style={{ width: `${course.progress}%` }}
                    ></div>
                </div>
              )}
          </div>
        </div>
      );
  }

  return (
    <div 
      onClick={handleCardClick}
      className={`bg-white dark:bg-slate-800/50 rounded-2xl shadow-md overflow-hidden transition-all duration-300 flex flex-col relative ${onSelectCourse ? 'hover:shadow-xl hover:-translate-y-1 cursor-pointer' : ''}`}
    >
      <img src={course.imageUrl} alt={course.title} className="w-full h-40 object-cover" />
      {onEditCourse && <StatusBadge status={course.status}/>}
      <div className="p-5 flex flex-col flex-grow">
        <p className="text-sm font-semibold text-brand-emerald-60 dark:text-brand-emerald-400">{faculty?.name || 'Fakultas'}</p>
        <h3 className="font-bold text-lg mt-1 text-slate-800 dark:text-white flex-grow">{course.title}</h3>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-2">{course.instructor}</p>
        
        {!isPublic && (
          <div className="mt-4">
              <div className="flex justify-between items-center mb-1 text-sm">
                  <span className="font-medium text-slate-600 dark:text-slate-300">{t('progress')}</span>
                  <span className="font-bold text-brand-emerald-600 dark:text-brand-emerald-400">{course.progress}%</span>
              </div>
              <div className="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5">
                  <div 
                    className="bg-brand-emerald-500 h-2.5 rounded-full" 
                    style={{ width: `${course.progress}%` }}
                  ></div>
              </div>
          </div>
        )}

        <div className={`flex items-center justify-between text-sm text-slate-500 dark:text-slate-400 ${isPublic ? 'mt-auto pt-4' : 'mt-4'}`}>
            <span className="flex items-center gap-1.5"><Icon className="w-4 h-4"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 0 0 1 0-5H20"/></Icon> {course.sks} {t('sks')}</span>
            <div className="flex items-center gap-2">
                {onEditCourse && (
                    <button onClick={handleEditClick} className="p-1.5 text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-full" aria-label="Edit Course">
                        <Icon className="w-4 h-4"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 2l1.5-5.5Z"/></Icon>
                    </button>
                )}
                <ModeBadge mode={course.mode} />
            </div>
        </div>
      </div>
    </div>
  );
};
