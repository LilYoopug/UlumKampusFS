import React, { useState, useMemo } from 'react';
import { ACADEMIC_CALENDAR_EVENTS } from '../constants';
import { useLanguage } from '../contexts/LanguageContext';
import { Icon } from './Icon';
import { AcademicCalendarEvent, Assignment, Course, TranslationKey, User } from '../types';

type CalendarEvent = {
    type: 'assignment' | 'live-class' | 'academic';
    date: Date;
    title: string;
    courseTitle?: string;
    category?: AcademicCalendarEvent['category'];
};

export const Calendar: React.FC<{ courses: Course[], currentUser: User, assignments: Assignment[] }> = ({ courses, currentUser, assignments }) => {
    const { t } = useLanguage();
    const [currentDate, setCurrentDate] = useState(new Date());
    const [selectedDate, setSelectedDate] = useState(new Date());

    const allEvents = useMemo(() => {
        let relevantCourses = courses;

        if (currentUser.role === 'Mahasiswa') {
            relevantCourses = courses.filter(c => c.progress > 0 || c.completionDate);
        }
        
        const relevantCourseIds = relevantCourses.map(c => c.id);

        const assignmentEvents: CalendarEvent[] = assignments
            .filter(a => relevantCourseIds.includes(a.courseId))
            .map(a => ({
                type: 'assignment',
                date: new Date(a.dueDate),
                title: a.title,
                courseTitle: courses.find(c => c.id === a.courseId)?.title || 'Unknown Course',
            }));

        const liveClassEvents: CalendarEvent[] = [];
        relevantCourses.forEach(course => {
            course.modules.forEach(module => {
                if (module.type === 'live' && module.startTime) {
                    liveClassEvents.push({
                        type: 'live-class',
                        date: new Date(module.startTime),
                        title: module.title,
                        courseTitle: course.title,
                    });
                }
            });
        });

        const academicEvents: CalendarEvent[] = [];
        ACADEMIC_CALENDAR_EVENTS.forEach(ae => {
            const startDate = new Date(ae.startDate);
            const endDate = ae.endDate ? new Date(ae.endDate) : startDate;

            let eventDate = new Date(startDate);
            while (eventDate <= endDate) {
                academicEvents.push({
                    type: 'academic',
                    date: new Date(eventDate),
                    title: t(ae.titleKey),
                    category: ae.category,
                });
                eventDate.setDate(eventDate.getDate() + 1);
            }
        });


        return [...assignmentEvents, ...liveClassEvents, ...academicEvents];
    }, [t, courses, currentUser, assignments]);

    const eventsByDate = useMemo(() => {
        const map = new Map<string, { assignments: boolean, liveClasses: boolean, academic: boolean }>();
        allEvents.forEach(event => {
            const dateStr = event.date.toISOString().split('T')[0];
            const entry = map.get(dateStr) || { assignments: false, liveClasses: false, academic: false };
            if (event.type === 'assignment') entry.assignments = true;
            if (event.type === 'live-class') entry.liveClasses = true;
            if (event.type === 'academic') entry.academic = true;
            map.set(dateStr, entry);
        });
        return map;
    }, [allEvents]);

    const selectedDateEvents = useMemo(() => {
        const dateStr = selectedDate.toISOString().split('T')[0];
        return allEvents
            .filter(event => event.date.toISOString().split('T')[0] === dateStr)
            .sort((a, b) => a.date.getTime() - b.date.getTime());
    }, [selectedDate, allEvents]);


    const changeMonth = (amount: number) => {
        setCurrentDate(prev => {
            const newDate = new Date(prev);
            newDate.setMonth(newDate.getMonth() + amount);
            return newDate;
        });
    };

    const goToToday = () => {
        const today = new Date();
        setCurrentDate(today);
        setSelectedDate(today);
    }

    const monthNames: any[] = ['month_january', 'month_february', 'month_march', 'month_april', 'month_may', 'month_june', 'month_july', 'month_august', 'month_september', 'month_october', 'month_november', 'month_december'];
    const dayNames: any[] = ['day_sun', 'day_mon', 'day_tue', 'day_wed', 'day_thu', 'day_fri', 'day_sat'];

    const renderHeader = () => {
        return (
            <div className="flex justify-between items-center px-2 py-4">
                <div className="flex items-center gap-2">
                    <h2 className="text-xl font-bold text-slate-800 dark:text-white">
                        {t(monthNames[currentDate.getMonth()])}
                    </h2>
                    <span className="text-xl font-light text-slate-500 dark:text-slate-400">{currentDate.getFullYear()}</span>
                </div>
                <div className="flex items-center gap-2">
                    <button onClick={goToToday} className="px-3 py-1 text-sm font-semibold border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-slate-700 dark:text-slate-200">{t('calendar_today')}</button>
                    <button onClick={() => changeMonth(-1)} className="p-2 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" aria-label="Previous month">
                        <Icon className="w-5 h-5 rtl:scale-x-[-1]"><path d="m15 18-6-6 6-6"/></Icon>
                    </button>
                    <button onClick={() => changeMonth(1)} className="p-2 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" aria-label="Next month">
                        <Icon className="w-5 h-5 rtl:scale-x-[-1]"><path d="m9 18 6-6-6-6"/></Icon>
                    </button>
                </div>
            </div>
        );
    };

    const renderDays = () => {
        return (
            <div className="grid grid-cols-7 text-center text-sm font-semibold text-slate-500 dark:text-slate-400 pb-2">
                {dayNames.map(day => <div key={day}>{t(day)}</div>)}
            </div>
        );
    };

    const renderCells = () => {
        const month = currentDate.getMonth();
        const year = currentDate.getFullYear();
        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const cells = [];
        const today = new Date();
        
        // Blank cells for previous month
        for (let i = 0; i < firstDayOfMonth; i++) {
            cells.push(<div key={`blank-${i}`} className="p-2 border-t border-e border-slate-100 dark:border-slate-800"></div>);
        }

        // Days of current month
        for (let day = 1; day <= daysInMonth; day++) {
            const cellDate = new Date(year, month, day);
            const dateStr = cellDate.toISOString().split('T')[0];
            const isToday = cellDate.toDateString() === today.toDateString();
            const isSelected = cellDate.toDateString() === selectedDate.toDateString();
            const eventsOnDay = eventsByDate.get(dateStr);

            cells.push(
                <div 
                    key={day}
                    onClick={() => setSelectedDate(cellDate)}
                    className={`relative p-2 h-20 sm:h-24 border-t border-e border-slate-100 dark:border-slate-800 transition-colors cursor-pointer ${isSelected ? 'bg-brand-emerald-50 dark:bg-brand-emerald-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'}`}
                >
                    <span className={`flex items-center justify-center w-7 h-7 rounded-full text-sm font-semibold ${isToday ? 'bg-brand-emerald-600 text-white' : 'text-slate-800 dark:text-slate-200'} ${isSelected ? 'ring-2 ring-brand-emerald-50' : ''}`}>
                        {day}
                    </span>
                    {eventsOnDay && (
                        <div className="absolute bottom-2 start-2 end-2 flex justify-center gap-1.5">
                            {eventsOnDay.liveClasses && <div className="w-2 h-2 rounded-full bg-brand-emerald-500"></div>}
                            {eventsOnDay.assignments && <div className="w-2 h-2 rounded-full bg-brand-sand-500"></div>}
                            {eventsOnDay.academic && <div className="w-2 h-2 rounded-full bg-indigo-500"></div>}
                        </div>
                    )}
                </div>
            );
        }

        return <div className="grid grid-cols-7">{cells}</div>;
    };
    
    const EventItem: React.FC<{event: CalendarEvent}> = ({ event }) => {
        const getCategoryLabel = () => {
            if (!event.category) return t('calendar_academic_event');
            const keyMap: Record<string, TranslationKey> = {
                holiday: 'calendar_category_holiday',
                exam: 'calendar_category_exam',
                registration: 'calendar_category_registration',
                academic: 'calendar_category_academic',
            };
            return t(keyMap[event.category] || 'calendar_academic_event');
        };

        return (
             <div className="flex items-center gap-4">
                <div className={`flex-shrink-0 w-3 h-3 rounded-full ${event.type === 'live-class' ? 'bg-brand-emerald-500' : event.type === 'assignment' ? 'bg-brand-sand-500' : 'bg-indigo-500'}`}></div>
                <div className="flex-1">
                    <p className="font-semibold text-slate-800 dark:text-white">{event.title}</p>
                    {event.courseTitle && <p className="text-sm text-slate-500 dark:text-slate-400">{event.courseTitle}</p>}
                    <p className="text-xs text-slate-400 dark:text-slate-500 mt-1">
                        {event.type === 'academic' 
                            ? getCategoryLabel()
                            : `${event.date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} - ${event.type === 'live-class' ? t('calendar_live_class') : t('calendar_assignment_due')}`
                        }
                    </p>
                </div>
                 {event.type === 'live-class' && (
                    <a
                        href="#"
                        onClick={(e) => e.preventDefault()}
                        className="ms-auto flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 bg-brand-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors"
                    >
                        <Icon className="w-4 h-4"><path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.934a.5.5 0 0 0-.777-.416L16 11"/><rect x="2" y="7" width="14" height="10" rx="2" ry="2"/></Icon>
                        <span className="hidden sm:inline lg:hidden xl:inline">{t('calendar_join_class')}</span>
                    </a>
                )}
            </div>
        );
    }

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('calendar_title')}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">{t('calendar_subtitle')}</p>
            </div>
            
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div className="lg:col-span-2 bg-white dark:bg-slate-800/50 rounded-2xl shadow-md p-4">
                    {renderHeader()}
                    {renderDays()}
                    {renderCells()}
                </div>
                <div className="lg:col-span-1">
                    <div className="bg-white dark:bg-slate-800/50 rounded-2xl shadow-md p-6">
                        <h3 className="text-lg font-bold text-slate-800 dark:text-white">
                            {selectedDate.toLocaleDateString(t('language') === 'id' ? 'id-ID' : t('language'), { weekday: 'long', day: 'numeric', month: 'long' })}
                        </h3>
                         <div className="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 space-y-4">
                            {selectedDateEvents.length > 0 ? (
                                selectedDateEvents.map((event, index) => <EventItem key={index} event={event} />)
                            ) : (
                                <p className="text-slate-500 dark:text-slate-400 text-sm text-center py-8">{t('calendar_no_events')}</p>
                            )}
                        </div>
                        <div className="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700 space-y-2 text-sm">
                            <div className="flex items-center gap-2">
                                <div className="w-3 h-3 rounded-full bg-brand-emerald-500"></div>
                                <span className="text-slate-600 dark:text-slate-300">{t('calendar_legend_live')}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <div className="w-3 h-3 rounded-full bg-brand-sand-500"></div>
                                <span className="text-slate-600 dark:text-slate-300">{t('calendar_legend_assignment')}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <div className="w-3 h-3 rounded-full bg-indigo-500"></div>
                                <span className="text-slate-600 dark:text-slate-300">{t('calendar_legend_academic')}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    );
};
