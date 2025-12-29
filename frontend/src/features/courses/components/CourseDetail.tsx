import React, { useState, useMemo } from 'react';
import { Course, CourseModule, User, Assignment } from '@/types';
import { Icon } from '@/src/ui/components/Icon';
import { useLanguage } from '@/contexts/LanguageContext';
import { VideoPlayer } from '@/src/ui/components/VideoPlayer';
import { ModuleManagement } from '@/src/features/management/components/ModuleManagement';
import { CourseAssignmentsTab } from '@/src/features/assignments/components/CourseAssignmentsTab';
import { DiscussionForum } from '@/src/features/landing/components/DiscussionForum';

interface CourseDetailProps {
  course: Course;
  onBack: () => void;
  initialParams?: any;
  currentUser: User;
  assignments: Assignment[];
  onCreateAssignment: (assignment: Omit<Assignment, 'id' | 'submissions'>) => void;
  onUpdateAssignment: (assignment: Assignment) => void;
}

type Tab = 'overview' | 'material' | 'assignments' | 'discussion';

const LiveSessionCard: React.FC<{ module: CourseModule, language: string }> = ({ module, language }) => {
    const { t } = useLanguage();
    const now = new Date();
    const startTime = new Date(module.startTime || '');
    const endTime = new Date(startTime.getTime() + 60 * 60 * 1000); // Assume 1 hour duration
    
    let status: 'LIVE' | 'UPCOMING' | 'FINISHED' = 'UPCOMING';
    if (now >= startTime && now <= endTime) {
        status = 'LIVE';
    } else if (now > endTime) {
        status = 'FINISHED';
    }

    const isJoinable = status === 'LIVE' || (startTime.getTime() - now.getTime()) <= 15 * 60 * 1000; // Joinable 15 mins before

    return (
        <div className="flex items-center gap-4 p-4 rounded-lg bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800">
            <div className="flex-shrink-0 text-red-500">
                <Icon className="w-6 h-6"><path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.934a.5.5 0 0 0-.777-.416L16 11"/><rect x="2" y="7" width="14" height="10" rx="2" ry="2"/></Icon>
            </div>
            <div className="flex-1">
                <div className="flex items-center gap-2">
                    <p className="font-semibold text-slate-800 dark:text-white">{module.title}</p>
                    {status === 'LIVE' && <span className="px-2 py-0.5 text-xs font-bold text-white bg-red-600 rounded-full animate-pulse">LIVE</span>}
                </div>
                <p className="text-sm text-slate-500 dark:text-slate-400">{t('module_live_starts_at')} {startTime.toLocaleString(language === 'id' ? 'id-ID' : 'en-US', { dateStyle: 'full', timeStyle: 'short' })}</p>
            </div>
            <a href={module.liveUrl} target="_blank" rel="noopener noreferrer" className={`flex-shrink-0 px-4 py-2 font-semibold rounded-lg transition-colors flex items-center gap-2 ${!isJoinable || status === 'FINISHED' ? 'bg-slate-300 dark:bg-slate-600 text-slate-500 cursor-not-allowed' : 'bg-red-600 text-white hover:bg-red-700'}`} aria-disabled={!isJoinable || status === 'FINISHED'}>
                <Icon className="w-5 h-5"><path d="M15 3h6v6"/><path d="M10 14 21 3"/></Icon>
                {t('module_join_live')}
            </a>
        </div>
    );
};

