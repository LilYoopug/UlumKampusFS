import React, { useMemo, useState, useEffect, useRef } from 'react';
import { Assignment, Course, User } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { AssignmentCard } from './AssignmentCard';
import { Icon } from '@/src/ui/components/Icon';
import { assignmentAPI, studentAPI } from '@/services/apiService';

// Helper to find course by ID
const findCourse = (courseId: string, courses: Course[]): Course | undefined => courses.find(c => c.id === courseId);

interface AssignmentsProps {
    courses: Course[];
    currentUser: User;
    onSelectAssignment: (assignment: Assignment) => void;
    initialAssignmentId?: string;
}

export const Assignments: React.FC<AssignmentsProps> = ({ courses, currentUser, onSelectAssignment, initialAssignmentId }) => {
    const { t } = useLanguage();
    const [assignmentsData, setAssignmentsData] = useState<Assignment[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const assignmentRefs = useRef<Record<string, HTMLDivElement | null>>({});

    // Fetch assignments from backend API
    useEffect(() => {
        const fetchAssignments = async () => {
            try {
                setLoading(true);
                setError(null);
                
                let response;
                // For students, use student-specific API to get their assignments
                if (currentUser.role === 'Mahasiswa') {
                    response = await studentAPI.getMyAssignments();
                } else {
                    response = await assignmentAPI.getAll();
                }
                
                const data = response.data || [];
                setAssignmentsData(Array.isArray(data) ? data : []);
            } catch (err) {
                console.error('Failed to fetch assignments:', err);
                setError('Gagal memuat tugas. Silakan coba lagi.');
                setAssignmentsData([]);
            } finally {
                setLoading(false);
            }
        };

        fetchAssignments();
    }, [currentUser.role]);

    // For students using studentAPI.getMyAssignments(), assignments are already filtered
    // For other roles, filter by enrolled courses
    const studentAssignments = useMemo(() => {
        if (currentUser.role === 'Mahasiswa') {
            // Student API already returns only their assignments
            return assignmentsData;
        }
        
        // For other roles, filter by enrolled courses
        const enrolledCourseIds = courses
            .filter(c => c.progress > 0 || c.completionDate)
            .map(c => c.id);
        return assignmentsData.filter(a => enrolledCourseIds.includes(a.courseId));
    }, [assignmentsData, courses, currentUser.role]);

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

    // Highlight initial assignment if provided
    useEffect(() => {
        if (initialAssignmentId && !loading && assignmentsData.length > 0) {
            const timer = setTimeout(() => {
                // Try both string and original ID since backend might return number or string
                const element = assignmentRefs.current[initialAssignmentId] || assignmentRefs.current[String(initialAssignmentId)];
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    element.classList.add('ring-2', 'ring-brand-emerald-400', 'bg-brand-emerald-50', 'dark:bg-brand-emerald-900/30');
                    setTimeout(() => {
                        element.classList.remove('ring-2', 'ring-brand-emerald-400', 'bg-brand-emerald-50', 'dark:bg-brand-emerald-900/30');
                    }, 3000);
                }
            }, 200);
            return () => clearTimeout(timer);
        }
    }, [initialAssignmentId, loading, assignmentsData]);

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-brand-emerald-500"></div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="text-center py-8">
                <p className="text-red-500">{error}</p>
            </div>
        );
    }

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
                            <div key={assignment.id} ref={el => (assignmentRefs.current[String(assignment.id)] = el)} className="transition-all duration-500">
                                <AssignmentCard assignment={assignment} onSelectAssignment={onSelectAssignment} course={findCourse(assignment.courseId, courses)} currentUser={currentUser} />
                            </div>
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
                            <div key={assignment.id} ref={el => (assignmentRefs.current[String(assignment.id)] = el)} className="transition-all duration-500">
                                <AssignmentCard assignment={assignment} onSelectAssignment={onSelectAssignment} course={findCourse(assignment.courseId, courses)} currentUser={currentUser} />
                            </div>
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
