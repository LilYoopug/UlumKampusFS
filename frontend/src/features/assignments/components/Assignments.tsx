import React, { useMemo } from 'react';
import { ASSIGNMENTS } from '@/constants';
import { Assignment, Course, User } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { AssignmentCard } from './AssignmentCard';

// Helper to find course by ID
const findCourse = (courseId: string, courses: Course[]): Course | undefined => courses.find(c => c.id === courseId);

interface AssignmentsProps {
    courses: Course[];
    currentUser: User;
    onSelectAssignment: (assignment: Assignment) => void;
}

export const Assignments: React.FC<AssignmentsProps> = ({ courses, currentUser, onSelectAssignment }) => {
    const { t } = useLanguage();

    const enrolledCourseIds = useMemo(() =>
        courses
            .filter(c => c.progress > 0 || c.completionDate)
            .map(c => c.id),
        [courses]
    );

    const studentAssignments = useMemo(() =>
        ASSIGNMENTS.filter(a => enrolledCourseIds.includes(a.courseId)),
        [enrolledCourseIds]
    );

    const { active, completed } = useMemo(() => {
        const active: Assignment[] = [];
        const completed: Assignment[] = [];
        studentAssignments.forEach(assignment => {
            const userSubmissions = assignment.submissions
                .filter(s => s.studentId === currentUser.studentId)
                .sort((a, b) => new Date(b.submittedAt).getTime() - new Date(a.submittedAt).getTime());

            const latestSubmission = userSubmissions[0];
            
            if (latestSubmission) {
                completed.push(assignment);
            } else {
                active.push(assignment);
            }
        });
        active.sort((a, b) => new Date(a.dueDate).getTime() - new Date(b.dueDate).getTime());
        completed.sort((a, b) => {
            const subA = a.submissions.filter(s => s.studentId === currentUser.studentId).sort((x, y) => new Date(y.submittedAt).getTime() - new Date(x.submittedAt).getTime())[0];
            const subB = b.submissions.filter(s => s.studentId === currentUser.studentId).sort((x, y) => new Date(y.submittedAt).getTime() - new Date(x.submittedAt).getTime())[0];
            return new Date(subB?.submittedAt || 0).getTime() - new Date(subA?.submittedAt || 0).getTime();
        });
        return { active, completed };
    }, [studentAssignments, currentUser]);

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('assignments_title')}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">{t('assignments_subtitle')}</p>
            </div>
            
            <section>
                <h2 className="text-2xl font-bold text-slate-800 dark:text-white mb-4">{t('assignments_active')}</h2>
                {active.length > 0 ? (
                    <div className="space-y-4">
                        {active.map(assignment => (
                            <AssignmentCard key={assignment.id} assignment={assignment} onSelectAssignment={onSelectAssignment} course={findCourse(assignment.courseId, courses)} currentUser={currentUser} />
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-12 text-slate-500 bg-white dark:bg-slate-800/50 rounded-lg">
                        <p>{t('assignments_no_active')}</p>
                    </div>
                )}
            </section>
            
            <section>
                <h2 className="text-2xl font-bold text-slate-800 dark:text-white mb-4">{t('assignments_completed')}</h2>
                {completed.length > 0 ? (
                    <div className="space-y-4">
                         {completed.map(assignment => (
                            <AssignmentCard key={assignment.id} assignment={assignment} onSelectAssignment={onSelectAssignment} course={findCourse(assignment.courseId, courses)} currentUser={currentUser} />
                        ))}
                    </div>
                ) : (
                     <div className="text-center py-12 text-slate-500 bg-white dark:bg-slate-800/50 rounded-lg">
                        <p>{t('assignments_no_completed')}</p>
                    </div>
                )}
            </section>
        </div>
    );
};
