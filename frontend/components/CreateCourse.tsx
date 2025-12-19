import React from 'react';
import { CourseForm } from './CourseForm';
import { Course } from '../types';
import { useLanguage } from '../contexts/LanguageContext';

interface CreateCourseProps {
    onSave: (courseData: Course) => void;
    onCancel: () => void;
    initialData?: Course | null;
}

export const CreateCourse: React.FC<CreateCourseProps> = ({ onSave, onCancel, initialData }) => {
    const { t } = useLanguage();
    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">
                    {initialData ? t('edit_course_title') : t('create_course_title')}
                </h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">
                    {initialData ? t('edit_course_subtitle') : t('create_course_subtitle')}
                </p>
            </div>
            <CourseForm
                onSave={onSave}
                onCancel={onCancel}
                initialData={initialData}
            />
        </div>
    );
};
