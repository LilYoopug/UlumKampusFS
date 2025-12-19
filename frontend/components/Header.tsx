import React, { useState, useEffect, useRef, useMemo } from 'react';
import { Icon } from './Icon';
import { useLanguage } from '../contexts/LanguageContext';
import { LanguageSwitcher } from './LanguageSwitcher';
import { RoleSwitcher } from './RoleSwitcher';
import { User, Notification, NotificationLink, Page } from '../types';
import { timeAgo } from '../utils/time';

interface HeaderProps {
    toggleDarkMode: () => void;
    isDarkMode: boolean;
    currentUser: User;
    setCurrentUser: (user: User) => void;
    navigateTo: (page: Page) => void;
    handleNotificationClick: (link: NotificationLink) => void;
    onToggleMobileSidebar: () => void;
    notifications: Notification[];
    onMarkNotificationAsRead: (id: string) => void;
    onMarkAllNotificationsAsRead: () => void;
}

export const Header: React.FC<HeaderProps> = ({ toggleDarkMode, isDarkMode, currentUser, setCurrentUser, navigateTo, handleNotificationClick, onToggleMobileSidebar, notifications, onMarkNotificationAsRead, onMarkAllNotificationsAsRead }) => {
  const { t } = useLanguage();
  const [isNotifOpen, setIsNotifOpen] = useState(false);
  const notifRef = useRef<HTMLDivElement>(null);

  const unreadCount = useMemo(() => notifications.filter(n => !n.isRead).length, [notifications]);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (notifRef.current && !notifRef.current.contains(event.target as Node)) {
        setIsNotifOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);
  
  const handleNotifItemClick = (notif: Notification) => {
      handleNotificationClick(notif.link);
      if (!notif.isRead) {
          onMarkNotificationAsRead(notif.id);
      }
      setIsNotifOpen(false);
  }

 const notifIcons: Record<Notification['type'], React.ReactNode> = {
  forum: <Icon className="w-6 h-6 text-blue-500"><path d="M21 15a2 2 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></Icon>,
     grade: <Icon className="w-6 h-6 text-green-500"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></Icon>,
     assignment: <Icon className="w-6 h-6 text-amber-500"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></Icon>,
     announcement: <Icon className="w-6 h-6 text-red-500"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></Icon>,
  };

  return (
    <header className="flex-shrink-0 bg-white dark:bg-slate-800/50 p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
      <div className="flex items-center gap-2 sm:hidden">
        <button onClick={onToggleMobileSidebar} className="p-2 text-slate-600 dark:text-slate-300">
          <Icon className="w-6 h-6"><path d="M4 6h16M4 12h16M4 18h16"/></Icon>
        </button>
         <div className="p-2 bg-brand-emerald-500 rounded-lg">
             <Icon className="text-white h-8 w-8">
                 <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
             </Icon>
         </div>
      </div>
      
      <div className="hidden sm:flex flex-1"></div>

      <div className="flex items-center space-x-2 sm:space-x-4">
        <RoleSwitcher currentUser={currentUser} setCurrentUser={setCurrentUser} />
        <LanguageSwitcher />
         <button onClick={toggleDarkMode} className="p-2 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
           {isDarkMode ? (
             <Icon className="w-6 h-6 text-amber-400">
                 <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
             </Icon>
           ) : (
              <Icon className="w-6 h-6 text-slate-500 dark:text-slate-400">
                 <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
              </Icon>
           )}
         </button>
        <div className="relative" ref={notifRef}>
            <button onClick={() => setIsNotifOpen(p => !p)} className="relative p-2 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
              <Icon className="w-6 h-6 text-slate-500 dark:text-slate-400">
                 <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
              </Icon>
              {unreadCount > 0 && (
                <span className="absolute top-1 end-1 flex h-4 w-4 items-center justify-center text-xs font-bold text-white bg-red-500 rounded-full">
                  {unreadCount}
                </span>
              )}
            </button>
            {isNotifOpen && (
                <div className="absolute end-0 mt-2 w-80 sm:w-96 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 z-50 flex flex-col">
                    <div className="p-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 className="font-bold text-slate-800 dark:text-white">{t('notifications_title')}</h3>
                    </div>
                    <div className="max-h-96 overflow-y-auto">
                        {notifications.map(notif => (
                             <div key={notif.id} onClick={() => handleNotifItemClick(notif)} className={`p-4 flex items-start gap-4 border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer ${!notif.isRead ? 'bg-slate-50 dark:bg-slate-900/30' : ''}`}>
                                   <div className="flex-shrink-0 mt-1 p-2 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                                       {notifIcons[notif.type]}
                                   </div>
                                <div className="flex-1">
                                    <p className="text-sm text-slate-600 dark:text-slate-300">
                                        <span className="font-semibold text-slate-800 dark:text-white">{notif.context}</span> {t(notif.messageKey)}
                                    </p>
                                    <p className="text-xs text-slate-400 dark:text-slate-500 mt-1">{timeAgo(notif.timestamp)}</p>
                                </div>
                                {!notif.isRead && <div className="w-2.5 h-2.5 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></div>}
                            </div>
                        ))}
                         {notifications.length === 0 && (
                            <p className="p-8 text-center text-sm text-slate-50">{t('notifications_no_new')}</p>
                        )}
                    </div>
                     <div className="p-2 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 text-center">
                        <button onClick={() => navigateTo('notifications')} className="text-sm font-semibold text-brand-emerald-600 hover:underline">{t('notifications_view_all')}</button>
                    </div>
                </div>
            )}
        </div>

      </div>
    </header>
  );
};
