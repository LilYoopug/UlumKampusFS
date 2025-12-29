import React from 'react';
import { Course } from '@/types';
import { LandingLayout } from '@/src/features/landing/components/LandingLayout';
import { CourseCard } from '@/src/features/courses/components/CourseCard';
import { useLanguage } from '@/contexts/LanguageContext';

interface PublicCourseCatalogProps {
  courses: Course[];
  onBack: () => void;
  onNavigateToLogin: () => void;
  onSelectCourse: (course: Course) => void;
}

export const PublicCourseCatalog: React.FC<PublicCourseCatalogProps> = ({ courses, onBack, onNavigateToLogin, onSelectCourse }) => {
  const { t } = useLanguage();

  return (
    <LandingLayout onNavigateToLogin={onNavigateToLogin} page="catalog" onBack={onBack}>
      <main className="pt-24 pb-20 bg-white dark:bg-slate-900">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h1 className="text-4xl font-bold text-slate-800 dark:text-white">{t('homepage_programs_title')}</h1>
            <p className="mt-2 text-lg text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">{t('catalog_subtitle')}</p>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {courses.map(course => (
              <CourseCard key={course.id} course={course} onSelectCourse={onSelectCourse} isPublic={true} />
            ))}
          </div>
        </div>
      </main>
    </LandingLayout>
  );
};