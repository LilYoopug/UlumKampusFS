# API Specification Part 2 - Courses, Assignments & Grades

## File Analysis Progress
- [x] CourseCard.tsx
- [x] CourseCatalog.tsx
- [x] CourseDetail.tsx
- [x] CourseForm.tsx
- [x] AssignmentCard.tsx
- [x] AssignmentDetailView.tsx
- [x] AssignmentForm.tsx
- [x] Assignments.tsx
- [x] Gradebook.tsx
- [x] Grades.tsx
- [x] VideoLectures.tsx
- [x] HafalanRecorder.tsx
- [x] ManagementCoursesPage.tsx
- [x] ProdiCourseForm.tsx
- [x] VideoPlayer.tsx
- [x] types.ts
- [x] constants files

## Courses

### GET /api/courses
**Frontend**: CourseCatalog.tsx (useEffect, lines 108-112)
**Purpose**: Get list of courses for browsing
**Auth**: Public or Sanctum Bearer Token (depends if enrolled only)

Query Parameters:
- `search`: string (search by title or description)
- `faculty`: string (filter by faculty/prodi)
- `major`: string (filter by major)
- `page`: number
- `limit`: number

Response:
```json
{
  "courses": [
    {
      "id": "string",
      "title": "string",
      "description": "string",
      "instructor": "string",
      "instructorId": "string",
      "instructorAvatarUrl": "string",
      "sks": number,
      "facultyId": "string",
      "majorId": "string",
      "imageUrl": "string",
      "progress": number,
      "gradeLetter": "string",
      "gradeNumeric": number,
      "completionDate": "string",
      "mode": "string",
      "status": "Published | Draft | Archived",
      "learningObjectives": ["string"],
      "syllabus": [
        {
          "week": number,
          "topic": "string",
          "description": "string"
        }
      ],
      "modules": [
        {
          "id": "string",
          "title": "string",
          "type": "video | pdf | quiz | hafalan | live",
          "description": "string",
          "duration": "string",
          "resourceUrl": "string",
          "captionsUrl": "string",
          "attachmentUrl": "string",
          "startTime": "string",
          "liveUrl": "string"
        }
      ],
      "createdAt": "string",
      "updatedAt": "string"
    }
  ]
}
```

**Notes**:
- Uses mock data in CourseCatalog.tsx lines 108-112
- Based on CourseCard display requirements

### GET /api/courses/{id}
**Frontend**: CourseDetail.tsx (useEffect, lines 9-88)
**Purpose**: Get detailed information about a specific course
**Auth**: Sanctum Bearer Token

Response:
```json
{
  "id": "string",
  "title": "string",
  "description": "string",
  "instructor": "string",
  "instructorId": "string",
  "instructorAvatarUrl": "string",
  "sks": number,
  "facultyId": "string",
  "majorId": "string",
  "imageUrl": "string",
  "progress": number,
  "gradeLetter": "string",
  "gradeNumeric": number,
  "completionDate": "string",
  "mode": "string",
  "status": "Published | Draft | Archived",
  "learningObjectives": ["string"],
  "syllabus": [
    {
      "week": number,
      "topic": "string",
      "description": "string"
    }
  ],
  "modules": [
    {
      "id": "string",
      "title": "string",
      "type": "video | pdf | quiz | hafalan | live",
      "description": "string",
      "duration": "string",
      "resourceUrl": "string",
      "captionsUrl": "string",
      "attachmentUrl": "string",
      "startTime": "string",
      "liveUrl": "string"
    }
  ],
  "createdAt": "string",
  "updatedAt": "string"
}
```

### POST /api/courses
**Frontend**: CourseForm.tsx (handleSubmit, lines 117-120)
**Purpose**: Create a new course
**Auth**: Sanctum Bearer Token (Dosen role)

