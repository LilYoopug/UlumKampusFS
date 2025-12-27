import React, { useState, useMemo, ChangeEvent } from 'react';
import { Assignment, Course, Submission, User } from '../../../../types';
import { useLanguage } from '../../../../contexts/LanguageContext';
import { Icon } from '../../../ui/components/Icon';
import { ALL_USERS } from '../../../../constants';
import { HafalanRecorder } from '../../../features/resources/components/HafalanRecorder';
import { numericToLetter } from '../../../../utils/gradeConverter';

const SubmissionGradingCard: React.FC<{
    submission: Submission;
    student?: User;
    onSaveGrade: (submission: Submission, gradeNumeric: number | undefined, feedback: string) => void;
}> = ({ submission, student, onSaveGrade }) => {
    const { t } = useLanguage();
    const [gradeNumeric, setGradeNumeric] = useState<string>(submission.gradeNumeric?.toString() ?? '');
    const [feedback, setFeedback] = useState(submission.feedback || '');
    
    const derivedGradeLetter = useMemo(() => {
        const num = parseInt(gradeNumeric, 10);
        return !isNaN(num) ? numericToLetter(num) : '';
    }, [gradeNumeric]);

    const handleSave = () => {
        const num = gradeNumeric === '' ? undefined : parseInt(gradeNumeric, 10);
        onSaveGrade(submission, num, feedback);
    };

    return (
        <div className="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-200 dark:border-slate-700">
            <div className="flex justify-between items-center">
                <div>
                    <p className="font-bold text-slate-800 dark:text-white">{student?.name || submission.studentId}</p>
                    <p className="text-xs text-slate-500 dark:text-slate-400">{t('assignments_submitted_on')} {new Date(submission.submittedAt).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })}</p>
                </div>
                <a href={submission.file.url} download className="flex items-center gap-2 text-sm font-semibold text-brand-emerald-600 hover:underline">
                    <Icon className="w-4 h-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></Icon>
                    {submission.file.name}
                </a>
            </div>
            <div className="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 space-y-4">
                {submission.gradeLetter || submission.gradeNumeric !== undefined ? (
                    <div>
                        <h4 className="font-semibold text-slate-800 dark:text-white mb-2">{t('assignments_grade')}: <span className="font-bold text-brand-emerald-60 dark:text-brand-emerald-40">{submission.gradeLetter} ({submission.gradeNumeric})</span></h4>
                        <h4 className="font-semibold text-slate-800 dark:text-white mb-1">{t('assignments_feedback')}:</h4>
                        <p className="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap">{submission.feedback}</p>
                    </div>
                ) : (
                    <>
                        <div>
                            <label htmlFor={`grade-${submission.studentId}`} className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Nilai (Angka)</label>
                            <div className="flex items-center gap-4">
                                <input
                                    id={`grade-${submission.studentId}`}
                                    type="number"
                                    min="0"
                                    max="100"
                                    value={gradeNumeric}
                                    onChange={(e) => setGradeNumeric(e.target.value)}
                                    className="w-24 px-3 py-2 rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                                />
                                {derivedGradeLetter && <span className="font-bold text-xl text-brand-emerald-600 dark:text-brand-emerald-400">{derivedGradeLetter}</span>}
                            </div>
                        </div>
                        <div>
                             <label htmlFor={`feedback-${submission.studentId}`} className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('assignments_feedback')}</label>
                            <textarea
                                id={`feedback-${submission.studentId}`}
                                value={feedback}
                                onChange={(e) => setFeedback(e.target.value)}
                                rows={4}
                                placeholder={t('assignments_enter_feedback')}
                                className="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                            />
                        </div>
                        <div className="text-end">
                            <button onClick={handleSave} className="px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">{t('assignments_save_grade')}</button>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};


