import React, { useState } from 'react';
import { Icon } from '@/src/ui/components/Icon';
import { User } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { UserForm } from '@/src/features/shared/components/UserForm';

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

interface UserManagementPageProps {
    users: User[];
    onCreateUser?: (user: User) => Promise<void>;
    onUpdateUser?: (user: User) => Promise<void>;
    onDeleteUser?: (id: string) => Promise<void>;
}

export const UserManagementPage: React.FC<UserManagementPageProps> = ({ 
    users = [], 
    onCreateUser, 
    onUpdateUser, 
    onDeleteUser 
}) => {
    const { t } = useLanguage();
    const [allUsers, setAllUsers] = useState<User[]>(users);
    const [isUserFormOpen, setIsUserFormOpen] = useState(false);
    const [editingUser, setEditingUser] = useState<User | null>(null);
    const [userToDelete, setUserToDelete] = useState<User | null>(null);

    // Update users when the prop changes
    React.useEffect(() => {
        setAllUsers(users);
    }, [users]);

    const handleAddNew = () => {
        setEditingUser(null);
        setIsUserFormOpen(true);
    };
    
    const handleEdit = (user: User) => {
        setEditingUser(user);
        setIsUserFormOpen(true);
    };

    const handleDeleteClick = (user: User) => {
        setUserToDelete(user);
    };

    const handleConfirmDelete = async () => {
        if (userToDelete && onDeleteUser) {
            try {
                await onDeleteUser(userToDelete.studentId!);
                setUserToDelete(null);
            } catch (error) {
                console.error('Error deleting user:', error);
            }
        }
    };
    
    const handleSaveUser = async (user: User) => {
        try {
            if (editingUser) { // Update
                if (onUpdateUser) {
                    await onUpdateUser({ ...user, studentId: editingUser.studentId });
                }
            } else { // Create
                if (onCreateUser) {
                    await onCreateUser(user);
                }
            }
            setIsUserFormOpen(false);
            setEditingUser(null);
        } catch (error) {
            console.error('Error saving user:', error);
        }
    };
    
    const handleCancelForm = () => {
        setIsUserFormOpen(false);
        setEditingUser(null);
    };

    const handleExportPDF = () => {
        if (typeof window !== 'undefined' && window.jspdf) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            (doc as any).autoTable({
                head: [['Nama', 'Email', 'Peran']],
                body: allUsers.map(user => [user.name, user.email, user.role]),
            });
            doc.save('daftar-pengguna.pdf');
        } else {
            console.error('jsPDF is not available');
        }
    };

    const handleExportXLSX = () => {
        if (typeof window !== 'undefined' && window.XLSX) {
            const worksheet = window.XLSX.utils.json_to_sheet(allUsers.map(u => ({ Nama: u.name, Email: u.email, Peran: u.role })));
            const workbook = window.XLSX.utils.book_new();
            window.XLSX.utils.book_append_sheet(workbook, worksheet, 'Pengguna');
            window.XLSX.writeFile(workbook, 'daftar-pengguna.xlsx');
        } else {
            console.error('XLSX is not available');
        }
    };

    return (
        <>
        <div className="space-y-6">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('admin_user_management')}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">{t('admin_user_management_description')}</p>
            </div>

            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4">
                    <div className="relative flex-grow w-full sm:w-auto">
                        <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                        </Icon>
                        <input type="text" placeholder={t('admin_search_users')} className="w-full ps-10 pe-4 py-2 rounded-full bg-slate-100 dark:bg-slate-700 border border-transparent focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white" />
                    </div>
                     <button className="flex-shrink-0 flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <Icon className="w-5 h-5"><path d="M3 6h18"/><path d="M7 12h10"/><path d="M10 18h4"/></Icon>
                        {t('button_filter')}
                    </button>
                    <div className="flex-grow flex justify-start sm:justify-end items-center gap-2 w-full sm:w-auto">
                         <button onClick={handleExportPDF} className="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/50 rounded-full transition-colors" title={t('admin_export_pdf')}>
                            <Icon className="w-5 h-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12v-1h3v1"/><path d="M10 15h3"/><path d="M10 18h3"/></Icon>
                        </button>
                        <button onClick={handleExportXLSX} className="p-2 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/50 rounded-full transition-colors" title={t('admin_export_xlsx')}>
                             <Icon className="w-5 h-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-4_M15 14h-3_M9 14h3"/><path d="M10.5 10.5 13.5 7.5_M13.5 10.5 10.5 7.5"/></Icon>
                        </button>
                        <button onClick={handleAddNew} className="flex items-center gap-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">
                            <Icon className="w-5 h-5"><path d="M6 8L2 12l4 4"/><path d="M10 20v-3.3a2.4 2.4 0 0 1 .7-1.7l6.6-6.6a2.4 2.4 0 0 1 3.4 0l1.6 1.6a2.4 2.4 0 0 1 0 3.4L15 16.7a2.4 2.4 0 0 1-1.7.7H10"/></Icon>
                            {t('admin_add_user')}
                        </button>
                    </div>
                </div>
                <div className="overflow-x-auto max-h-[60vh]">
                    <table className="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                        <thead className="text-xs text-slate-70 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300 sticky top-0">
                            <tr>
                                <th scope="col" className="px-6 py-3">{t('admin_user_name')}</th>
                                <th scope="col" className="px-6 py-3">{t('admin_user_email')}</th>
                                <th scope="col" className="px-6 py-3">{t('admin_user_role')}</th>
                                <th scope="col" className="px-6 py-3">{t('admin_user_phone')}</th>
                                <th scope="col" className="px-6 py-3 text-end">{t('admin_user_actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {allUsers.length > 0 ? (
                                allUsers.map(user => (
                                    <tr key={user.studentId} className="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600/50">
                                        <td className="px-6 py-4 font-medium text-slate-900 dark:text-white">{user.name}</td>
                                        <td className="px-6 py-4">{user.email}</td>
                                        <td className="px-6 py-4">{user.role}</td>
                                        <td className="px-6 py-4">
                                            {user.role === 'Super Admin' && user.phoneNumber ? user.phoneNumber : '-'}
                                        </td>
                                        <td className="px-6 py-4 text-end">
                                            <div className="flex items-center justify-end gap-2">
                                                <button onClick={() => handleEdit(user)} className="font-medium text-brand-emerald-600 dark:text-brand-emerald-500 hover:underline">{t('admin_manage_user')}</button>
                                                <button onClick={() => handleDeleteClick(user)} className="font-medium text-red-600 dark:text-red-500 hover:underline">{t('button_delete')}</button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={5} className="px-6 py-12 text-center">
                                        <div className="flex flex-col items-center justify-center">
                                            <Icon className="w-12 h-12 text-slate-4 mb-3">
                                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 0 0 1 .707.293l5.414 5.414a1 0 0 1 .293.707V19a2 2 0 1-2 2Z"/>
                                            </Icon>
                                            <p className="text-slate-500 dark:text-slate-400 font-medium">{t('admin_no_user_data')}</p>
                                            <p className="text-slate-40 dark:text-slate-500 text-sm mt-1">{t('admin_add_user_to_get_started')}</p>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        {isUserFormOpen && (
            <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={handleCancelForm} role="dialog" aria-modal="true">
                <div className="w-full max-w-2xl" onClick={e => e.stopPropagation()}>
                    <UserForm 
                        onSave={handleSaveUser} 
                        onCancel={handleCancelForm} 
                        initialData={editingUser} 
                    />
                </div>
            </div>
        )}
        
         <ConfirmationModal
            isOpen={!!userToDelete}
            onClose={() => setUserToDelete(null)}
            onConfirm={handleConfirmDelete}
            title={t('admin_delete_confirm_title')}
            message={t('admin_delete_confirm_text')}
        />
        </>
    );
};