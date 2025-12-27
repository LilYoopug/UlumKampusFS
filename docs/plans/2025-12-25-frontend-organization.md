# Frontend Organization Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Reorganize the frontend file structure to improve maintainability, scalability, and developer experience by implementing a feature-based and type-based directory structure.

**Architecture:** Implement a modular architecture with feature-based organization, separating components by functionality and type. Move from a flat structure to a nested, logical structure that groups related functionality together.

**Tech Stack:** React, TypeScript, Vite

---

### Task 1: Create new feature-based directory structure

**Files:**
- Create: `frontend/src/features/auth/components/Login.tsx`
- Create: `frontend/src/features/auth/components/Register.tsx`
- Create: `frontend/src/features/auth/components/Profile.tsx`
- Create: `frontend/src/features/auth/index.ts`
- Create: `frontend/src/features/dashboard/components/Dashboard.tsx`
- Create: `frontend/src/features/dashboard/components/Sidebar.tsx`
- Create: `frontend/src/features/dashboard/components/Header.tsx`
- Create: `frontend/src/features/dashboard/index.ts`
- Create: `frontend/src/features/courses/components/CourseCard.tsx`
- Create: `frontend/src/features/courses/components/CourseDetail.tsx`
- Create: `frontend/src/features/courses/components/CourseForm.tsx`
- Create: `frontend/src/features/courses/components/CourseCatalog.tsx`
- Create: `frontend/src/features/courses/index.ts`
- Create: `frontend/src/features/assignments/components/AssignmentCard.tsx`
- Create: `frontend/src/features/assignments/components/AssignmentForm.tsx`
- Create: `frontend/src/features/assignments/components/AssignmentDetailView.tsx`
- Create: `frontend/src/features/assignments/index.ts`
- Create: `frontend/src/features/grades/components/Gradebook.tsx`
- Create: `frontend/src/features/grades/components/Grades.tsx`
- Create: `frontend/src/features/grades/index.ts`
- Create: `frontend/src/features/calendar/components/Calendar.tsx`
- Create: `frontend/src/features/calendar/index.ts`
- Create: `frontend/src/features/resources/components/QuranReader.tsx`
- Create: `frontend/src/features/resources/components/HadithReader.tsx`
- Create: `frontend/src/features/resources/components/DoaReader.tsx`
- Create: `frontend/src/features/resources/components/VideoLectures.tsx`
- Create: `frontend/src/features/resources/components/ELibrary.tsx`
- Create: `frontend/src/features/resources/components/ManageELibrary.tsx`
- Create: `frontend/src/features/resources/index.ts`
- Create: `frontend/src/features/administration/components/AdministrasiPage.tsx`
- Create: `frontend/src/features/administration/components/StudentRegistrationPage.tsx`
- Create: `frontend/src/features/administration/components/RegistrasiPage.tsx`
- Create: `frontend/src/features/administration/index.ts`
- Create: `frontend/src/features/management/components/ManagementAdministrationPage.tsx`
- Create: `frontend/src/features/management/components/ManagementCoursesPage.tsx`
- Create: `frontend/src/features/management/components/ManajemenDashboard.tsx`
- Create: `frontend/src/features/management/components/ManajemenFakultasPage.tsx`
- Create: `frontend/src/features/management/components/ModuleManagement.tsx`
- Create: `frontend/src/features/management/components/UserManagementPage.tsx`
- Create: `frontend/src/features/management/index.ts`
- Create: `frontend/src/features/prodi/components/ProdiDashboard.tsx`
- Create: `frontend/src/features/prodi/components/ProdiCoursesPage.tsx`
- Create: `frontend/src/features/prodi/components/ProdiLecturersPage.tsx`
- Create: `frontend/src/features/prodi/components/ProdiStudentsPage.tsx`
- Create: `frontend/src/features/prodi/components/ProdiCourseForm.tsx`
- Create: `frontend/src/features/prodi/index.ts`
- Create: `frontend/src/features/dosen/components/DosenDashboard.tsx`
- Create: `frontend/src/features/dosen/index.ts`
- Create: `frontend/src/features/superadmin/components/SuperAdminDashboard.tsx`
- Create: `frontend/src/features/superadmin/index.ts`
- Create: `frontend/src/features/landing/components/Homepage.tsx`
- Create: `frontend/src/features/landing/components/AboutSection.tsx`
- Create: `frontend/src/features/landing/components/ContactSection.tsx`
- Create: `frontend/src/features/landing/components/LandingHeader.tsx`
- Create: `frontend/src/features/landing/components/LandingFooter.tsx`
- Create: `frontend/src/features/landing/components/LandingLayout.tsx`
- Create: `frontend/src/features/landing/index.ts`
- Create: `frontend/src/features/shared/components/Button.tsx`
- Create: `frontend/src/features/shared/components/Input.tsx`
- Create: `frontend/src/features/shared/components/Modal.tsx`
- Create: `frontend/src/features/shared/components/LoadingSpinner.tsx`
- Create: `frontend/src/features/shared/components/Dropdown.tsx`
- Create: `frontend/src/features/shared/index.ts`
- Create: `frontend/src/ui/components/AnimatedSection.tsx`
- Create: `frontend/src/ui/components/Icon.tsx`
- Create: `frontend/src/ui/index.ts`