export const AssignmentDetailView: React.FC<{
    assignment: Assignment;
    course?: Course;
    onBack: () => void;
    onUpdateAssignment: (updatedAssignment: Assignment) => void;
    currentUser: User;
}> = ({ assignment, course, onBack, onUpdateAssignment, currentUser }) => {
    const { t } = useLanguage();
    
    const isInstructor = currentUser.role === 'Dosen' && course?.instructor === currentUser.name;
    
    // State for editing assignment
    const [isEditing, setIsEditing] = useState(false);

    const userSubmissions = useMemo(() => 
        assignment.submissions
            .filter(s => s.studentId === currentUser.studentId)
            .sort((a, b) => new Date(b.submittedAt).getTime() - new Date(a.submittedAt).getTime())
    , [assignment.submissions, currentUser.studentId]);

    const latestSubmission = userSubmissions[0] || null;
    const submissionHistory = userSubmissions.slice(1);
    
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isDragging, setIsDragging] = useState(false);

    const dueDate = new Date(assignment.dueDate);
    const isPastDue = dueDate < new Date();
    const isOverdue = isPastDue && !latestSubmission;

    const handleFileChange = (event: ChangeEvent<HTMLInputElement>) => {
        if (event.target.files && event.target.files[0]) {
            setSelectedFile(event.target.files[0]);
        }
    };

    const handleNewSubmission = (newSubmission: Submission) => {
        setIsSubmitting(true);
        // Simulate network delay for upload
        setTimeout(() => {
            const updatedAssignment: Assignment = {
                ...assignment,
                submissions: [...assignment.submissions, newSubmission]
            };

            onUpdateAssignment(updatedAssignment);
            setSelectedFile(null);
            setIsSubmitting(false);
        }, 1500);
    };

    const handleFileSubmit = () => {
        if (!selectedFile) return;
        const newSubmission: Submission = {
            studentId: currentUser.studentId,
            submittedAt: new Date().toISOString(),
            file: {
                name: selectedFile.name,
                url: '#', // In a real app, this would be a URL from a storage service
            },
        };
        handleNewSubmission(newSubmission);
    };
    
    const handleAddAttachments = (event: ChangeEvent<HTMLInputElement>) => {
        if (event.target.files) {
            const newFiles = Array.from(event.target.files).map((file: File) => ({
                name: file.name,
                url: '#' // Placeholder URL
            }));
            
            const uniqueNewFiles = newFiles.filter(nf => !assignment.files.some((ef: { name: string; }) => ef.name === nf.name));

            const updatedFiles = [...assignment.files, ...uniqueNewFiles];
            onUpdateAssignment({ ...assignment, files: updatedFiles });
        }
    };

    const handleRemoveAttachment = (fileName: string) => {
        const updatedFiles = assignment.files.filter((f: { name: string }) => f.name !== fileName);
        onUpdateAssignment({ ...assignment, files: updatedFiles });
    };

    const handleSaveGrade = (submissionToUpdate: Submission, gradeNumeric: number | undefined, feedback: string) => {
        const gradeLetter = gradeNumeric !== undefined ? numericToLetter(gradeNumeric) : undefined;
        const updatedSubmissions = assignment.submissions.map(s => {
            if (s.studentId === submissionToUpdate.studentId && s.submittedAt === submissionToUpdate.submittedAt) {
                return { ...s, gradeNumeric, gradeLetter, feedback };
            }
            return s;
        });
        onUpdateAssignment({ ...assignment, submissions: updatedSubmissions });
    };


    const handleDragOver = (event: React.DragEvent<HTMLLabelElement>) => {
        event.preventDefault();
        setIsDragging(true);
    };

    const handleDragLeave = (event: React.DragEvent<HTMLLabelElement>) => {
        event.preventDefault();
        setIsDragging(false);
    };

    const handleDrop = (event: React.DragEvent<HTMLLabelElement>) => {
        event.preventDefault();
        setIsDragging(false);
        if (event.dataTransfer.files && event.dataTransfer.files[0]) {
            setSelectedFile(event.dataTransfer.files[0]);
        }
    };


    const renderSubmissionArea = (submission: Submission | null) => {
        if (!submission) return null;
        
        const formattedGrade = [submission.gradeLetter, submission.gradeNumeric !== undefined ? `(${submission.gradeNumeric})` : null]
            .filter(Boolean)
            .join(' ');

        return (
            <div className="space-y-6">
                <div>
                    <h3 className="text-lg font-bold mb-2 text-slate-800 dark:text-white">{t('assignments_submitted_file')}</h3>
                    <div className="p-4 bg-slate-100 dark:bg-slate-900/50 rounded-lg flex items-center justify-between">
                        <p className="font-medium text-slate-700 dark:text-slate-200">{submission.file.name}</p>
                        <a href={submission.file.url} download className="text-brand-emerald-600 hover:underline">{t('assignments_download_file')}</a>
                    </div>
                    <p className="text-sm text-slate-500 dark:text-slate-400 mt-2">{t('assignments_submitted_on')} {new Date(submission.submittedAt).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' })}</p>
                </div>
                
                {formattedGrade && (
                     <div>
                        <h3 className="text-lg font-bold mb-2 text-slate-80 dark:text-white">{t('assignments_grade')}</h3>
                        <div className="p-4 bg-brand-sand-100 dark:bg-brand-sand-900/50 rounded-lg">
                            <p className="text-3xl font-bold text-brand-sand-700 dark:text-brand-sand-200">{formattedGrade}</p>
                        </div>
                    </div>
                )}
                
                <div>
                    <h3 className="text-lg font-bold mb-2 text-slate-800 dark:text-white">{t('assignments_feedback')}</h3>
                    <div className="p-4 border border-slate-200 dark:border-slate-700 rounded-lg min-h-[100px]">
                        {submission.feedback ? (
                            <p className="text-slate-600 dark:text-slate-300 whitespace-pre-wrap">{submission.feedback}</p>
                        ) : (
                            <p className="text-slate-500 dark:text-slate-400">{t('assignments_no_feedback')}</p>
                        )}
                    </div>
                </div>
            </div>
        );
    }
    
    const renderSubmissionInput = () => {
        if (isOverdue) {
            return (
                <div className="text-center p-8 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <p className="font-bold text-red-600 dark:text-red-300">{t('assignments_overdue')}</p>
                    <p className="text-sm text-red-500 dark:text-red-400">{t('assignments_deadline_passed')}</p>
                </div>
            )
        }
        
        if (assignment.type === 'hafalan') {
            return <HafalanRecorder assignment={assignment} onNewSubmission={handleNewSubmission} currentUser={currentUser} />;
        }
      
        return (
            <div>
                <label
                    htmlFor="file-upload"
                    onDragOver={handleDragOver}
                    onDragLeave={handleDragLeave}
                    onDrop={handleDrop}
                    className={`relative flex flex-col items-center justify-center w-full h-48 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-lg cursor-pointer bg-slate-50 dark:bg-slate-900/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors ${isDragging ? 'border-brand-emerald-500 bg-brand-emerald-50 dark:bg-brand-emerald-900/50' : ''}`}
                >
                    <div className="flex flex-col items-center justify-center pt-5 pb-6">
                        <Icon className="w-10 h-10 mb-3 text-slate-400"><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></Icon>
                        <p className="mb-2 text-sm text-slate-500 dark:text-slate-400 font-semibold">{t('assignments_select_file_cta')}</p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">{t('assignments_drag_drop_cta')}</p>
                    </div>
                    <input id="file-upload" type="file" className="hidden" onChange={handleFileChange} />
                </label>
                {selectedFile && (
                    <div className="mt-4 font-medium text-slate-700 dark:text-slate-300">{t('assignments_file_selected')} <span className="text-brand-emerald-600 dark:text-brand-emerald-400">{selectedFile.name}</span></div>
                )}
                <div className="mt-6 text-end">
                    <button
                    onClick={handleFileSubmit}
                    disabled={!selectedFile || isSubmitting}
                    className="px-6 py-2.5 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 disabled:bg-slate-400 disabled:cursor-not-allowed transition-colors flex items-center justify-center min-w-[180px]"
                    >
                        {isSubmitting ? (
                            <>
                                <Icon className="w-5 h-5 animate-spin me-2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></Icon>
                                {t('assignments_submitting_button')}
                            </>
                        ) : (
                            latestSubmission ? t('assignments_upload_new_version') : t('assignments_submit_button')
                        )}
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <button onClick={onBack} className="flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors font-medium">
                <Icon className="w-5 h-5"><path d="m15 18-6-6 6-6"/></Icon>
                <span>{t('assignments_back_to_list')}</span>
            </button>
            
            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <p className="text-sm font-semibold text-brand-emerald-600 dark:text-brand-emerald-40">{course?.title || t('assignments_unknown_course')}</p>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white mt-1">{assignment.title}</h1>
                <p className={`mt-2 ${isOverdue ? 'text-red-500' : 'text-slate-500 dark:text-slate-400'}`}>{t('assignments_due_date')} {new Date(assignment.dueDate).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' })} {isOverdue && `(${t('assignments_overdue')})`}</p>
                
                 {isEditing ? (
                     <div className="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700 space-y-4">
                         <div>
                             <label className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Judul Tugas</label>
                             <input
                                 type="text"
                                 defaultValue={assignment.title}
                                 onChange={(e) => {
                                     const updatedAssignment = { ...assignment, title: e.target.value };
                                     onUpdateAssignment(updatedAssignment);
                                 }}
                                 className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                             />
                         </div>
                         <div>
                             <label className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Deskripsi</label>
                             <textarea
                                 defaultValue={assignment.description}
                                 onChange={(e) => {
                                     const updatedAssignment = { ...assignment, description: e.target.value };
                                     onUpdateAssignment(updatedAssignment);
                                 }}
                                 rows={5}
                                 className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                             />
                         </div>
                         <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                             <div>
                                 <label className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Batas Waktu</label>
                                 <input
                                     type="datetime-local"
                                     defaultValue={new Date(assignment.dueDate).toISOString().slice(0, 16)}
                                     onChange={(e) => {
                                         const updatedAssignment = { ...assignment, dueDate: new Date(e.target.value).toISOString() };
                                         onUpdateAssignment(updatedAssignment);
                                     }}
                                     className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                                 />
                             </div>
                             <div>
                                 <label className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Kategori</label>
                                 <select
                                     defaultValue={assignment.category}
                                     onChange={(e) => {
                                         const updatedAssignment = { ...assignment, category: e.target.value as 'Tugas' | 'Ujian' };
                                         onUpdateAssignment(updatedAssignment);
                                     }}
                                     className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                                 >
                                     <option value="Tugas" className="dark:bg-slate-700 dark:text-white">Tugas</option>
                                     <option value="Ujian" className="dark:bg-slate-700 dark:text-white">Ujian</option>
                                 </select>
                             </div>
                         </div>
                         <div className="flex justify-end gap-2 pt-2">
                             <button
                                 onClick={() => setIsEditing(false)}
                                 className="px-4 py-2 bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white rounded-lg hover:bg-slate-300 dark:hover:bg-slate-500"
                             >
                                 Batal
                             </button>
                         </div>
                     </div>
                 ) : (
                     <div className="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                         <h3 className="text-lg font-bold mb-2 text-slate-80 dark:text-white">{t('assignments_instructions')}</h3>
                         <p className="text-slate-600 dark:text-slate-300 whitespace-pre-wrap">{assignment.description}</p>
                     </div>
                 )}
                
                <div className="mt-6">
                    <h3 className="text-lg font-bold mb-2 text-slate-800 dark:text-white">{t('assignments_attachments')}</h3>
                    {assignment.files.length > 0 ? (
                        <ul className="space-y-2">
                           {assignment.files.map(file => (
                               <li key={file.name} className="flex items-center justify-between p-2 bg-slate-50 dark:bg-slate-900/50 rounded-md group">
                                   <a href={file.url} download className="flex items-center gap-2 text-brand-emerald-700 hover:underline dark:text-brand-emerald-400">
                                       <Icon className="w-5 h-5"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.59a2 2 0 0 1-2.83-2.83l.79-.79"/></Icon>
                                       {file.name}
                                   </a>
                                   {isInstructor && (
                                       <button onClick={() => handleRemoveAttachment(file.name)} className="p-1 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity" title={`Remove ${file.name}`}>
                                            <Icon className="w-4 h-4"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></Icon>
                                       </button>
                                   )}
                               </li>
                           ))}
                        </ul>
                    ) : (
                        <p className="text-sm text-slate-500 dark:text-slate-400 italic">No attachments for this assignment.</p>
                    )}
                    {isInstructor && (
                        <div className="mt-4">
                            <label className="cursor-pointer inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-white font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors text-sm">
                                <Icon className="w-5 h-5"><path d="M5 12h14"/><path d="M12 5v14"/></Icon>
                                {t('assignments_add_files')}
                                <input type="file" multiple className="hidden" onChange={handleAddAttachments} />
                            </label>
                        </div>
                    )}
                </div>
            </div>
            
            { isInstructor &&
                <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                    <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">{t('assignments_submissions_by_students')}</h2>
                    {assignment.submissions.length > 0 ? (
                        <div className="space-y-4">
                            {assignment.submissions.map(sub => (
                                <SubmissionGradingCard
                                    key={`${sub.studentId}-${sub.submittedAt}`}
                                    submission={sub}
                                    student={ALL_USERS.find(u => u.studentId === sub.studentId)}
                                    onSaveGrade={handleSaveGrade}
                                />
                            ))}
                        </div>
                    ) : (
                        <p className="text-center text-slate-500 dark:text-slate-400 py-8">{t('assignments_no_submissions_yet')}</p>
                    )}
                </div>
            }

            { currentUser.role === 'Mahasiswa' &&
                <>
                    <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                        <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">{latestSubmission ? t('assignments_current_submission') : t('assignments_submission')}</h2>
                        {renderSubmissionArea(latestSubmission)}
                        {!latestSubmission && renderSubmissionInput()}
                    </div>
                    
                    {submissionHistory.length > 0 && (
                        <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                            <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">{t('assignments_submission_history')}</h2>
                            <div className="space-y-6">
                                {submissionHistory.map((sub, index) => (
                                <div key={sub.submittedAt} className="border-t border-slate-200 dark:border-slate-700 pt-6">
                                    <h3 className="text-lg font-bold mb-4 text-slate-600 dark:text-slate-300">{t('assignments_version')} {submissionHistory.length - index}</h3>
                                    {renderSubmissionArea(sub)}
                                </div>
                                ))}
                            </div>
                        </div>
                    )}
                    
                    {latestSubmission && (
                        <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                            <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">{t('assignments_upload_new_version')}</h2>
                            {renderSubmissionInput()}
                        </div>
                    )}
                </>
            }
        </div>
    );
};
