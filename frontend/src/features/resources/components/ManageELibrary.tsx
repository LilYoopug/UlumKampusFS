import React, { useState, useMemo } from 'react';
import { LibraryResource } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { Icon } from '@/src/ui/components/Icon';
import { ResourceForm } from '@/src/features/shared/components/ResourceForm';

interface ManageELibraryProps {
    resources: LibraryResource[];
    onCreate: (data: Omit<LibraryResource, 'id'>) => void;
    onUpdate: (data: LibraryResource) => void;
    onDelete: (id: string) => void;
}

const ConfirmationModal: React.FC<{
    isOpen: boolean;
    onClose: () => void;
    onConfirm: () => void;
    title: string;
    message: string;
}> = ({ isOpen, onClose, onConfirm, title, message }) => {
    const { t } = useLanguage();
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 p-4" onClick={onClose} role="dialog" aria-modal="true">
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-md" onClick={e => e.stopPropagation()}>
                <div className="p-6">
                    <div className="flex items-start gap-4">
                        <div className="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/50 sm:mx-0">
                             <Icon className="h-6 w-6 text-red-600 dark:text-red-400">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </Icon>
                        </div>
                        <div className="mt-0 text-start">
                            <h3 className="text-lg leading-6 font-bold text-slate-900 dark:text-white" id="modal-title">
                                {title}
                            </h3>
                            <div className="mt-2">
                                <p className="text-sm text-slate-500 dark:text-slate-400">
                                    {message}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="flex justify-end items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 rounded-b-lg">
                    <button type="button" onClick={onClose} className="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">{t('button_cancel')}</button>
                    <button type="button" onClick={onConfirm} className="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors">{t('button_confirm_delete')}</button>
                </div>
            </div>
        </div>
    );
};

export const ManageELibrary: React.FC<ManageELibraryProps> = ({ resources, onCreate, onUpdate, onDelete }) => {
    const { t } = useLanguage();
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editingResource, setEditingResource] = useState<LibraryResource | null>(null);
    const [deletingResource, setDeletingResource] = useState<LibraryResource | null>(null);
    const [searchTerm, setSearchTerm] = useState('');

    const filteredResources = useMemo(() => {
        // Filter out any malformed resources and then filter by search term
        return resources
            .filter(resource => resource && resource.title && resource.author)
            .filter(resource =>
                resource.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                resource.author.toLowerCase().includes(searchTerm.toLowerCase())
            );
    }, [resources, searchTerm]);
    
    const handleAddNew = () => {
        setEditingResource(null);
        setIsFormOpen(true);
    };

    const handleEdit = (resource: LibraryResource) => {
        setEditingResource(resource);
        setIsFormOpen(true);
    };
    
    const handleDelete = (resource: LibraryResource) => {
        setDeletingResource(resource);
    };

    const handleConfirmDelete = () => {
        if (deletingResource) {
            onDelete(deletingResource.id);
            setDeletingResource(null);
        }
    };

    const handleSave = (data: LibraryResource | Omit<LibraryResource, 'id'>) => {
        if ('id' in data && data.id) {
            onUpdate(data as LibraryResource);
        } else {
            onCreate(data);
        }
        setIsFormOpen(false);
    };

    return (
        <>
            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('manage_elibrary_title')}</h1>
                    <p className="text-slate-500 dark:text-slate-400 mt-1">{t('manage_elibrary_subtitle')}</p>
                </div>

                <div className="bg-white dark:bg-slate-800/50 p-4 sm:p-6 rounded-2xl shadow-md">
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div className="relative flex-grow w-full sm:w-auto">
                            <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                                <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                            </Icon>
                            <input
                                type="text"
                                placeholder="Cari judul atau penulis..."
                                value={searchTerm}
                                onChange={e => setSearchTerm(e.target.value)}
                                className="w-full sm:w-80 ps-10 pe-4 py-2 rounded-full bg-slate-100 dark:bg-slate-700 border border-transparent focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white"
                            />
                        </div>
                         <button onClick={handleAddNew} className="flex-shrink-0 flex items-center gap-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">
                            <Icon className="w-5 h-5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></Icon>
                            {t('manage_elibrary_add_resource')}
                        </button>
                    </div>
                     <div className="overflow-x-auto">
                        <table className="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                            <thead className="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                                <tr>
                                    <th scope="col" className="px-6 py-3">{t('manage_elibrary_table_cover')}</th>
                                    <th scope="col" className="px-6 py-3">{t('manage_elibrary_table_title')}</th>
                                    <th scope="col" className="px-6 py-3">{t('manage_elibrary_table_author')}</th>
                                    <th scope="col" className="px-6 py-3">{t('manage_elibrary_table_year')}</th>
                                    <th scope="col" className="px-6 py-3">{t('manage_elibrary_table_type')}</th>
                                    <th scope="col" className="px-6 py-3 text-end">{t('manage_elibrary_table_actions')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredResources.length > 0 ? (
                                    filteredResources.map(resource => (
                                        <tr key={resource.id} className="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600/50">
                                            <td className="px-6 py-4">
                                                <img src={resource.coverUrl} alt={resource.title} className="w-12 h-16 object-cover rounded-sm"/>
                                            </td>
                                            <td className="px-6 py-4 font-medium text-slate-900 dark:text-white">{resource.title}</td>
                                            <td className="px-6 py-4">{resource.author}</td>
                                            <td className="px-6 py-4">{resource.year}</td>
                                            <td className="px-6 py-4 capitalize">{resource.type}</td>
                                            <td className="px-6 py-4 text-end">
                                                <div className="flex items-center justify-end gap-4">
                                                    <button onClick={() => handleEdit(resource)} className="font-medium text-brand-emerald-600 dark:text-brand-emerald-500 hover:underline">{t('button_edit')}</button>
                                                    <button onClick={() => handleDelete(resource)} className="font-medium text-red-600 dark:text-red-500 hover:underline">{t('button_delete')}</button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={6} className="text-center py-12 text-slate-500">
                                            Tidak ada sumber daya yang cocok dengan pencarian Anda.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {isFormOpen && (
                 <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={() => setIsFormOpen(false)} role="dialog" aria-modal="true">
                    <div className="w-full max-w-2xl" onClick={e => e.stopPropagation()}>
                        <ResourceForm
                            onSave={handleSave}
                            onClose={() => setIsFormOpen(false)}
                            initialData={editingResource}
                        />
                    </div>
                </div>
            )}
            
            <ConfirmationModal
                isOpen={!!deletingResource}
                onClose={() => setDeletingResource(null)}
                onConfirm={handleConfirmDelete}
                title={t('manage_elibrary_delete_confirm_title')}
                message={`${t('manage_elibrary_delete_confirm_text')} (${deletingResource?.title})`}
            />
        </>
    );
};
