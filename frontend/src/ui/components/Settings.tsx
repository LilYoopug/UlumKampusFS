import React, { useState } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { Icon } from '@/src/ui/components/Icon';
// FIX: Imported TranslationKey and User to fix type errors and use correct props.
import { TranslationKey, User } from '@/types';
import { useDarkMode } from '@/hooks/useDarkMode';
import { userAPI } from '@/services/apiService';

type SettingsSection = 'profile' | 'appearance' | 'notifications' | 'account';

interface SettingsProps {
    isDarkMode: boolean;
    toggleDarkMode: () => void;
    currentUser: User;
}

interface ToggleSwitchProps {
    checked: boolean;
    onChange: () => void;
    label?: string;
    id?: string;
}

const ToggleSwitch: React.FC<ToggleSwitchProps> = ({ checked, onChange, label, id }) => {
    const switchId = id || `toggle-${Math.random().toString(36).substr(2, 9)}`;
    return (
        <label htmlFor={switchId} className="relative inline-flex items-center cursor-pointer">
            <input
                type="checkbox"
                id={switchId}
                checked={checked}
                onChange={onChange}
                className="sr-only peer"
                role="switch"
                aria-checked={checked}
                aria-label={label}
            />
            <div className="w-11 h-6 bg-slate-200 dark:bg-slate-700 rounded-full peer peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-emerald-300 dark:peer-focus:ring-brand-emerald-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-brand-emerald-600 pointer-events-none"></div>
        </label>
    );
};

