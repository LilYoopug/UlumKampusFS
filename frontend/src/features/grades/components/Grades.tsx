import React, { useState, useEffect, useRef } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { Icon } from '@/src/ui/components/Icon';
import { Course, User } from '@/types';
import { gradeAPI, studentAPI } from '@/services/apiService';

const CertificateModal: React.FC<{ course: Course; onClose: () => void; currentUser: User; }> = ({ course, onClose, currentUser }) => {
    const { t } = useLanguage();

    const handlePrint = () => {
        const printContent = document.getElementById('certificate-to-print');
        if (printContent) {
            const printHtml = printContent.innerHTML;
            
            const iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);
            
            const doc = iframe.contentWindow?.document;
            if (doc) {
                doc.open();
                doc.write(`
                    <html>
                        <head>
                            <title>${t('grades_certificate_of_completion')} - ${course.title}</title>
                            <script src="https://cdn.tailwindcss.com"></script>
                            <style>
                                @page { size: A4 landscape; margin: 0; }
                                body { 
                                    -webkit-print-color-adjust: exact; 
                                    print-color-adjust: exact;
                                    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
                                }
                                .brand-text { color: #047857; }
                            </style>
                        </head>
                        <body>
                            ${printHtml}
                        </body>
                    </html>
                `);
                doc.close();
                iframe.contentWindow?.focus();
                iframe.contentWindow?.print();
            }
            
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        }
    };
    
    return (
         <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={onClose} role="dialog" aria-modal="true">
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-3xl" onClick={e => e.stopPropagation()}>
                <div id="certificate-to-print" className="p-8 md:p-12 aspect-[297/210] flex flex-col justify-center items-center text-center bg-slate-50 dark:bg-slate-900 border-8 border-brand-emerald-700 dark:border-brand-emerald-600 relative">
                     <div className="absolute inset-2 border-2 border-brand-emerald-500/50 dark:border-brand-emerald-400/50"></div>
                     <div className="relative z-10">
                        <div className="flex items-center justify-center gap-3">
                             <div className="p-2 bg-brand-emerald-600 rounded-lg">
                                <Icon className="text-white h-8 w-8"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 0 0 1 0-5H20"/></Icon>
                            </div>
                            <h1 className="text-4xl font-bold brand-text text-brand-emerald-800 dark:text-brand-emerald-300">UlumCampus</h1>
                        </div>
                        <p className="mt-6 text-xl uppercase tracking-widest text-slate-500 dark:text-slate-400">{t('grades_certificate_of_completion')}</p>
                        <p className="mt-8 text-lg text-slate-600 dark:text-slate-300">{t('grades_certificate_awarded_to')}</p>
                        <p className="mt-2 text-4xl font-bold text-slate-800 dark:text-white">{currentUser.name}</p>
                        <p className="mt-8 text-lg text-slate-600 dark:text-slate-300">{t('grades_modal_completed_course')}</p>
                        <p className="mt-2 text-2xl font-semibold text-brand-emerald-700 dark:text-brand-emerald-400">{course.title}</p>
                        <p className="mt-8 text-sm text-slate-500 dark:text-slate-400">{t('grades_certificate_on')} {new Date(course.completionDate!).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                    </div>
                </div>
                <div className="flex justify-end items-center gap-3 p-4 bg-slate-100 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 rounded-b-lg">
                    <button onClick={onClose} className="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">{t('grades_modal_close')}</button>
                    <button onClick={handlePrint} className="flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-emerald-600 text-white font-semibold hover:bg-brand-emerald-700 transition-colors">
                        <Icon className="w-5 h-5"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></Icon>
                        {t('grades_modal_print')}
                    </button>
                </div>
            </div>
        </div>
    );
};


interface GradesProps {
  courses: Course[];
  currentUser: User;
  initialCourseId?: string;
}