**Step 1: Create the new directory structure**

```bash
mkdir -p frontend/src/features/auth/components
mkdir -p frontend/src/features/dashboard/components
mkdir -p frontend/src/features/courses/components
mkdir -p frontend/src/features/assignments/components
mkdir -p frontend/src/features/grades/components
mkdir -p frontend/src/features/calendar/components
mkdir -p frontend/src/features/resources/components
mkdir -p frontend/src/features/administration/components
mkdir -p frontend/src/features/management/components
mkdir -p frontend/src/features/prodi/components
mkdir -p frontend/src/features/dosen/components
mkdir -p frontend/src/features/superadmin/components
mkdir -p frontend/src/features/landing/components
mkdir -p frontend/src/features/shared/components
mkdir -p frontend/src/ui/components
```

**Step 2: Verify directory creation**

Run: `find frontend/src/features -type d | head -20`
Expected: All feature directories exist

**Step 3: Commit**

```bash
git add frontend/src/features
git commit -m "feat: create new feature-based directory structure"
```

### Task 2: Move components to appropriate feature directories

**Files:**
- Modify: `frontend/components/Login.tsx` → `frontend/src/features/auth/components/Login.tsx`
- Modify: `frontend/components/Register.tsx` → `frontend/src/features/auth/components/Register.tsx`
- Modify: `frontend/components/Profile.tsx` → `frontend/src/features/auth/components/Profile.tsx`
- Modify: `frontend/components/Dashboard.tsx` → `frontend/src/features/dashboard/components/Dashboard.tsx`
- Modify: `frontend/components/Sidebar.tsx` → `frontend/src/features/dashboard/components/Sidebar.tsx`
- Modify: `frontend/components/Header.tsx` → `frontend/src/features/dashboard/components/Header.tsx`
- Modify: `frontend/components/CourseCard.tsx` → `frontend/src/features/courses/components/CourseCard.tsx`
- Modify: `frontend/components/CourseDetail.tsx` → `frontend/src/features/courses/components/CourseDetail.tsx`
- Modify: `frontend/components/CourseForm.tsx` → `frontend/src/features/courses/components/CourseForm.tsx`
- Modify: `frontend/components/CourseCatalog.tsx` → `frontend/src/features/courses/components/CourseCatalog.tsx`
- Modify: `frontend/components/AssignmentCard.tsx` → `frontend/src/features/assignments/components/AssignmentCard.tsx`
- Modify: `frontend/components/AssignmentForm.tsx` → `frontend/src/features/assignments/components/AssignmentForm.tsx`
- Modify: `frontend/components/AssignmentDetailView.tsx` → `frontend/src/features/assignments/components/AssignmentDetailView.tsx`
- Modify: `frontend/components/Gradebook.tsx` → `frontend/src/features/grades/components/Gradebook.tsx`
- Modify: `frontend/components/Grades.tsx` → `frontend/src/features/grades/components/Grades.tsx`
- Modify: `frontend/components/Calendar.tsx` → `frontend/src/features/calendar/components/Calendar.tsx`
- Modify: `frontend/components/QuranReader.tsx` → `frontend/src/features/resources/components/QuranReader.tsx`
- Modify: `frontend/components/HadithReader.tsx` → `frontend/src/features/resources/components/HadithReader.tsx`
- Modify: `frontend/components/DoaReader.tsx` → `frontend/src/features/resources/components/DoaReader.tsx`
- Modify: `frontend/components/VideoLectures.tsx` → `frontend/src/features/resources/components/VideoLectures.tsx`
- Modify: `frontend/components/ELibrary.tsx` → `frontend/src/features/resources/components/ELibrary.tsx`
- Modify: `frontend/components/ManageELibrary.tsx` → `frontend/src/features/resources/components/ManageELibrary.tsx`
- Modify: `frontend/components/AdministrasiPage.tsx` → `frontend/src/features/administration/components/AdministrasiPage.tsx`
- Modify: `frontend/components/StudentRegistrationPage.tsx` → `frontend/src/features/administration/components/StudentRegistrationPage.tsx`
- Modify: `frontend/components/RegistrasiPage.tsx` → `frontend/src/features/administration/components/RegistrasiPage.tsx`
- Modify: `frontend/components/ManagementAdministrationPage.tsx` → `frontend/src/features/management/components/ManagementAdministrationPage.tsx`
- Modify: `frontend/components/ManagementCoursesPage.tsx` → `frontend/src/features/management/components/ManagementCoursesPage.tsx`
- Modify: `frontend/components/ManajemenDashboard.tsx` → `frontend/src/features/management/components/ManajemenDashboard.tsx`
- Modify: `frontend/components/ManajemenFakultasPage.tsx` → `frontend/src/features/management/components/ManajemenFakultasPage.tsx`
- Modify: `frontend/components/ModuleManagement.tsx` → `frontend/src/features/management/components/ModuleManagement.tsx`
- Modify: `frontend/components/UserManagementPage.tsx` → `frontend/src/features/management/components/UserManagementPage.tsx`
- Modify: `frontend/components/ProdiDashboard.tsx` → `frontend/src/features/prodi/components/ProdiDashboard.tsx`
- Modify: `frontend/components/ProdiCoursesPage.tsx` → `frontend/src/features/prodi/components/ProdiCoursesPage.tsx`
- Modify: `frontend/components/ProdiLecturersPage.tsx` → `frontend/src/features/prodi/components/ProdiLecturersPage.tsx`
- Modify: `frontend/components/ProdiStudentsPage.tsx` → `frontend/src/features/prodi/components/ProdiStudentsPage.tsx`
- Modify: `frontend/components/ProdiCourseForm.tsx` → `frontend/src/features/prodi/components/ProdiCourseForm.tsx`
- Modify: `frontend/components/DosenDashboard.tsx` → `frontend/src/features/dosen/components/DosenDashboard.tsx`
- Modify: `frontend/components/SuperAdminDashboard.tsx` → `frontend/src/features/superadmin/components/SuperAdminDashboard.tsx`
- Modify: `frontend/components/Homepage.tsx` → `frontend/src/features/landing/components/Homepage.tsx`
- Modify: `frontend/components/AboutSection.tsx` → `frontend/src/features/landing/components/AboutSection.tsx`
- Modify: `frontend/components/ContactSection.tsx` → `frontend/src/features/landing/components/ContactSection.tsx`
- Modify: `frontend/components/LandingHeader.tsx` → `frontend/src/features/landing/components/LandingHeader.tsx`
- Modify: `frontend/components/LandingFooter.tsx` → `frontend/src/features/landing/components/LandingFooter.tsx`
- Modify: `frontend/components/LandingLayout.tsx` → `frontend/src/features/landing/components/LandingLayout.tsx`

