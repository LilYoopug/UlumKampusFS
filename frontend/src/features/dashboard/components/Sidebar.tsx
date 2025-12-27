import React, { useState } from 'react';
import { Page, User, UserRole } from '../types';
import { Icon } from './Icon';
import { useLanguage } from '../contexts/LanguageContext';

interface SidebarProps {
  currentPage: Page;
  navigateTo: (page: Page) => void;
  userRole: UserRole;
  currentUser: User;
  handleLogout: () => void;
  isMobileOpen: boolean;
  toggleMobileSidebar: () => void;
}

const NavItem: React.FC<{
  icon: React.ReactNode;
  label: string;
  isActive: boolean;
  onClick: () => void;
  isCollapsed: boolean;
}> = ({ icon, label, isActive, onClick, isCollapsed }) => (
  <li>
    <a
      href="#"
      onClick={(e) => {
        e.preventDefault();
        onClick();
      }}
      className={`flex items-center p-3 my-1 rounded-lg transition-colors ${
        isActive
          ? 'bg-brand-emerald-600 text-white shadow-lg'
          : 'text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
      }`}
      title={isCollapsed ? label : undefined}
    >
      {icon}
      {!isCollapsed && <span className="ms-3 font-medium">{label}</span>}
    </a>
  </li>
);