export const Settings: React.FC<SettingsProps> = ({ isDarkMode, toggleDarkMode, currentUser }) => {
    const { t } = useLanguage();
    const [activeSection, setActiveSection] = useState<SettingsSection>('profile');
    
    // State for Profile form
    const [name, setName] = useState(currentUser.name);
    const [bio, setBio] = useState(currentUser.bio);
    const [avatarUrl, setAvatarUrl] = useState(currentUser.avatarUrl);
    const [isAvatarModalOpen, setIsAvatarModalOpen] = useState(false);
    const [newAvatarUrl, setNewAvatarUrl] = useState('');
    const [previewUrl, setPreviewUrl] = useState(currentUser.avatarUrl);
    
    // State for saving
    const [isSaving, setIsSaving] = useState(false);
    const [saveMessage, setSaveMessage] = useState<{type: 'success' | 'error', text: string} | null>(null);

    // Function to save profile changes
    const saveProfile = async () => {
        setIsSaving(true);
        setSaveMessage(null);
        try {
            await userAPI.updateMe({
                name: name,
                bio: bio || '',
                avatarUrl: avatarUrl
            });
            setSaveMessage({ type: 'success', text: t('settings_profile_saved') });
            // Clear message after 3 seconds
            setTimeout(() => setSaveMessage(null), 3000);
        } catch (error) {
            console.error('Error saving profile:', error);
            setSaveMessage({ type: 'error', text: t('settings_profile_save_error') });
        } finally {
            setIsSaving(false);
        }
    };

    // State for Notification toggles
    const [courseNotifications, setCourseNotifications] = useState(true);
    const [assignmentNotifications, setAssignmentNotifications] = useState(true);
    const [forumNotifications, setForumNotifications] = useState(false);

    const navItems: { id: SettingsSection; label: TranslationKey; icon: React.ReactNode }[] = [
        { id: 'profile', label: 'settings_section_profile', icon: <Icon className="w-5 h-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></Icon> },
        { id: 'appearance', label: 'settings_section_appearance', icon: <Icon className="w-5 h-5"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></Icon> },
        { id: 'notifications', label: 'settings_section_notifications', icon: <Icon className="w-5 h-5"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" /></Icon> },
        { id: 'account', label: 'settings_section_account', icon: <Icon className="w-5 h-5"><path d="M15 7a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z"/><path d="M6 8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2z"/></Icon> },
    ];

    const renderContent = () => {
        switch (activeSection) {
            case 'profile':
                return (
                     <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                        <h3 className="text-xl font-bold text-slate-800 dark:text-white mb-6">{t('settings_section_profile')}</h3>
                        <form onSubmit={async (e) => {
                                e.preventDefault();
                                await saveProfile();
                            }} className="space-y-6">
                            <div className="flex items-center gap-6">
                                <img src={avatarUrl} alt={name} className="w-24 h-24 rounded-full"/>
                                <div>
                                <button 
                                    type="button"
                                    onClick={() => setIsAvatarModalOpen(true)}
                                    className="px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors text-sm"
                                >
                                    {t('settings_profile_edit_picture')}
                                </button>
                                </div>
                            </div>
                            <div>
                                <label htmlFor="full-name" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('settings_profile_name')}</label>
                                <input type="text" id="full-name" value={name} onChange={e => setName(e.target.value)} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-60 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500" />
                            </div>
                            <div>
                                <label htmlFor="bio" className="block text-sm font-medium text-slate-600 dark:text-slate-300 mb-1">{t('settings_profile_bio')}</label>
                                <textarea id="bio" value={bio} onChange={e => setBio(e.target.value)} rows={4} className="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-60 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"></textarea>
                            </div>
                            <div className="border-t border-slate-200 dark:border-slate-700 pt-6 flex items-center justify-between">
                                {saveMessage && (
                                    <p className={`text-sm ${saveMessage.type === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}`}>
                                        {saveMessage.text}
                                    </p>
                                )}
                                <div className="flex-1" />
                                <button
                                    type="submit"
                                    disabled={isSaving}
                                    className="px-5 py-2.5 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    {isSaving && (
                                        <svg className="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    )}
                                    {isSaving ? t('saving') : t('settings_profile_save')}
                                </button>
                            </div>
                        </form>
                        
                        {/* Avatar Change Modal */}
                        {isAvatarModalOpen && (
                            <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
                                <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md">
                                    <div className="p-6">
                                        <div className="flex justify-between items-center mb-4">
                                        <h2 className="text-xl font-bold text-slate-800 dark:text-white">
                                            {t('settings_profile_edit_picture')}
                                        </h2>
                                            <button 
                                                onClick={() => setIsAvatarModalOpen(false)}
                                                className="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                                            >
                                                <Icon className="w-6 h-6">
                                                    <path d="M18 6 6 18M6 6l12 12" />
                                                </Icon>
                                            </button>
                                        </div>
                                        
                                        <div className="space-y-4">
                                            <div>
                                                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                    {t('settings_profile_image_url')}
                                                </label>
                                                <input
                                                    type="text"
                                                    value={newAvatarUrl}
                                                    onChange={(e) => {
                                                        setNewAvatarUrl(e.target.value);
                                                        setPreviewUrl(e.target.value);
                                                    }}
                                                    placeholder="https://example.com/image.jpg"
                                                    className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-90 dark:text-white"
                                                />
                                            </div>
                                            
                                            <div className="text-center">
                                                <h3 className="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                                    {t('settings_profile_preview')}
                                                </h3>
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    {/* Different sizes preview */}
                                                    <div className="space-y-4">
                                                        <div className="text-center">
                                                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">{t('settings_profile_size_small')}</p>
                                                            <img 
                                                                src={previewUrl} 
                                                                alt="Preview" 
                                                                className="w-12 h-12 rounded-full mx-auto border-2 border-slate-200 dark:border-slate-60"
                                                                onError={(e) => {
                                                                    const target = e.target as HTMLImageElement;
                                                                    target.src = currentUser.avatarUrl; // fallback to current avatar
                                                                    setPreviewUrl(currentUser.avatarUrl);
                                                                }}
                                                            />
                                                        </div>
                                                        <div className="text-center">
                                                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">{t('settings_profile_size_medium')}</p>
                                                            <img 
                                                                src={previewUrl} 
                                                                alt="Preview" 
                                                                className="w-16 h-16 rounded-full mx-auto border-2 border-slate-200 dark:border-slate-60"
                                                                onError={(e) => {
                                                                    const target = e.target as HTMLImageElement;
                                                                    target.src = currentUser.avatarUrl; // fallback to current avatar
                                                                    setPreviewUrl(currentUser.avatarUrl);
                                                                }}
                                                            />
                                                        </div>
                                                        <div className="text-center">
                                                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">{t('settings_profile_size_large')}</p>
                                                            <img 
                                                                src={previewUrl} 
                                                                alt="Preview" 
                                                                className="w-24 h-24 rounded-full mx-auto border-2 border-slate-200 dark:border-slate-60"
                                                                onError={(e) => {
                                                                    const target = e.target as HTMLImageElement;
                                                                    target.src = currentUser.avatarUrl; // fallback to current avatar
                                                                    setPreviewUrl(currentUser.avatarUrl);
                                                                }}
                                                            />
                                                        </div>
                                                    </div>
                                                    
                                                    {/* Context previews */}
                                                    <div className="space-y-4">
                                                        <div>
                                                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">Forum Discussion</p>
                                                            <div className="flex items-center space-x-2 p-2 bg-slate-50 dark:bg-slate-700 rounded-lg">
                                                                <img 
                                                                    src={previewUrl} 
                                                                    alt="Forum Preview" 
                                                                    className="w-8 h-8 rounded-full"
                                                                    onError={(e) => {
                                                                        const target = e.target as HTMLImageElement;
                                                                        target.src = currentUser.avatarUrl; // fallback to current avatar
                                                                        setPreviewUrl(currentUser.avatarUrl);
                                                                    }}
                                                                />
                                                                <div className="text-xs text-slate-600 dark:text-slate-300 truncate">User comment preview...</div>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">Assignment Comment</p>
                                                            <div className="flex items-center space-x-2 p-2 bg-slate-50 dark:bg-slate-700 rounded-lg">
                                                                <img 
                                                                    src={previewUrl} 
                                                                    alt="Comment Preview" 
                                                                    className="w-10 h-10 rounded-full"
                                                                    onError={(e) => {
                                                                        const target = e.target as HTMLImageElement;
                                                                        target.src = currentUser.avatarUrl; // fallback to current avatar
                                                                        setPreviewUrl(currentUser.avatarUrl);
                                                                    }}
                                                                />
                                                                <div className="text-xs text-slate-600 dark:text-slate-300 truncate">Assignment comment preview...</div>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">Profile View</p>
                                                            <div className="flex flex-col items-center p-2 bg-slate-50 dark:bg-slate-700 rounded-lg">
                                                                <img 
                                                                    src={previewUrl} 
                                                                    alt="Profile Preview" 
                                                                    className="w-16 h-16 rounded-full mb-1"
                                                                    onError={(e) => {
                                                                        const target = e.target as HTMLImageElement;
                                                                        target.src = currentUser.avatarUrl; // fallback to current avatar
                                                                        setPreviewUrl(currentUser.avatarUrl);
                                                                    }}
                                                                />
                                                                <span className="text-xs text-slate-600 dark:text-slate-300">Username</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    </div>
                                                </div>
                                            
                                            <div className="flex justify-end space-x-3 pt-4">
                                                <button
                                                    type="button"
                                                    onClick={() => setIsAvatarModalOpen(false)}
                                                    className="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                                >
                                                    {t('cancel')}
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={async () => {
                                                        if (newAvatarUrl) {
                                                            setAvatarUrl(newAvatarUrl);
                                                            // Update the preview URL to the new avatar
                                                            setPreviewUrl(newAvatarUrl);
                                                            // Save the profile with the new avatar URL
                                                            await saveProfile();
                                                            setIsAvatarModalOpen(false);
                                                        }
                                                    }}
                                                    disabled={!newAvatarUrl}
                                                    className="px-4 py-2 bg-brand-emerald-60 text-white rounded-lg hover:bg-brand-emerald-700 transition-colors disabled:opacity-50"
                                                >
                                                    {t('save')}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                );
            case 'appearance':
                return (
                     <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                        <h3 className="text-xl font-bold text-slate-800 dark:text-white mb-2">{t('settings_appearance_theme')}</h3>
                        <p className="text-slate-500 dark:text-slate-400 mb-6">{t('settings_appearance_desc')}</p>
                        <div className="flex items-center justify-between p-4 rounded-lg bg-slate-50 dark:bg-slate-900/50">
                            <span className="font-medium text-slate-800 dark:text-white">{isDarkMode ? t('settings_appearance_dark') : t('settings_appearance_light')}</span>
                            <ToggleSwitch checked={isDarkMode} onChange={toggleDarkMode} label="Toggle dark mode" id="theme-toggle" />
                        </div>
                    </div>
                );
            case 'notifications':
                 return (
                     <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                        <h3 className="text-xl font-bold text-slate-800 dark:text-white mb-6">{t('settings_section_notifications')}</h3>
                        <ul className="divide-y divide-slate-200 dark:divide-slate-700">
                           <li className="py-4 flex items-center justify-between">
                                <div>
                                    <p className="font-medium text-slate-800 dark:text-slate-100">{t('settings_notifications_course')}</p>
                                    <p className="text-sm text-slate-500 dark:text-slate-400">{t('settings_notifications_course_desc')}</p>
                                </div>
                                <ToggleSwitch checked={courseNotifications} onChange={() => setCourseNotifications(p => !p)} label="Toggle course notifications" id="course-notifications" />
                           </li>
                           <li className="py-4 flex items-center justify-between">
                                <div>
                                    <p className="font-medium text-slate-800 dark:text-slate-100">{t('settings_notifications_assignments')}</p>
                                    <p className="text-sm text-slate-500 dark:text-slate-400">{t('settings_notifications_assignments_desc')}</p>
                                </div>
                                <ToggleSwitch checked={assignmentNotifications} onChange={() => setAssignmentNotifications(p => !p)} label="Toggle assignment notifications" id="assignment-notifications" />
                           </li>
                           <li className="py-4 flex items-center justify-between">
                                <div>
                                    <p className="font-medium text-slate-800 dark:text-slate-100">{t('settings_notifications_forum')}</p>
                                    <p className="text-sm text-slate-500 dark:text-slate-400">{t('settings_notifications_forum_desc')}</p>
                                </div>
                                <ToggleSwitch checked={forumNotifications} onChange={() => setForumNotifications(p => !p)} label="Toggle forum notifications" id="forum-notifications" />
                           </li>
                        </ul>
                    </div>
                );
            case 'account':
                return (
                    <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md space-y-6">
                        <div>
                             <h3 className="text-xl font-bold text-slate-800 dark:text-white mb-2">{t('settings_account_password')}</h3>
                             <button className="px-4 py-2 bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors text-sm">{t('settings_account_password_button')}</button>
                        </div>
                         <div className="border-t border-red-200 dark:border-red-900/50 pt-6">
                             <h3 className="text-xl font-bold text-red-700 dark:text-red-400 mb-2">{t('settings_account_logout')}</h3>
                             <button className="px-4 py-2 bg-transparent border border-red-500 text-red-500 font-semibold rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-sm">{t('settings_account_logout_button')}</button>
                        </div>
                    </div>
                );
            default:
                return null;
        }
    };
    
    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('settings_title')}</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">{t('settings_subtitle')}</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                <aside className="md:col-span-1">
                    <nav className="space-y-1">
                        {navItems.map(item => (
                            <button
                                key={item.id}
                                onClick={() => setActiveSection(item.id)}
                                className={`w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-start font-medium transition-colors ${
                                    activeSection === item.id 
                                        ? 'bg-brand-emerald-100 dark:bg-brand-emerald-900/50 text-brand-emerald-700 dark:text-brand-emerald-300' 
                                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50'
                                }`}
                            >
                                {item.icon}
                                <span>{t(item.label)}</span>
                            </button>
                        ))}
                    </nav>
                </aside>
                
                <main className="md:col-span-3">
                    {renderContent()}
                </main>
            </div>
        </div>
    );
};