**Step 1: Move authentication components**

```bash
mv frontend/components/Login.tsx frontend/src/features/auth/components/Login.tsx
mv frontend/components/Register.tsx frontend/src/features/auth/components/Register.tsx
mv frontend/components/Profile.tsx frontend/src/features/auth/components/Profile.tsx
```

**Step 2: Move dashboard components**

```bash
mv frontend/components/Dashboard.tsx frontend/src/features/dashboard/components/Dashboard.tsx
mv frontend/components/Sidebar.tsx frontend/src/features/dashboard/components/Sidebar.tsx
mv frontend/components/Header.tsx frontend/src/features/dashboard/components/Header.tsx
```

**Step 3: Move course components**

```bash
mv frontend/components/CourseCard.tsx frontend/src/features/courses/components/CourseCard.tsx
mv frontend/components/CourseDetail.tsx frontend/src/features/courses/components/CourseDetail.tsx
mv frontend/components/CourseForm.tsx frontend/src/features/courses/components/CourseForm.tsx
mv frontend/components/CourseCatalog.tsx frontend/src/features/courses/components/CourseCatalog.tsx
mv frontend/components/CreateCourse.tsx frontend/src/features/courses/components/CreateCourse.tsx
mv frontend/components/PublicCourseCatalog.tsx frontend/src/features/courses/components/PublicCourseCatalog.tsx
```

