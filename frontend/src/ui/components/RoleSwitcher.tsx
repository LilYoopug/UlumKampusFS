import React, { useState, useRef, useEffect } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { User } from '@/types';
import { Icon } from '@/src/ui/components/Icon';

interface RoleSwitcherProps {
    currentUser: User;
    setCurrentUser: (user: User) => void;
    users: User[];
}

export const RoleSwitcher: React.FC<RoleSwitcherProps> = ({ currentUser, setCurrentUser, users }) => {
  const { t } = useLanguage();
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);
  
  const roleIconMap: Record<User['role'], React.ReactNode> = {
    'Mahasiswa': <Icon className="w-5 h-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></Icon>,
    'Dosen': <Icon className="w-5 h-5"><path d="M18 8.86a4 4 0 1 0-8 0c0 1.44.51 2.73 1.32 3.64-1.23.23-2.19.4-2.82.52-2.1.41-3.5 1.55-3.5 2.98v2.02c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2v-2.02c0-1.43-1.4-2.57-3.5-2.98-.63-.12-1.59-.29-2.82-.52.81-.91 1.32-2.2 1.32-3.64Z"/><path d="M12 2v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></Icon>,
    'Prodi Admin': <Icon className="w-5 h-5"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h9v11h-9Z"/></Icon>,
    'Manajemen Kampus': <Icon className="w-5 h-5"><path d="M18 20V8.35a2 2 0 0 0-.59-1.41l-5.07-5.06a2 2 0 0 0-2.82 0L4.59 6.94A2 2 0 0 0 4 8.35V20a2 2 0 0 2 2h12a2 2 0 0 0 2-2Z"/><path d="M12 15v-5"/><path d="M9 12h6"/></Icon>,
    'MABA': <Icon className="w-5 h-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></Icon>,
    'Super Admin': <Icon className="w-5 h-5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></Icon>,
  };

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <div className="relative" ref={dropdownRef}>
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="flex items-center gap-2 p-2 rounded-full hover:bg-slate-200 dark:hover:bg-slate-70 transition-colors"
        aria-label="Switch User Role"
      >
        <span className="text-slate-60 dark:text-slate-300">
            {roleIconMap[currentUser.role]}
        </span>
        <span className="font-semibold text-sm hidden md:inline text-slate-700 dark:text-slate-200">{currentUser.role}</span>
        <Icon className="w-5 h-5 text-slate-50 dark:text-slate-400">
          <polyline points="6 9 12 15 18 9"/>
        </Icon>
      </button>
      {isOpen && (
        <div className="absolute end-0 mt-2 w-60 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50">
          {users.map(user => (
            <button
              key={user.email}
              onClick={() => {
                setCurrentUser(user);
                setIsOpen(false);
              }}
              className={`w-full text-start flex items-center gap-3 px-4 py-2 text-sm ${
                currentUser.email === user.email
                  ? 'bg-brand-emerald-50 dark:bg-brand-emerald-900/50 text-brand-emerald-700 dark:text-brand-emerald-300'
                  : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-70'
              }`}
            >
              <div className="flex-shrink-0 text-slate-500 dark:text-slate-400">{roleIconMap[user.role]}</div>
              <div>
                <p className="font-semibold">{user.name}</p>
                <p className="text-xs text-slate-500 dark:text-slate-400">{user.role}</p>
              </div>
            </button>
          ))}
        </div>
      )}
    </div>
  );
};
