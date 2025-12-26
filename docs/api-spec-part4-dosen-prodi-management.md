# API Specification Part 4 - Dosen, Prodi & Management

## Dosen Features

### GET /api/dosen/dashboard
**Frontend**: DosenDashboard.tsx (useEffect lines 34-61)
**Purpose**: Get dosen-specific dashboard statistics
**Auth**: JWT (Dosen role required)

Query Parameters:
- `dosenId`: string (authenticated user's dosenId)

Response:
```json
{
  "stats": {
    "coursesTeaching": number,
    "totalStudents": number,
    "pendingGrades": number,
    "recentSubmissions": number
  },
  "courses": [
    {
      "id": "string",
      "title": "string",
      "studentCount": number,
      "assignmentsCount": number,
      "pendingGrades": number
    }
  ]
}
```

**Frontend Notes**:
- Displays summary data for dosen
- Links to courses they teach
- Shows assignments needing grading
- Uses mock data in DosenDashboard.tsx lines 40-80

### POST /api/announcements
**Frontend**: DosenDashboard.tsx (handleAnnouncementSubmit function lines 72-94)
**Purpose**: Create new announcements for courses or academic purposes
**Auth**: JWT (Dosen role required)

Request Body:
```json
{
  "title": "string",
  "content": "string",
  "category": "Mata Kuliah" | "Akademik",
  "course_id": "string | null",
  "authorName": "string"
}
```

Response:
```json
{
  "id": "string",
  "title": "string",
  "content": "string",
  "category": "Mata Kuliah" | "Akademik",
  "course_id": "string | null",
  "authorName": "string",
  "timestamp": "string"
}
```

## Prodi Features

### GET /api/prodi/{id}/lecturers
**Frontend**: ProdiLecturersPage.tsx (component logic lines 20-113)
**Purpose**: Get list of lecturers within a specific prodi
**Auth**: JWT (Prodi Admin role required)

Response:
```json
{
  "lecturers": [
    {
      "id": "string",
      "name": "string",
      "email": "string",
      "avatarUrl": "string",
      "courseCount": number
    }
  ]
}
```

### GET /api/prodi/{id}/courses/lecturers
**Frontend**: ProdiLecturersPage.tsx (component logic lines 20-113)
**Purpose**: Get lecturers assigned to courses within a specific prodi
**Auth**: JWT (Prodi Admin role required)

Response:
```json
{
  "assignments": [
    {
      "lecturerId": "string",
      "lecturerName": "string",
      "courseId": "string",
      "courseTitle": "string"
    }
  ]
}
```

### PUT /api/prodi/{id}/courses/{courseId}/assign-dosen
**Frontend**: ProdiLecturersPage.tsx (component logic lines 20-113)
**Purpose**: Assign a lecturer to a course within a specific prodi
**Auth**: JWT (Prodi Admin role required)

Request Body:
```json
{
  "lecturerId": "string"
}
```

Response:
```json
{
  "success": boolean,
  "message": "string"
}
```

## Management (Admin) Features

### CRUD for Faculties

#### GET /api/faculties
**Frontend**: ManajemenFakultasPage.tsx (component logic lines 20-317)
**Purpose**: Get all faculties with their details
**Auth**: JWT (Manajemen Kampus or Super Admin role required)

Response:
```json
[
  {
    "id": "string",
    "name": "string",
    "description": "string",
    "majors": [
      {
        "id": "string",
        "name": "string"
      }
    ],
    "createdAt": "string"
  }
]
```

#### POST /api/faculties
**Frontend**: ManajemenFakultasPage.tsx (handleAddFaculty function lines 65-77)
**Purpose**: Create a new faculty
**Auth**: JWT (Manajemen Kampus or Super Admin role required)

Request Body:
```json
{
  "name": "string",
  "description": "string"
}
```

Response:
```json
{
  "id": "string",
  "name": "string",
  "description": "string",
  "majors": [],
  "createdAt": "string"
}
```

#### PUT /api/faculties/{id}
**Frontend**: ManajemenFakultasPage.tsx (handleUpdateFaculty function lines 80-88)
**Purpose**: Update faculty details
**Auth**: JWT (Manajemen Kampus or Super Admin role required)

Request Body:
```json
{
  "name": "string",
  "description": "string"
}
```

Response:
```json
{
  "id": "string",
  "name": "string",
  "description": "string",
  "majors": [
    {
      "id": "string",
      "name": "string"
    }
  ],
  "createdAt": "string"
}
```

#### DELETE /api/faculties/{id}
**Frontend**: ManajemenFakultasPage.tsx (handleDeleteFaculty function lines 91-101)
**Purpose**: Delete a faculty (with validation check for associated users)
**Auth**: JWT (Manajemen Kampus or Super Admin role required)

Response:
```json
{
  "success": boolean,
  "message": "string"
}
```

## Module Management

### GET /api/courses/{id}/modules
**Frontend**: ModuleManagement.tsx (component logic lines 175-261)
**Purpose**: Get all modules for a specific course
**Auth**: JWT (Dosen role required for their courses, Prodi Admin for their prodi courses)

Response:
```json
{
  "modules": [
    {
      "id": "string",
      "title": "string",
      "type": "video" | "pdf" | "quiz" | "hafalan" | "live",
      "description": "string",
      "duration": "string",
      "resourceUrl": "string",
      "captionsUrl": "string",
      "attachmentUrl": "string",
      "startTime": "string",
      "liveUrl": "string"
    }
  ]
}
```

### POST /api/courses/{id}/modules
**Frontend**: ModuleManagement.tsx (handleSaveModule function lines 205-213)
**Purpose**: Create a new module for a specific course
**Auth**: JWT (Dosen role required for their courses)

Request Body:
```json
{
  "title": "string",
  "type": "video" | "pdf" | "quiz" | "hafalan" | "live",
  "description": "string",
  "duration": "string",
  "resourceUrl": "string",
  "captionsUrl": "string",
  "attachmentUrl": "string",
  "startTime": "string",
  "liveUrl": "string"
}
```

Response:
```json
{
  "id": "string",
  "title": "string",
  "type": "video" | "pdf" | "quiz" | "hafalan" | "live",
  "description": "string",
  "duration": "string",
  "resourceUrl": "string",
  "captionsUrl": "string",
  "attachmentUrl": "string",
  "startTime": "string",
  "liveUrl": "string"
}
```

### PUT /api/courses/{id}/modules/{moduleId}
**Frontend**: ModuleManagement.tsx (handleSaveModule function lines 205-213)
**Purpose**: Update an existing module for a specific course
**Auth**: JWT (Dosen role required for their courses)

Request Body:
```json
{
  "title": "string",
  "type": "video" | "pdf" | "quiz" | "hafalan" | "live",
  "description": "string",
  "duration": "string",
  "resourceUrl": "string",
  "captionsUrl": "string",
  "attachmentUrl": "string",
  "startTime": "string",
  "liveUrl": "string"
}
```

Response:
```json
{
  "id": "string",
  "title": "string",
  "type": "video" | "pdf" | "quiz" | "hafalan" | "live",
  "description": "string",
  "duration": "string",
  "resourceUrl": "string",
  "captionsUrl": "string",
  "attachmentUrl": "string",
  "startTime": "string",
  "liveUrl": "string"
}
```

### DELETE /api/courses/{id}/modules/{moduleId}
**Frontend**: ModuleManagement.tsx (handleDeleteModule function lines 201-203)
**Purpose**: Delete a module from a specific course
**Auth**: JWT (Dosen role required for their courses)

Response:
```json
{
  "success": boolean,
  "message": "string"
}
```

## Super Admin Features

### GET /api/superadmin/dashboard
**Frontend**: SuperAdminDashboard.tsx (component logic lines 82-122)
**Purpose**: Get super admin dashboard statistics and system status
**Auth**: JWT (Super Admin role required)

Response:
```json
{
  "systemStatus": {
    "database": "online" | "degraded" | "offline",
    "apiGateway": "online" | "degraded" | "offline",
    "videoServer": "online" | "degraded" | "offline",
    "mailService": "online" | "degraded" | "offline"
  },
  "stats": {
    "totalUsers": number,
    "totalCourses": number,
    "totalFaculties": number,
    "totalProdis": number
  }
}
```

## Role Switching & Management

### GET /api/users/all
**Frontend**: RoleSwitcher.tsx (using ALL_USERS constant from constants.ts)
**Purpose**: Get all users for role switching functionality
**Auth**: JWT (Super Admin role required)

Response:
```json
[
  {
    "id": "string",
    "name": "string",
    "email": "string",
    "role": "Mahasiswa" | "Dosen" | "Prodi Admin" | "Manajemen Kampus" | "Super Admin" | "MABA",
    "avatarUrl": "string"
  }
]
```

## User Management & Preferences

### GET /api/users/{id}/preferences
**Frontend**: Settings.tsx (component logic lines 25-374)
**Purpose**: Get user preferences and settings
**Auth**: JWT (Authenticated user)

Response:
```json
{
  "theme": "light" | "dark",
  "notifications": {
    "course": boolean,
    "assignments": boolean,
    "forum": boolean
  },
  "language": "id" | "en" | "ar"
}
```

### PUT /api/users/{id}/preferences
**Frontend**: Settings.tsx (saveProfile function lines 37-63)
**Purpose**: Update user preferences and settings
**Auth**: JWT (Authenticated user)

Request Body:
```json
{
  "theme": "light" | "dark",
  "notifications": {
    "course": boolean,
    "assignments": boolean,
    "forum": boolean
  },
  "language": "id" | "en" | "ar"
}
```

Response:
```json
{
  "success": boolean,
  "message": "string"
}
```

### PUT /api/users/{id}/profile
**Frontend**: Settings.tsx (saveProfile function lines 37-63)
**Purpose**: Update user profile information
**Auth**: JWT (Authenticated user)

Request Body:
```json
{
  "name": "string",
  "bio": "string",
  "avatarUrl": "string"
}
```

Response:
```json
{
  "id": "string",
  "name": "string",
  "bio": "string",
  "avatarUrl": "string",
  "email": "string",
  "role": "string"
}
```

## Notifications

### GET /api/notifications
**Frontend**: Notifications.tsx (component logic lines 13-56)
**Purpose**: Get user notifications
**Auth**: JWT (Authenticated user)

Response:
```json
[
  {
    "id": "string",
    "type": "forum" | "grade" | "assignment" | "announcement",
    "messageKey": "string",
    "context": "string",
    "timestamp": "string",
    "isRead": boolean,
    "link": {
      "page": "string",
      "params": "object"
    }
  }
]
```

### PUT /api/notifications/{id}/read
**Frontend**: Notifications.tsx (onMarkAsRead callback lines 23-27)
**Purpose**: Mark a notification as read
**Auth**: JWT (Authenticated user)

Response:
```json
{
  "success": boolean,
  "message": "string"
}
```

## Support Features

### POST /api/support/bugs
**Frontend**: ReportBugForm.tsx (handleSubmit function lines 11-22)
**Purpose**: Submit bug reports
**Auth**: JWT (Authenticated user)

Request Body:
```json
{
  "subject": "string",
  "description": "string",
  "steps": "string"
}
```

Response:
```json
{
  "success": boolean,
  "message": "string"
}
```

## Islamic & AI Features

### POST /api/ai/chat
**Frontend**: UstadzAI.tsx (askUstadzAI function called from handleSend lines 31-49)
**Purpose**: Send message to Islamic AI assistant
**Auth**: JWT (Authenticated user)

Request Body:
```json
{
  "message": "string"
}
```

Response:
```json
{
  "response": "string"
}
```

### GET /api/prayer-times
**Frontend**: PrayerTimes.tsx (component logic lines 65-310)
**Purpose**: Get prayer times for a specific location
**Auth**: JWT (Authenticated user)

Query Parameters:
- `locationId`: string
- `date`: string (optional, defaults to today)

Response:
```json
{
  "imsak": "string",
  "subuh": "string",
  "terbit": "string",
  "dhuha": "string",
  "dzuhur": "string",
  "ashar": "string",
  "maghrib": "string",
  "isya": "string",
  "tanggal": "string",
  "date": "string"
}
```

### GET /api/prayer-times/locations
**Frontend**: PrayerTimes.tsx (component logic lines 159-188)
**Purpose**: Get all available prayer time locations
**Auth**: JWT (Authenticated user)

Response:
```json
[
  {
    "id": "string",
    "lokasi": "string"
  }
]
```

## Type Definitions Summary

### Faculty Structure
```typescript
interface Faculty {
  id: string;
  name: string;
  description: string;
  majors: Major[];
  createdAt?: string;
}
```

### User Structure
```typescript
interface User {
  id?: string;
  name: string;
  email: string;
  password?: string;
  avatarUrl?: string;
  role: UserRole;
  studentId?: string;
  joinDate?: string;
  bio?: string;
  studentStatus?: 'Aktif' | 'Cuti' | 'Lulus' | 'DO' | 'Pendaftaran';
  gpa?: number;
  totalSks?: number;
  facultyId?: string;
  majorId?: string;
  badges?: string[];
  email_verified_at?: string;
  created_at?: string;
  updated_at?: string;
  remember_token?: string;
  phoneNumber?: string;
}
```

### Course Module Structure
```typescript
interface CourseModule {
  id: string;
  title: string;
  type: 'video' | 'pdf' | 'quiz' | 'hafalan' | 'live';
  description?: string;
  duration?: string;
  resourceUrl?: string;
  captionsUrl?: string;
  attachmentUrl?: string;
  startTime?: string;
  liveUrl?: string;
}
```

## ROLE-BASED ACCESS PATTERNS

### Super Admin (Highest Level)
- Can access: All faculties, all prodies, all users
- Endpoints: /api/superadmin/* (unfiltered)
- Permissions: Full CRUD on everything

### Manajemen Kampus
- Can access: All faculties, cross-prodi data
- Endpoints: /api/faculties/* (filtered by faculty if applicable)
- Permissions: Read all, limited write

### Prodi Admin
- Can access: Specific prodi only
- Endpoints: /api/prodi/{prodiId}/*
- Permissions: CRUD within assigned prodi

### Dosen
- Can access: Courses they teach
- Endpoints: /api/dosen/{dosenId}/*
- Permissions: Read assigned courses, grade submissions

## SPECIAL CONSIDERATIONS

### Faculty/Prodi Filtering
Many endpoints will need facultyId or prodiId:
- GET /api/courses?facultyId={id}
- GET /api/users?prodiId={id}
- GET /api/grades?facultyId={id}

### Bulk Operations
If you see:
- "Select all" checkboxes → Bulk update endpoints
- "Import" buttons → Bulk create endpoints
- "Export" buttons → Bulk read endpoints

## File Analysis Complete
- [x] DosenDashboard.tsx
- [x] ProdiLecturersPage.tsx
- [x] ManajemenFakultasPage.tsx
- [x] ModuleManagement.tsx
- [x] SuperAdminDashboard.tsx
- [x] RoleSwitcher.tsx
- [x] Settings.tsx
- [x] Notifications.tsx
- [x] Landing components
- [x] Support forms
- [x] UstadzAI.tsx
- [x] PrayerTimes.tsx
- [x] types.ts