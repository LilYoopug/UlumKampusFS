 import React from 'react';
 import { Icon } from '@/src/ui/components/Icon';
 import { User, Page } from '@/types';
 import { useLanguage } from '@/contexts/LanguageContext';
 
 // Add declarations for CDN-loaded libraries to the global window object
 declare global {
     interface Window {
         jspdf: any;
         XLSX: any;
     }
 }

const SystemStatus: React.FC<{ name: string; status: 'online' | 'degraded' | 'offline' }> = ({ name, status }) => {
    const statusMap = {
        online: { color: 'bg-green-500', text: 'Online' },
        degraded: { color: 'bg-amber-500', text: 'Degraded' },
        offline: { color: 'bg-red-500', text: 'Offline' },
    };
    const current = statusMap[status];

    return (
        <div className="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
            <span className="font-medium text-slate-700 dark:text-slate-200">{name}</span>
            <div className="flex items-center gap-2">
                <div className={`w-3 h-3 rounded-full ${current.color}`}></div>
                <span className="text-sm font-semibold text-slate-600 dark:text-slate-300">{current.text}</span>
            </div>
        </div>
    );
};

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

  interface SuperAdminDashboardProps {
      users?: User[];
      navigateTo?: (page: Page) => void;
  }


 export const SuperAdminDashboard: React.FC<SuperAdminDashboardProps> = ({ users = [] }) => {
     const { t } = useLanguage();

     // Function to update users state (if needed)
     const updateUsersState = (newUsers: User[]) => {
         // This function can remain for future use
     };

    return (
        <>
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-slate-800 dark:text-white">Super Admin Dashboard</h1>
                <p className="text-slate-500 dark:text-slate-400 mt-1">Manajemen sistem dan pengguna UlumCampus.</p>
            </div>
            
            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">Status Sistem</h2>
                <div className="space-y-3">
                    <SystemStatus name="Database Utama" status="online" />
                    <SystemStatus name="API Gateway" status="online" />
                    <SystemStatus name="Server Video" status="degraded" />
                    <SystemStatus name="Mail Service" status="online" />
                </div>
            </div>
            
            <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <h2 className="text-xl font-bold mb-4 text-slate-800 dark:text-white">Konfigurasi Situs</h2>
                <button className="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-white font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                    <Icon className="w-5 h-5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .3 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 0 0 1-2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 1 1.51 1.65 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></Icon>
                    Buka Pengaturan
                </button>
            </div>


         </div>
        

        </>
    );
};
