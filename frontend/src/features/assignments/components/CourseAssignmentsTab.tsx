import React, { useState, useMemo, useEffect } from 'react';
import { Assignment, Course, User } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { AssignmentCard } from './AssignmentCard';
import { AssignmentDetailView } from './AssignmentDetailView';
import { Icon } from '@/src/ui/components/Icon';
import { AssignmentForm } from './AssignmentForm';
import { assignmentAPI, courseAPI } from '@/services/apiService';


interface CourseAssignmentsTabProps {
    courseId: string;
    currentUser: User;
    assignments: Assignment[];
    onCreateAssignment: (assignment: Omit<Assignment, 'id' | 'submissions'>) => void;
    onUpdateAssignment: (assignment: Assignment) => void;
}

export const CourseAssignmentsTab: React.FC<CourseAssignmentsTabProps> = ({ courseId, currentUser, assignments: initialAssignments, onCreateAssignment, onUpdateAssignment }) => {
    const { t } = useLanguage();
    const [view, setView] = useState<'list' | 'detail'>('list');
    const [selectedAssignment, setSelectedAssignment] = useState<Assignment | null>(null);
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editingAssignment, setEditingAssignment] = useState<Assignment | null>(null);
    const [assignments, setAssignments] = useState<Assignment[]>(initialAssignments);
    const [course, setCourse] = useState<Course | null>(null);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Fetch course and assignments
    useEffect(() => {
        fetchData();
    }, [courseId]);

    const fetchData = async () => {
        try {
            setLoading(true);
            setError(null);

            // Fetch course details
            const courseResponse = await courseAPI.getById(courseId);
            const courseData = courseResponse.data as any;
            setCourse(courseData?.data || courseData);

            // Fetch assignments for this course
            const assignmentsResponse = await assignmentAPI.getAll({ course_id: courseId });
            const assignmentsData = assignmentsResponse.data as any;
            const assignmentsList: Assignment[] = Array.isArray(assignmentsData?.data)
                ? assignmentsData.data
                : Array.isArray(assignmentsData)
                    ? assignmentsData
                    : [];
            setAssignments(assignmentsList);
        } catch (error) {
            console.error('Error fetching data:', error);
            setError('Gagal memuat data. Silakan coba lagi.');
            // Fallback to initial assignments
            setAssignments(initialAssignments);
        } finally {
            setLoading(false);
        }
    };

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
        setAssignments(prev => prev.map(a => a.id === updatedAssignment.id ? updatedAssignment : a));
        setSelectedAssignment(updatedAssignment);
        onUpdateAssignment(updatedAssignment);
    };
    
    const handleSaveNewAssignment = async (newAssignmentData: Omit<Assignment, 'id' | 'submissions'>) => {
        try {
            setSaving(true);
            setError(null);

            // Prepare data for API - map frontend format to backend format
            const apiData: any = {
                course_id: newAssignmentData.courseId,
                title: newAssignmentData.title,
                description: newAssignmentData.description,
                due_date: newAssignmentData.dueDate,
                submission_type: newAssignmentData.type || 'file',
                category: newAssignmentData.category || 'Tugas',
                max_points: newAssignmentData.maxScore || 100,
                instructions: newAssignmentData.instructions || newAssignmentData.description,
                is_published: true,
            };

            const response = await assignmentAPI.create(apiData);
            const responseData = response.data as any;
            const newAssignment: Assignment = responseData?.data || responseData;
            
            setAssignments(prev => [...prev, newAssignment]);
            setIsFormOpen(false);
            onCreateAssignment(newAssignmentData);
        } catch (error: any) {
            console.error('Error creating assignment:', error);
            const errorMessage = error.response?.data?.message || 'Gagal membuat tugas. Silakan coba lagi.';
            setError(errorMessage);
            alert(errorMessage);
        } finally {
            setSaving(false);
        }
    };

    const handleUpdateAssignment = async (assignmentData: any) => {
        if (!editingAssignment) return;

        try {
            setSaving(true);
            setError(null);

            // Prepare data for API - map frontend format to backend format
            const apiData: any = {
                title: assignmentData.title,
                description: assignmentData.description,
                due_date: assignmentData.dueDate,
                submission_type: assignmentData.type || 'file',
                category: assignmentData.category || 'Tugas',
                max_points: assignmentData.maxScore || 100,
                instructions: assignmentData.instructions || assignmentData.description,
            };

            const response = await assignmentAPI.update(editingAssignment.id, apiData);
            const responseData = response.data as any;
            const updatedAssignment: Assignment = responseData?.data || responseData;
            
            setAssignments(prev => prev.map(a => a.id === editingAssignment.id ? updatedAssignment : a));
            setIsFormOpen(false);
            setEditingAssignment(null);
            onUpdateAssignment({ ...editingAssignment, ...updatedAssignment });
        } catch (error: any) {
            console.error('Error updating assignment:', error);
            const errorMessage = error.response?.data?.message || 'Gagal memperbarui tugas. Silakan coba lagi.';
            setError(errorMessage);
            alert(errorMessage);
        } finally {
            setSaving(false);
        }
    };

    const handleDeleteAssignment = async (assignmentId: string) => {
        if (!confirm('Apakah Anda yakin ingin menghapus tugas ini?')) return;

        try {
            setSaving(true);
            await assignmentAPI.delete(assignmentId);
            setAssignments(prev => prev.filter(a => a.id !== assignmentId));
        } catch (error: any) {
            console.error('Error deleting assignment:', error);
            alert('Gagal menghapus tugas. Silakan coba lagi.');
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center py-8">
                <div className="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-brand-emerald-500"></div>
            </div>
        );
    }

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
            {error && (
                <div className="p-3 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 rounded-lg text-sm">
                    {error}
                </div>
            )}

            {isInstructor && (
                <div className="text-end mb-4">
                    <button onClick={() => setIsFormOpen(true)} className="flex items-center gap-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors ml-auto" disabled={saving}>
                        <Icon className="w-5 h-5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></Icon>
                        {t('assignment_form_add_button')}
                    </button>
                </div>
            )}
             {courseAssignments.length > 0 ? (
                 courseAssignments.map(assignment => (
                     <AssignmentCard 
                         key={assignment.id}
                         assignment={assignment} 
                         onSelectAssignment={handleViewDetails} 
                         course={course} 
                         currentUser={currentUser}
                         onEdit={isInstructor ? (a) => {
                             setEditingAssignment(a);
                             setIsFormOpen(true);
                         } : undefined}
                         onDelete={isInstructor ? handleDeleteAssignment : undefined}
                         isSaving={saving}
                     />
                 ))
             ) : (
                <div className="text-center py-16 text-slate-500">
                    <Icon className="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600">
                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </Icon>
                    <p className="mt-4">Belum ada tugas untuk mata kuliah ini.</p>
                    {isInstructor && (
                        <button 
                            onClick={() => setIsFormOpen(true)}
                            className="mt-4 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors"
                        >
                            Buat Tugas Pertama
                        </button>
                    )}
                </div>
            )}
            
             {isFormOpen && (
                  <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={() => { if (!saving) { setIsFormOpen(false); setEditingAssignment(null); } }} role="dialog" aria-modal="true">
                     <AssignmentForm
                         courseId={courseId}
                         onSave={editingAssignment ? handleUpdateAssignment : handleSaveNewAssignment}
                         onClose={() => { setIsFormOpen(false); setEditingAssignment(null); }}
                         initialData={editingAssignment || undefined}
                     />
                 </div>
             )}
        </div>
    );
};