**Step 4: Move assignment components**

```bash
mv frontend/components/AssignmentCard.tsx frontend/src/features/assignments/components/AssignmentCard.tsx
mv frontend/components/AssignmentForm.tsx frontend/src/features/assignments/components/AssignmentForm.tsx
mv frontend/components/AssignmentDetailView.tsx frontend/src/features/assignments/components/AssignmentDetailView.tsx
mv frontend/components/Assignments.tsx frontend/src/features/assignments/components/Assignments.tsx
mv frontend/components/CourseAssignmentsTab.tsx frontend/src/features/assignments/components/CourseAssignmentsTab.tsx
```

**Step 5: Move grade components**

```bash
mv frontend/components/Gradebook.tsx frontend/src/features/grades/components/Gradebook.tsx
mv frontend/components/Grades.tsx frontend/src/features/grades/components/Grades.tsx
```

**Step 6: Move calendar components**

```bash
mv frontend/components/Calendar.tsx frontend/src/features/calendar/components/Calendar.tsx
```

**Step 7: Move resource components**

```bash
mv frontend/components/QuranReader.tsx frontend/src/features/resources/components/QuranReader.tsx
mv frontend/components/HadithReader.tsx frontend/src/features/resources/components/HadithReader.tsx
mv frontend/components/DoaReader.tsx frontend/src/features/resources/components/DoaReader.tsx
mv frontend/components/VideoLectures.tsx frontend/src/features/resources/components/VideoLectures.tsx
mv frontend/components/ELibrary.tsx frontend/src/features/resources/components/ELibrary.tsx
mv frontend/components/ManageELibrary.tsx frontend/src/features/resources/components/ManageELibrary.tsx
mv frontend/components/IslamicResources.tsx frontend/src/features/resources/components/IslamicResources.tsx
mv frontend/components/HafalanRecorder.tsx frontend/src/features/resources/components/HafalanRecorder.tsx
mv frontend/components/UstadzAI.tsx frontend/src/features/resources/components/UstadzAI.tsx
```

**Step 8: Move administration components**

```bash
mv frontend/components/AdministrasiPage.tsx frontend/src/features/administration/components/AdministrasiPage.tsx
mv frontend/components/StudentRegistrationPage.tsx frontend/src/features/administration/components/StudentRegistrationPage.tsx
mv frontend/components/RegistrasiPage.tsx frontend/src/features/administration/components/RegistrasiPage.tsx
```

**Step 9: Move management components**

```bash
mv frontend/components/ManagementAdministrationPage.tsx frontend/src/features/management/components/ManagementAdministrationPage.tsx
mv frontend/components/ManagementCoursesPage.tsx frontend/src/features/management/components/ManagementCoursesPage.tsx
mv frontend/components/ManajemenDashboard.tsx frontend/src/features/management/components/ManajemenDashboard.tsx
mv frontend/components/ManajemenFakultasPage.tsx frontend/src/features/management/components/ManajemenFakultasPage.tsx
mv frontend/components/ModuleManagement.tsx frontend/src/features/management/components/ModuleManagement.tsx
mv frontend/components/UserManagementPage.tsx frontend/src/features/management/components/UserManagementPage.tsx
```

**Step 10: Move prodi components**

```bash
mv frontend/components/ProdiDashboard.tsx frontend/src/features/prodi/components/ProdiDashboard.tsx
mv frontend/components/ProdiCoursesPage.tsx frontend/src/features/prodi/components/ProdiCoursesPage.tsx
mv frontend/components/ProdiLecturersPage.tsx frontend/src/features/prodi/components/ProdiLecturersPage.tsx
mv frontend/components/ProdiStudentsPage.tsx frontend/src/features/prodi/components/ProdiStudentsPage.tsx
mv frontend/components/ProdiCourseForm.tsx frontend/src/features/prodi/components/ProdiCourseForm.tsx
```

