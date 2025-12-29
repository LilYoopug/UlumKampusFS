import React, { useState, useMemo, useEffect } from 'react';
import { Course, Assignment, User, Badge } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { Icon } from '@/src/ui/components/Icon';
import { ALL_USERS, BADGES } from '@/constants';
import { numericToLetter } from '@/utils/gradeConverter';
import { AssignmentForm } from '@/src/features/assignments/components/AssignmentForm';

interface GradebookProps {
    courses: Course[];
    assignments: Assignment[];
    currentUser: User;
    users: User[];
    onUpdateUser: (user: User) => void;
    onSelectAssignment: (assignment: Assignment) => void;
    onCreateAssignment?: (assignment: Omit<Assignment, 'id' | 'submissions'>) => void;
    onUpdateAssignment?: (assignment: Assignment) => void;
}

interface StudentProgress {
    studentId: string;
    progress: number;
    averageGrade: number | null;
    status: 'In Progress' | 'Completed';
    completionDate: string | null;
}

// --- Local Components ---

const CertificateModal: React.FC<{ course: Course; student: User; onClose: () => void; completionDate: string; }> = ({ course, student, onClose, completionDate }) => {
    const { t } = useLanguage();
    const handlePrint = () => { /* Print logic from original Grades.tsx can be added here if needed */ alert('Printing certificate...'); };
    
    return (
         <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={onClose} role="dialog" aria-modal="true">
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-3xl" onClick={e => e.stopPropagation()}>
                <div className="p-8 md:p-12 aspect-[297/210] flex flex-col justify-center items-center text-center bg-slate-50 dark:bg-slate-900 border-8 border-brand-emerald-700 dark:border-brand-emerald-600 relative">
                     <div className="absolute inset-2 border-2 border-brand-emerald-500/50 dark:border-brand-emerald-400/50"></div>
                     <div className="relative z-10">
                        <h1 className="text-4xl font-bold text-brand-emerald-800 dark:text-brand-emerald-300">UlumCampus</h1>
                        <p className="mt-6 text-xl uppercase tracking-widest text-slate-500 dark:text-slate-400">{t('grades_certificate_of_completion')}</p>
                        <p className="mt-8 text-lg text-slate-600 dark:text-slate-300">{t('grades_certificate_awarded_to')}</p>
                        <p className="mt-2 text-4xl font-bold text-slate-800 dark:text-white">{student.name}</p>
                        <p className="mt-8 text-lg text-slate-600 dark:text-slate-300">{t('grades_modal_completed_course')}</p>
                        <p className="mt-2 text-2xl font-semibold text-brand-emerald-700 dark:text-brand-emerald-400">{course.title}</p>
                        <p className="mt-8 text-sm text-slate-500 dark:text-slate-400">{t('grades_certificate_on')} {new Date(completionDate).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                    </div>
                </div>
                <div className="flex justify-end items-center gap-3 p-4 bg-slate-100 dark:bg-slate-800/50 rounded-b-lg">
                    <button onClick={onClose} className="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-600 font-semibold">{t('grades_modal_close')}</button>
                </div>
            </div>
        </div>
    );
};

const BadgeAwardModal: React.FC<{ 
    student: User; 
    onClose: () => void; 
    onAward: (badgeId: string) => void;
}> = ({ student, onClose, onAward }) => {
    const { t } = useLanguage();
    const availableBadges = useMemo(() => {
        const studentBadges = student.badges || [];
        return BADGES.filter(b => !studentBadges.includes(b.id));
    }, [student.badges]);

    return (
        <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={onClose} role="dialog" aria-modal="true">
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-md" onClick={e => e.stopPropagation()}>
                <div className="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 className="text-lg font-bold">Berikan Lencana kepada {student.name}</h3>
                </div>
                <div className="p-6 max-h-80 overflow-y-auto">
                    {availableBadges.length > 0 ? (
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {availableBadges.map(badge => (
                                <button key={badge.id} onClick={() => onAward(badge.id)} className="text-center p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700">
                                    <div className="mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-brand-sand-100 dark:bg-brand-sand-900/50 text-brand-sand-600 dark:text-brand-sand-300">
                                        {badge.icon}
                                    </div>
                                    <p className="mt-3 font-semibold text-slate-800 dark:text-white text-sm">{t(badge.titleKey)}</p>
                                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">{t(badge.descriptionKey)}</p>
                                </button>
                            ))}
                        </div>
                    ) : (
                        <p className="text-center text-slate-500">Mahasiswa ini sudah memiliki semua lencana yang tersedia.</p>
                    )}
                </div>
                 <div className="flex justify-end p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700">
                    <button onClick={onClose} className="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-600 font-semibold">{t('button_cancel')}</button>
                </div>
            </div>
        </div>
    );
};


// --- Main Component ---

export const Gradebook: React.FC<GradebookProps> = ({ courses, assignments, currentUser, users, onUpdateUser, onSelectAssignment, onCreateAssignment, onUpdateAssignment }) => {
    const { t } = useLanguage();
    const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
    const [activeTab, setActiveTab] = useState<'progress' | 'assignments'>('progress');
    
    // State for student progress management
    const [studentProgress, setStudentProgress] = useState<StudentProgress[]>([]);
    
    // State for modals
    const [isBadgeModalOpen, setIsBadgeModalOpen] = useState(false);
    const [studentToBadge, setStudentToBadge] = useState<User | null>(null);
    const [isCertModalOpen, setIsCertModalOpen] = useState(false);
    const [studentForCert, setStudentForCert] = useState<User | null>(null);
    const [isAssignmentFormOpen, setIsAssignmentFormOpen] = useState(false);
    const [editingAssignment, setEditingAssignment] = useState<Assignment | null>(null);

    const myCourses = useMemo(() => courses.filter(c => c.instructor === currentUser.name), [courses, currentUser]);
    const totalStudents = useMemo(() => users.filter(u => u.role === 'Mahasiswa').length, [users]);

    // Initialize student progress when a course is selected
    useEffect(() => {
        if (selectedCourse) {
            const enrolledStudents = users.filter(u => u.role === 'Mahasiswa');
            setStudentProgress(enrolledStudents.map(student => {
                 // Mock data for demonstration
                const progress = Math.floor(Math.random() * 90) + 10;
                const averageGrade = progress > 50 ? Math.floor(Math.random() * 20) + 75 : Math.floor(Math.random() * 20) + 60;
                return {
                    studentId: student.studentId,
                    progress: progress,
                    averageGrade: averageGrade,
                    status: 'In Progress',
                    completionDate: null,
                };
            }));
        }
    }, [selectedCourse, users]);

    const handleMarkAsComplete = (studentId: string) => {
        setStudentProgress(prev => prev.map(sp => {
            if (sp.studentId === studentId) {
                return { ...sp, progress: 100, status: 'Completed', completionDate: new Date().toISOString() };
            }
            return sp;
        }));
    };

    const handleOpenBadgeModal = (studentId: string) => {
        const student = users.find(u => u.studentId === studentId);
        if (student) {
            setStudentToBadge(student);
            setIsBadgeModalOpen(true);
        }
    };
    
    const handleAwardBadge = (badgeId: string) => {
        if (studentToBadge) {
            const updatedBadges = [...(studentToBadge.badges || []), badgeId];
            onUpdateUser({ ...studentToBadge, badges: updatedBadges });
        }
        setIsBadgeModalOpen(false);
        setStudentToBadge(null);
    };

    const handleViewCertificate = (studentId: string) => {
        const student = users.find(u => u.studentId === studentId);
        if (student) {
            setStudentForCert(student);
            setIsCertModalOpen(true);
        }
    };

    const handleSaveNewAssignment = (newAssignmentData: Omit<Assignment, 'id' | 'submissions'>) => {
        if (onCreateAssignment) {
            onCreateAssignment(newAssignmentData);
        }
        setIsAssignmentFormOpen(false);
    };

    const getCourseStats = (course: Course) => {
        const courseAssignments = assignments.filter(a => a.courseId === course.id);
        const totalSubmissionsToGrade = courseAssignments.reduce((sum, ass) => {
            const gradedCount = ass.submissions.filter(s => s.gradeLetter || s.gradeNumeric !== undefined).length;
            const toGrade = ass.submissions.length - gradedCount;
            return sum + (toGrade > 0 ? toGrade : 0);
        }, 0);
        return {
            assignmentCount: courseAssignments.length,
            toGradeCount: totalSubmissionsToGrade
        };
    };

    if (!selectedCourse) {
        return (
             <div className="space-y-8">
                <div>
                    <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('gradebook_title')}</h1>
                    <p className="text-slate-500 dark:text-slate-400 mt-1">{t('gradebook_subtitle')}</p>
                </div>
                <div className="space-y-6">
                    <h2 className="text-2xl font-bold text-slate-800 dark:text-white">{t('gradebook_my_courses')}</h2>
                    {myCourses.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {myCourses.map(course => {
                            const stats = getCourseStats(course);
                            return (
                                <div key={course.id} className="bg-white dark:bg-slate-800/50 p-6 rounded-lg shadow-md border border-slate-200 dark:border-slate-700 flex flex-col">
                                    <h3 className="text-xl font-bold text-slate-800 dark:text-white">{course.title}</h3>
                                    <div className="flex-grow my-4 flex items-center gap-6">
                                        <div className="text-center">
                                            <p className="text-3xl font-bold text-slate-700 dark:text-slate-200">{stats.assignmentCount}</p>
                                            <p className="text-sm text-slate-500">Tugas</p>
                                        </div>
                                        <div className="text-center">
                                            <p className="text-3xl font-bold text-amber-500">{stats.toGradeCount}</p>
                                            <p className="text-sm text-slate-500">{t('gradebook_to_grade')}</p>
                                        </div>
                                    </div>
                                    <button onClick={() => setSelectedCourse(course)} className="mt-auto w-full px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">
                                        Monitor Progres
                                    </button>
                                </div>
                            )
                        })}
                        </div>
                    ) : (
                        <div className="text-center py-16 text-slate-500 bg-white dark:bg-slate-800/50 rounded-lg">
                            <p>{t('gradebook_no_courses')}</p>
                        </div>
                    )}
                </div>
            </div>
        );
    }
    
    // --- Detailed Course View ---
    const courseAssignments = assignments.filter(a => a.courseId === selectedCourse.id);
    const getAssignmentStats = (assignment: Assignment) => {
        const submittedCount = new Set(assignment.submissions.map(s => s.studentId)).size;
        const gradedCount = assignment.submissions.filter(s => s.gradeLetter || s.gradeNumeric !== undefined).length;
        return { submittedCount, gradedCount };
    };

    return (
        <>
            <div className="space-y-6">
                <button onClick={() => setSelectedCourse(null)} className="flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors font-medium">
                    <Icon className="w-5 h-5"><path d="m15 18-6-6 6-6"/></Icon>
                    <span>{t('gradebook_back_to_courses')}</span>
                </button>
                <div>
                    <h1 className="text-3xl font-bold text-slate-800 dark:text-white">Manajemen: "{selectedCourse.title}"</h1>
                </div>

                <div className="bg-white dark:bg-slate-800/50 rounded-2xl shadow-md">
                    <div className="border-b border-slate-200 dark:border-slate-700">
                        <nav className="-mb-px flex space-x-6 px-6" aria-label="Tabs">
                           <button onClick={() => setActiveTab('progress')} className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'progress' ? 'border-brand-emerald-500 text-brand-emerald-600' : 'border-transparent text-slate-500 hover:border-slate-300'}`}>
                                Progres Mahasiswa
                            </button>
                            <button onClick={() => setActiveTab('assignments')} className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'assignments' ? 'border-brand-emerald-500 text-brand-emerald-600' : 'border-transparent text-slate-500 hover:border-slate-300'}`}>
                                Daftar Tugas
                            </button>
                        </nav>
                    </div>
                    <div className="p-6">
                        {activeTab === 'progress' && (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm text-left">
                                    <thead className="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                                        <tr>
                                            <th className="px-4 py-3 text-slate-700 dark:text-slate-300">Mahasiswa</th>
                                            <th className="px-4 py-3 text-slate-700 dark:text-slate-300">Progres</th>
                                            <th className="px-4 py-3 text-slate-700 dark:text-slate-300">Rata-rata</th>
                                            <th className="px-4 py-3 text-slate-700 dark:text-slate-300">Status</th>
                                            <th className="px-4 py-3 text-end text-slate-700 dark:text-slate-300">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {studentProgress.map(sp => {
                                            const student = users.find(u => u.studentId === sp.studentId);
                                            if (!student) return null;
                                            return (
                                                <tr key={student.studentId} className="border-b dark:border-slate-700">
                                                    <td className="px-4 py-3 font-medium text-slate-900 dark:text-white">{student.name}</td>
                                                    <td className="px-4 py-3">
                                                        <div className="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5">
                                                            <div className="bg-brand-emerald-500 h-2.5 rounded-full" style={{ width: `${sp.progress}%` }}></div>
                                                        </div>
                                                    </td>
                                                    <td className="px-4 py-3 text-slate-600 dark:text-slate-300">{sp.averageGrade?.toFixed(1) || '-'}</td>
                                                    <td className="px-4 py-3 text-slate-600 dark:text-slate-300">{sp.status}</td>
                                                    <td className="px-4 py-3 text-end">
                                                        <div className="flex justify-end gap-2">
                                                            {sp.status === 'Completed' ? (
                                                                <button onClick={() => handleViewCertificate(student.studentId)} className="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-md">Lihat Sertifikat</button>
                                                            ) : (
                                                                <button onClick={() => handleMarkAsComplete(student.studentId)} className="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-md">Tandai Lulus</button>
                                                            )}
                                                             <button onClick={() => handleOpenBadgeModal(student.studentId)} className="text-xs px-2 py-1 bg-amber-100 text-amber-800 rounded-md">Beri Lencana</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        )}
                         {activeTab === 'assignments' && (
                             <>
                             {/* Add Assignment Button - only show when on assignments tab */}
                             <div className="mb-4 flex justify-end">
                                 <button 
                                     onClick={() => setIsAssignmentFormOpen(true)} 
                                     className="flex items-center gap-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors"
                                 >
                                     <Icon className="w-5 h-5">
                                         <line x1="12" y1="5" x2="12" y2="19"/>
                                         <line x1="5" y1="12" x2="19" y2="12"/>
                                     </Icon>
                                     Tambah Tugas
                                 </button>
                             </div>
                             <div className="overflow-x-auto">
                                 <table className="w-full text-sm text-left">
                                     <thead className="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                                         <tr>
                                             <th className="px-6 py-3 text-slate-700 dark:text-slate-300">{t('gradebook_table_assignment')}</th>
                                             <th className="px-6 py-3 text-slate-700 dark:text-slate-300">{t('gradebook_table_due')}</th>
                                             <th className="px-6 py-3 text-slate-700 dark:text-slate-300">{t('gradebook_table_submissions')}</th>
                                             <th className="px-6 py-3 text-end text-slate-700 dark:text-slate-300">{t('gradebook_table_actions')}</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         {courseAssignments.map(assignment => {
                                             const stats = getAssignmentStats(assignment);
                                             return (
                                                 <tr key={assignment.id} className="border-b dark:border-slate-700">
                                                     <td className="px-6 py-4 font-medium text-slate-900 dark:text-white">{assignment.title}</td>
                                                     <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{new Date(assignment.dueDate).toLocaleDateString('id-ID')}</td>
                                                     <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{stats.submittedCount} / {totalStudents}</td>
                                             <td className="px-6 py-4 text-end">
                                                 <div className="flex justify-end gap-3">
                                                     <button 
                                                         onClick={() => {
                                                             setEditingAssignment(assignment);
                                                             setIsAssignmentFormOpen(true);
                                                         }}
                                                         className="font-semibold text-blue-600 dark:text-blue-400 hover:underline"
                                                         title="Edit assignment"
                                                     >
                                                         Edit
                                                     </button>
                                                     <button onClick={() => onSelectAssignment(assignment)} className="font-semibold text-brand-emerald-600 dark:text-brand-emerald-500 hover:underline">{t('gradebook_action_grade')}</button>
                                                 </div>
                                             </td>
                                                 </tr>
                                             );
                                         })}
                                     </tbody>
                                 </table>
                             </div>
                             </>
                         )}
                    </div>
                </div>
            </div>

             {isBadgeModalOpen && studentToBadge && (
                 <BadgeAwardModal student={studentToBadge} onClose={() => setIsBadgeModalOpen(false)} onAward={handleAwardBadge} />
             )}
             {isCertModalOpen && studentForCert && selectedCourse && (
                 <CertificateModal 
                     student={studentForCert} 
                     course={selectedCourse}
                     completionDate={studentProgress.find(sp => sp.studentId === studentForCert.studentId)?.completionDate || new Date().toISOString()}
                     onClose={() => setIsCertModalOpen(false)}
                 />
             )}
             {isAssignmentFormOpen && selectedCourse && (
                 <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={() => { setIsAssignmentFormOpen(false); setEditingAssignment(null); }} role="dialog" aria-modal="true">
                     <AssignmentForm
                         courseId={selectedCourse.id}
                         onSave={editingAssignment ? 
                             (assignmentData) => {
                                 // For editing, we need to update the existing assignment
                                 if (onUpdateAssignment) {
                                     onUpdateAssignment({ ...editingAssignment, ...assignmentData, id: editingAssignment.id });
                                 }
                                 setIsAssignmentFormOpen(false);
                                 setEditingAssignment(null);
                             } 
                             : handleSaveNewAssignment
                         }
                         onClose={() => { setIsAssignmentFormOpen(false); setEditingAssignment(null); }}
                         initialData={editingAssignment || undefined}
                     />
                 </div>
             )}
         </>
     );
 };
