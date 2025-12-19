import React, { useState, useEffect, useMemo, ChangeEvent, DragEvent } from 'react';
import { Course, CourseModule, CourseStatus, SyllabusWeek } from '../types';
import { FACULTIES } from '../constants';
import { Icon } from './Icon';
import { useLanguage } from '../contexts/LanguageContext';

interface CourseFormProps {
    onSave: (courseData: Course) => void;
    onCancel: () => void;
    initialData?: Course | null;
}

const DraggableList: React.FC<{ items: any[], renderItem: (item: any, index: number) => React.ReactNode, onReorder: (items: any[]) => void }> = ({ items, renderItem, onReorder }) => {
    const [draggingItem, setDraggingItem] = useState<number | null>(null);

    const handleDragStart = (e: DragEvent<HTMLLIElement>, index: number) => {
        setDraggingItem(index);
        e.dataTransfer.effectAllowed = 'move';
    };

    const handleDragOver = (e: DragEvent<HTMLLIElement>, index: number) => {
        e.preventDefault();
        if (draggingItem === null || draggingItem === index) return;

        const reorderedItems = [...items];
        const [removed] = reorderedItems.splice(draggingItem, 1);
        reorderedItems.splice(index, 0, removed);
        
        onReorder(reorderedItems);
        setDraggingItem(index);
    };

    const handleDragEnd = () => {
        setDraggingItem(null);
    };

    return (
        <ul className="space-y-3">
            {items.map((item, index) => (
                <li
                    key={item.id || index}
                    draggable
                    onDragStart={(e) => handleDragStart(e, index)}
                    onDragOver={(e) => handleDragOver(e, index)}
                    onDragEnd={handleDragEnd}
                    className={`transition-opacity ${draggingItem === index ? 'opacity-50' : 'opacity-100'}`}
                >
                    {renderItem(item, index)}
                </li>
            ))}
        </ul>
    );
};