**Step 11: Move dosen components**

```bash
mv frontend/components/DosenDashboard.tsx frontend/src/features/dosen/components/DosenDashboard.tsx
```

**Step 12: Move superadmin components**

```bash
mv frontend/components/SuperAdminDashboard.tsx frontend/src/features/superadmin/components/SuperAdminDashboard.tsx
```

**Step 13: Move landing components**

```bash
mv frontend/components/Homepage.tsx frontend/src/features/landing/components/Homepage.tsx
mv frontend/components/AboutSection.tsx frontend/src/features/landing/components/AboutSection.tsx
mv frontend/components/ContactSection.tsx frontend/src/features/landing/components/ContactSection.tsx
mv frontend/components/LandingHeader.tsx frontend/src/features/landing/components/LandingHeader.tsx
mv frontend/components/LandingFooter.tsx frontend/src/features/landing/components/LandingFooter.tsx
mv frontend/components/LandingLayout.tsx frontend/src/features/landing/components/LandingLayout.tsx
```

**Step 14: Commit the moves**

```bash
git add .
git commit -m "feat: move components to feature-based directories"
```

### Task 3: Move remaining shared and UI components

**Files:**
- Modify: `frontend/components/Button.tsx` (will be created as shared)
- Modify: `frontend/components/Input.tsx` (will be created as shared)
- Modify: `frontend/components/Modal.tsx` (will be created as shared)
- Modify: `frontend/components/LoadingSpinner.tsx` → `frontend/src/features/shared/components/LoadingSpinner.tsx`
- Modify: `frontend/components/Dropdown.tsx` → `frontend/src/features/shared/components/Dropdown.tsx`
- Modify: `frontend/components/AnimatedSection.tsx` → `frontend/src/ui/components/AnimatedSection.tsx`
- Modify: `frontend/components/Icon.tsx` → `frontend/src/ui/components/Icon.tsx`
- Modify: `frontend/components/VideoPlayer.tsx` → `frontend/src/ui/components/VideoPlayer.tsx`

**Step 1: Move remaining shared components**

```bash
mv frontend/components/LoadingSpinner.tsx frontend/src/features/shared/components/LoadingSpinner.tsx
mv frontend/components/Dropdown.tsx frontend/src/features/shared/components/Dropdown.tsx
mv frontend/components/AnimatedSection.tsx frontend/src/ui/components/AnimatedSection.tsx
mv frontend/components/Icon.tsx frontend/src/ui/components/Icon.tsx
mv frontend/components/VideoPlayer.tsx frontend/src/ui/components/VideoPlayer.tsx
```

**Step 2: Move other UI components**

```bash
# Move other UI components that are reusable
mv frontend/components/Notifications.tsx frontend/src/ui/components/Notifications.tsx
mv frontend/components/Settings.tsx frontend/src/ui/components/Settings.tsx
mv frontend/components/Help.tsx frontend/src/ui/components/Help.tsx
mv frontend/components/ReportBugForm.tsx frontend/src/ui/components/ReportBugForm.tsx
mv frontend/components/UserGuideContent.tsx frontend/src/ui/components/UserGuideContent.tsx
```

**Step 3: Move page not found and role-related components**

```bash
mv frontend/components/PageNotFound.tsx frontend/src/ui/components/PageNotFound.tsx
mv frontend/components/RoleDashboardNotAvailable.tsx frontend/src/ui/components/RoleDashboardNotAvailable.tsx
mv frontend/components/RolePageNotAvailable.tsx frontend/src/ui/components/RolePageNotAvailable.tsx
mv frontend/components/RoleSwitcher.tsx frontend/src/ui/components/RoleSwitcher.tsx
```

**Step 4: Move Islamic worship components**

```bash
mv frontend/components/Worship.tsx frontend/src/features/resources/components/Worship.tsx
mv frontend/components/PrayerTimes.tsx frontend/src/features/resources/components/PrayerTimes.tsx
```

**Step 5: Move announcement and discussion components**

