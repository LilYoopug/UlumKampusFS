# Frontend Files Inventory

This document provides a complete inventory of all files in the UlumCampus frontend directory with categorization based on file types and purposes.

## 1. Components

All UI components organized in various directories:

### Main Components Directory
- `/components/AboutSection.tsx` - About section component for the homepage
- `/components/AdministrasiPage.tsx` - Administrative and payment management page
- `/components/AnimatedSection.tsx` - Animated section component for UI effects
- `/components/AnnouncementsPage.tsx` - Page for viewing all announcements
- `/components/AssignmentCard.tsx` - Individual assignment display card
- `/components/AssignmentDetailView.tsx` - Detailed view for assignments
- `/components/AssignmentForm.tsx` - Form component for creating assignments
- `/components/Assignments.tsx` - Main assignments page component
- `/components/Calendar.tsx` - Academic calendar component
- `/components/ContactSection.tsx` - Contact section for the homepage
- `/components/CourseAssignmentsTab.tsx` - Tab for course assignments in course detail
- `/components/CourseCard.tsx` - Course display card component
- `/components/CourseCatalog.tsx` - Course catalog page component
- `/components/CourseDetail.tsx` - Course detail page component
- `/components/CourseForm.tsx` - Form for creating/editing courses
- `/components/CreateCourse.tsx` - Page for creating a new course
- `/components/Dashboard.tsx` - Main dashboard component
- `/components/DiscussionForum.tsx` - Discussion forum component
- `/components/DoaReader.tsx` - Islamic prayers reader component
- `/components/DosenDashboard.tsx` - Lecturer-specific dashboard
- `/components/Dropdown.tsx` - Dropdown UI component
- `/components/ELibrary.tsx` - Islamic e-library component
- `/components/FaqContent.tsx` - FAQ content section for help page
- `/components/FaqSection.tsx` - FAQ section for the homepage
- `/components/Gradebook.tsx` - Gradebook component for lecturers
- `/components/Grades.tsx` - Student grades and certificates page
- `/components/HadithReader.tsx` - Hadith reader component
- `/components/HafalanRecorder.tsx` - Memorization recorder component
- `/components/Header.tsx` - Header navigation component
- `/components/Help.tsx` - Help center component
- `/components/Homepage.tsx` - Main homepage component
- `/components/Icon.tsx` - Icon component
- `/components/IslamicResources.tsx` - Islamic resources section
- `/components/LandingFooter.tsx` - Landing page footer component
- `/components/LandingHeader.tsx` - Landing page header component
- `/components/LandingLayout.tsx` - Landing page layout component
- `/components/LanguageSwitcher.tsx` - Language switching UI component
- `/components/Login.tsx` - Login page component
- `/components/ManageELibrary.tsx` - E-library management component
- `/components/ManagementAdministrationPage.tsx` - Management administration page
- `/components/ManagementCoursesPage.tsx` - Course management page
- `/components/ManajemenDashboard.tsx` - Management dashboard component
- `/components/ManajemenFakultasPage.tsx` - Faculty management page
- `/components/ModuleManagement.tsx` - Module management component
- `/components/Notifications.tsx` - Notifications component
- `/components/PageNotFound.tsx` - 404 page component
- `/components/PrayerTimes.tsx` - Prayer times component
- `/components/ProdiCourseForm.tsx` - Program study course form
- `/components/ProdiCoursesPage.tsx` - Program study courses management
- `/components/ProdiDashboard.tsx` - Program study dashboard
- `/components/ProdiLecturersPage.tsx` - Program study lecturers management
- `/components/ProdiStudentsPage.tsx` - Program study students management
- `/components/Profile.tsx` - User profile page component
- `/components/PublicCourseCatalog.tsx` - Public course catalog component
- `/components/QuranReader.tsx` - Quran reader component
- `/components/Register.tsx` - Registration page component
- `/components/RegistrasiPage.tsx` - Registration page component
- `/components/ReportBugForm.tsx` - Bug reporting form
- `/components/ResourceForm.tsx` - Resource form for e-library
- `/components/RoleDashboardNotAvailable.tsx` - Role dashboard not available component
- `/components/RolePageNotAvailable.tsx` - Role page not available component
- `/components/RoleSwitcher.tsx` - Role switching component
- `/components/Settings.tsx` - User settings component
- `/components/Sidebar.tsx` - Sidebar navigation component
- `/components/StudentRegistrationPage.tsx` - Student registration form
- `/components/SuperAdminDashboard.tsx` - Super admin dashboard
- `/components/UserForm.tsx` - User form component
- `/components/UserGuideContent.tsx` - User guide content component
- `/components/UserManagementPage.tsx` - User management page
- `/components/UstadzAI.tsx` - AI-based Q&A component (Ustadz AI)
- `/components/VideoLectures.tsx` - Video lectures collection component
- `/components/VideoPlayer.tsx` - Video player component
- `/components/Worship.tsx` - Worship and moral education component

