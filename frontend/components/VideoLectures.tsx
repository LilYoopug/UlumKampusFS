import React, { useState, useMemo } from 'react';
import { Course, CourseModule, User } from '../types';
import { useLanguage } from '../contexts/LanguageContext';
import { Icon } from './Icon';
import { VideoPlayer } from './VideoPlayer';

interface VideoLecture {
    module: CourseModule;
    course: Course;
}

export const VideoLectures: React.FC<{ onSelectCourse: (course: Course) => void, courses: Course[], currentUser: User }> = ({ onSelectCourse, courses, currentUser }) => {
    const { t } = useLanguage();
    const [searchTerm, setSearchTerm] = useState('');
    const [playingVideo, setPlayingVideo] = useState<VideoLecture | null>(null);

    const allVideoLectures = useMemo(() => {
        let relevantCourses = courses;

        // For students, only show lectures from enrolled courses.
        if (currentUser.role === 'Mahasiswa') {
            relevantCourses = courses.filter(c => c.progress > 0 || c.completionDate);
        }

        const videos: VideoLecture[] = [];
        relevantCourses.forEach(course => {
            course.modules.forEach(module => {
                if (module.type === 'video') {
                    videos.push({ module, course });
                }
            });
        });
        return videos;
    }, [courses, currentUser]);

    const filteredLectures = useMemo(() => {
        if (!searchTerm) {
            return allVideoLectures;
        }
        return allVideoLectures.filter(lecture =>
            lecture.module.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
            lecture.course.title.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [allVideoLectures, searchTerm]);

    const lecturesByCourse = useMemo(() => {
        const grouped = new Map<string, { course: Course, modules: CourseModule[] }>();
        filteredLectures.forEach(({ module, course }) => {
            if (!grouped.has(course.id)) {
                grouped.set(course.id, { course, modules: [] });
            }
            grouped.get(course.id)!.modules.push(module);
        });
        return Array.from(grouped.values());
    }, [filteredLectures]);

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('video_lectures_title')}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">{t('video_lectures_subtitle')}</p>
            </div>

            <div className="relative">
                <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </Icon>
                <input
                    type="text"
                    placeholder={t('video_lectures_search_placeholder')}
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="w-full max-w-lg ps-10 pe-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                />
            </div>

            {lecturesByCourse.length > 0 ? (
                <div className="space-y-8">
                    {lecturesByCourse.map(({ course, modules }) => (
                        <div key={course.id} className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                            <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">{course.title}</h2>
                            <ul className="space-y-3">
                                {modules.map(module => (
                                    <li key={module.id}>
                                        <a
                                            href="#"
                                            onClick={(e) => {
                                                e.preventDefault();
                                                setPlayingVideo({ module, course });
                                            }}
                                            className="flex items-center gap-4 p-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors border border-slate-200 dark:border-slate-700"
                                        >
                                            <div className="flex-shrink-0 text-blue-500">
                                                <Icon className="w-6 h-6"><polygon points="5 3 19 12 5 21 5 3"/></Icon>
                                            </div>
                                            <div className="flex-1">
                                                <p className="font-semibold text-slate-800 dark:text-white">{module.title}</p>
                                                {module.duration && (
                                                    <p className="text-sm text-slate-500 dark:text-slate-400">{module.duration}</p>
                                                )}
                                            </div>
                                            <div className="text-slate-400">
                                                <Icon className="w-5 h-5 rtl:scale-x-[-1]"><path d="m9 18 6-6-6-6"/></Icon>
                                            </div>
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="text-center py-16 text-slate-500">
                     <Icon className="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600">
                        <rect x="2" y="7" width="20" height="10" rx="2" ry="2"/>
                        <path d="m10 12 4 2.5-4 2.5v-5Z"/>
                     </Icon>
                    <h3 className="mt-4 text-lg font-semibold">{t('video_lectures_no_videos')}</h3>
                </div>
            )}

            {playingVideo && playingVideo.module.resourceUrl && (
                <VideoPlayer
                    videoUrl={playingVideo.module.resourceUrl}
                    captionsUrl={playingVideo.module.captionsUrl}
                    title={playingVideo.module.title}
                    courseTitle={playingVideo.course.title}
                    onClose={() => setPlayingVideo(null)}
                    onGoToCourse={() => {
                        setPlayingVideo(null);
                        onSelectCourse(playingVideo.course);
                    }}
                />
            )}
        </div>
    );
};