```bash
mv frontend/components/AnnouncementsPage.tsx frontend/src/features/landing/components/AnnouncementsPage.tsx
mv frontend/components/DiscussionForum.tsx frontend/src/features/landing/components/DiscussionForum.tsx
```

**Step 6: Move FAQ components**

```bash
mv frontend/components/FaqContent.tsx frontend/src/features/landing/components/FaqContent.tsx
mv frontend/components/FaqSection.tsx frontend/src/features/landing/components/FaqSection.tsx
```

**Step 7: Commit the remaining moves**

```bash
git add .
git commit -m "feat: move remaining components to appropriate directories"
```

### Task 4: Create barrel files (index.ts) for each feature

**Files:**
- Create: `frontend/src/features/auth/index.ts`
- Create: `frontend/src/features/dashboard/index.ts`
- Create: `frontend/src/features/courses/index.ts`
- Create: `frontend/src/features/assignments/index.ts`
- Create: `frontend/src/features/grades/index.ts`
- Create: `frontend/src/features/calendar/index.ts`
- Create: `frontend/src/features/resources/index.ts`
- Create: `frontend/src/features/administration/index.ts`
- Create: `frontend/src/features/management/index.ts`
- Create: `frontend/src/features/prodi/index.ts`
- Create: `frontend/src/features/dosen/index.ts`
- Create: `frontend/src/features/superadmin/index.ts`
- Create: `frontend/src/features/landing/index.ts`
- Create: `frontend/src/features/shared/index.ts`
- Create: `frontend/src/ui/index.ts`

**Step 1: Create auth barrel file**

```ts
// frontend/src/features/auth/index.ts
export { default as Login } from './components/Login';
export { default as Register } from './components/Register';
export { default as Profile } from './components/Profile';
```

**Step 2: Create dashboard barrel file**

```ts
// frontend/src/features/dashboard/index.ts
export { default as Dashboard } from './components/Dashboard';
export { default as Sidebar } from './components/Sidebar';
export { default as Header } from './components/Header';
```

**Step 3: Create courses barrel file**

```ts
// frontend/src/features/courses/index.ts
export { default as CourseCard } from './components/CourseCard';
export { default as CourseDetail } from './components/CourseDetail';
export { default as CourseForm } from './components/CourseForm';
export { default as CourseCatalog } from './components/CourseCatalog';
export { default as CreateCourse } from './components/CreateCourse';
export { default as PublicCourseCatalog } from './components/PublicCourseCatalog';
```

**Step 4: Create assignments barrel file**

```ts
// frontend/src/features/assignments/index.ts
export { default as AssignmentCard } from './components/AssignmentCard';
export { default as AssignmentForm } from './components/AssignmentForm';
export { default as AssignmentDetailView } from './components/AssignmentDetailView';
export { default as Assignments } from './components/Assignments';
export { default as CourseAssignmentsTab } from './components/CourseAssignmentsTab';
```

**Step 5: Create grades barrel file**

```ts
// frontend/src/features/grades/index.ts
export { default as Gradebook } from './components/Gradebook';
export { default as Grades } from './components/Grades';
```

**Step 6: Create calendar barrel file**

```ts
// frontend/src/features/calendar/index.ts
export { default as Calendar } from './components/Calendar';
```

**Step 7: Create resources barrel file**

```ts
// frontend/src/features/resources/index.ts
export { default as QuranReader } from './components/QuranReader';
export { default as HadithReader } from './components/HadithReader';
export { default as DoaReader } from './components/DoaReader';
export { default as VideoLectures } from './components/VideoLectures';
export { default as ELibrary } from './components/ELibrary';
export { default as ManageELibrary } from './components/ManageELibrary';
export { default as IslamicResources } from './components/IslamicResources';
export { default as HafalanRecorder } from './components/HafalanRecorder';
export { default as UstadzAI } from './components/UstadzAI';
export { default as Worship } from './components/Worship';
export { default as PrayerTimes } from './components/PrayerTimes';
```

**Step 8: Create administration barrel file**

```ts
// frontend/src/features/administration/index.ts
export { default as AdministrasiPage } from './components/AdministrasiPage';
export { default as StudentRegistrationPage } from './components/StudentRegistrationPage';
export { default as RegistrasiPage } from './components/RegistrasiPage';
```

**Step 9: Create management barrel file**

