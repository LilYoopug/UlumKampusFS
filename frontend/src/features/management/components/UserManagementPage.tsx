import React, { useState, useEffect } from 'react';
import { Icon } from '@/src/ui/components/Icon';
import { User } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { UserForm } from '@/src/features/shared/components/UserForm';
import { apiService } from '@/services/apiService';

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

export const UserManagementPage: React.FC = () => {
    const { t } = useLanguage();
    const [allUsers, setAllUsers] = useState<User[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [isUserFormOpen, setIsUserFormOpen] = useState(false);
    const [editingUser, setEditingUser] = useState<User | null>(null);
    const [userToDelete, setUserToDelete] = useState<User | null>(null);

    // Fetch users from backend API
    useEffect(() => {
        fetchUsers();
    }, []);

    const fetchUsers = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await apiService.getUsers();
            const usersList = response.data || [];
            setAllUsers(usersList);
        } catch (err) {
            console.error('Error fetching users:', err);
            setError('Failed to load users. Please try again.');
        } finally {
            setLoading(false);
        }
    };

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
        if (userToDelete) {
            try {
                setLoading(true);
                setError(null);
                const userId = userToDelete.id || userToDelete.studentId || '';
                if (!userId) {
                    throw new Error('User ID is required for deletion');
                }
                await apiService.deleteUser(userId);
                
                // Refresh the users list
                await fetchUsers();
                setUserToDelete(null);
            } catch (error) {
                console.error('Error deleting user:', error);
                setError(error instanceof Error ? error.message : 'Failed to delete user');
            } finally {
                setLoading(false);
            }
        }
    };
    
    const handleSaveUser = async (user: User) => {
        try {
            setLoading(true);
            setError(null);
            
            if (editingUser) { // Update
                const userId = editingUser.id || editingUser.studentId || '';
                if (!userId) {
                    throw new Error('User ID is required for updates');
                }
                await apiService.updateUser(userId, user);
            } else { // Create
                await apiService.createUser(user);
            }
            
            // Refresh the users list
            await fetchUsers();
            setIsUserFormOpen(false);
            setEditingUser(null);
        } catch (error) {
            console.error('Error saving user:', error);
            setError(error instanceof Error ? error.message : 'Failed to save user');
        } finally {
            setLoading(false);
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
            {error && (
                <div className="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-200 px-4 py-3 rounded-lg">
                    <div className="flex items-center">
                        <Icon className="w-5 h-5 mr-2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </Icon>
                        {error}
                    </div>
                </div>
            )}

            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('admin_user_management')}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">{t('admin_user_management_description')}</p>
            </div>

            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <div className="flex flex-col lg:flex-row items-stretch lg:items-center gap-3 lg:gap-4 mb-4">
                    <div className="relative flex-grow w-full lg:w-auto">
                        <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                        </Icon>
                        <input type="text" placeholder={t('admin_search_users')} className="w-full ps-10 pe-4 py-3 lg:py-2 rounded-full bg-slate-100 dark:bg-slate-700 border border-transparent focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white text-base lg:text-sm" />
                    </div>
                    <div className="flex items-center gap-2">
                        <button className="flex-1 lg:flex-none flex items-center justify-center gap-2 px-4 py-3 lg:py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 active:scale-[0.98] transition-all">
                            <Icon className="w-5 h-5"><path d="M3 6h18"/><path d="M7 12h10"/><path d="M10 18h4"/></Icon>
                            {t('button_filter')}
                        </button>
                        <button onClick={handleExportPDF} className="p-3 lg:p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/50 rounded-full active:scale-[0.98] transition-all" title={t('admin_export_pdf')}>
                            <Icon className="w-5 h-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12v-1h3v1"/><path d="M10 15h3"/><path d="M10 18h3"/></Icon>
                        </button>
                        <button onClick={handleExportXLSX} className="p-3 lg:p-2 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/50 rounded-full active:scale-[0.98] transition-all" title={t('admin_export_xlsx')}>
                             <Icon className="w-5 h-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-4_M15 14h-3_M9 14h3"/><path d="M10.5 10.5 13.5 7.5M13.5 10.5 10.5 7.5"/></Icon>
                        </button>
                    </div>
                    <button onClick={handleAddNew} className="w-full lg:w-auto flex items-center justify-center gap-2 px-4 py-3 lg:py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 active:scale-[0.98] transition-all">
                        <Icon className="w-5 h-5"><path d="M12 5v14"/><path d="M5 12h14"/></Icon>
                        {t('admin_add_user')}
                    </button>
                </div>
                
                {loading && allUsers.length === 0 ? (
                    <div className="flex justify-center items-center py-12">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-emerald-600"></div>
                    </div>
                ) : (
                    <>
                        {/* Desktop Table View */}
                        <div className="hidden lg:block overflow-x-auto max-h-[60vh]">
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
                                            <tr key={user.id || user.studentId} className="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600/50">
                                                <td className="px-6 py-4 font-medium text-slate-900 dark:text-white">{user.name}</td>
                                                <td className="px-6 py-4">{user.email}</td>
                                                <td className="px-6 py-4">{user.role}</td>
                                                <td className="px-6 py-4">
                                                    {user.phoneNumber || user.phone || '-'}
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
                                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 1-2 2Z"/>
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

                        {/* Mobile Card View */}
                        <div className="lg:hidden space-y-3 max-h-[60vh] overflow-y-auto">
                            {allUsers.length > 0 ? (
                                allUsers.map(user => (
                                    <div key={user.id || user.studentId} className="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4 space-y-3">
                                        <div className="flex items-start gap-3">
                                            <div className="w-12 h-12 rounded-full bg-brand-emerald-100 dark:bg-brand-emerald-900/50 flex items-center justify-center flex-shrink-0">
                                                <span className="text-lg font-bold text-brand-emerald-600 dark:text-brand-emerald-400">
                                                    {user.name.charAt(0).toUpperCase()}
                                                </span>
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <h3 className="font-semibold text-slate-800 dark:text-white text-base truncate">{user.name}</h3>
                                                <p className="text-sm text-slate-500 dark:text-slate-400 truncate">{user.email}</p>
                                            </div>
                                            <span className={`flex-shrink-0 px-2.5 py-1 rounded-full text-xs font-medium ${
                                                user.role === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300' :
                                                user.role === 'lecturer' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' :
                                                'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300'
                                            }`}>
                                                {user.role}
                                            </span>
                                        </div>

                                        {(user.phoneNumber || user.phone) && (
                                            <div className="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                                                <Icon className="w-4 h-4">
                                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                                </Icon>
                                                <span>{user.phoneNumber || user.phone}</span>
                                            </div>
                                        )}

                                        <div className="flex gap-2 pt-2 border-t border-slate-200 dark:border-slate-600">
                                            <button
                                                onClick={() => handleEdit(user)}
                                                className="flex-1 py-2.5 px-4 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 active:scale-[0.98] transition-all text-sm"
                                            >
                                                {t('admin_manage_user')}
                                            </button>
                                            <button
                                                onClick={() => handleDeleteClick(user)}
                                                className="py-2.5 px-4 bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 font-semibold rounded-lg hover:bg-red-200 dark:hover:bg-red-900 active:scale-[0.98] transition-all text-sm"
                                            >
                                                <Icon className="w-5 h-5">
                                                    <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                                </Icon>
                                            </button>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="flex flex-col items-center justify-center py-12">
                                    <Icon className="w-12 h-12 text-slate-400 mb-3">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 1-2 2Z"/>
                                    </Icon>
                                    <p className="text-slate-500 dark:text-slate-400 font-medium">{t('admin_no_user_data')}</p>
                                    <p className="text-slate-400 dark:text-slate-500 text-sm mt-1">{t('admin_add_user_to_get_started')}</p>
                                </div>
                            )}
                        </div>
                    </>
                )}
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