export const CourseForm: React.FC<CourseFormProps> = ({ onSave, onCancel, initialData }) => {
  const { t } = useLanguage();
  const isEditMode = !!initialData;
  const [formData, setFormData] = useState<Omit<Course, 'progress'>>({
      id: '',
      title: '',
      description: '',
      instructor: '',
      instructorAvatarUrl: '',
      instructorBioKey: 'bio_yusuf_al_fatih',
      sks: 3,
      facultyId: '',
      majorId: '',
      imageUrl: '',
      mode: 'VOD',
      status: 'Draft',
      learningObjectives: [],
      syllabus: [{ week: 1, topic: '', description: '' }],
      modules: [],
  });
  
  const [showModuleForm, setShowModuleForm] = useState(false);
  const [editingModule, setEditingModule] = useState<CourseModule | null>(null);

  useEffect(() => {
    if (initialData) {
        setFormData({
            ...initialData,
            learningObjectives: initialData.learningObjectives || [],
            syllabus: initialData.syllabus?.length > 0 ? initialData.syllabus : [{ week: 1, topic: '', description: '' }],
            modules: initialData.modules || [],
        });
    }
  }, [initialData]);

  const handleChange = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    if (name.startsWith('objective-')) {
        const index = parseInt(name.split('-')[1]);
        const newObjectives = [...formData.learningObjectives];
        newObjectives[index] = value;
        setFormData(prev => ({ ...prev, learningObjectives: newObjectives }));
    } else if (name.startsWith('syllabus-')) {
        const [_, indexStr, field] = name.split('-');
        const index = parseInt(indexStr);
        const newSyllabus = [...formData.syllabus];
        newSyllabus[index] = { ...newSyllabus[index], [field]: field === 'week' ? parseInt(value) : value };
        setFormData(prev => ({ ...prev, syllabus: newSyllabus }));
    } else {
        setFormData(prev => ({ ...prev, [name]: value }));
    }
  };
  
  const handleAddObjective = () => setFormData(prev => ({ ...prev, learningObjectives: [...prev.learningObjectives, ''] }));
  const handleRemoveObjective = (index: number) => setFormData(prev => ({ ...prev, learningObjectives: prev.learningObjectives.filter((_, i) => i !== index) }));
  
  const handleAddSyllabusWeek = () => {
    const nextWeek = formData.syllabus.length > 0 ? Math.max(...formData.syllabus.map(s => s.week)) + 1 : 1;
    setFormData(prev => ({ ...prev, syllabus: [...prev.syllabus, { week: nextWeek, topic: '', description: '' }] }));
  }
  const handleRemoveSyllabusWeek = (index: number) => setFormData(prev => ({ ...prev, syllabus: prev.syllabus.filter((_, i) => i !== index) }));

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSave(formData as Course);
  };
  
  const handleSaveModule = (moduleData: CourseModule) => {
    if (editingModule) {
        setFormData(prev => ({...prev, modules: prev.modules.map(m => m.id === moduleData.id ? moduleData : m)}));
    } else {
        setFormData(prev => ({...prev, modules: [...prev.modules, moduleData]}));
    }
    setShowModuleForm(false);
    setEditingModule(null);
  }
  
  const handleEditModule = (module: CourseModule) => {
    setEditingModule(module);
    setShowModuleForm(true);
  }

  const handleDeleteModule = (moduleId: string) => {
    setFormData(prev => ({ ...prev, modules: prev.modules.filter(m => m.id !== moduleId) }));
  };
  
  const getModuleIcon = (type: CourseModule['type']) => {
    switch (type) {
      case 'video':
        return <Icon className="w-5 h-5 text-blue-500"><polygon points="5 3 19 12 5 21 5 3"/></Icon>;
      case 'pdf':
        return <Icon className="w-5 h-5 text-red-500"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/></Icon>;
      case 'quiz':
        return <Icon className="w-5 h-5 text-green-500"><path d="m9 12 2 2 4-4"/></Icon>;
      case 'live':
        return <Icon className="w-5 h-5 text-red-500"><path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.934a.5.5 0 0 0-.777-.416L16 11"/><rect x="2" y="7" width="14" height="10" rx="2" ry="2"/></Icon>;
      default:
        return <Icon className="w-5 h-5 text-slate-500"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/></Icon>;
    }
  };
  
  const availableMajors = useMemo(() => {
    if (!formData.facultyId) return [];
    const selectedFaculty = FACULTIES.find(f => f.id === formData.facultyId);
    return selectedFaculty ? selectedFaculty.majors : [];
  }, [formData.facultyId]);

  return (
    <>
    <form onSubmit={handleSubmit} className="space-y-8">
      <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md space-y-4">
        <h2 className="text-xl font-bold text-slate-800 dark:text-white">Informasi Dasar</h2>
        <div>
          <label htmlFor="title" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('create_course_form_title')}</label>
          <input type="text" name="title" id="title" value={formData.title} onChange={handleChange} placeholder={t('create_course_form_title_placeholder')} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white" required />
        </div>
        <div>
          <label htmlFor="description" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('create_course_form_description')}</label>
          <textarea name="description" id="description" value={formData.description} onChange={handleChange} rows={3} placeholder={t('create_course_form_description_placeholder')} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white" required></textarea>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label htmlFor="facultyId" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('create_course_form_faculty')}</label>
                <select name="facultyId" id="facultyId" value={formData.facultyId} onChange={handleChange} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white" required>
                    <option value="" className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">{t('create_course_form_select_faculty')}</option>
                    {FACULTIES.map(faculty => <option key={faculty.id} value={faculty.id} className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">{faculty.name}</option>)}
                </select>
            </div>
             <div>
                <label htmlFor="majorId" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('create_course_form_major')}</label>
                <select name="majorId" id="majorId" value={formData.majorId} onChange={handleChange} disabled={availableMajors.length === 0} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white">
                    <option value="" className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">{t('create_course_form_select_major')}</option>
                    {availableMajors.map(major => <option key={major.id} value={major.id} className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">{major.name}</option>)}
                </select>
            </div>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label htmlFor="sks" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('create_course_form_sks')}</label>
            <input type="number" name="sks" id="sks" value={formData.sks} onChange={handleChange} min="1" max="6" className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white" required />
          </div>
          <div>
            <label htmlFor="status" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('create_course_form_status')}</label>
            <select name="status" id="status" value={formData.status} onChange={handleChange} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white">
                <option value="Draft" className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">Draft</option>
                <option value="Published" className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">Published</option>
                <option value="Archived" className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">Archived</option>
            </select>
          </div>
        </div>
        <div>
          <label htmlFor="imageUrl" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('create_course_form_image_url')}</label>
          <input type="url" name="imageUrl" id="imageUrl" value={formData.imageUrl} onChange={handleChange} placeholder={t('create_course_form_image_url_placeholder')} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white" />
        </div>
      </div>
      
      {/* Modules */}
      <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md space-y-4">
        <h2 className="text-xl font-bold text-slate-800 dark:text-white">Modul Pembelajaran</h2>
        {formData.modules.length > 0 ? (
          <DraggableList 
            items={formData.modules}
            onReorder={(reordered) => setFormData(p => ({...p, modules: reordered}))}
            renderItem={(module: CourseModule) => (
              <div className="flex items-center gap-4 p-3 rounded-lg bg-slate-100 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 cursor-grab">
                <Icon className="w-5 h-5 text-slate-400 flex-shrink-0"><path d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01"/></Icon>
                <div className="flex-shrink-0">{getModuleIcon(module.type)}</div>
                <div className="flex-1 overflow-hidden">
                    <p className="font-semibold text-slate-800 dark:text-white truncate">{module.title}</p>
                </div>
                {module.attachmentUrl && (
                    <a href={module.attachmentUrl} target="_blank" rel="noopener noreferrer" onClick={e => e.stopPropagation()} className="p-1 text-slate-500 hover:text-brand-emerald-600">
                        <Icon className="w-5 h-5"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.59a2 2 0 0 1-2.83-2.83l.79-.79"/></Icon>
                    </a>
                )}
                <div className="flex gap-2">
                    <button type="button" onClick={() => handleEditModule(module)} className="p-1 text-slate-500 hover:text-blue-500"><Icon className="w-5 h-5"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></Icon></button>
                    <button type="button" onClick={() => handleDeleteModule(module.id)} className="p-1 text-slate-500 hover:text-red-500"><Icon className="w-5 h-5"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></Icon></button>
                </div>
              </div>
            )}
          />
        ) : (
          <p className="text-center text-slate-500 py-4">{t('detail_no_materials_dosen')}</p>
        )}
        <button type="button" onClick={() => { setEditingModule(null); setShowModuleForm(true); }} className="w-full mt-2 flex items-center justify-center gap-2 px-4 py-2 border-2 border-dashed border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-400 font-semibold rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
            <Icon className="w-5 h-5"><path d="M5 12h14"/><path d="M12 5v14"/></Icon>
            {t('module_add_button')}
        </button>
      </div>

      <div className="flex justify-end gap-4">
        <button type="button" onClick={onCancel} className="px-6 py-2 rounded-lg bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">{t('button_cancel')}</button>
        <button type="submit" className="px-6 py-2 rounded-lg bg-brand-emerald-600 text-white font-semibold hover:bg-brand-emerald-700 transition-colors">{isEditMode ? t('create_course_button_save_changes') : t('create_course_button_create')}</button>
      </div>
    </form>
     {showModuleForm && (
        <div className="fixed inset-0 bg-black/60 flex justify-center items-center z-50 p-4" onClick={() => setShowModuleForm(false)}>
            <div className="w-full max-w-lg" onClick={e => e.stopPropagation()}>
                <ModuleForm 
                    onSave={handleSaveModule} 
                    onClose={() => setShowModuleForm(false)} 
                    initialData={editingModule} 
                />
            </div>
        </div>
      )}
    </>
  );
};