export const CourseDetail: React.FC<CourseDetailProps> = ({ course, onBack, initialParams, currentUser, assignments, onCreateAssignment, onUpdateAssignment }) => {
  const { t, language } = useLanguage();
  const [activeTab, setActiveTab] = useState<Tab>(initialParams?.initialTab || 'overview');
  const [playingVideo, setPlayingVideo] = useState<CourseModule | null>(null);
  const [viewingModule, setViewingModule] = useState<CourseModule | null>(null);

  const isInstructor = currentUser.role === 'Dosen' && course.instructor === currentUser.name;
  
  const getModuleIcon = (type: CourseModule['type']) => {
    switch (type) {
      case 'video':
        return <Icon className="w-6 h-6 text-blue-500"><polygon points="5 3 19 12 5 21 5 3"/></Icon>;
      case 'pdf':
        return <Icon className="w-6 h-6 text-red-500"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></Icon>;
      case 'quiz':
        return <Icon className="w-6 h-6 text-green-500"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></Icon>;
      case 'live':
        return <Icon className="w-6 h-6 text-red-500"><path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.934a.5.5 0 0 0-.777-.416L16 11"/><rect x="2" y="7" width="14" height="10" rx="2" ry="2"/></Icon>;
      default:
        return <Icon className="w-6 h-6 text-slate-500"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/></Icon>;
    }
  };

  const tabs: { id: Tab, labelKey: any, visible: boolean }[] = [
    { id: 'overview', labelKey: 'detail_tab_overview', visible: true },
    { id: 'material', labelKey: isInstructor ? 'detail_tab_manage_modules' : 'detail_tab_material', visible: true },
    { id: 'assignments', labelKey: 'detail_tab_assignments', visible: true },
    { id: 'discussion', labelKey: 'detail_tab_discussion', visible: true },
  ];

  const renderTabContent = () => {
    switch (activeTab) {
      case 'overview':
        return (
          <div className="space-y-6">
            <div>
              <h3 className="text-xl font-bold text-slate-800 dark:text-white">{t('detail_learning_objectives')}</h3>
              <ul className="mt-2 list-disc list-inside space-y-1 text-slate-600 dark:text-slate-300">
                {course.learningObjectives.map((obj, i) => <li key={i}>{obj}</li>)}
              </ul>
            </div>
            <div>
              <h3 className="text-xl font-bold text-slate-800 dark:text-white">{t('detail_syllabus')}</h3>
              <ul className="mt-2 space-y-3">
                {course.syllabus.map(item => (
                  <li key={item.week} className="flex items-start gap-4">
                    <div className="flex-shrink-0 w-16 text-center">
                      <p className="font-bold text-brand-emerald-600 dark:text-brand-emerald-400">{t('detail_week')}</p>
                      <p className="text-3xl font-bold text-slate-800 dark:text-white">{item.week}</p>
                    </div>
                    <div className="border-s-2 border-slate-200 dark:border-slate-700 ps-4">
                      <h4 className="font-semibold text-slate-800 dark:text-white">{item.topic}</h4>
                      <p className="text-sm text-slate-500 dark:text-slate-400">{item.description}</p>
                    </div>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        );
      case 'material':
        if (isInstructor) {
            return <ModuleManagement course={course} />;
        }
        return course.modules.length > 0 ? (
          <ul className="space-y-3">
            {course.modules.map(module => (
              <li key={module.id}>
                {module.type === 'live' ? (
                    <LiveSessionCard module={module} language={language} />
                ) : (
                    <a
                      href="#"
                      onClick={(e) => {
                        e.preventDefault();
                        if (module.resourceUrl || module.liveUrl) {
                            setViewingModule(module);
                        }
                      }}
                      className={`flex items-center gap-4 p-4 rounded-lg transition-colors ${module.resourceUrl ? 'hover:bg-slate-100 dark:hover:bg-slate-700/50' : 'opacity-60 cursor-not-allowed'}`}
                    >
                      <div className="flex-shrink-0">{getModuleIcon(module.type)}</div>
                      <div className="flex-1">
                        <p className="font-semibold text-slate-800 dark:text-white">{module.title}</p>
                        <p className="text-sm text-slate-500 dark:text-slate-400">{module.description}</p>
                      </div>
                      {module.attachmentUrl && (
                          <a href={module.attachmentUrl} target="_blank" rel="noopener noreferrer" onClick={e => e.stopPropagation()} className="p-2 text-slate-500 hover:text-brand-emerald-600 dark:hover:text-brand-emerald-400 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700">
                              <Icon className="w-5 h-5"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.59a2 2 0 0 1-2.83-2.83l.79-.79"/></Icon>
                          </a>
                      )}
                      <Icon className="w-5 h-5 text-slate-400 rtl:scale-x-[-1]"><path d="m9 18 6-6-6-6"/></Icon>
                    </a>
                )}
              </li>
            ))}
          </ul>
        ) : (
            <div className="text-center py-16 text-slate-500">
                <Icon className="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                </Icon>
                <h3 className="mt-4 text-lg font-semibold text-slate-800 dark:text-white">{t('detail_no_materials_student_title')}</h3>
                <p className="mt-1">{t('detail_no_materials_student_subtitle')}</p>
            </div>
        );
      case 'assignments':
        return <CourseAssignmentsTab courseId={course.id} currentUser={currentUser} assignments={assignments} onCreateAssignment={onCreateAssignment} onUpdateAssignment={onUpdateAssignment} />;
      case 'discussion':
        return <DiscussionForum courseId={course.id} currentUser={currentUser} initialThreadId={initialParams?.threadId} />;
      default:
        return null;
    }
  };

  return (
    <div className="space-y-6">
      <button onClick={onBack} className="flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors font-medium">
        <Icon className="w-5 h-5"><path d="m15 18-6-6 6-6"/></Icon>
        <span>{t('back_to_catalog')}</span>
      </button>

      <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <img src={course.imageUrl} alt={course.title} className="w-full h-48 object-cover rounded-lg md:col-span-1"/>
          <div className="md:col-span-2">
            <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{course.title}</h1>
            <p className="text-slate-500 dark:text-slate-400 mt-1">{course.description}</p>
            <div className="mt-4 flex items-center gap-4">
              <img src={course.instructorAvatarUrl} alt={course.instructor} className="w-12 h-12 rounded-full"/>
              <div>
                <p className="font-semibold text-slate-800 dark:text-white">{course.instructor}</p>
                <p className="text-sm text-slate-500 dark:text-slate-400">{t('dosen')}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
       <div className="bg-white dark:bg-slate-800/50 rounded-2xl shadow-md">
        <div className="border-b border-slate-200 dark:border-slate-700">
          <nav className="-mb-px flex space-x-6 overflow-x-auto px-6" aria-label="Tabs">
            {tabs.filter(t => t.visible).map(tab => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`${
                  activeTab === tab.id
                    ? 'border-brand-emerald-500 text-brand-emerald-600 dark:text-brand-emerald-400'
                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:border-slate-600'
                } whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors`}
              >
                {t(tab.labelKey)}
              </button>
            ))}
          </nav>
        </div>

        <div className="p-6">
          {renderTabContent()}
        </div>
      </div>
      
       {playingVideo && playingVideo.resourceUrl && (
        <VideoPlayer 
            videoUrl={playingVideo.resourceUrl} 
            captionsUrl={playingVideo.captionsUrl}
            title={playingVideo.title}
            courseTitle={course.title}
            onClose={() => setPlayingVideo(null)}
            onGoToCourse={onBack}
        />
       )}

       {viewingModule && (
            <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={() => setViewingModule(null)} role="dialog" aria-modal="true">
                <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-2xl" onClick={e => e.stopPropagation()}>
                    <div className="p-6 border-b border-slate-200 dark:border-slate-700">
                        <div className="flex justify-between items-start">
                            <div>
                                <h3 className="text-2xl font-bold text-slate-800 dark:text-white">{viewingModule.title}</h3>
                                <p className="text-sm text-slate-500 dark:text-slate-400">{course.title}</p>
                            </div>
                            <button onClick={() => setViewingModule(null)} className="p-2 rounded-full text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                                <Icon className="w-6 h-6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></Icon>
                            </button>
                        </div>
                    </div>
                    <div className="p-6 max-h-[60vh] overflow-y-auto">
                        {viewingModule.description && <p className="text-slate-600 dark:text-slate-300 mb-6">{viewingModule.description}</p>}

                        {viewingModule.attachmentUrl && (
                            <div className="mb-6">
                                <h4 className="font-semibold mb-2">Lampiran</h4>
                                <a href={viewingModule.attachmentUrl} target="_blank" rel="noopener noreferrer" className="flex items-center gap-3 p-3 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600">
                                    <Icon className="w-5 h-5 text-slate-500"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.59a2 2 0 0 1-2.83-2.83l.79-.79"/></Icon>
                                    <span className="font-medium text-brand-emerald-600 dark:text-brand-emerald-400 truncate">Buka Lampiran</span>
                                </a>
                            </div>
                        )}
                    </div>
                    <div className="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                        <button
                            onClick={() => {
                                if (viewingModule.type === 'video' && viewingModule.resourceUrl) setPlayingVideo(viewingModule);
                                else if (viewingModule.type === 'pdf' && viewingModule.resourceUrl) window.open(viewingModule.resourceUrl, '_blank');
                                setViewingModule(null);
                            }}
                            className="flex items-center gap-2 px-6 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700"
                        >
                            {viewingModule.type === 'video' ? <Icon className="w-5 h-5"><polygon points="5 3 19 12 5 21 5 3"/></Icon> : <Icon className="w-5 h-5"><path d="M15 3h6v6"/><path d="M10 14 21 3"/></Icon>}
                            {viewingModule.type === 'video' ? 'Putar Video' : 'Buka Materi'}
                        </button>
                    </div>
                </div>
            </div>
        )}
    </div>
  );
};
