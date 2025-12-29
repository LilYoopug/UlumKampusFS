import React, { useState, DragEvent, ChangeEvent } from 'react';
import { Course, CourseModule } from '@/types';
import { Icon } from '@/src/ui/components/Icon';
import { useLanguage } from '@/contexts/LanguageContext';

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
                    key={item.id}
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

// Module Form Component
const ModuleForm: React.FC<{onSave: (module: CourseModule) => void, onClose: () => void, initialData: CourseModule | null}> = ({ onSave, onClose, initialData }) => {
    const { t } = useLanguage();
    const isEditMode = !!initialData;
    const [title, setTitle] = useState(initialData?.title || '');
    const [type, setType] = useState<CourseModule['type']>(initialData?.type || 'video');
    const [description, setDescription] = useState(initialData?.description || '');
    const [resourceUrl, setResourceUrl] = useState(initialData?.resourceUrl || '');
    const [attachmentUrl, setAttachmentUrl] = useState(initialData?.attachmentUrl || '');
    const [attachmentType, setAttachmentType] = useState<'link' | 'upload'>('link');
    const [attachmentFile, setAttachmentFile] = useState<File | null>(null);
    const [startTime, setStartTime] = useState(initialData?.startTime ? initialData.startTime.slice(0, 16) : '');
    const [liveUrl, setLiveUrl] = useState(initialData?.liveUrl || '');


    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!title.trim()) return;

        let finalAttachmentUrl = attachmentUrl;
        if (attachmentType === 'upload' && attachmentFile) {
            finalAttachmentUrl = URL.createObjectURL(attachmentFile);
        }

        const moduleData: CourseModule = {
            id: initialData?.id || `M${Date.now()}`,
            title,
            type,
            description,
            resourceUrl: type === 'live' ? undefined : resourceUrl,
            attachmentUrl: finalAttachmentUrl,
            startTime: type === 'live' ? new Date(startTime).toISOString() : undefined,
            liveUrl: type === 'live' ? liveUrl : undefined,
        };
        onSave(moduleData);
    };
    
    const handleAttachmentFileChange = (e: ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            setAttachmentFile(e.target.files[0]);
        }
    }
    
    const formInputClass = "w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-emerald-500";

    return (
        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div className="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h1 className="text-2xl font-bold text-slate-800 dark:text-white">
                    {isEditMode ? t('module_edit_title') : t('module_add_title')}
                </h1>
                <button onClick={onClose} className="p-2 rounded-full text-slate-500 hover:bg-slate-200 dark:text-slate-400 dark:hover:bg-slate-700 transition-colors">
                    <Icon className="w-6 h-6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></Icon>
                </button>
            </div>
            <form id="module-form" onSubmit={handleSubmit} className="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <div>
                    <label htmlFor="mod-type" className="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">{t('module_form_type')}</label>
                    <select id="mod-type" value={type} onChange={e => setType(e.target.value as any)} className={formInputClass}>
                        <option value="video" className="dark:bg-slate-700 dark:text-white">Video</option>
                        <option value="pdf" className="dark:bg-slate-700 dark:text-white">PDF</option>
                        <option value="live" className="dark:bg-slate-700 dark:text-white">{t('module_type_live')}</option>
                        <option value="quiz" className="dark:bg-slate-700 dark:text-white">Kuis</option>
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
                    <div className="pt-4 border-t border-slate-200 dark:border-slate-700 space-y-2">
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300">Lampiran (Opsional)</label>
                         <div className="flex items-center gap-4">
                            {type !== 'quiz' && (
                                <label className="flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-300">
                                    <input type="radio" name="attachmentType" value="upload" checked={attachmentType === 'upload'} onChange={() => setAttachmentType('upload')} className="w-4 h-4 text-brand-emerald-60"/>
                                    <span className="text-slate-700 dark:text-slate-300">Unggah File</span>
                                </label>
                            )}
                            <label className="flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-300">
                                <input type="radio" name="attachmentType" value="link" checked={attachmentType === 'link'} onChange={() => setAttachmentType('link')} className="w-4 h-4 text-brand-emerald-600"/>
                                <span className="text-slate-700 dark:text-slate-300">Sisipkan Link</span>
                            </label>
                        </div>

                        {attachmentType === 'link' ? (
                            <input type="url" value={attachmentUrl} onChange={e => setAttachmentUrl(e.target.value)} placeholder="https://..." className={formInputClass}/>
                        ) : (type !== 'quiz' && type !== 'live') ? (
                            <input type="file" onChange={handleAttachmentFileChange} className="w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-emerald-50 file:text-brand-emerald-700 hover:file:bg-brand-emerald-100"/>
                        ) : null}
                    </div>
                )}
                
            </form>
            <div className="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                <button type="button" onClick={onClose} className="px-4 py-2 bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white rounded-lg font-semibold">{t('button_cancel')}</button>
                <button type="submit" form="module-form" className="px-4 py-2 bg-brand-emerald-600 text-white rounded-lg font-semibold">{t('button_save')}</button>
            </div>
        </div>
    );
};


export const ModuleManagement: React.FC<{ course: Course }> = ({ course }) => {
    const { t } = useLanguage();
    const [modules, setModules] = useState<CourseModule[]>(course.modules);
    const [showModuleForm, setShowModuleForm] = useState(false);
    const [editingModule, setEditingModule] = useState<CourseModule | null>(null);

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

    const handleEditModule = (module: CourseModule) => {
        setEditingModule(module);
        setShowModuleForm(true);
    }

    const handleDeleteModule = (moduleId: string) => {
        setModules(prev => prev.filter(m => m.id !== moduleId));
    };
    
    const handleSaveModule = (moduleData: CourseModule) => {
        if (editingModule) {
            setModules(prev => prev.map(m => m.id === moduleData.id ? moduleData : m));
        } else {
            setModules(prev => [...prev, moduleData]);
        }
        setShowModuleForm(false);
        setEditingModule(null);
    }

    return (
        <div className="space-y-4">
            {modules.length > 0 ? (
                <DraggableList 
                    items={modules}
                    onReorder={setModules}
                    renderItem={(module: CourseModule) => (
                        <div className="flex items-center gap-4 p-3 rounded-lg bg-slate-100 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 cursor-grab">
                            <Icon className="w-5 h-5 text-slate-400 flex-shrink-0"><path d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01"/></Icon>
                            <div className="flex-shrink-0">{getModuleIcon(module.type)}</div>
                            <div className="flex-1 overflow-hidden">
                                <p className="font-semibold text-slate-800 dark:text-white truncate">{module.title}</p>
                                {module.type === 'live' && module.startTime && (
                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                        {new Date(module.startTime).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })}
                                    </p>
                                )}
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
            {showModuleForm && (
                <div className="fixed inset-0 bg-black/60 flex justify-center items-center z-50 p-4" onClick={() => setShowModuleForm(false)}>
                    <div className="w-full max-w-lg" onClick={e => e.stopPropagation()}>
                        <ModuleForm onSave={handleSaveModule} onClose={() => setShowModuleForm(false)} initialData={editingModule} />
                    </div>
                </div>
            )}
        </div>
    );
};