```ts
// frontend/src/features/management/index.ts
export { default as ManagementAdministrationPage } from './components/ManagementAdministrationPage';
export { default as ManagementCoursesPage } from './components/ManagementCoursesPage';
export { default as ManajemenDashboard } from './components/ManajemenDashboard';
export { default as ManajemenFakultasPage } from './components/ManajemenFakultasPage';
export { default as ModuleManagement } from './components/ModuleManagement';
export { default as UserManagementPage } from './components/UserManagementPage';
```

**Step 10: Create prodi barrel file**

```ts
// frontend/src/features/prodi/index.ts
export { default as ProdiDashboard } from './components/ProdiDashboard';
export { default as ProdiCoursesPage } from './components/ProdiCoursesPage';
export { default as ProdiLecturersPage } from './components/ProdiLecturersPage';
export { default as ProdiStudentsPage } from './components/ProdiStudentsPage';
export { default as ProdiCourseForm } from './components/ProdiCourseForm';
```

**Step 11: Create dosen barrel file**

```ts
// frontend/src/features/dosen/index.ts
export { default as DosenDashboard } from './components/DosenDashboard';
```

**Step 12: Create superadmin barrel file**

```ts
// frontend/src/features/superadmin/index.ts
export { default as SuperAdminDashboard } from './components/SuperAdminDashboard';
```

**Step 13: Create landing barrel file**

```ts
// frontend/src/features/landing/index.ts
export { default as Homepage } from './components/Homepage';
export { default as AboutSection } from './components/AboutSection';
export { default as ContactSection } from './components/ContactSection';
export { default as LandingHeader } from './components/LandingHeader';
export { default as LandingFooter } from './components/LandingFooter';
export { default as LandingLayout } from './components/LandingLayout';
export { default as AnnouncementsPage } from './components/AnnouncementsPage';
export { default as DiscussionForum } from './components/DiscussionForum';
export { default as FaqContent } from './components/FaqContent';
export { default as FaqSection } from './components/FaqSection';
```

**Step 14: Create shared barrel file**

```ts
// frontend/src/features/shared/index.ts
export { default as LoadingSpinner } from './components/LoadingSpinner';
export { default as Dropdown } from './components/Dropdown';
```

**Step 15: Create UI barrel file**

```ts
// frontend/src/ui/index.ts
export { default as AnimatedSection } from './components/AnimatedSection';
export { default as Icon } from './components/Icon';
export { default as VideoPlayer } from './components/VideoPlayer';
export { default as Notifications } from './components/Notifications';
export { default as Settings } from './components/Settings';
export { default as Help } from './components/Help';
export { default as ReportBugForm } from './components/ReportBugForm';
export { default as UserGuideContent } from './components/UserGuideContent';
export { default as PageNotFound } from './components/PageNotFound';
export { default as RoleDashboardNotAvailable } from './components/RoleDashboardNotAvailable';
export { default as RolePageNotAvailable } from './components/RolePageNotAvailable';
export { default as RoleSwitcher } from './components/RoleSwitcher';
```

**Step 16: Commit all barrel files**

```bash
git add .
git commit -m "feat: add barrel files for all feature directories"
```

### Task 5: Update App.tsx and other main files to use new import paths

**Files:**
- Modify: `frontend/App.tsx:1-100` (update imports)

**Step 1: Check the current App.tsx to understand import structure**

Run: `head -50 frontend/App.tsx`
Expected: See current import structure

**Step 2: Update App.tsx imports to use new structure**

```bash
cp frontend/App.tsx frontend/App.tsx.backup
```

**Step 3: Commit the backup**

```bash
git add frontend/App.tsx.backup
git commit -m "backup: App.tsx before updating import paths"
```

### Task 6: Clean up old components directory

**Files:**
- Delete: `frontend/components/*` (after all files have been moved)

**Step 1: Verify all components have been moved**

Run: `ls -la frontend/components/`
Expected: Only components that couldn't be categorized or new ones to be created

**Step 2: Remove empty components directory if it's truly empty**

```bash
# Only if empty after moving all components
rmdir frontend/components 2>/dev/null || echo "Components directory not empty, checking contents..."
```

**Step 3: Commit the cleanup**

```bash
git add .
git commit -m "refactor: clean up old components directory"
```