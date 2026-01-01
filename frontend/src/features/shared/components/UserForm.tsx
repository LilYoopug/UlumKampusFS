import React, { useState, useEffect } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { User, UserRole } from '@/types';
import { Icon } from '@/src/ui/components/Icon';

interface UserFormProps {
    onSave: (userData: User) => void;
    onCancel: () => void;
    initialData?: User | null;
}

export const UserForm: React.FC<UserFormProps> = ({ onSave, onCancel, initialData }) => {
    const { t } = useLanguage();
    const isEditMode = !!initialData;

    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [role, setRole] = useState<UserRole>('Mahasiswa');
    const [phoneNumber, setPhoneNumber] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [passwordError, setPasswordError] = useState('');

    useEffect(() => {
        if (initialData) {
            setName(initialData.name);
            setEmail(initialData.email);
            setRole(initialData.role);
            setPhoneNumber(initialData.phoneNumber || '');
            setPassword(''); // Always reset password field when editing
            setPasswordConfirmation('');
            setPasswordError('');
        } else {
            setName('');
            setEmail('');
            setRole('Mahasiswa');
            setPhoneNumber('');
            setPassword('');
            setPasswordConfirmation('');
            setPasswordError('');
        }
    }, [initialData]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Validate password confirmation for new users or when password is being changed
        if (!isEditMode || (password && password.trim() !== '')) {
            if (password !== passwordConfirmation) {
                setPasswordError('Password tidak cocok');
                return;
            }
            if (password.length < 8) {
                setPasswordError('Password minimal 8 karakter');
                return;
            }
            setPasswordError('');
        } else if (password !== passwordConfirmation) {
            // Clear error if both fields are empty (not changing password)
            setPasswordError('');
        }
        
        const userData = {
            ...initialData,
            name,
            email,
            role,
            phoneNumber,
            ...(password && password.trim() !== '' && { 
                password,
                password_confirmation: passwordConfirmation 
            }),
        } as User;
        onSave(userData);
    };
    
    const userRoles: UserRole[] = ['Mahasiswa', 'Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin'];

    return (
        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div className="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h1 className="text-2xl font-bold text-slate-800 dark:text-white">
                    {isEditMode ? t('admin_user_form_edit_title') : t('admin_user_form_add_title')}
                </h1>
                <button onClick={onCancel} className="p-2 rounded-full text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <Icon className="w-6 h-6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></Icon>
                </button>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-6">
                <div>
                    <label htmlFor="user-name" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('admin_user_form_name')}</label>
                    <input
                        type="text"
                        id="user-name"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                        placeholder="Masukkan nama lengkap"
                        required
                    />
                </div>
                <div>
                    <label htmlFor="user-email" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('admin_user_form_email')}</label>
                    <input
                        type="email"
                        id="user-email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                        placeholder="Masukkan alamat email"
                        required
                        disabled={isEditMode} // Prevent editing email as it's often a unique key
                    />
                </div>
                <div>
                    <label htmlFor="user-role" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('admin_user_form_role')}</label>
                    <select
                        id="user-role"
                        value={role}
                        onChange={(e) => setRole(e.target.value as UserRole)}
                        className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                        required
                    >
                        <option value="" disabled>{t('admin_user_form_select_role')}</option>
                        {userRoles.map(r => <option key={r} value={r}>{r}</option>)}
                    </select>
                </div>
                <div>
                    <label htmlFor="user-phone" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">Nomor Telepon</label>
                    <input
                        type="tel"
                        id="user-phone"
                        value={phoneNumber}
                        onChange={(e) => setPhoneNumber(e.target.value)}
                        className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                        placeholder="Masukkan nomor telepon"
                    />
                </div>
                <div>
                    <label htmlFor="user-password" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">
                        {isEditMode ? 'Password (Kosongkan jika tidak ingin diubah)' : 'Password'}
                    </label>
                    <input
                        type="password"
                        id="user-password"
                        value={password}
                        onChange={(e) => {
                            setPassword(e.target.value);
                            setPasswordError(''); // Clear error when typing
                        }}
                        className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                        placeholder={isEditMode ? "Kosongkan jika tidak ingin diubah" : "Masukkan password"}
                        required={!isEditMode}
                    />
                </div>
                <div>
                    <label htmlFor="user-password-confirmation" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">
                        {isEditMode ? 'Konfirmasi Password (Kosongkan jika tidak ingin diubah)' : 'Konfirmasi Password'}
                    </label>
                    <input
                        type="password"
                        id="user-password-confirmation"
                        value={passwordConfirmation}
                        onChange={(e) => {
                            setPasswordConfirmation(e.target.value);
                            setPasswordError(''); // Clear error when typing
                        }}
                        className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                        placeholder={isEditMode ? "Kosongkan jika tidak ingin diubah" : "Ulangi password"}
                        required={!isEditMode}
                    />
                    {passwordError && (
                        <p className="mt-1 text-sm text-red-600 dark:text-red-400">{passwordError}</p>
                    )}
                </div>
                <div className="flex justify-end items-center gap-4 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <button
                        type="button"
                        onClick={onCancel}
                        className="px-6 py-2 rounded-lg bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors"
                    >
                        {t('button_cancel')}
                    </button>
                    <button
                        type="submit"
                        className="px-6 py-2 rounded-lg bg-brand-emerald-600 text-white font-semibold hover:bg-brand-emerald-700 transition-colors"
                    >
                        {isEditMode ? t('admin_button_save_user') : t('admin_button_create_user')}
                    </button>
                </div>
            </form>
        </div>
    );
};
