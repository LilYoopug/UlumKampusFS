import React, { useState, useEffect, useCallback } from 'react';
import { LanguageProvider } from './contexts/LanguageContext';
import { useDarkMode } from './hooks/useDarkMode';
import { Page, User, Course, Assignment, LibraryResource, Announcement, NotificationLink, UserRole, AnnouncementCategory } from './types';
import { mapBackendRoleToFrontend } from './utils/roleMapper';
import {
  ALL_USERS,
  COURSES_DATA,
  ASSIGNMENTS,
  ANNOUNCEMENTS_DATA,
  INITIAL_ELIBRARY_RESOURCES,
  NOTIFICATIONS_DATA,
  DISCUSSION_THREADS,
  ACADEMIC_CALENDAR_EVENTS,
  FACULTIES,
  USER_PASSWORDS
} from './constants';

// Import all components
import { Sidebar } from './components/Sidebar';
import { Header } from './components/Header';
import { Dashboard } from './components/Dashboard';
import { DosenDashboard } from './components/DosenDashboard';
import { ProdiDashboard } from './components/ProdiDashboard';
import { ManajemenDashboard } from './components/ManajemenDashboard';
import { SuperAdminDashboard } from './components/SuperAdminDashboard';
import { CourseCatalog } from './components/CourseCatalog';
import { CourseDetail } from './components/CourseDetail';
import { CreateCourse } from './components/CreateCourse';
import { Grades } from './components/Grades';
import { Gradebook } from './components/Gradebook';
import { Assignments as AssignmentsPage } from './components/Assignments';
import { VideoLectures } from './components/VideoLectures';
import { ELibrary } from './components/ELibrary';
import { ManageELibrary } from './components/ManageELibrary';
import { Profile } from './components/Profile';
import { Settings } from './components/Settings';
import { Worship } from './components/Worship';
import { Help } from './components/Help';
import { Calendar } from './components/Calendar';
import { Notifications as NotificationsPage } from './components/Notifications';
import { AnnouncementsPage as AllAnnouncementsPage } from './components/AnnouncementsPage';
import { ProdiCoursesPage } from './components/ProdiCoursesPage';
import { ProdiStudentsPage } from './components/ProdiStudentsPage';
import { ProdiLecturersPage } from './components/ProdiLecturersPage';
import { ManagementCoursesPage } from './components/ManagementCoursesPage';
import { UstadzAI } from './components/UstadzAI';
import { Homepage } from './components/Homepage';
import { Login } from './components/Login';
import { Register } from './components/Register';
import { PublicCourseCatalog } from './components/PublicCourseCatalog';
import { AdministrasiPage } from './components/AdministrasiPage';
import { ManagementAdministrationPage } from './components/ManagementAdministrationPage';
import { StudentRegistrationPage } from './components/StudentRegistrationPage';

import { RegistrasiPage } from './components/RegistrasiPage';
import { PageNotFound } from './components/PageNotFound';
 import { RolePageNotAvailable } from './components/RolePageNotAvailable';
 import { UserManagementPage } from './components/UserManagementPage';
 import { ManajemenFakultasPage } from './components/ManajemenFakultasPage';

// FIX: Export handleNavClick for use in landing page components.
export const handleNavClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
    e.preventDefault();
    const href = e.currentTarget.getAttribute('href');
    if (!href) return;
    
    if (href === '#') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    const targetId = href.substring(1);
    if (targetId) {
        const targetElement = document.getElementById(targetId);
        if (targetElement) {
            targetElement.scrollIntoView({ behavior: 'smooth' });
        }
    }
};

type AppView = Page | 'home' | 'login' | 'register' | 'public-catalog';