const mahasiswaNav = [
    { page: 'dashboard', icon: <Icon className="w-6 h-6"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></Icon>, labelKey: 'sidebar_dashboard' },
    { page: 'calendar', icon: <Icon className="w-6 h=6"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 2v4"/><path d="M16 2v4"/></Icon>, labelKey: 'sidebar_calendar' },
    { page: 'courses', icon: <Icon className="w-6 h-6"><path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M6 17h12"/></Icon>, labelKey: 'sidebar_catalog' },
    { page: 'grades', icon: <Icon className="w-6 h-6"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></Icon>, labelKey: 'sidebar_grades' },
    { page: 'assignments', icon: <Icon className="w-6 h-6"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></Icon>, labelKey: 'sidebar_assignments' },
    { page: 'video-lectures', icon: <Icon className="w-6 h-6"><path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.934a.5.5 0 0 0-.777-.416L16 11"/><rect x="2" y="7" width="14" height="10" rx="2" ry="2"/></Icon>, labelKey: 'sidebar_video_lectures' },
];

const sharedNavBottom = [
    { page: 'settings', icon: <Icon className="w-6 h-6"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></Icon>, labelKey: 'sidebar_settings' },
    { page: 'help', icon: <Icon className="w-6 h-6"><circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" /></Icon>, labelKey: 'sidebar_help' },
];

const getNavItemsForRole = (role: UserRole | string) => {
    const dashboardItem = { page: 'dashboard' as Page, icon: <Icon className="w-6 h-6"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></Icon>, labelKey: 'sidebar_dashboard' };
    const announcementsItem = { page: 'announcements' as Page, icon: <Icon className="w-6 h-6"><path d="M9 7H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h4l5 5V2L9 7z"/><path d="M15.5 12a4.5 4.5 0 0 0-4.5-4.5v9a4.5 4.5 0 0 0 4.5-4.5z"/></Icon>, labelKey: 'sidebar_announcements' };
    const manageELibraryItem = { page: 'manage-elibrary' as Page, icon: <Icon className="w-6 h-6"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H14a2 2 0 0 1 2 2v12.5"/><path d="M14 22v-6.5l5.5-5.5a2.12 2.12 0 0 1 3 3L17 18.5V22H14z"/></Icon>, labelKey: 'sidebar_manage_elibrary'};

switch (role) {
        case 'Dosen':
            return [
                dashboardItem,
                announcementsItem,
                { page: 'courses', icon: <Icon className="w-6 h-6"><path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M6 17h12"/></Icon>, labelKey: 'sidebar_my_courses' },
                { page: 'gradebook', icon: <Icon className="w-6 h-6"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></Icon>, labelKey: 'sidebar_gradebook' },
                manageELibraryItem,
                ...sharedNavBottom,
            ];
        case 'Prodi Admin':
             return [
                dashboardItem,
                { page: 'prodi-courses' as Page, icon: <Icon className="w-6 h-6"><path d="M17 3a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H7z"/><path d="M17 8h2"/><path d="M17 12h2"/><path d="M7 3a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H7z"/><path d="M7 8h2"/><path d="M7 12h2"/></Icon>, labelKey: 'sidebar_prodi_courses' },
                { page: 'prodi-students' as Page, icon: <Icon className="w-6 h-6"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></Icon>, labelKey: 'sidebar_prodi_students' },
                { page: 'prodi-lecturers' as Page, icon: <Icon className="w-6 h-6"><path d="M18 8.86a4 4 0 1 0-8 0c0 1.44.51 2.73 1.32 3.64-1.23.23-2.19.4-2.82.52-2.1.41-3.5 1.55-3.5 2.98v2.02c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2v-2.02c0-1.43-1.4-2.57-3.5-2.98-.63-.12-1.59-.29-2.82-.52.81-.91 1.32-2.2 1.32-3.64Z"/></Icon>, labelKey: 'sidebar_prodi_lecturers' },
                announcementsItem,
                manageELibraryItem,
                ...sharedNavBottom,
            ];
case 'Manajemen Kampus':
                  return [
                     dashboardItem,
                     announcementsItem,
                     { page: 'prodi-lecturers' as Page, icon: <Icon className="w-6 h-6"><path d="M18 8.86a4 4 0 1 0-8 0c0 1.44.51 2.73 1.32 3.64-1.23.23-2.19.4-2.82.52-2.1.41-3.5 1.55-3.5 2.98v2.02c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2v-2.02c0-1.43-1.4-2.57-3.5-2.98-.63-.12-1.59-.29-2.82-.52.81-.91 1.32-2.2 1.32-3.64Z"/></Icon>, labelKey: 'sidebar_prodi_lecturers' },
                     { page: 'prodi-students' as Page, icon: <Icon className="w-6 h-6"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></Icon>, labelKey: 'sidebar_prodi_students' },
                     { page: 'prodi-courses' as Page, icon: <Icon className="w-6 h-6"><path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M6 17h12"/></Icon>, labelKey: 'sidebar_my_courses' },
                     { page: 'student-registration' as Page, icon: <Icon className="w-6 h-6"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="12" y1="3" x2="12" y2="11"/></Icon>, labelKey: 'management_student_registration' },
                     { page: 'management-administration' as Page, icon: <Icon className="w-6 h-6"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><path d="M9 3v18"/><path d="M3 9h18"/><path d="M15 9v6"/><path d="M9 15h6"/></Icon>, labelKey: 'sidebar_administrasi' },
                     { page: 'manajemen-fakultas' as Page, icon: <Icon className="w-6 h-6"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></Icon>, labelKey: 'management_faculty' },
                     ...sharedNavBottom,
                 ];
          case 'Super Admin':
               return [
                  dashboardItem,
                  announcementsItem,
                  { page: 'user-management' as Page, icon: <Icon className="w-6 h-6"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></Icon>, labelKey: 'sidebar_user_management' },
                  ...sharedNavBottom,
              ];
     case 'MABA':
          return [
              { page: 'registrasi', icon: <Icon className="w-6 h-6"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="12" y1="3" x2="12" y2="11"/></Icon>, labelKey: 'sidebar_registrasi' },
              { page: 'administrasi', icon: <Icon className="w-6 h-6"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><path d="M9 3v18"/><path d="M3 9h18"/><path d="M15 9v6"/><path d="M9 15h6"/></Icon>, labelKey: 'sidebar_administrasi' },
              ...sharedNavBottom
          ];
     case 'Mahasiswa':
     default:
         return [
             mahasiswaNav[0], // Dashboard
             announcementsItem,
             ...mahasiswaNav.slice(1, 5), // The rest of mahasiswaNav until administrasi
              { page: 'administrasi', icon: <Icon className="w-6 h-6"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><path d="M9 3v18"/><path d="M3 9h18"/><path d="M15 9v6"/><path d="M9 15h6"/></Icon>, labelKey: 'sidebar_administrasi' }, // Add administrasi specifically for students
             { page: 'elibrary', icon: <Icon className="w-6 h-6"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></Icon>, labelKey: 'sidebar_elibrary' },
             { page: 'worship', icon: <Icon className="w-6 h-6"><path d="M12.22 2h-4.44l-2 6-6 2 6 2 2 6 2-6 6-2-6-2z"/><path d="M20.91 14.65a2.43 2.43 0 0 0-2.26 2.26l.09.63a2.43 2.43 0 0 0 2.26 2.26l.63.09a2.43 2.43 0 0 0 2.26-2.26Z"/><path d="M17 21.5a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0Z"/></Icon>, labelKey: 'sidebar_worship' },
             ...sharedNavBottom
         ];
    }
};


export const Sidebar: React.FC<SidebarProps> = ({ currentPage, navigateTo, userRole, currentUser, handleLogout, isMobileOpen, toggleMobileSidebar }) => {
  const { t } = useLanguage();
  
  const [isCollapsed, setIsCollapsed] = useState(false);
  
  const navItems = getNavItemsForRole(userRole);

  const renderNavSection = (items: typeof navItems) => (
    <>
        {items.map(item => (
             <NavItem
                key={item.page}
                icon={item.icon}
                label={t(item.labelKey as any)}
                isActive={currentPage === item.page || (currentPage === 'course-detail' && item.page === 'courses')}
                onClick={() => navigateTo(item.page as Page)}
                isCollapsed={isCollapsed}
            />
        ))}
    </>
  );

  return (
    <>
      {isMobileOpen && <div onClick={toggleMobileSidebar} className="fixed inset-0 bg-black/60 z-40 sm:hidden"></div>}
       <aside className={`fixed inset-y-0 start-0 z-50 ${isCollapsed ? 'w-20' : 'w-64'} flex-shrink-0 bg-white dark:bg-slate-800 p-4 flex flex-col border-s border-slate-200 dark:border-slate-700 rtl:border-s-0 rtl:border-e transform transition-all duration-300 ease-in-out ${isMobileOpen ? 'translate-x-0' : '-translate-x-full'} sm:relative sm:translate-x-0 sm:bg-white sm:dark:bg-slate-800/50`}>
        <div className="flex items-center justify-between mb-8">
            <div className="flex items-center">
                <div className="p-2 bg-brand-emerald-500 rounded-lg">
                  <Icon className="text-white h-8 w-8">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                  </Icon>
                </div>
                {!isCollapsed && <h1 className="text-2xl font-bold ms-3 text-brand-emerald-700 dark:text-brand-emerald-4">UlumCampus</h1>}
            </div>
             <div className="flex items-center space-x-2">
               <button 
                 onClick={() => setIsCollapsed(!isCollapsed)}
                 className="p-1 text-slate-500 dark:text-slate-400 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 hidden sm:block"
                 title={isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'}
               >
                 <Icon className="w-6 h-6">
                   {isCollapsed ? (
                     <path d="M9 18l6-6-6-6" />
                   ) : (
                     <path d="M15 18l-6-6 6-6" />
                   )}
                 </Icon>
               </button>
             </div>
        </div>

         <nav className="flex-1 overflow-y-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
          <ul className="space-y-1">
            {renderNavSection(navItems)}
          </ul>
        </nav>

        <div className="mt-auto pt-4">
            <div className={`p-4 bg-slate-100 dark:bg-slate-900/50 rounded-lg flex ${isCollapsed ? 'justify-center' : 'items-center'}`}>
               <div className="flex-shrink-0">
                 <div>
                   <img 
                     src={currentUser.avatarUrl} 
                     alt={currentUser.name} 
                     className="w-10 h-10 rounded-full border-2 border-brand-emerald-50 cursor-pointer transition-all duration-30" 
                     onClick={() => navigateTo('profile')}
                     title={isCollapsed ? currentUser.name : undefined}
                   />
                 </div>
               </div>
              {!isCollapsed && (
                <div className="ms-4 flex-1 overflow-hidden">
                  <p onClick={() => navigateTo('profile')} className="font-semibold text-slate-800 dark:text-slate-100 truncate cursor-pointer">{currentUser.name}</p>
                  <p className="text-sm text-slate-500 dark:text-slate-400 truncate">{currentUser.role}</p>
                </div>
              )}
              {!isCollapsed && (
                <button onClick={handleLogout} className="ms-2 p-2 rounded-full text-slate-50 dark:text-slate-400 opacity-0 group-hover:opacity-100 hover:bg-slate-200 dark:hover:bg-slate-700 transition-opacity" title={t('settings_account_logout')}>
                  <Icon className="w-5 h-5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></Icon>
                </button>
              )}
           </div>
        </div>
      </aside>
    </>
  );
};