export const Grades: React.FC<GradesProps> = ({ courses: propCourses, currentUser, initialCourseId }) => {
  const { t } = useLanguage();
  const [viewingCertificate, setViewingCertificate] = useState<Course | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [completedCourses, setCompletedCourses] = useState<Course[]>([]);
  const courseRefs = useRef<Record<string, HTMLTableRowElement | null>>({});

  useEffect(() => {
    const fetchGrades = async () => {
      try {
        setLoading(true);
        setError(null);
        
        if (currentUser.role === 'Mahasiswa') {
          // For students, fetch directly from student API to get courses with grades
          const response = await studentAPI.getMyCourses();
          const courses = response.data || [];
          // Filter only completed courses with grades
          const completed = courses.filter((course: Course) => 
            course.progress === 100 && course.gradeLetter
          );
          setCompletedCourses(completed);
        } else if (currentUser.role === 'Dosen') {
          // For dosen, use the courses passed via props (already filtered to their courses)
          const dosenCourses = propCourses.filter((course: Course) => course.instructor === currentUser.name);
          const completedDosenCourses = dosenCourses.filter((course: Course) => course.progress === 100 && course.gradeLetter);
          setCompletedCourses(completedDosenCourses);
        } else {
          // For other roles, use prop courses
          const completed = propCourses.filter(course => course.progress === 100 && course.gradeLetter);
          setCompletedCourses(completed);
        }
      } catch (err) {
        console.error('Error fetching grades:', err);
        setError('Gagal memuat nilai. Silakan coba lagi.');
        // Fallback to props if API fails
        const completed = propCourses.filter(course => course.progress === 100 && course.gradeLetter);
        setCompletedCourses(completed);
      } finally {
        setLoading(false);
      }
    };

    fetchGrades();
 }, [currentUser, propCourses]);

  // Highlight initial course if provided
  useEffect(() => {
    if (initialCourseId && !loading && completedCourses.length > 0) {
      const timer = setTimeout(() => {
        // Try both original and string ID since backend might return number or string
        const element = courseRefs.current[initialCourseId] || courseRefs.current[String(initialCourseId)];
        console.log('Looking for course:', initialCourseId, 'Found element:', !!element, 'Available refs:', Object.keys(courseRefs.current));
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
  }, [initialCourseId, loading, completedCourses]);

  if (loading) {
    return (
      <div className="flex justify-center items-center h-full">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-emerald-600"></div>
      </div>
    );
  }

  if (completedCourses.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center h-full text-center text-slate-500 dark:text-slate-400">
        <Icon className="w-24 h-24 text-slate-300 dark:text-slate-600">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </Icon>
        <h1 className="mt-6 text-2xl font-bold text-slate-700 dark:text-slate-200">{t('grades_no_completed_title')}</h1>
        <p className="mt-2 max-w-md">{t('grades_no_completed_subtitle')}</p>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('grades_title')}</h1>
        <p className="text-slate-500 dark:text-slate-400 mt-1">{t('grades_subtitle')}</p>
      </div>

      {/* Transcript Section */}
      <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
        <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">{t('grades_transcript_title')}</h2>
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left text-slate-500 dark:text-slate-400">
            <thead className="text-xs text-slate-70 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
              <tr>
                <th scope="col" className="px-6 py-3">{t('grades_table_course')}</th>
                <th scope="col" className="px-6 py-3">{t('grades_table_instructor')}</th>
                <th scope="col" className="px-6 py-3 text-center">{t('grades_table_sks')}</th>
                <th scope="col" className="px-6 py-3 text-center">{t('grades_table_grade')}</th>
              </tr>
            </thead>
            <tbody>
              {completedCourses.map(course => (
                <tr key={course.id} ref={el => (courseRefs.current[String(course.id)] = el)} className="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600/50 transition-all duration-500">
                  <th scope="row" className="px-6 py-4 font-medium text-slate-900 whitespace-nowrap dark:text-white">
                    {course.title}
                  </th>
                  <td className="px-6 py-4">{course.instructor}</td>
                  <td className="px-6 py-4 text-center">{course.sks}</td>
                  <td className="px-6 py-4 text-center font-bold text-brand-emerald-600 dark:text-brand-emerald-40">{course.gradeLetter}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Certificates Section */}
      <div className="space-y-4">
        <h2 className="text-xl font-bold text-slate-800 dark:text-white">{t('grades_certificates_title')}</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {completedCourses.map(course => (
            <div key={course.id} className="bg-white dark:bg-slate-800/50 rounded-lg shadow-md overflow-hidden group">
              <div className="p-5 bg-cover bg-center" style={{ backgroundImage: `linear-gradient(to right, #059669, #10b981)`}}>
                 <div className="text-center text-white py-8">
                    <p className="text-sm uppercase tracking-widest">{t('grades_certificate_of_completion')}</p>
                    <h3 className="text-2xl font-bold mt-2">{course.title}</h3>
                 </div>
              </div>
              <div className="p-5">
                <p className="text-slate-500 dark:text-slate-400">{t('grades_certificate_awarded_to')}</p>
                <p className="font-semibold text-lg text-slate-800 dark:text-white">{currentUser.name}</p>
                <p className="text-sm text-slate-500 dark:text-slate-400 mt-2">{t('grades_certificate_on')} {new Date(course.completionDate!).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                <button 
                  onClick={() => setViewingCertificate(course)}
                  className="mt-4 w-full bg-brand-emerald-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-brand-emerald-700 transition-colors flex items-center justify-center gap-2">
                    <Icon className="w-5 h-5"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></Icon>
                    {t('grades_certificate_view')}
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>

      {viewingCertificate && (
        <CertificateModal 
            course={viewingCertificate} 
            onClose={() => setViewingCertificate(null)} 
            currentUser={currentUser}
        />
      )}
    </div>
  );
};