function App() {
    const { isDarkMode, toggleDarkMode } = useDarkMode();

    const [view, setView] = useState<AppView>('home');
    const [viewParams, setViewParams] = useState<any>(null);
    const [currentUser, setCurrentUser] = useState<User | null>(null);
    const [isMobileSidebarOpen, setIsMobileSidebarOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Data states
    const [users, setUsers] = useState<User[]>(ALL_USERS);
    const [courses, setCourses] = useState<Course[]>(COURSES_DATA);
    const [assignments, setAssignments] = useState<Assignment[]>(ASSIGNMENTS);
    const [elibraryResources, setElibraryResources] = useState<LibraryResource[]>(INITIAL_ELIBRARY_RESOURCES);
    const [myLibrary, setMyLibrary] = useState<string[]>([]);
    const [announcements, setAnnouncements] = useState<Announcement[]>(ANNOUNCEMENTS_DATA);
    const [notifications, setNotifications] = useState<any[]>(NOTIFICATIONS_DATA);
    const [discussionThreads, setDiscussionThreads] = useState<any[]>(DISCUSSION_THREADS);
    const [calendarEvents, setCalendarEvents] = useState<any[]>(ACADEMIC_CALENDAR_EVENTS);
    const [faculties, setFaculties] = useState<any[]>(FACULTIES);

    const toggleMobileSidebar = () => setIsMobileSidebarOpen(prev => !prev);

    const navigateTo = useCallback((page: AppView, params: any = null) => {
        setView(page);
        setViewParams(params);
        window.scrollTo(0, 0);
        setIsMobileSidebarOpen(false);
    }, []);

    const fetchData = async () => {
        if (!currentUser) return;
        setIsLoading(true);
        setError(null);
        try {
            // Use mock data from constants instead of API calls
            setUsers(ALL_USERS);
            setCourses(COURSES_DATA);
            setAssignments(ASSIGNMENTS);
            setAnnouncements(ANNOUNCEMENTS_DATA);
            setElibraryResources(INITIAL_ELIBRARY_RESOURCES);
            setNotifications(NOTIFICATIONS_DATA);
            setDiscussionThreads(DISCUSSION_THREADS);
            setCalendarEvents(ACADEMIC_CALENDAR_EVENTS);
            setFaculties(FACULTIES);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Failed to load data');
        } finally {
            setIsLoading(false);
        }
    };

    const handleLogin = async (email: string, password: string) => {
        setIsLoading(true);
        setError(null);
        try {
            // Mock authentication - find user by email and validate password
            const user = ALL_USERS.find(u => u.email === email);
            const expectedPassword = USER_PASSWORDS[email];

             if (user && expectedPassword && password === expectedPassword) {
                 localStorage.setItem('auth_token', 'mock-token-' + Date.now());
                 localStorage.setItem('current_user_id', user.studentId);
                 setCurrentUser(user);
                 await fetchData();
                 setView('dashboard');
             } else {
                 throw new Error('Invalid credentials');
             }
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Login failed');
            alert('Login failed: ' + (err instanceof Error ? err.message : 'Unknown error'));
        } finally {
            setIsLoading(false);
        }
    };
    
    const handleLogout = async () => {
         setCurrentUser(null);
         setUsers([]);
         setCourses([]);
         setAssignments([]);
         setAnnouncements([]);
         setElibraryResources([]);
         setNotifications([]);
         setDiscussionThreads([]);
         setCalendarEvents([]);
         setFaculties([]);
         setView('home');
         setIsMobileSidebarOpen(false);
         localStorage.removeItem('auth_token');
         localStorage.removeItem('current_user_id');
    };

    const handleSetCurrentUser = (user: User) => {
        setCurrentUser(user);
        setView('dashboard');
    }
    
    // Data modification functions
    const handleSaveCourse = (courseData: Course) => {
        setCourses(prev => {
            const exists = prev.some(c => c.id === courseData.id);
            if (exists) {
                return prev.map(c => (c.id === courseData.id ? { ...c, ...courseData } : c));
            }
            const newCourse = { ...courseData };
            if (currentUser) {
                newCourse.instructor = currentUser.name;
                newCourse.instructorAvatarUrl = currentUser.avatarUrl;
            }
            return [...prev, newCourse];
        });
        navigateTo('courses');
    };
    
    const handleUpdateAssignment = (updatedAssignment: Assignment) => {
        setAssignments(prev => prev.map(a => a.id === updatedAssignment.id ? updatedAssignment : a));
    };

    const handleCreateAssignment = (newAssignmentData: Omit<Assignment, 'id' | 'submissions'>) => {
        const newAssignment: Assignment = {
            ...newAssignmentData,
            id: `ASG-${Date.now()}`,
            submissions: [],
        };
        setAssignments(prev => [newAssignment, ...prev]);
    };
    
    const handleUpdateUser = (updatedUser: User) => {
        setUsers(prev => prev.map(u => u.studentId === updatedUser.studentId ? updatedUser : u));
        if (currentUser && currentUser.studentId === updatedUser.studentId) {
            setCurrentUser(updatedUser);
        }
    };


    const myCourses = currentUser ? courses.filter(c => c.instructor === currentUser.name) : [];

    const publicViews: AppView[] = ['home', 'login', 'register', 'public-catalog'];

    useEffect(() => {
        if (!currentUser && !publicViews.includes(view)) {
            navigateTo('login');
        }
    }, [view, currentUser, navigateTo]);

    // Redirect to appropriate dashboard based on user role after login or refresh
    useEffect(() => {
        if (currentUser && view === 'dashboard') {
            // The dashboard component already handles role-based rendering
            // This ensures that even if someone manually navigates to /dashboard,
            // they see the appropriate dashboard for their role
        }
    }, [currentUser, view]);

    useEffect(() => {
        const token = localStorage.getItem('auth_token');
        const storedUserId = localStorage.getItem('current_user_id');
        if (token && !currentUser) {
            const fetchCurrentUser = async () => {
                try {
                    // Mock current user retrieval from token and stored user ID
                    let mockUser = null;
                    if (storedUserId) {
                        // Find the user by the stored ID
                        mockUser = ALL_USERS.find(u => u.studentId === storedUserId);
                    } else {
                        // Fallback to first user if no stored ID
                        mockUser = ALL_USERS[0];
                    }
                    if (mockUser) {
                        setCurrentUser(mockUser);
                        await fetchData();
                    }
                } catch (error) {
                    // If token is invalid, remove it
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('current_user_id');
                }
            };
            fetchCurrentUser();
        }
    }, []);

    const renderLoggedInContent = () => {
        if (!currentUser) return null;
        
        switch (view) {
            case 'dashboard':
                switch (currentUser.role) {
                    case 'Mahasiswa': return <Dashboard currentUser={currentUser} courses={courses} onSelectCourse={(course) => navigateTo('course-detail', { course })} navigateTo={navigateTo} announcements={announcements} />;
                    case 'Dosen': return <DosenDashboard onSelectCourse={(course) => navigateTo('course-detail', { course })} courses={courses} currentUser={currentUser} />;
                    case 'Prodi Admin': return <ProdiDashboard currentUser={currentUser} courses={courses} users={users} />;
                    case 'Manajemen Kampus': return <ManajemenDashboard faculties={faculties} currentUser={currentUser} users={users} navigateTo={navigateTo} />;
                    case 'Super Admin': return <SuperAdminDashboard users={users} onCreateUser={async (user) => { 
                setUsers(prev => [...prev, user]); 
                fetchData(); 
            }} onUpdateUser={async (user) => { 
                setUsers(prev => prev.map(u => u.studentId === user.studentId ? user : u)); 
                fetchData(); 
            }} onDeleteUser={async (id) => { 
                setUsers(prev => prev.filter(u => u.studentId !== id)); 
                fetchData(); 
            }} />;
                    default: return <RolePageNotAvailable />;
                }
            case 'calendar': return <Calendar courses={courses} currentUser={currentUser} assignments={assignments} />;
            case 'courses': return <CourseCatalog courses={courses} currentUser={currentUser} onSelectCourse={(course) => navigateTo('course-detail', { course })} onEditCourse={(course) => navigateTo('edit-course', { course })} navigateTo={navigateTo} />;
            case 'course-detail': 
                const courseToDisplay = viewParams.course || courses.find(c => c.id === viewParams.courseId);
                return <CourseDetail course={courseToDisplay} onBack={() => navigateTo(viewParams?.from || 'courses')} initialParams={viewParams} currentUser={currentUser} assignments={assignments} onCreateAssignment={handleCreateAssignment} onUpdateAssignment={handleUpdateAssignment} />;
            case 'create-course': return <CreateCourse onSave={handleSaveCourse} onCancel={() => navigateTo('courses')} />;
            case 'edit-course': return <CreateCourse onSave={handleSaveCourse} onCancel={() => navigateTo('courses')} initialData={viewParams.course} />;
            case 'grades': return <Grades courses={courses} currentUser={currentUser} />;
            case 'gradebook': return <Gradebook courses={courses} assignments={assignments} currentUser={currentUser} users={users} onUpdateUser={handleUpdateUser} onSelectAssignment={(assignment) => navigateTo('course-detail', { course: courses.find(c => c.id === assignment.courseId), initialTab: 'assignments' })} />;
            case 'assignments': return <AssignmentsPage courses={courses} currentUser={currentUser} onSelectAssignment={(assignment) => navigateTo('course-detail', { course: courses.find(c => c.id === assignment.courseId), initialTab: 'assignments' })} />;
            case 'video-lectures': return <VideoLectures courses={courses} currentUser={currentUser} onSelectCourse={(course) => navigateTo('course-detail', { course })} />;
            case 'elibrary': return <ELibrary resources={elibraryResources} myLibrary={myLibrary} onToggleLibrary={(id) => setMyLibrary(p => p.includes(id) ? p.filter(i => i !== id) : [...p, id])} />;
            case 'manage-elibrary': return <ManageELibrary resources={elibraryResources} onCreate={async (data) => { 
                setElibraryResources(prev => [...prev, data]); 
                fetchData(); 
            }} onUpdate={async (data) => { 
                setElibraryResources(prev => prev.map(r => r.id === data.id ? data : r)); 
                fetchData(); 
            }} onDelete={async (id) => { 
                setElibraryResources(prev => prev.filter(r => r.id !== id)); 
                fetchData(); 
            }} />;
            case 'profile': return <Profile courses={courses} currentUser={currentUser} navigateTo={navigateTo} />;
            case 'settings': return <Settings isDarkMode={isDarkMode} toggleDarkMode={toggleDarkMode} currentUser={currentUser} />;
            case 'worship': return <Worship />;
            case 'help': return <Help currentUser={currentUser} />;
            case 'notifications': return <NotificationsPage onNotificationClick={(link: NotificationLink) => navigateTo(link.page, link.params)} notifications={notifications} onMarkAsRead={async (id) => { setNotifications(prev => prev.map(n => n.id === id ? {...n, isRead: true} : n)); }} />;
            case 'announcements': return <AllAnnouncementsPage initialAnnouncementId={viewParams?.announcementId} currentUser={currentUser} announcements={announcements} />;
case 'prodi-courses': 
    if (currentUser?.role === 'Manajemen Kampus') {
        return <ManagementCoursesPage courses={courses} />;
    } else {
        return <ProdiCoursesPage courses={courses} onCreateCourse={async (course) => { setCourses(prev => [...prev, course]); }} onUpdateCourse={async (course) => { setCourses(prev => prev.map(c => c.id === course.id ? course : c)); }} onDeleteCourse={async (id) => { setCourses(prev => prev.filter(c => c.id !== id)); }} />;
    }
            case 'prodi-students': return <ProdiStudentsPage users={users} />;
            case 'prodi-lecturers': return <ProdiLecturersPage users={users} courses={courses} />;
            case 'registrasi': return <RegistrasiPage />;
            case 'administrasi': return <AdministrasiPage currentUser={currentUser} />;
 case 'management-administration': return <ManagementAdministrationPage currentUser={currentUser} />;
            case 'student-registration': return <StudentRegistrationPage currentUser={currentUser} />;

case 'user-management': return <UserManagementPage users={users} onCreateUser={async (user) => { 
                  setUsers(prev => [...prev, user]); 
                  fetchData(); 
              }} onUpdateUser={async (user) => { 
                  setUsers(prev => prev.map(u => u.studentId === user.studentId ? user : u)); 
                  fetchData(); 
              }} onDeleteUser={async (id) => { 
                  setUsers(prev => prev.filter(u => u.studentId !== id)); 
                  fetchData(); 
              }} />;
              case 'manajemen-fakultas': return <ManajemenFakultasPage faculties={faculties} setFaculties={setFaculties} users={users} />;
              default: return <PageNotFound />;
        }
    };
    
    const renderApp = () => {
        // Logged-out views
        if (!currentUser) {
            switch (view) {
                case 'home':
                    return <Homepage onNavigateToLogin={() => navigateTo('login')} onNavigateToRegister={() => navigateTo('register')} onNavigateToCatalog={() => navigateTo('public-catalog')} />;
                case 'login':
                    return <Login onLogin={handleLogin} onNavigateToRegister={() => navigateTo('register')} onBack={() => navigateTo('home')} />;
                case 'register':
                    return <Register onRegister={async (data) => {
                        try {
                            // Mock registration - add user to mock data
                            const newUser = {
                                ...data,
                                studentId: 'UC' + Date.now().toString().slice(-5),
                                avatarUrl: 'https://picsum.photos/seed/' + data.name.split(' ')[0].toLowerCase() + '/100/100',
                                role: 'Mahasiswa',
                                joinDate: new Date().toISOString().split('T')[0],
                                bio: '',
                                studentStatus: 'Aktif',
                                gpa: 0,
                                totalSks: 0
                            };
                             setUsers(prev => [...prev, newUser]);
                             // Automatically log in the new user
                             localStorage.setItem('auth_token', 'mock-token-' + Date.now());
                             localStorage.setItem('current_user_id', newUser.studentId);
                             setCurrentUser(newUser);
                             await fetchData();
                             setView('dashboard');
                        } catch (error) {
                            alert('Registration failed: ' + (error instanceof Error ? error.message : 'Unknown error'));
                        }
                    }} onNavigateToLogin={() => navigateTo('login')} onBack={() => navigateTo('home')} />;
                case 'public-catalog':
                    return <PublicCourseCatalog courses={courses.filter(c => c.status === 'Published')} onBack={() => navigateTo('home')} onNavigateToLogin={() => navigateTo('login')} onSelectCourse={() => navigateTo('login')} />;
                default:
                    // This is for protected pages, handled by the useEffect redirect
                    return null;
            }
        }

        // Logged-in view
        return (
            <div className="flex h-screen bg-slate-100 dark:bg-slate-900 font-sans">
                <Sidebar
                    key={currentUser.role}
                    currentPage={view as Page}
                    navigateTo={navigateTo}
                    userRole={currentUser.role as UserRole}
                    currentUser={currentUser}
                    handleLogout={handleLogout}
                    isMobileOpen={isMobileSidebarOpen}
                    toggleMobileSidebar={toggleMobileSidebar}
                />
                <div className="flex-1 flex flex-col overflow-hidden">
                    <Header
                        toggleDarkMode={toggleDarkMode}
                        isDarkMode={isDarkMode}
                        currentUser={currentUser}
                        setCurrentUser={handleSetCurrentUser}
                        navigateTo={navigateTo}
                        handleNotificationClick={(link) => navigateTo(link.page, link.params)}
                        onToggleMobileSidebar={toggleMobileSidebar}
                        notifications={notifications}
                        onMarkNotificationAsRead={async (id) => { setNotifications(prev => prev.map(n => n.id === id ? {...n, isRead: true} : n)); }}
                        onMarkAllNotificationsAsRead={async () => { setNotifications(prev => prev.map(n => ({...n, isRead: true}))); }}
                    />
                    <main className="flex-1 overflow-x-hidden overflow-y-auto p-6 sm:p-8">
                        {renderLoggedInContent()}
                    </main>
                </div>
                {currentUser.role === 'Mahasiswa' && <UstadzAI />}
            </div>
        );
    }
    
    return (
        <LanguageProvider>
            {renderApp()}
        </LanguageProvider>
    );
}

export default App;