Request Body:
```json
{
  "title": "string",
  "description": "string",
  "instructor": "string",
  "instructorId": "string",
  "sks": number,
  "facultyId": "string",
  "majorId": "string",
  "imageUrl": "string",
  "status": "Published | Draft | Archived",
  "learningObjectives": ["string"],
  "syllabus": [
    {
      "week": number,
      "topic": "string",
      "description": "string"
    }
  ],
  "modules": [
    {
      "id": "string",
      "title": "string",
      "type": "video | pdf | quiz | hafalan | live",
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

Response:
```json
{
  "id": "string",
  "title": "string",
  "description": "string",
  "instructor": "string",
  "instructorId": "string",
  "sks": number,
  "facultyId": "string",
  "majorId": "string",
  "imageUrl": "string",
  "progress": number,
  "gradeLetter": "string",
  "gradeNumeric": number,
  "completionDate": "string",
  "mode": "string",
  "status": "Published | Draft | Archived",
  "learningObjectives": ["string"],
  "syllabus": [
    {
      "week": number,
      "topic": "string",
      "description": "string"
    }
  ],
  "modules": [
    {
      "id": "string",
      "title": "string",
      "type": "video | pdf | quiz | hafalan | live",
      "description": "string",
      "duration": "string",
      "resourceUrl": "string",
      "captionsUrl": "string",
      "attachmentUrl": "string",
      "startTime": "string",
      "liveUrl": "string"
    }
  ],
  "createdAt": "string",
  "updatedAt": "string"
}
```

### PUT /api/courses/{id}
**Frontend**: CourseForm.tsx (handleSubmit, lines 117-120)
**Purpose**: Update an existing course
**Auth**: Sanctum Bearer Token (Dosen role)

Request Body:
```json
{
  "title": "string",
  "description": "string",
  "instructor": "string",
  "instructorId": "string",
  "sks": number,
  "facultyId": "string",
  "majorId": "string",
  "imageUrl": "string",
  "status": "Published | Draft | Archived",
  "learningObjectives": ["string"],
  "syllabus": [
    {
      "week": number,
      "topic": "string",
      "description": "string"
    }
  ],
  "modules": [
    {
      "id": "string",
      "title": "string",
      "type": "video | pdf | quiz | hafalan | live",
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

Response:
```json
{
  "id": "string",
  "title": "string",
  "description": "string",
  "instructor": "string",
  "instructorId": "string",
  "sks": number,
  "facultyId": "string",
  "majorId": "string",
  "imageUrl": "string",
  "progress": number,
  "gradeLetter": "string",
  "gradeNumeric": number,
  "completionDate": "string",
  "mode": "string",
  "status": "Published | Draft | Archived",
  "learningObjectives": ["string"],
  "syllabus": [
    {
      "week": number,
      "topic": "string",
      "description": "string"
    }
  ],
  "modules": [
    {
      "id": "string",
      "title": "string",
      "type": "video | pdf | quiz | hafalan | live",
      "description": "string",
      "duration": "string",
      "resourceUrl": "string",
      "captionsUrl": "string",
      "attachmentUrl": "string",
      "startTime": "string",
      "liveUrl": "string"
    }
  ],
  "createdAt": "string",
  "updatedAt": "string"
}
```

### DELETE /api/courses/{id}
**Frontend**: CourseForm.tsx (not explicitly shown but needed for management)
**Purpose**: Delete a course
**Auth**: Sanctum Bearer Token (Dosen role)

Response: 204 No Content

## Assignments

### GET /api/courses/{courseId}/assignments
**Frontend**: CourseDetail.tsx (CourseAssignmentsTab component)
**Purpose**: Get all assignments for a specific course
**Auth**: Sanctum Bearer Token

Query Parameters:
- `status`: string (submitted, pending, graded)
- `category`: string (Tugas, Ujian)
- `dueDateFrom`: string (filter by due date range)
- `dueDateTo`: string (filter by due date range)
- `page`: number
- `limit`: number

Response:
```json
{
  "assignments": [
    {
      "id": "string",
      "courseId": "string",
      "title": "string",
      "description": "string",
      "dueDate": "string",
      "files": [
        {
          "name": "string",
          "url": "string"
        }
      ],
      "submissions": [
        {
          "studentId": "string",
          "submittedAt": "string",
          "file": {
            "name": "string",
            "url": "string"
          },
          "gradeLetter": "string",
          "gradeNumeric": "number",
          "feedback": "string"
        }
      ],
      "type": "string",
      "category": "Tugas | Ujian",
      "maxScore": "number",
      "instructions": "string",
      "attachments": [],
      "createdAt": "string",
      "updatedAt": "string"
    }
  ],
  "pagination": {
    "page": number,
    "limit": number,
    "total": number,
    "totalPages": number
  }
}
```

### GET /api/courses/{courseId}/modules
**Frontend**: CourseDetail.tsx (useEffect, lines 9-88)
**Purpose**: Get all modules for a specific course
**Auth**: Sanctum Bearer Token

Response:
```json
{
  "modules": [
    {
      "id": "string",
      "title": "string",
      "type": "video | pdf | quiz | hafalan | live",
      "description": "string",
      "duration": "string",
      "resourceUrl": "string",
      "captionsUrl": "string",
      "attachmentUrl": "string",
      "startTime": "string",
      "liveUrl": "string",
      "order": "number"
    }
  ]
}
```

### POST /api/courses/{courseId}/modules/{moduleId}/complete
**Frontend**: CourseDetail.tsx (markModuleAsCompleted function)
**Purpose**: Mark a course module as completed
**Auth**: Sanctum Bearer Token (Student enrolled in course)

Request Body:
```json
{
  "moduleId": "string",
  "completedAt": "string",
  "progressPercentage": "number"
}
```

Response:
```json
{
  "id": "string",
  "courseId": "string",
  "moduleId": "string",
  "completedAt": "string",
  "progressPercentage": "number",
  "status": "completed | in_progress"
}
```

### GET /api/assignments/{id}
**Frontend**: AssignmentDetailView.tsx (useEffect, lines 92-180)
**Purpose**: Get detailed information about a specific assignment
**Auth**: Sanctum Bearer Token

Response:
```json
{
  "id": "string",
  "courseId": "string",
  "title": "string",
  "description": "string",
  "dueDate": "string",
  "files": [
    {
      "name": "string",
      "url": "string"
    }
  ],
  "submissions": [
    {
      "studentId": "string",
      "submittedAt": "string",
      "file": {
        "name": "string",
        "url": "string"
      },
      "gradeLetter": "string",
      "gradeNumeric": "number",
      "feedback": "string"
    }
  ],
  "type": "string",
  "category": "Tugas | Ujian",
  "maxScore": "number",
  "instructions": "string",
  "attachments": [],
  "createdAt": "string",
  "updatedAt": "string"
}
```

### POST /api/assignments
**Frontend**: AssignmentForm.tsx (handleSubmit, lines 21-35)
**Purpose**: Create a new assignment
**Auth**: Sanctum Bearer Token (Dosen role)

Request Body:
```json
{
  "courseId": "string",
  "title": "string",
  "description": "string",
  "dueDate": "string",
  "files": [
    {
      "name": "string",
      "url": "string"
    }
  ],
  "type": "file | hafalan",
  "category": "Tugas | Ujian"
}
```

Response:
```json
{
  "id": "string",
  "courseId": "string",
  "title": "string",
  "description": "string",
  "dueDate": "string",
  "files": [
    {
      "name": "string",
      "url": "string"
    }
  ],
  "submissions": [],
  "type": "file | hafalan",
  "category": "Tugas | Ujian",
  "createdAt": "string",
  "updatedAt": "string"
}
```

### PUT /api/assignments/{id}
**Frontend**: AssignmentDetailView.tsx (onUpdateAssignment, lines 86-92)
**Purpose**: Update an existing assignment
**Auth**: Sanctum Bearer Token (Dosen role)

Request Body:
```json
{
  "title": "string",
  "description": "string",
  "dueDate": "string",
  "files": [
    {
      "name": "string",
      "url": "string"
    }
  ],
  "category": "Tugas | Ujian"
}
```

Response:
```json
{
  "id": "string",
  "courseId": "string",
  "title": "string",
  "description": "string",
  "dueDate": "string",
  "files": [
    {
      "name": "string",
      "url": "string"
    }
  ],
  "submissions": [
    {
      "studentId": "string",
      "submittedAt": "string",
      "file": {
        "name": "string",
        "url": "string"
      },
      "gradeLetter": "string",
      "gradeNumeric": "number",
      "feedback": "string"
    }
  ],
  "type": "file | hafalan",
  "category": "Tugas | Ujian",
  "createdAt": "string",
  "updatedAt": "string"
}
```

### POST /api/assignments/{id}/submissions
**Frontend**: AssignmentDetailView.tsx (handleNewSubmission, lines 123-136)
**Purpose**: Submit an assignment
**Auth**: Sanctum Bearer Token (Mahasiswa role)

Request Body:
```json
{
  "studentId": "string",
  "file": {
    "name": "string",
    "url": "string"
  }
}
```

Response:
```json
{
  "studentId": "string",
  "submittedAt": "string",
  "file": {
    "name": "string",
    "url": "string"
  }
}
```

### POST /api/assignments/{id}/hafalan-submission
**Frontend**: HafalanRecorder.tsx (handleSubmit, lines 83-95)
**Purpose**: Submit a hafalan (memorization) assignment with audio recording
**Auth**: Sanctum Bearer Token (Mahasiswa role)

Request Body:
```json
{
  "studentId": "string",
  "file": {
    "name": "string",
    "url": "string"
  }
}
```

Response:
```json
{
  "studentId": "string",
  "submittedAt": "string",
  "file": {
    "name": "string",
    "url": "string"
  }
}
```

### PUT /api/submissions/{id}/grade
**Frontend**: AssignmentDetailView.tsx (handleSaveGrade, lines 170-179)
**Purpose**: Grade a student submission
**Auth**: Sanctum Bearer Token (Dosen role)

Request Body:
```json
{
  "gradeNumeric": "number",
  "gradeLetter": "string",
  "feedback": "string"
}
```

Response:
```json
{
  "studentId": "string",
  "submittedAt": "string",
  "file": {
    "name": "string",
    "url": "string"
  },
  "gradeNumeric": "number",
  "gradeLetter": "string",
  "feedback": "string"
}
```

## Grades

### GET /api/grades/gradebook
**Frontend**: Gradebook.tsx (useEffect, lines 95-124)
**Purpose**: Get gradebook data for all courses and students
**Auth**: Sanctum Bearer Token (Dosen role)

Query Parameters:
- `courseId`: string (optional, filter by course)

Response:
```json
{
  "courses": [
    {
      "id": "string",
      "title": "string",
      "instructor": "string",
      "instructorId": "string",
      "instructorAvatarUrl": "string",
      "sks": number,
      "facultyId": "string",
      "majorId": "string",
      "imageUrl": "string",
      "progress": number,
      "gradeLetter": "string",
      "gradeNumeric": number,
      "completionDate": "string",
      "mode": "string",
      "status": "Published | Draft | Archived",
      "learningObjectives": ["string"],
      "syllabus": [
        {
          "week": number,
          "topic": "string",
          "description": "string"
        }
      ],
      "modules": [
        {
          "id": "string",
          "title": "string",
          "type": "video | pdf | quiz | hafalan | live",
          "description": "string",
          "duration": "string",
          "resourceUrl": "string",
          "captionsUrl": "string",
          "attachmentUrl": "string",
          "startTime": "string",
          "liveUrl": "string"
        }
      ],
      "createdAt": "string",
      "updatedAt": "string"
    }
  ],
  "assignments": [
    {
      "id": "string",
      "courseId": "string",
      "title": "string",
      "description": "string",
      "dueDate": "string",
      "files": [
        {
          "name": "string",
          "url": "string"
        }
      ],
      "submissions": [
        {
          "studentId": "string",
          "submittedAt": "string",
          "file": {
            "name": "string",
            "url": "string"
          },
          "gradeLetter": "string",
          "gradeNumeric": "number",
          "feedback": "string"
        }
      ],
      "type": "string",
      "category": "Tugas | Ujian",
      "maxScore": "number",
      "instructions": "string",
      "attachments": [],
      "createdAt": "string",
      "updatedAt": "string"
    }
  ],
  "users": [
    {
      "id": "string",
      "name": "string",
      "email": "string",
      "avatarUrl": "string",
      "role": "Mahasiswa | Dosen | Prodi Admin | Manajemen Kampus | Super Admin | MABA",
      "studentId": "string",
      "joinDate": "string",
      "bio": "string",
      "studentStatus": "Aktif | Cuti | Lulus | DO | Pendaftaran",
      "gpa": "number",
      "totalSks": "number",
      "facultyId": "string",
      "majorId": "string",
      "badges": ["string"],
      "email_verified_at": "string",
      "created_at": "string",
      "updated_at": "string",
      "remember_token": "string",
      "phoneNumber": "string"
    }
  ]
}
```

### GET /api/students/{id}/grades
**Frontend**: Grades.tsx (useEffect, lines 95-124)
**Purpose**: Get grades for a specific student
**Auth**: Sanctum Bearer Token

Response:
```json
{
  "courses": [
    {
      "id": "string",
      "title": "string",
      "instructor": "string",
      "instructorId": "string",
      "instructorAvatarUrl": "string",
      "sks": number,
      "facultyId": "string",
      "majorId": "string",
      "imageUrl": "string",
      "progress": number,
      "gradeLetter": "string",
      "gradeNumeric": number,
      "completionDate": "string",
      "mode": "string",
      "status": "Published | Draft | Archived",
      "learningObjectives": ["string"],
      "syllabus": [
        {
          "week": number,
          "topic": "string",
          "description": "string"
        }
      ],
      "modules": [
        {
          "id": "string",
          "title": "string",
          "type": "video | pdf | quiz | hafalan | live",
          "description": "string",
          "duration": "string",
          "resourceUrl": "string",
          "captionsUrl": "string",
          "attachmentUrl": "string",
          "startTime": "string",
          "liveUrl": "string"
        }
      ],
      "createdAt": "string",
      "updatedAt": "string"
    }
  ]
}
```

### GET /api/grades
**Frontend**: Grades.tsx (useEffect, lines 95-124)
**Purpose**: Get grades with optional filters
**Auth**: Sanctum Bearer Token

Query Parameters:
- `studentId`: string (optional, filter by student)
- `courseId`: string (optional, filter by course)
- `assignmentId`: string (optional, filter by assignment)
- `semester`: string (optional, filter by semester)
- `year`: number (optional, filter by year)
- `page`: number (optional)
- `limit`: number (optional)

Response:
```json
{
  "grades": [
    {
      "id": "string",
      "user_id": "string",
      "course_id": "string",
      "assignment_id": "string",
      "grade": "number",
      "grade_letter": "string",
      "comments": "string",
      "created_at": "string",
      "updated_at": "string",
      "user": {
        "id": "string",
        "name": "string",
        "email": "string",
        "avatarUrl": "string",
        "role": "Mahasiswa | Dosen | Prodi Admin | Manajemen Kampus | Super Admin | MABA",
        "studentId": "string",
        "joinDate": "string",
        "bio": "string",
        "studentStatus": "Aktif | Cuti | Lulus | DO | Pendaftaran",
        "gpa": "number",
        "totalSks": "number",
        "facultyId": "string",
        "majorId": "string",
        "badges": ["string"],
        "email_verified_at": "string",
        "created_at": "string",
        "updated_at": "string",
        "remember_token": "string",
        "phoneNumber": "string"
      },
      "course": {
        "id": "string",
        "title": "string",
        "instructor": "string",
        "instructorId": "string",
        "instructorAvatarUrl": "string",
        "sks": number,
        "facultyId": "string",
        "majorId": "string",
        "imageUrl": "string",
        "progress": number,
        "gradeLetter": "string",
        "gradeNumeric": number,
        "completionDate": "string",
        "mode": "string",
        "status": "Published | Draft | Archived",
        "learningObjectives": ["string"],
        "syllabus": [
          {
            "week": number,
            "topic": "string",
            "description": "string"
          }
        ],
        "modules": [
          {
            "id": "string",
            "title": "string",
            "type": "video | pdf | quiz | hafalan | live",
            "description": "string",
            "duration": "string",
            "resourceUrl": "string",
            "captionsUrl": "string",
            "attachmentUrl": "string",
            "startTime": "string",
            "liveUrl": "string"
          }
        ],
        "createdAt": "string",
        "updatedAt": "string"
      },
      "assignment": {
        "id": "string",
        "courseId": "string",
        "title": "string",
        "description": "string",
        "dueDate": "string",
        "files": [
          {
            "name": "string",
            "url": "string"
          }
        ],
        "submissions": [
          {
            "studentId": "string",
            "submittedAt": "string",
            "file": {
              "name": "string",
              "url": "string"
            },
            "gradeLetter": "string",
            "gradeNumeric": "number",
            "feedback": "string"
          }
        ],
        "type": "string",
        "category": "Tugas | Ujian",
        "maxScore": "number",
        "instructions": "string",
        "attachments": [],
        "createdAt": "string",
        "updatedAt": "string"
      }
    }
  ],
  "pagination": {
    "page": number,
    "limit": number,
    "total": number,
    "totalPages": number
  }
}
```

### GET /api/courses/{courseId}/grades
**Frontend**: Gradebook.tsx (useEffect, lines 95-124)
**Purpose**: Get grades for all students in a course
**Auth**: Sanctum Bearer Token (Dosen teaching course or Student enrolled)

Response:
```json
{
  "grades": [
    {
      "id": "string",
      "user_id": "string",
      "course_id": "string",
      "assignment_id": "string",
      "grade": "number",
      "grade_letter": "string",
      "comments": "string",
      "created_at": "string",
      "updated_at": "string",
      "user": {
        "id": "string",
        "name": "string",
        "studentId": "string"
      },
      "assignment": {
        "id": "string",
        "title": "string",
        "category": "string"
      }
    }
  ]
}
```

### GET /api/students/{studentId}/grades
**Frontend**: Grades.tsx (useEffect, lines 95-124)
**Purpose**: Get grades for a specific student across courses
**Auth**: Sanctum Bearer Token (Student or authorized Dosen/Admin)

Response:
```json
{
  "grades": [
    {
      "id": "string",
      "user_id": "string",
      "course_id": "string",
      "assignment_id": "string",
      "grade": "number",
      "grade_letter": "string",
      "comments": "string",
      "created_at": "string",
      "updated_at": "string",
      "course": {
        "id": "string",
        "title": "string",
        "instructor": "string"
      },
      "assignment": {
        "id": "string",
        "title": "string",
        "category": "string"
      }
    }
  ]
}
```

### POST /api/grades/batch-update
**Frontend**: Gradebook.tsx (handleSaveGrade in bulk mode)
**Purpose**: Update multiple grades at once
**Auth**: Sanctum Bearer Token (Dosen or Admin)

Request Body:
```json
{
  "grades": [
    {
      "studentId": "string",
      "assignmentId": "string",
      "grade": "number",
      "gradeLetter": "string",
      "comments": "string"
    }
  ]
}
```

Response:
```json
{
  "updatedGrades": [
    {
      "id": "string",
      "user_id": "string",
      "assignment_id": "string",
      "grade": "number",
      "grade_letter": "string",
      "comments": "string"
    }
  ],
  "failedUpdates": [
    {
      "studentId": "string",
      "assignmentId": "string",
      "error": "string"
    }
  ]
}
```

### POST /api/grades
**Frontend**: AssignmentDetailView.tsx (handleSaveGrade, lines 170-179)
**Purpose**: Create or update a grade for a student assignment
**Auth**: Sanctum Bearer Token (Dosen role)

Request Body:
```json
{
  "user_id": "string",
  "course_id": "string",
  "assignment_id": "string",
  "grade": "number",
  "grade_letter": "string",
  "comments": "string"
}
```

Response:
```json
{
  "id": "string",
  "user_id": "string",
  "course_id": "string",
  "assignment_id": "string",
  "grade": "number",
  "grade_letter": "string",
  "comments": "string",
  "created_at": "string",
  "updated_at": "string"
}
```

## Video Lectures & Hafalan

### GET /api/resources/videos
**Frontend**: VideoLectures.tsx (useEffect, lines 17-34)
**Purpose**: Get list of video lectures for all courses
**Auth**: Sanctum Bearer Token

Query Parameters:
- `courseId`: string (optional, filter by course)
- `search`: string (optional, search by title)

Response:
```json
{
  "videos": [
    {
      "module": {
        "id": "string",
        "title": "string",
        "type": "video",
        "description": "string",
        "duration": "string",
        "resourceUrl": "string",
        "captionsUrl": "string",
        "attachmentUrl": "string",
        "startTime": "string",
        "liveUrl": "string"
      },
      "course": {
        "id": "string",
        "title": "string",
        "instructor": "string",
        "instructorId": "string",
        "instructorAvatarUrl": "string",
        "sks": number,
        "facultyId": "string",
        "majorId": "string",
        "imageUrl": "string",
        "progress": number,
        "gradeLetter": "string",
        "gradeNumeric": number,
        "completionDate": "string",
        "mode": "string",
        "status": "Published | Draft | Archived",
        "learningObjectives": ["string"],
        "syllabus": [
          {
            "week": number,
            "topic": "string",
            "description": "string"
          }
        ],
        "modules": [
          {
            "id": "string",
            "title": "string",
            "type": "video | pdf | quiz | hafalan | live",
            "description": "string",
            "duration": "string",
            "resourceUrl": "string",
            "captionsUrl": "string",
            "attachmentUrl": "string",
            "startTime": "string",
            "liveUrl": "string"
          }
        ],
        "createdAt": "string",
        "updatedAt": "string"
      }
    }
  ]
}
```

### POST /api/assignments/{id}/tajwid-analysis
**Frontend**: HafalanRecorder.tsx (handleAnalyze, lines 69-81)
**Purpose**: Analyze hafalan (memorization) submission using AI
**Auth**: Sanctum Bearer Token

Request Body:
```json
{
  "audioBlob": "binary data"
}
```

Response:
```json
{
  "overallScore": "number",
  "feedback": [
    {
      "type": "error | info",
      "rule": "string",
      "comment": "string"
    }
  ]
}
```

### POST /api/assignments/{id}/hafalan-analysis
**Frontend**: HafalanRecorder.tsx (handleSubmit, lines 83-95)
**Purpose**: Submit and analyze hafalan (memorization) with detailed tajwid analysis
**Auth**: Sanctum Bearer Token (Student or Dosen)

Request Body:
```json
{
  "audioUrl": "string",
  "surahNumber": "number",
  "ayahRange": {
    "start": "number",
    "end": "number"
  },
  "submissionId": "string"
}
```

Response:
```json
{
  "id": "string",
  "submissionId": "string",
  "audioUrl": "string",
  "analysis": {
    "accuracyScore": "number",
    "tajwidMistakes": [
      {
        "ayahNumber": "number",
        "position": "number",
        "mistakeType": "string",
        "correction": "string",
        "feedback": "string"
      }
    ],
    "pronunciationScore": "number",
    "fluencyScore": "number"
  },
  "createdAt": "string",
  "completedAt": "string"
}
```

## Management Features

### GET /api/prodi/courses
**Frontend**: ManagementCoursesPage.tsx (useEffect, lines 14-20)
**Purpose**: Get all courses for prodi administration
**Auth**: Sanctum Bearer Token (Prodi Admin role)

Query Parameters:
- `facultyId`: string (optional, filter by faculty)
- `search`: string (optional, search by title, id, or instructor)

Response:
```json
{
  "courses": [
    {
      "id": "string",
      "title": "string",
      "instructor": "string",
      "sks": number,
      "status": "Published | Draft | Archived"
    }
  ]
}
```

### GET /api/prodi/courses/{id}
**Frontend**: ProdiCourseForm.tsx (useEffect, lines 29-50)
**Purpose**: Get detailed information about a specific course for prodi administration
**Auth**: Sanctum Bearer Token (Prodi Admin role)

Response:
```json
{
  "id": "string",
  "title": "string",
  "instructor": "string",
  "sks": number,
  "facultyId": "string",
  "majorId": "string",
  "status": "Published | Draft | Archived"
}
```

### POST /api/prodi/courses
**Frontend**: ProdiCourseForm.tsx (handleSubmit, lines 70-83)
**Purpose**: Create a new course for prodi administration
**Auth**: Sanctum Bearer Token (Prodi Admin role)

Request Body:
```json
{
  "title": "string",
  "instructor": "string",
  "sks": number,
  "facultyId": "string",
  "majorId": "string",
  "status": "Published | Draft | Archived"
}
```

Response:
```json
{
  "id": "string",
  "title": "string",
  "instructor": "string",
  "sks": number,
  "facultyId": "string",
  "majorId": "string",
  "status": "Published | Draft | Archived"
}
```

### PUT /api/prodi/courses/{id}
**Frontend**: ProdiCourseForm.tsx (handleSubmit, lines 70-83)
**Purpose**: Update an existing course for prodi administration
**Auth**: Sanctum Bearer Token (Prodi Admin role)

Request Body:
```json
{
  "title": "string",
  "instructor": "string",
  "sks": number,
  "facultyId": "string",
  "majorId": "string",
  "status": "Published | Draft | Archived"
}
```

Response:
```json
{
  "id": "string",
  "title": "string",
  "instructor": "string",
  "sks": number,
  "facultyId": "string",
  "majorId": "string",
  "status": "Published | Draft | Archived"
}
```

### POST /api/courses/{id}/students/{studentId}/complete
**Frontend**: Gradebook.tsx (handleMarkAsComplete, lines 140-147)
**Purpose**: Mark a student as having completed a course
**Auth**: Sanctum Bearer Token (Dosen role)

Response:
```json
{
  "studentId": "string",
  "courseId": "string",
  "progress": 100,
  "status": "Completed",
  "completionDate": "string"
}
```

### POST /api/students/{id}/badges
**Frontend**: Gradebook.tsx (handleAwardBadge, lines 157-164)
**Purpose**: Award a badge to a student
**Auth**: Sanctum Bearer Token (Dosen role)

Request Body:
```json
{
  "badgeId": "string"
}
```

Response:
```json
{
  "studentId": "string",
  "badges": ["string"]
}
```