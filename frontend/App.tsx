import React, { useState, useEffect, useCallback } from 'react';
import { LanguageProvider } from './contexts/LanguageContext';
import { useDarkMode } from './hooks/useDarkMode';
import { Page, User, Course, Assignment, LibraryResource, Announcement, NotificationLink, UserRole, AnnouncementCategory } from './types';
import { mapBackendRoleToFrontend } from './utils/roleMapper';
import { apiService, studentAPI, discussionThreadAPI, facultyAPI } from './services/apiService';

// Import all components using new feature-based barrel files
import { Sidebar, Header } from './src/features/dashboard';
import { Dashboard } from './src/features/dashboard';
import { DosenDashboard } from './src/features/dosen';
import { ProdiDashboard } from './src/features/prodi';
import { ManajemenDashboard } from './src/features/management';
import { SuperAdminDashboard } from './src/features/superadmin';
import { CourseCatalog, CourseDetail, CreateCourse } from './src/features/courses';
import { Grades, Gradebook } from './src/features/grades';
import { Assignments as AssignmentsPage } from './src/features/assignments';
import { VideoLectures } from './src/features/resources';
import { ELibrary, ManageELibrary } from './src/features/resources';
import { Profile } from './src/features/auth';
import { Settings } from './src/ui';
import { Worship } from './src/features/resources';
import { Help } from './src/ui';
import { Calendar } from './src/features/calendar';
import { Notifications as NotificationsPage } from './src/ui';
import { AnnouncementsPage as AllAnnouncementsPage } from './src/features/landing';
import { ProdiCoursesPage, ProdiStudentsPage, ProdiLecturersPage } from './src/features/prodi';
import { ManagementCoursesPage } from './src/features/management';
import { UstadzAI } from './src/features/resources';
import { Homepage } from './src/features/landing';
import { Login, Register } from './src/features/auth';
import { PublicCourseCatalog } from './src/features/courses';
import { AdministrasiPage } from './src/features/administration';
import { ManagementAdministrationPage } from './src/features/management';
import { StudentRegistrationPage } from './src/features/administration';

