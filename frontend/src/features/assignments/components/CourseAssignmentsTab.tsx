import React, { useState, useMemo } from 'react';
import { Assignment, Course, User } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { AssignmentCard } from './AssignmentCard';
import { AssignmentDetailView } from './AssignmentDetailView';
import { Icon } from '@/src/ui/components/Icon';
import { AssignmentForm } from './AssignmentForm';
import { COURSES_DATA } from '@/constants';


interface CourseAssignmentsTabProps {
    courseId: string;
    currentUser: User;
    assignments: Assignment[];
    onCreateAssignment: (assignment: Omit<Assignment, 'id' | 'submissions'>) => void;
    onUpdateAssignment: (assignment: Assignment) => void;
}

export const CourseAssignmentsTab: React.FC<CourseAssignmentsTabProps> = ({ courseId, currentUser, assignments, onCreateAssignment, onUpdateAssignment }) => {
    const { t } = useLanguage();
    const [view, setView] = useState<'list' | 'detail'>('list');
    const [selectedAssignment, setSelectedAssignment] = useState<Assignment | null>(null);
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editingAssignment, setEditingAssignment] = useState<Assignment | null>(null);

    const course = useMemo(() => COURSES_DATA.find(c => c.id === courseId), [courseId]);
    const courseAssignments = useMemo(() => assignments.filter(a => a.courseId === courseId), [assignments, courseId]);
    
    const isInstructor = currentUser.role === 'Dosen' && course?.instructor === currentUser.name;

    const handleViewDetails = (assignment: Assignment) => {
        setSelectedAssignment(assignment);
        setView('detail');
    };

    const handleBackToList = () => {
        setSelectedAssignment(null);
        setView('list');
    };
    
    const handleUpdateAssignmentAndList = (updatedAssignment: Assignment) => {
        onUpdateAssignment(updatedAssignment);
        setSelectedAssignment(updatedAssignment); 
    };
    
    const handleSaveNewAssignment = (newAssignmentData: Omit<Assignment, 'id' | 'submissions'>) => {
        onCreateAssignment(newAssignmentData);
        setIsFormOpen(false);
    };

    if (view === 'detail' && selectedAssignment) {
        return (
            <AssignmentDetailView 
                assignment={selectedAssignment}
                course={course}
                onBack={handleBackToList}
                onUpdateAssignment={handleUpdateAssignmentAndList}
                currentUser={currentUser} 
            />
        );
    }
    
    return (
        <div className="space-y-4">
            {isInstructor && (
                <div className="text-end mb-4">
                    <button onClick={() => setIsFormOpen(true)} className="flex items-center gap-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors ml-auto">
                        <Icon className="w-5 h-5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></Icon>
                        {t('assignment_form_add_button')}
                    </button>
                </div>
            )}
             {courseAssignments.length > 0 ? (
                 courseAssignments.map(assignment => (
                     <div key={assignment.id} className="relative">
                         <AssignmentCard 
                             assignment={assignment} 
                             onSelectAssignment={handleViewDetails} 
                             course={course} 
                             currentUser={currentUser} 
                         />
                         {isInstructor && (
                             <button 
                                 onClick={() => {
                                     setEditingAssignment(assignment);
                                     setIsFormOpen(true);
                                 }}
                                 className="absolute top-4 right-24 p-2 text-slate-500 hover:text-blue-500 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700"
                                 title="Edit assignment"
                             >
                                 <Icon className="w-5 h-5"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></Icon>
                             </button>
                         )}
                     </div>
                 ))
             ) : (
                <div className="text-center py-16 text-slate-500">
                    <p>Belum ada tugas untuk mata kuliah ini.</p>
                </div>
            )}
            
             {isFormOpen && (
                  <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={() => { setIsFormOpen(false); setEditingAssignment(null); }} role="dialog" aria-modal="true">
                     <AssignmentForm
                         courseId={courseId}
                         onSave={editingAssignment ? 
                             (assignmentData) => {
                                 // For editing, we need to update the existing assignment
                                 onUpdateAssignment({ ...editingAssignment, ...assignmentData, id: editingAssignment.id });
                                 setIsFormOpen(false);
                                 setEditingAssignment(null);
                             } 
                             : handleSaveNewAssignment
                         }
                         onClose={() => { setIsFormOpen(false); setEditingAssignment(null); }}
                         initialData={editingAssignment || undefined}
                     />
                 </div>
             )}
        </div>
    );
};