### Shared Components
- `/src/components/shared/LoadingSpinner.tsx` - Loading spinner component

## 2. Mock Data Files

- `/services/mockService.ts` - Mock services that simulate API responses with mock data
- `/constants.tsx` - Constants containing mock data for users, courses, assignments, announcements, library resources, discussion threads, notifications, calendar events, faculties, and payment mock data

## 3. Type Definitions

- `/types.ts` - Comprehensive type definitions for all entities in the application including User, Course, Assignment, Announcement, LibraryResource, DiscussionThread, Notification, Grade, Faculty, Major, CourseModule, and various other interfaces

## 4. Backend-Correlated Files (API Services, Data Fetching, DTOs, Schemas)

### API Services
- `/services/apiService.ts` - Core API service that handles all backend communications using axios with interceptors for authentication and error handling. Includes services for: authentication, users, courses, assignments, announcements, library resources, discussion threads, notifications, grades, calendar events, faculties, majors, dashboard analytics, course modules, and payment.

### Data Fetching Utilities
- `/services/geminiService.ts` - AI-powered service using Google's Gemini API for Islamic Q&A and tajwid analysis
- `/services/mockService.ts` - Mock data service that simulates backend API responses (mentioned above under Mock Data but also serves as a backend-correlation in development)

## 5. Models/Entities that Connect to Backend

The following files define the data structures that directly correspond to backend entities:

- `/types.ts` - Contains all the main model interfaces that map to backend data structures:
  - User model: Defines user properties including authentication, profile, and role information
  - Course model: Defines course properties including syllabus, modules, and progress tracking
  - Assignment model: Defines assignment properties including submissions and grading
  - Announcement model: Defines announcement properties
  - LibraryResource model: Defines library resource properties
  - DiscussionThread model: Defines discussion thread properties
  - Notification model: Defines notification properties
  - Grade model: Defines grade properties
  - Faculty model: Defines faculty properties
  - Major model: Defines major properties
  - CourseModule model: Defines course module properties
  - CalendarEvent model: Defines academic calendar event properties
  - Submission model: Defines assignment submission properties
  - and many more specific models for Quran, Hadith, Doa, etc.

## Project Configuration Files

- `/vite.config.ts` - Vite build configuration
- `/tsconfig.json` - TypeScript configuration
- `/package.json` - Project dependencies and scripts
- `/package-lock.json` - Locked dependency versions
- `/eslint.config.ts` - ESLint configuration
- `/eslint.config.js` - ESLint configuration in JS format
- `/metadata.json` - Additional metadata file
- `.env` - Environment variables
- `.env.example` - Example environment variables
- `.gitignore` - Git ignore rules

## Main Application Files

- `/App.tsx` - Main application component with routing and state management
- `/index.tsx` - Application entry point
- `/index.html` - HTML template
- `/.git` - Git repository information

## Utility and Helper Files

- `/utils/gradeConverter.ts` - Grade conversion utilities
- `/utils/roleMapper.ts` - Role mapping utilities
- `/utils/time.ts` - Time-related utilities
- `/contexts/LanguageContext.tsx` - Language context provider for internationalization
- `/hooks/useDarkMode.ts` - Custom hook for dark mode functionality
- `/hooks/useIntersectionObserver.ts` - Custom hook for intersection observer functionality
- `/translations.ts` - Translation resources for i18n support