import { RegistrasiPage } from './src/features/administration';
import { PageNotFound } from './src/ui';
 import { RolePageNotAvailable } from './src/ui';
 import { UserManagementPage } from './src/features/management';
 import { ManajemenFakultasPage } from './src/features/management';

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

    // Data states - initialized with empty arrays, data will be fetched from API
    const [users, setUsers] = useState<User[]>([]);
    const [courses, setCourses] = useState<Course[]>([]);
    const [assignments, setAssignments] = useState<Assignment[]>([]);
    const [elibraryResources, setElibraryResources] = useState<LibraryResource[]>([]);
    const [myLibrary, setMyLibrary] = useState<string[]>([]);
    const [announcements, setAnnouncements] = useState<Announcement[]>([]);
    const [notifications, setNotifications] = useState<any[]>([]);
    const [discussionThreads, setDiscussionThreads] = useState<any[]>([]);
    const [calendarEvents, setCalendarEvents] = useState<any[]>([]);
    const [faculties, setFaculties] = useState<any[]>([]);

    const toggleMobileSidebar = () => setIsMobileSidebarOpen(prev => !prev);

    const navigateTo = useCallback((page: AppView, params: any = null) => {
        setView(page);
        setViewParams(params);
        window.scrollTo(0, 0);
        setIsMobileSidebarOpen(false);
    }, []);

    const fetchData = async (userRole?: string) => {
        if (!currentUser && !userRole) return;
        setIsLoading(true);
        setError(null);
        const role = userRole || currentUser?.role;
        try {
            // Use API service to fetch real data from backend
            // For students, fetch courses with progress from student API
            const coursesPromise = role === 'Mahasiswa' 
                ? studentAPI.getAllCoursesWithProgress()
                : apiService.getCourses();

            const [usersData, coursesData, assignmentsData, announcementsData, elibraryData, notificationsData, calendarData, discussionsData, facultiesData] = await Promise.all([
                apiService.getUsers(),
                coursesPromise,
                apiService.getAssignments(),
                apiService.getAnnouncements(),
                apiService.getLibraryResources(),
                apiService.getNotifications(),
                apiService.getAcademicCalendarEvents(),
                discussionThreadAPI.getAll(),
                facultyAPI.getAll()
            ]);
            
            const usersList = usersData.data || usersData;
            setUsers(usersList);
            setCourses(coursesData.data || coursesData);
            setAssignments(assignmentsData.data || assignmentsData);
            setAnnouncements(announcementsData.data || announcementsData);
            setElibraryResources(elibraryData.data || elibraryData);
            setNotifications(notificationsData.data || notificationsData);
            setCalendarEvents(calendarData.data || calendarData);
            setDiscussionThreads(discussionsData.data || discussionsData);
            setFaculties(facultiesData.data || facultiesData);
        } catch (err) {
            console.error('Failed to fetch data:', err);
            setError(err instanceof Error ? err.message : 'Failed to load data');
        } finally {
            setIsLoading(false);
        }
    };

    const handleLogin = async (email: string, password: string) => {
        setIsLoading(true);
        setError(null);
        try {
            // Try to authenticate via API first
            const response = await apiService.login(email, password);
            
            if (response.data.user && response.data.token) {
                localStorage.setItem('auth_token', response.data.token);
                localStorage.setItem('current_user_id', response.data.user.studentId || response.data.user.id || '');
                setCurrentUser(response.data.user);
                await fetchData(response.data.user.role);
                
                // MABA users go to registration page, others go to dashboard
                if (response.data.user.role === 'MABA') {
                    setView('registrasi');
                } else {
                    setView('dashboard');
                }
            } else {
                throw new Error('Invalid response from server');
            }
        } catch (err) {
            console.error('API login failed:', err);
            setError(err instanceof Error ? err.message : 'Login failed');
            alert('Login failed: ' + (err instanceof Error ? err.message : 'Unknown error'));
        } finally {
            setIsLoading(false);
        }
    };
    
    const handleLogout = async () => {
         try {
             await apiService.logout();
         } catch (err) {
             console.error('Logout error:', err);
         }
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
        
        // MABA users go to registration page, others go to dashboard
        if (user.role === 'MABA') {
            setView('registrasi');
        } else {
            setView('dashboard');
        }
    }
    
    // Data modification functions
    const handleSaveCourse = async (courseData: Course) => {
        try {
            const exists = courses.some(c => c.id === courseData.id);
            let savedCourse;
            
            if (exists) {
                savedCourse = await apiService.updateCourse(courseData.id, courseData);
                setCourses(prev => prev.map(c => (c.id === courseData.id ? savedCourse : c)));
            } else {
                savedCourse = await apiService.createCourse(courseData);
                setCourses(prev => [...prev, savedCourse]);
            }
            navigateTo('courses');
        } catch (err) {
            console.error('Failed to save course:', err);
            alert('Failed to save course: ' + (err instanceof Error ? err.message : 'Unknown error'));
        }
    };
    
    const handleUpdateAssignment = async (updatedAssignment: Assignment) => {
        try {
            await apiService.updateAssignment(updatedAssignment.id, updatedAssignment);
            setAssignments(prev => prev.map(a => a.id === updatedAssignment.id ? updatedAssignment : a));
        } catch (err) {
            console.error('Failed to update assignment:', err);
            alert('Failed to update assignment: ' + (err instanceof Error ? err.message : 'Unknown error'));
        }
    };

    const handleCreateAssignment = async (newAssignmentData: Omit<Assignment, 'id' | 'submissions'>) => {
        try {
            const newAssignment = await apiService.createAssignment(newAssignmentData);
            setAssignments(prev => [newAssignment, ...prev]);
        } catch (err) {
            console.error('Failed to create assignment:', err);
            alert('Failed to create assignment: ' + (err instanceof Error ? err.message : 'Unknown error'));
        }
    };
    
    const handleUpdateUser = async (updatedUser: User) => {
        try {
            // Always use the database id for updates, not studentId
            // This ensures the backend validation ignore() rule works correctly
            const userId = updatedUser.id || '';
            
            if (!userId) {
                console.error('User ID is missing from:', updatedUser);
                throw new Error('User ID is required for updates. Backend requires database id field, not studentId.');
            }
            
            await apiService.updateUser(userId, updatedUser);
            setUsers(prev => prev.map(u => u.studentId === updatedUser.studentId ? updatedUser : u));
            if (currentUser && currentUser.studentId === updatedUser.studentId) {
                setCurrentUser(updatedUser);
            }
        } catch (err) {
            console.error('Failed to update user:', err);
            alert('Failed to update user: ' + (err instanceof Error ? err.message : 'Unknown error'));
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

    // Fetch library resources when accessing manage-elibrary page
    useEffect(() => {
        if (currentUser && view === 'manage-elibrary') {
            const fetchLibraryResources = async () => {
                try {
                    const response = await apiService.getLibraryResources();
                    setElibraryResources(response.data);
                } catch (err) {
                    console.error('Failed to fetch library resources:', err);
                }
            };
            fetchLibraryResources();
        }
    }, [view, currentUser]);

    useEffect(() => {
        const token = localStorage.getItem('auth_token');
        if (token && !currentUser) {
            const fetchCurrentUser = async () => {
                try {
                    // Try to fetch current user from backend using the token
                    const response = await apiService.getCurrentUser();
                    if (response.data) {
                        setCurrentUser(response.data);
                        localStorage.setItem('current_user_id', response.data.id || response.data.studentId || '');
                        await fetchData(response.data.role);
                        
                        // Redirect based on role after fetching user data
                        if (response.data.role === 'MABA') {
                            setView('registrasi');
                        } else {
                            setView('dashboard');
                        }
                    }
                } catch (error) {
                    console.error('Failed to fetch current user:', error);
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
                    case 'Manajemen Kampus': return <ManajemenDashboard currentUser={currentUser} navigateTo={navigateTo} />;
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
            case 'courses': return <CourseCatalog currentUser={currentUser} onSelectCourse={(course) => navigateTo('course-detail', { course })} onEditCourse={(course) => navigateTo('edit-course', { course })} navigateTo={navigateTo} />;
            case 'course-detail': 
                const courseToDisplay = viewParams.course || courses.find(c => c.id === viewParams.courseId);
                return <CourseDetail course={courseToDisplay} onBack={() => navigateTo(viewParams?.from || 'courses')} initialParams={viewParams} currentUser={currentUser} assignments={assignments} onCreateAssignment={handleCreateAssignment} onUpdateAssignment={handleUpdateAssignment} />;
            case 'create-course': return <CreateCourse onSave={handleSaveCourse} onCancel={() => navigateTo('courses')} />;
            case 'edit-course': return <CreateCourse onSave={handleSaveCourse} onCancel={() => navigateTo('courses')} initialData={viewParams.course} />;
            case 'grades': return <Grades courses={courses} currentUser={currentUser} initialCourseId={viewParams?.courseId} />;
            case 'gradebook': return <Gradebook currentUser={currentUser} users={users} onUpdateUser={handleUpdateUser} onSelectAssignment={(assignment) => navigateTo('course-detail', { course: courses.find(c => c.id === assignment.courseId), initialTab: 'assignments' })} />;
            case 'assignments': return <AssignmentsPage courses={courses} currentUser={currentUser} onSelectAssignment={(assignment) => navigateTo('course-detail', { course: courses.find(c => c.id === assignment.courseId), initialTab: 'assignments' })} initialAssignmentId={viewParams?.assignmentId} />;
            case 'video-lectures': return <VideoLectures courses={courses} currentUser={currentUser} onSelectCourse={(course) => navigateTo('course-detail', { course })} />;
            case 'elibrary': return <ELibrary resources={elibraryResources} myLibrary={myLibrary} onToggleLibrary={(id) => setMyLibrary(p => p.includes(id) ? p.filter(i => i !== id) : [...p, id])} />;
            case 'manage-elibrary': return <ManageELibrary resources={elibraryResources} onCreate={async (data) => { 
                try {
                    const newResource = await apiService.createLibraryResource(data);
                    setElibraryResources(prev => [...prev, newResource]); 
                } catch (err) {
                    console.error('Failed to create resource:', err);
                    alert('Failed to create resource');
                }
            }} onUpdate={async (data) => { 
                try {
                    await apiService.updateLibraryResource(data.id, data);
                    setElibraryResources(prev => prev.map(r => r.id === data.id ? data : r)); 
                } catch (err) {
                    console.error('Failed to update resource:', err);
                    alert('Failed to update resource');
                }
            }} onDelete={async (id) => { 
                try {
                    await apiService.deleteLibraryResource(id);
                    setElibraryResources(prev => prev.filter(r => r.id !== id)); 
                } catch (err) {
                    console.error('Failed to delete resource:', err);
                    alert('Failed to delete resource');
                }
            }} />;
            case 'profile': return <Profile courses={courses} currentUser={currentUser} navigateTo={navigateTo} />;
            case 'settings': return <Settings isDarkMode={isDarkMode} toggleDarkMode={toggleDarkMode} currentUser={currentUser} />;
            case 'worship': return <Worship />;
            case 'help': return <Help currentUser={currentUser} />;
            case 'notifications': return <NotificationsPage onNotificationClick={(link: NotificationLink) => navigateTo(link.page, link.params)} notifications={notifications} onMarkAsRead={async (id) => { 
                try {
                    await apiService.markNotificationAsRead(id);
                    setNotifications(prev => prev.map(n => n.id === id ? {...n, isRead: true} : n)); 
                } catch (err) {
                    console.error('Failed to mark notification as read:', err);
                }
            }} initialNotificationId={viewParams?.notificationId} />;
            case 'announcements': return <AllAnnouncementsPage initialAnnouncementId={viewParams?.announcementId} currentUser={currentUser} announcements={announcements} />;
case 'prodi-courses': 
    if (currentUser?.role === 'Manajemen Kampus') {
        return <ManagementCoursesPage />;
    } else {
        return <ProdiCoursesPage />;
    }
            case 'prodi-students': return <ProdiStudentsPage />;
            case 'prodi-lecturers': return <ProdiLecturersPage />;
            case 'registrasi': return <RegistrasiPage />;
            case 'administrasi': return <AdministrasiPage currentUser={currentUser} />;
 case 'management-administration': return <ManagementAdministrationPage currentUser={currentUser} />;
            case 'student-registration': return <StudentRegistrationPage currentUser={currentUser} />;

            case 'user-management': return <UserManagementPage />;
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
                        // Try backend API registration
                        const response = await apiService.register({
                            name: data.name,
                            email: data.email,
                            phone_number: data.phoneNumber, // Frontend uses phone_number
                            password: data.password,
                            password_confirmation: data.password_confirmation
                        });

                        if (response.data.user && response.data.token) {
                            localStorage.setItem('auth_token', response.data.token);
                            localStorage.setItem('current_user_id', response.data.user.id || response.data.user.studentId || '');
                            setCurrentUser(response.data.user);
                            await fetchData(response.data.user.role);
                            setView('dashboard');
                        } else {
                            throw new Error('Invalid response from server');
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
