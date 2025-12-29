import React from 'react';
import { Icon } from '@/src/ui/components/Icon';
import { useLanguage } from '@/contexts/LanguageContext';
import { Notification, NotificationLink } from '@/types';
import { timeAgo } from '@/utils/time';

interface NotificationsProps {
    onNotificationClick: (link: NotificationLink) => void;
    notifications: Notification[];
    onMarkAsRead: (id: string) => void;
}

export const Notifications: React.FC<NotificationsProps> = ({ onNotificationClick, notifications, onMarkAsRead }) => {
    const { t } = useLanguage();

    const notifIcons: Record<Notification['type'], React.ReactNode> = {
        forum: <Icon className="w-6 h-6 text-blue-500"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></Icon>,
        grade: <Icon className="w-6 h-6 text-green-500"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></Icon>,
        assignment: <Icon className="w-6 h-6 text-amber-500"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></Icon>,
        announcement: <Icon className="w-6 h-6 text-red-500"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></Icon>,
    };

    const handleNotificationClick = (notif: Notification) => {
        onNotificationClick(notif.link);
        if (!notif.isRead) {
            onMarkAsRead(notif.id);
        }
    };

    return (
        <div className="max-w-4xl mx-auto">
            <h1 className="text-3xl font-bold text-slate-800 dark:text-white mb-6">{t('notifications_title')}</h1>
            <div className="bg-white dark:bg-slate-800/50 rounded-lg shadow-md">
                <ul className="divide-y divide-slate-200 dark:divide-slate-700">
                    {notifications.map(notif => (
                        <li key={notif.id} onClick={() => handleNotificationClick(notif)} className={`p-4 flex items-start gap-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer ${!notif.isRead ? 'bg-slate-50 dark:bg-slate-900/30' : ''}`}>
                             <div className="flex-shrink-0 mt-1 p-2 bg-slate-100 dark:bg-slate-700 rounded-full">
                                {notifIcons[notif.type]}
                            </div>
                            <div className="flex-1">
                                <p className="text-slate-600 dark:text-slate-300">
                                    <span className="font-semibold text-slate-800 dark:text-white">{notif.context}</span> {t(notif.messageKey)}
                                </p>
                                <p className="text-sm text-slate-400 dark:text-slate-500 mt-1">{timeAgo(notif.timestamp)}</p>
                            </div>
                            {!notif.isRead && <div className="w-2.5 h-2.5 rounded-full bg-blue-500 mt-2 flex-shrink-0" title="Unread"></div>}
                        </li>
                    ))}
                    {notifications.length === 0 && (
                        <li className="p-8 text-center text-sm text-slate-500">{t('notifications_no_new')}</li>
                    )}
                </ul>
            </div>
        </div>
    );
};