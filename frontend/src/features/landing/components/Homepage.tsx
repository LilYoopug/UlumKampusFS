import React, { useEffect, useState } from 'react';
import { LandingLayout } from '@/src/features/landing/components/LandingLayout';
import { useLanguage } from '@/contexts/LanguageContext';
import { Icon } from '@/src/ui/components/Icon';
import { useIntersectionObserver } from '@/hooks/useIntersectionObserver';
import { AnimatedSection } from '@/src/ui/components/AnimatedSection';
import { AboutSection } from '@/src/features/landing/components/AboutSection';
import { FaqSection } from '@/src/features/landing/components/FaqSection';
import { ContactSection } from '@/src/features/landing/components/ContactSection';
import { handleNavClick } from '@/App';
import { facultyAPI, courseAPI } from '@/services/apiService';
import { Faculty, Course } from '@/types';

interface HomepageProps {
  onNavigateToLogin: () => void;
  onNavigateToRegister: () => void;
  onNavigateToCatalog: () => void;
}

export const Homepage: React.FC<HomepageProps> = ({ onNavigateToLogin, onNavigateToRegister, onNavigateToCatalog }) => {
  const { t } = useLanguage();
  const [faculties, setFaculties] = useState<Faculty[]>([]);
  const [courses, setCourses] = useState<Course[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [facultiesResponse, coursesResponse] = await Promise.all([
          facultyAPI.getPublic(),
          courseAPI.getPublic()
        ]);
        setFaculties(facultiesResponse.data);
        setCourses(coursesResponse.data);
      } catch (error) {
        console.error('Failed to fetch data:', error);
        setFaculties([]);
        setCourses([]);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);
  
  const features = [
    { title: t('homepage_feature1_title'), description: t('homepage_feature1_desc'), icon: <><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></> },
    { title: t('homepage_feature2_title'), description: t('homepage_feature2_desc'), icon: <path d="M18 8.86a4 4 0 1 0-8 0c0 1.44.51 2.73 1.32 3.64-1.23.23-2.19.4-2.82.52-2.1.41-3.5 1.55-3.5 2.98v2.02c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2v-2.02c0-1.43-1.4-2.57-3.5-2.98-.63-.12-1.59-.29-2.82-.52.81-.91 1.32-2.2 1.32-3.64Z"/> },
    { title: t('homepage_feature3_title'), description: t('homepage_feature3_desc'), icon: <><path d="M12.22 2h-4.44l-2 6-6 2 6 2 2 6 2-6 6-2-6-2z"/><path d="M20.91 14.65a2.43 2.43 0 0 0-2.26 2.26l.09.63a2.43 2.43 0 0 0 2.26 2.26l.63.09a2.43 2.43 0 0 0 2.26-2.26l-.09-.63a2.43 2.43 0 0 0-2.26-2.26Z"/></> },
    { title: t('homepage_feature4_title'), description: t('homepage_feature4_desc'), icon: <><path d="M21.5 21H16V3h5.5a2.5 2.5 0 0 1 0 5h-5.5"/><path d="M2 3h5.5a2.5 2.5 0 0 1 0 5H2V3z"/><path d="M12 3v18"/></> },
  ];

  const testimonials = [
      { text: t('homepage_testimonial1_text'), name: t('homepage_testimonial1_name'), role: t('homepage_testimonial1_role'), avatar: 'https://picsum.photos/seed/fauzan/100/100' },
      { text: t('homepage_testimonial2_text'), name: t('homepage_testimonial2_name'), role: t('homepage_testimonial2_role'), avatar: 'https://picsum.photos/seed/aisyah/100/100' },
      { text: t('homepage_testimonial3_text'), name: t('homepage_testimonial3_name'), role: t('homepage_testimonial3_role'), avatar: 'https://picsum.photos/seed/iqbal/100/100' },
  ];

  const handleNavigate = (e: React.MouseEvent<HTMLAnchorElement>) => {
    e.preventDefault();
    onNavigateToCatalog();
  };

  return (
    <LandingLayout onNavigateToLogin={onNavigateToLogin}>
      <main>
        {/* Hero Section */}
        <section className="relative h-[90vh] min-h-[600px] flex items-center text-white">
          <div className="absolute inset-0 bg-black/60 z-10"></div>
          <div className="absolute inset-0 bg-cover bg-center" style={{backgroundImage: "url('https://picsum.photos/seed/campus/1800/1200')"}}></div>
          <div className="container mx-auto px-4 relative z-20 text-center">
            <h1 className="text-4xl md:text-6xl font-extrabold leading-tight text-shadow-lg">{t('homepage_hero_title')}</h1>
            <p className="mt-4 max-w-3xl mx-auto text-lg md:text-xl text-slate-200 text-shadow">{t('homepage_hero_subtitle')}</p>
            <div className="mt-8 flex justify-center gap-4">
              <button onClick={onNavigateToRegister} className="px-8 py-3 bg-brand-emerald-600 font-semibold rounded-full hover:bg-brand-emerald-500 transition-transform transform hover:scale-105">{t('homepage_hero_cta_register')}</button>
              <a href="#" onClick={handleNavigate} className="px-8 py-3 bg-white/20 backdrop-blur-sm font-semibold rounded-full hover:bg-white/30 transition-colors">{t('homepage_hero_cta_explore')}</a>
            </div>
          </div>
        </section>

        {/* Features Section */}
        <AnimatedSection className="py-20 bg-slate-50 dark:bg-brand-midnight">
          <div className="container mx-auto px-4 text-center">
            <h2 className="text-3xl md:text-4xl font-bold text-slate-800 dark:text-white">{t('homepage_features_title')}</h2>
            <div className="mt-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              {features.map((feature, index) => (
                <div key={index} className="p-8 bg-white dark:bg-slate-800/50 rounded-2xl shadow-lg text-start">
                  <div className="p-3 inline-block bg-brand-emerald-100 dark:bg-brand-emerald-900/50 rounded-xl text-brand-emerald-600 dark:text-brand-emerald-400">
                    <Icon className="w-8 h-8">{feature.icon}</Icon>
                  </div>
                  <h3 className="mt-4 text-xl font-bold text-slate-800 dark:text-white">{feature.title}</h3>
                  <p className="mt-2 text-slate-500 dark:text-slate-400">{feature.description}</p>
                </div>
              ))}
            </div>
          </div>
        </AnimatedSection>

        <AboutSection />

        {/* Programs Section */}
        <AnimatedSection id="programs" className="py-20 bg-white dark:bg-slate-900">
          <div className="container mx-auto px-4 text-center">
            <h2 className="text-3xl md:text-4xl font-bold text-slate-800 dark:text-white">{t('homepage_programs_title')}</h2>
            <p className="mt-2 text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">{t('homepage_programs_subtitle')}</p>
            <div className="mt-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              {loading ? (
                // Loading skeleton
                Array.from({ length: 4 }).map((_, index) => (
                  <div key={index} className="animate-pulse bg-slate-200 dark:bg-slate-700 rounded-2xl h-96"></div>
                ))
              ) : (
                faculties.slice(0, 4).map(faculty => (
                  <div key={faculty.id} className="group relative overflow-hidden rounded-2xl shadow-lg">
                    <img src={`https://picsum.photos/seed/${faculty.id}/500/700`} alt={faculty.name} className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                    <div className="absolute bottom-0 left-0 right-0 p-6 text-white text-start">
                      <h3 className="text-2xl font-bold">{faculty.name}</h3>
                      <p className="mt-1 text-slate-200">{faculty.description}</p>
                    </div>
                  </div>
                ))
              )}
            </div>
            <a href="#" onClick={handleNavigate} className="mt-12 inline-block px-8 py-3 border-2 border-brand-emerald-600 text-brand-emerald-600 font-semibold rounded-full hover:bg-brand-emerald-600 hover:text-white transition-colors">
              {t('homepage_programs_view_all')}
            </a>
          </div>
        </AnimatedSection>

        {/* Courses Section */}
        <AnimatedSection className="py-20 bg-slate-50 dark:bg-brand-midnight">
          <div className="container mx-auto px-4">
            <div className="text-center mb-12">
              <h2 className="text-3xl md:text-4xl font-bold text-slate-800 dark:text-white">Jelajahi berbagai disiplin ilmu Islam yang otentik dan relevan.</h2>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {loading ? (
                // Loading skeleton
                Array.from({ length: 6 }).map((_, index) => (
                  <div key={index} className="animate-pulse bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 h-64"></div>
                ))
              ) : (
                courses.slice(0, 6).map(course => (
                  <div key={course.id} className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div className="flex items-start gap-4">
                      <div className="flex-1">
                        <h3 className="text-xl font-bold text-slate-800 dark:text-white mb-2">{course.name}</h3>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mb-3">{course.faculty?.name || 'Fakultas'}</p>
                        <div className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                          <Icon className="w-4 h-4"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></Icon>
                          <span>{course.instructor || 'Instructor'}</span>
                        </div>
                        <div className="flex items-center gap-4 mt-3">
                          <span className="text-sm text-brand-emerald-600 dark:text-brand-emerald-400 font-semibold">{course.sks || course.credit_hours || 0} SKS</span>
                          <span className="text-sm px-3 py-1 bg-brand-emerald-100 dark:bg-brand-emerald-900/50 text-brand-emerald-600 dark:text-brand-emerald-400 rounded-full">
                            {course.mode === 'live' ? 'Live' : 'VOD'}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                ))
              )}
            </div>
            <div className="mt-12 text-center">
              <a href="#" onClick={handleNavigate} className="inline-block px-8 py-3 bg-brand-emerald-600 text-white font-semibold rounded-full hover:bg-brand-emerald-500 transition-colors">
                Lihat Semua Program
              </a>
            </div>
          </div>
        </AnimatedSection>
        
        {/* Testimonials Section */}
        <AnimatedSection className="py-20 bg-slate-50 dark:bg-brand-midnight">
          <div className="container mx-auto px-4">
             <h2 className="text-3xl md:text-4xl font-bold text-slate-800 dark:text-white text-center">{t('homepage_testimonials_title')}</h2>
             <div className="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
                {testimonials.map((testimonial, index) => (
                    <div key={index} className="p-8 bg-white dark:bg-slate-800/50 rounded-2xl shadow-lg flex flex-col">
                        <Icon className="w-10 h-10 text-brand-sand-400"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.75-2-2-2H4c-1.25 0-2 .75-2 2v6c0 7 4 8 7 8Z"/><path d="M17 21c3 0 7-1 7-8V5c0-1.25-.75-2-2-2h-4c-1.25 0-2 .75-2 2v6c0 7 4 8 7 8Z"/></Icon>
                        <p className="mt-4 text-slate-600 dark:text-slate-300 flex-grow">"{testimonial.text}"</p>
                        <div className="mt-6 flex items-center gap-4 pt-6 border-t border-slate-200 dark:border-slate-700">
                            <img src={testimonial.avatar} alt={testimonial.name} className="w-12 h-12 rounded-full" />
                            <div>
                                <p className="font-semibold text-slate-800 dark:text-white">{testimonial.name}</p>
                                <p className="text-sm text-slate-500 dark:text-slate-400">{testimonial.role}</p>
                            </div>
                        </div>
                    </div>
                ))}
             </div>
          </div>
        </AnimatedSection>

        <FaqSection />
        
        <ContactSection />

        {/* CTA Section */}
        <AnimatedSection className="py-20 bg-cover bg-center text-white" style={{backgroundImage: `linear-gradient(rgba(6, 78, 59, 0.8), rgba(6, 78, 59, 0.8)), url('https://picsum.photos/seed/cta-bg/1800/600')`}}>
          <div className="container mx-auto px-4 text-center">
             <h2 className="text-3xl md:text-4xl font-bold">{t('homepage_cta_title')}</h2>
             <p className="mt-2 text-slate-200 max-w-2xl mx-auto">{t('homepage_cta_subtitle')}</p>
             <button onClick={onNavigateToRegister} className="mt-8 px-8 py-3 bg-white text-brand-emerald-700 font-semibold rounded-full hover:bg-slate-200 transition-transform transform hover:scale-105">{t('homepage_hero_cta_register')}</button>
          </div>
        </AnimatedSection>

      </main>
    </LandingLayout>
  );
};