// Module Form Component
const ModuleForm: React.FC<{onSave: (module: CourseModule) => void, onClose: () => void, initialData: CourseModule | null}> = ({ onSave, onClose, initialData }) => {
    const { t } = useLanguage();
    const isEditMode = !!initialData;
    const [title, setTitle] = useState(initialData?.title || '');
    const [type, setType] = useState<CourseModule['type']>(initialData?.type || 'video');
    const [description, setDescription] = useState(initialData?.description || '');
    const [resourceUrl, setResourceUrl] = useState(initialData?.resourceUrl || '');
    const [attachmentUrl, setAttachmentUrl] = useState(initialData?.attachmentUrl || '');
    const [startTime, setStartTime] = useState(initialData?.startTime ? initialData.startTime.slice(0, 16) : '');
    const [liveUrl, setLiveUrl] = useState(initialData?.liveUrl || '');

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!title.trim()) return;

        const moduleData: CourseModule = {
            id: initialData?.id || `M${Date.now()}`,
            title,
            type,
            description,
            resourceUrl: type === 'live' ? undefined : resourceUrl,
            attachmentUrl: type === 'live' ? undefined : attachmentUrl,
            startTime: type === 'live' ? new Date(startTime).toISOString() : undefined,
            liveUrl: type === 'live' ? liveUrl : undefined,
        };
        onSave(moduleData);
    };
    
    const formInputClass = "w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-emerald-50 text-slate-800 dark:text-white";

    return (
        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div className="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h1 className="text-2xl font-bold text-slate-800 dark:text-white">
                    {isEditMode ? t('module_edit_title') : t('module_add_title')}
                </h1>
                <button onClick={onClose} className="p-2 rounded-full text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <Icon className="w-6 h-6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></Icon>
                </button>
            </div>
            <form id="module-form" onSubmit={handleSubmit} className="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <div>
                    <label htmlFor="mod-type" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('module_form_type')}</label>
                    <select id="mod-type" value={type} onChange={e => setType(e.target.value as any)} className={formInputClass + " text-slate-800 dark:text-white"}>
                        <option value="video" className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">Video</option>
                        <option value="pdf" className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">PDF</option>
                        <option value="live" className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">{t('module_type_live')}</option>
                        <option value="quiz" className="bg-white dark:bg-slate-700 text-slate-800 dark:text-white">Kuis</option>
                    </select>
                </div>
                <div>
                    <label htmlFor="mod-title" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('module_form_title')}</label>
                    <input id="mod-title" type="text" value={title} onChange={e => setTitle(e.target.value)} className={formInputClass} required/>
                </div>
                <div>
                    <label htmlFor="mod-desc" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('module_form_description')}</label>
                    <textarea id="mod-desc" value={description} onChange={e => setDescription(e.target.value)} rows={2} placeholder={t('module_form_description_placeholder')} className={formInputClass}></textarea>
                </div>
                
                {type === 'live' ? (
                     <>
                        <div>
                            <label htmlFor="mod-start-time" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('module_form_start_time')}</label>
                            <input id="mod-start-time" type="datetime-local" value={startTime} onChange={e => setStartTime(e.target.value)} className={formInputClass} required/>
                        </div>
                        <div>
                            <label htmlFor="mod-live-url" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('module_form_live_url')}</label>
                            <input id="mod-live-url" type="url" value={liveUrl} onChange={e => setLiveUrl(e.target.value)} placeholder={t('module_form_live_url_placeholder')} className={formInputClass}/>
                        </div>
                    </>
                ) : (
                    <div>
                        <label htmlFor="mod-resource" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Link Materi Utama (URL)</label>
                        <input id="mod-resource" type="url" value={resourceUrl} onChange={e => setResourceUrl(e.target.value)} placeholder="https://... (untuk video atau PDF)" className={formInputClass}/>
                    </div>
                )}


                {type !== 'live' && (
                    <div className="pt-4 border-t border-slate-200 dark:border-slate-70 space-y-2">
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300">Lampiran (Opsional)</label>
                        <input type="url" value={attachmentUrl} onChange={e => setAttachmentUrl(e.target.value)} placeholder="https://..." className={formInputClass}/>
                    </div>
                )}
                
            </form>
            <div className="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                <button type="button" onClick={onClose} className="px-4 py-2 bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white rounded-lg font-semibold hover:bg-slate-300 dark:hover:bg-slate-500">{t('button_cancel')}</button>
                <button type="submit" form="module-form" onClick={handleSubmit} className="px-4 py-2 bg-brand-emerald-600 text-white rounded-lg font-semibold hover:bg-brand-emerald-700">{t('button_save')}</button>
            </div>
        </div>
    );
};
