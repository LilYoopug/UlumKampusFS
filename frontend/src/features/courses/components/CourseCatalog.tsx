import React, { useState, useEffect, useMemo, useRef } from 'react';
import { CourseCard } from './CourseCard';
import { Course, Major, Page, User, Faculty } from '../types';
import { Icon } from './Icon';
import { useLanguage } from '../contexts/LanguageContext';
import { FACULTIES } from '../constants';

interface CourseCatalogProps {
  onSelectCourse: (course: Course) => void;
  onEditCourse: (course: Course) => void;
  courses: Course[];
  currentUser: User;
  navigateTo: (page: Page) => void;
}

// --- MultiSelectDropdown Component ---
interface Option {
  value: string;
  label: string;
}

interface MultiSelectDropdownProps {
  options: Option[];
  selectedValues: string[];
  onChange: (selected: string[]) => void;
  placeholder: string;
  disabled?: boolean;
}

const MultiSelectDropdown: React.FC<MultiSelectDropdownProps> = ({ options, selectedValues, onChange, placeholder, disabled = false }) => {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);
  const { t } = useLanguage();

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSelect = (value: string) => {
    const newSelected = selectedValues.includes(value)
      ? selectedValues.filter(v => v !== value)
      : [...selectedValues, value];
    onChange(newSelected);
  };

  return (
    <div className="relative" ref={dropdownRef}>
      <button
        onClick={() => setIsOpen(!isOpen)}
        disabled={disabled}
        className="w-full h-full flex items-center justify-between px-4 py-2 text-start bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 disabled:bg-slate-100 dark:disabled:bg-slate-800 disabled:cursor-not-allowed"
      >
        <span className={`truncate ${selectedValues.length > 0 ? 'text-slate-800 dark:text-slate-100' : 'text-slate-500 dark:text-slate-400'}`}>
            {selectedValues.length > 0 ? `${placeholder} (${selectedValues.length})` : placeholder}
        </span>
        <Icon className={`w-5 h-5 text-slate-400 transition-transform flex-shrink-0 ms-2 ${isOpen ? 'rotate-180' : ''}`}>
          <polyline points="6 9 12 15 18 9"/>
        </Icon>
      </button>
      {isOpen && (
        <div className="absolute end-0 start-0 mt-2 max-h-60 overflow-y-auto bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-2 z-10">
          {options.map(option => (
            <label key={option.value} className="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer">
              <input
                type="checkbox"
                checked={selectedValues.includes(option.value)}
                onChange={() => handleSelect(option.value)}
                className="w-4 h-4 text-brand-emerald-600 bg-slate-100 border-slate-300 rounded focus:ring-brand-emerald-500 dark:focus:ring-brand-emerald-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600"
              />
              <span className="truncate">{option.label}</span>
            </label>
          ))}
           {options.length === 0 && (
              <div className="px-4 py-2 text-sm text-slate-500 text-center">{t('catalog_no_options')}</div>
            )}
        </div>
      )}
    </div>
  );
};

