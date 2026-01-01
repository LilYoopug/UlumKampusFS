import React from 'react';
import { Assignment, Course, Submission, User } from '../../../../types';
import { useLanguage } from '../../../../contexts/LanguageContext';
import { Icon } from '../../../ui/components/Icon';


// Helper to get assignment status for the logged-in user
const getAssignmentStatus = (assignment: Assignment, currentUser: User): { status: string; gradeLetter?: string; gradeNumeric?: number; isOverdue: boolean } => {
    const userSubmissions = assignment.submissions
        .filter(s => s.studentId === currentUser.studentId)
        .sort((a, b) => new Date(b.submittedAt).getTime() - new Date(a.submittedAt).getTime());

    const latestSubmission = userSubmissions[0];
    const dueDate = new Date(assignment.dueDate);
    const isOverdue = dueDate < new Date() && !latestSubmission;

    if (latestSubmission) {
        if (latestSubmission.gradeLetter || latestSubmission.gradeNumeric !== undefined) {
            return { status: 'graded', gradeLetter: latestSubmission.gradeLetter, gradeNumeric: latestSubmission.gradeNumeric, isOverdue: false };
        }
        return { status: 'submitted', isOverdue: false };
    }
    
    return { status: 'not_submitted', isOverdue };
};


export const AssignmentCard: React.FC<{
    assignment: Assignment;
    onSelectAssignment: (assignment: Assignment) => void;
    course?: Course;
    currentUser: User;
    onEdit?: (assignment: Assignment) => void;
    onDelete?: (assignmentId: string) => void;
    isSaving?: boolean;
}> = ({ assignment, onSelectAssignment, course, currentUser, onEdit, onDelete, isSaving }) => {
    const { t } = useLanguage();
    const { status, gradeLetter, gradeNumeric, isOverdue } = getAssignmentStatus(assignment, currentUser);

    const statusMap: Record<string, { textKey: any; color: string; }> = {
        'not_submitted': { textKey: 'assignments_status_not_submitted', color: isOverdue ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' },
        'submitted': { textKey: 'assignments_status_submitted', color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' },
        'graded': { textKey: 'assignments_status_graded', color: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' },
    };

    const currentStatus = statusMap[status];
    const attachmentsText = assignment.files.length === 1 ? t('assignments_attachment_single') : t('assignments_attachment_plural');

    const formattedGrade = [gradeLetter, gradeNumeric !== undefined ? `(${gradeNumeric})` : null]
        .filter(Boolean)
        .join(' ');

    return (
        <div className="bg-white dark:bg-slate-800/50 p-5 rounded-lg shadow-md border border-slate-200 dark:border-slate-700">
            <div className="flex justify-between items-start gap-4">
                <div className="flex-1 min-w-0">
                    <p className="text-sm font-semibold text-brand-emerald-600 dark:text-brand-emerald-400">{course?.title || t('assignments_unknown_course')}</p>
                    <h3 className="text-lg font-bold text-slate-800 dark:text-white mt-1">{assignment.title}</h3>
                </div>
                <div className="flex items-center gap-2 flex-shrink-0">
                    <span className={`text-xs font-bold px-2.5 py-1 rounded-full ${currentStatus.color}`}>
                        {t(currentStatus.textKey)}
                    </span>
                    {onEdit && onDelete && (
                        <div className="flex gap-1">
                            <button 
                                onClick={() => onEdit(assignment)}
                                className="p-2 text-slate-500 hover:text-blue-500 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                title="Edit assignment"
                                disabled={isSaving}
                            >
                                <Icon className="w-5 h-5"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></Icon>
                            </button>
                            <button 
                                onClick={() => onDelete(assignment.id)}
                                className="p-2 text-slate-500 hover:text-red-500 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                title="Delete assignment"
                                disabled={isSaving}
                            >
                                <Icon className="w-5 h-5"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></Icon>
                            </button>
                        </div>
                    )}
                </div>
            </div>
            <div className="mt-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div className="flex-1">
                    <p className={`text-sm font-medium ${isOverdue ? 'text-red-500 dark:text-red-400' : 'text-slate-50 dark:text-slate-400'}`}>
                        {t('assignments_due_date')} {new Date(assignment.dueDate).toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                        {isOverdue && ` (${t('assignments_overdue')})`}
                    </p>
                    {status === 'graded' && formattedGrade && (
                         <p className="text-sm font-medium mt-1 text-slate-700 dark:text-slate-300">{t('assignments_grade')}: <span className="font-bold text-brand-emerald-600 dark:text-brand-emerald-40">{formattedGrade}</span></p>
                    )}
                    {assignment.files.length > 0 && (
                        <p className="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-2">
                             <Icon className="w-4 h-4"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.59a2 2 0 0 1-2.83-2.83l.79-.79"/></Icon>
                             <span>{assignment.files.length} {attachmentsText}</span>
                        </p>
                    )}
                </div>
                <button
                    onClick={() => onSelectAssignment(assignment)}
                    className="w-full sm:w-auto px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors flex-shrink-0 dark:bg-brand-emerald-600 dark:hover:bg-brand-emerald-700"
                >
                    {t('assignments_view_details')}
                </button>
            </div>
        </div>
    );
};
