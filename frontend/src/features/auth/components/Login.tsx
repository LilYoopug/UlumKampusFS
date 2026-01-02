import React, { useState } from 'react';
import { LandingLayout } from '@/src/features/landing/components/LandingLayout';
import { useLanguage } from '@/contexts/LanguageContext';
import { User } from '@/types';
import { TranslationKey } from '@/translations';

interface LoginProps {
  onLogin: (email: string, password: string) => Promise<void>;
  onNavigateToRegister: () => void;
  onBack: () => void;
}

export const Login: React.FC<LoginProps> = ({ onLogin, onNavigateToRegister, onBack }) => {
    const { t } = useLanguage();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [isLoading, setIsLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setIsLoading(true);

        try {
            // Call the parent component's login handler
            await onLogin(email, password);
        } catch (err: any) {
            setError(err.message || t('auth_login_error'));
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <LandingLayout onNavigateToLogin={() => {}} page="auth" onBack={onBack}>
            <main className="pt-24 pb-20 bg-slate-50 dark:bg-brand-midnight min-h-screen flex items-center justify-center">
                <div className="w-full max-w-md p-8 space-y-6 bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700">
                    <div className="text-center">
                        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('auth_login_title')}</h1>
                    </div>
                    {error && <p className="text-center text-red-500 bg-red-100 dark:bg-red-90/50 p-3 rounded-lg">{error}</p>}
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label htmlFor="email" className="block text-sm font-medium text-slate-600 dark:text-slate-300">{t('auth_email_label')}</label>
                            <div className="mt-1">
                                <input id="email" name="email" type="email" autoComplete="email" required value={email} onChange={e => setEmail(e.target.value)}
                                    className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                                    placeholder={t('auth_email_placeholder')}
                                    disabled={isLoading}
                                />
                            </div>
                        </div>
                        <div>
                            <label htmlFor="password" className="block text-sm font-medium text-slate-600 dark:text-slate-300">{t('auth_password_label')}</label>
                            <div className="mt-1">
                                <input id="password" name="password" type="password" required value={password} onChange={e => setPassword(e.target.value)}
                                    className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                                    placeholder={t('auth_password_placeholder')}
                                    disabled={isLoading}
                                />
                            </div>
                        </div>
                        <div>
                            <button
                                type="submit"
                                className="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-emerald-600 hover:bg-brand-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled={isLoading}
                            >
                                {isLoading ? t('auth_loading' as TranslationKey) : t('auth_login_button' as TranslationKey)}
                            </button>
                        </div>
                    </form>
                    <p className="text-center text-sm text-slate-500 dark:text-slate-400">
                        {t('auth_no_account')}{' '}
                        <button onClick={onNavigateToRegister} className="font-medium text-brand-emerald-600 hover:text-brand-emerald-500">{t('auth_register_link')}</button>
                    </p>
                </div>
            </main>
        </LandingLayout>
    );
};
