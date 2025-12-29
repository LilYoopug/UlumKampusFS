import React, { useState } from 'react';
import { LandingLayout } from '@/src/features/landing/components/LandingLayout';
import { useLanguage } from '@/contexts/LanguageContext';

interface RegisterProps {
    onRegister: (data: { name: string; email: string; phone_number: string; password: string; password_confirmation: string; role?: string }) => void;
  onNavigateToLogin: () => void;
  onBack: () => void;
}

export const Register: React.FC<RegisterProps> = ({ onRegister, onNavigateToLogin, onBack }) => {
    const { t } = useLanguage();
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [phoneNumber, setPhoneNumber] = useState('');
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [error, setError] = useState('');

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (password !== confirmPassword) {
            setError(t('auth_password_mismatch'));
            return;
        }
        setError('');
        try {
            await onRegister({ name, email, phoneNumber, password, password_confirmation: confirmPassword });
        } catch (err) {
            setError('Registration failed');
        }
    };
    
    return (
        <LandingLayout onNavigateToLogin={onNavigateToLogin} page="auth" onBack={onBack}>
            <main className="pt-24 pb-20 bg-slate-50 dark:bg-brand-midnight min-h-screen flex items-center justify-center">
                <div className="w-full max-w-md p-8 space-y-6 bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700">
                     <div className="text-center">
                        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('auth_register_title')}</h1>
                    </div>
                    {error && <p className="text-center text-red-500 bg-red-100 dark:bg-red-900/50 p-3 rounded-lg">{error}</p>}
                     <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-slate-600 dark:text-slate-300">{t('auth_name_label')}</label>
                            <input id="name" type="text" required value={name} onChange={e => setName(e.target.value)}
                                className="w-full mt-1 px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                                placeholder={t('auth_name_placeholder')}
                            />
                        </div>
                        <div>
                            <label htmlFor="email" className="block text-sm font-medium text-slate-600 dark:text-slate-300">{t('auth_email_label')}</label>
                            <input id="email" type="email" required value={email} onChange={e => setEmail(e.target.value)}
                                className="w-full mt-1 px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                                placeholder={t('auth_email_placeholder')}
                            />
                        </div>
                        <div>
                            <label htmlFor="phone" className="block text-sm font-medium text-slate-600 dark:text-slate-300">Nomor Telepon</label>
                            <input id="phone" type="tel" required value={phoneNumber} onChange={e => setPhoneNumber(e.target.value)}
                                className="w-full mt-1 px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                                placeholder="Masukkan nomor telepon"
                            />
                        </div>
                        <div>
                            <label htmlFor="password" className="block text-sm font-medium text-slate-600 dark:text-slate-300">{t('auth_password_label')}</label>
                            <input id="password" type="password" required value={password} onChange={e => setPassword(e.target.value)}
                                className="w-full mt-1 px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                                placeholder={t('auth_password_placeholder')}
                            />
                        </div>
                         <div>
                            <label htmlFor="confirm-password" className="block text-sm font-medium text-slate-600 dark:text-slate-300">{t('auth_confirm_password_label')}</label>
                            <input id="confirm-password" type="password" required value={confirmPassword} onChange={e => setConfirmPassword(e.target.value)}
                                className="w-full mt-1 px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                                placeholder={t('auth_password_placeholder')}
                            />
                        </div>
                        <div>
                            <button type="submit" className="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-emerald-600 hover:bg-brand-emerald-700">
                                {t('auth_register_button')}
                            </button>
                        </div>
                    </form>
                    <p className="text-center text-sm text-slate-500 dark:text-slate-400">
                        {t('auth_has_account')}{' '}
                        <button onClick={onNavigateToLogin} className="font-medium text-brand-emerald-600 hover:text-brand-emerald-500">{t('auth_login_link')}</button>
                    </p>
                </div>
            </main>
        </LandingLayout>
    );
};