// --- Main CourseCatalog Component ---
export const CourseCatalog: React.FC<CourseCatalogProps> = ({ onSelectCourse, onEditCourse, courses, currentUser, navigateTo }) => {
  const { t } = useLanguage();
  const [selectedFaculties, setSelectedFaculties] = useState<string[]>([]);
  const [selectedMajors, setSelectedMajors] = useState<string[]>([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [sortBy, setSortBy] = useState('title-asc');
  const [faculties, setFaculties] = useState<Faculty[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  const sortOptions = [
      { value: 'title-asc', label: t('sort_title_asc') },
      { value: 'title-desc', label: t('sort_title_desc') },
      { value: 'instructor-asc', label: t('sort_instructor_asc') },
      { value: 'instructor-desc', label: t('sort_instructor_desc') },
      { value: 'sks-asc', label: t('sort_sks_asc') },
      { value: 'sks-desc', label: t('sort_sks_desc') },
  ];

  // Use mock faculties from constants
 useEffect(() => {
    setFaculties(FACULTIES);
    setLoading(false);
  }, []);

  const handleFacultyChange = (selectedIds: string[]) => {
    setSelectedFaculties(selectedIds);
    // When faculties change, filter the selected majors to only include those from the newly selected faculties.
    const newAvailableMajorsIds = faculties
        .filter(f => selectedIds.includes(f.id))
        .flatMap(f => f.majors.map(m => m.id));
    
    setSelectedMajors(currentSelectedMajors =>
        currentSelectedMajors.filter(majorId => newAvailableMajorsIds.includes(majorId))
    );
  };

  const facultyOptions = useMemo(() => faculties.map(f => ({ value: f.id, label: f.name })), [faculties]);

  const majorOptions = useMemo(() => {
    if (selectedFaculties.length === 0) return [];
    
    return faculties
        .filter(f => selectedFaculties.includes(f.id))
        .flatMap(f => f.majors.map(m => ({ value: m.id, label: m.name })));
  }, [selectedFaculties, faculties]);
  
  const { myCourses, otherCourses } = useMemo(() => {
    let relevantCourses: Course[];

    // For Mahasiswa, only show courses they are enrolled in.
    if (currentUser.role === 'Mahasiswa') {
        relevantCourses = courses.filter(c => c.progress > 0 || c.completionDate);
    } else {
        relevantCourses = courses;
    }

    let filteredCourses = relevantCourses.filter(course => {
        const searchMatch = course.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            course.instructor.toLowerCase().includes(searchTerm.toLowerCase());
        const facultyMatch = selectedFaculties.length === 0 || selectedFaculties.includes(course.facultyId);
        const majorMatch = selectedMajors.length === 0 || (course.majorId && selectedMajors.includes(course.majorId));
        return searchMatch && facultyMatch && majorMatch;
    });

    filteredCourses.sort((a, b) => {
        const [key, order] = sortBy.split('-');
        let valA: string | number;
        let valB: string | number;
        if (key === 'sks') {
            valA = a.sks;
            valB = b.sks;
        } else if (key === 'title') {
            valA = a.title.toLowerCase();
            valB = b.title.toLowerCase();
        } else { // instructor
            valA = a.instructor.toLowerCase();
            valB = b.instructor.toLowerCase();
        }
        if (valA < valB) return order === 'asc' ? -1 : 1;
        if (valA > valB) return order === 'asc' ? 1 : -1;
        return 0;
    });

    if (currentUser.role === 'Dosen') {
        const my = filteredCourses.filter(c => c.instructor === currentUser.name);
        const others = filteredCourses.filter(c => c.instructor !== currentUser.name);
        return { myCourses: my, otherCourses: others };
    }

    return { myCourses: filteredCourses, otherCourses: [] };
  }, [searchTerm, selectedFaculties, selectedMajors, sortBy, courses, currentUser]);

  const renderCourseGrid = (courseList: Course[], isMyCourse: boolean) => (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      {courseList.map(course => (
        <CourseCard
          key={course.id}
          course={course}
          onSelectCourse={onSelectCourse}
          onEditCourse={isMyCourse ? onEditCourse : undefined}
          faculties={faculties}
        />
      ))}
    </div>
  );

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
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{(currentUser.role === 'Mahasiswa') ? t('sidebar_my_courses') : t('catalog_title')}</h1>
            <p className="text-slate-500 dark:text-slate-400 mt-1">{(currentUser.role === 'Mahasiswa') ? "Lihat semua mata kuliah yang Anda ikuti." : t('catalog_subtitle')}</p>
        </div>
        {(currentUser.role === 'Dosen') && (
            <button
                onClick={() => navigateTo('create-course')}
                className="flex items-center gap-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors flex-shrink-0"
            >
                <Icon className="w-5 h-5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></Icon>
                {t('catalog_create_course')}
            </button>
        )}
      </div>
      
      <div className="p-4 bg-white dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-center">
        <div className="relative lg:col-span-2">
          <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3" />
          </Icon>
          <input
            type="text"
            placeholder={t('catalog_search')}
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full h-full ps-10 pe-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
          />
        </div>
        <MultiSelectDropdown
          options={facultyOptions}
          selectedValues={selectedFaculties}
          onChange={handleFacultyChange}
          placeholder={t('catalog_filter_faculty')}
        />
        <MultiSelectDropdown
          options={majorOptions}
          selectedValues={selectedMajors}
          onChange={setSelectedMajors}
          placeholder={t('catalog_filter_major')}
          disabled={selectedFaculties.length === 0}
        />
                <div className="relative lg:col-span-2">
                    <label htmlFor="sort-by" className="sr-only">{t('catalog_sort_by')}</label>
                    <Icon className="absolute end-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5 pointer-events-none">
                        <polyline points="6 9 12 15 18 9"/>
                    </Icon>
                    <select
                        id="sort-by"
                        value={sortBy}
                        onChange={(e) => setSortBy(e.target.value)}
                        className="w-full appearance-none h-full ps-4 pe-10 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                    >
                        <optgroup label={t('catalog_sort_by')} className="bg-white dark:bg-slate-800">
                            {sortOptions.map(opt => <option key={opt.value} value={opt.value} className="bg-white dark:bg-slate-800">{opt.label}</option>)}
                        </optgroup>
                    </select>
                </div>
      </div>
      
     {(currentUser.role === 'Dosen') ? (
        <div className="space-y-8">
            <div>
                <h2 className="text-2xl font-bold text-slate-800 dark:text-white mb-4">Mata Kuliah Anda</h2>
                {myCourses.length > 0 ? renderCourseGrid(myCourses, true) : <p className="text-center py-8 text-slate-50">Anda belum membuat mata kuliah.</p>}
            </div>
            <div>
                <h2 className="text-2xl font-bold text-slate-800 dark:text-white mb-4">Mata Kuliah Lainnya</h2>
                {otherCourses.length > 0 ? renderCourseGrid(otherCourses, false) : <p className="text-center py-8 text-slate-500">Tidak ada mata kuliah lain yang ditemukan.</p>}
            </div>
        </div>
      ) : (
        <>
            {myCourses.length > 0 ? renderCourseGrid(myCourses, false) : (
                <div className="text-center py-16 col-span-full">
                    <Icon className="mx-auto w-16 h-16 text-slate-400"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></Icon>
                    <h3 className="mt-4 text-lg font-semibold">{t('catalog_no_courses')}</h3>
                    <p className="mt-1 text-slate-500">{t('catalog_no_courses_subtitle')}</p>
                </div>
            )}
        </>
      )}
    </div>
  );
};
