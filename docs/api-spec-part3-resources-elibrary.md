# API Specification Part 3 - Resources, E-Library & Islamic Content


### GET /api/resources/islamic/search
**Frontend**: IslamicResources.tsx
**Purpose**: Search across all Islamic content (Quran, Hadith, Doa)
**Auth**: Public

Query Parameters:
- `query`: string (search term)
- `contentType`: string (quran, hadith, doa, all)
- `language`: string (arabic, translation, both)

Response:
```json
{
  "status": true,
  "data": [
    {
      "type": "quran | hadith | doa",
      "id": "string",
      "title": "string",
      "arabic": "string",
      "translation": "string",
      "reference": "string"
    }
  ]
}
```

## AI Chatbot (Ustadz AI)

### POST /api/ai/chat
**Frontend**: UstadzAI.tsx (lines 31-49)
**Purpose**: Send message to Islamic AI chatbot and get response
**Auth**: Sanctum Bearer Token (authenticated users)

Request:
```json
{
  "message": "Apa hukum shalat jamaah?"
}
```

Response:
```json
{
  "status": "success",
  "response": "Shalat jamaah hukumnya..."
}
```

### GET /api/ai/chat/conversations
**Frontend**: UstadzAI.tsx
**Purpose**: Get user's chat history with the Islamic AI
**Auth**: Sanctum Bearer Token (authenticated users)

Query Parameters:
- `page`: number (optional)
- `limit`: number (optional)
- `filter`: string (optional, subject or topic filter)

Response:
```json
{
  "conversations": [
    {
      "id": "string",
      "title": "string",
      "createdAt": "string",
      "lastMessage": "string",
      "messages": [
        {
          "id": "string",
          "role": "user | assistant",
          "content": "string",
          "timestamp": "string"
        }
      ]
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

### POST /api/ai/chat/conversation
**Frontend**: UstadzAI.tsx
**Purpose**: Create new AI conversation with context
**Auth**: Sanctum Bearer Token (authenticated users)

Request:
```json
{
  "initialMessage": "string",
  "context": {
    "subject": "string",
    "topic": "string",
    "gradeLevel": "string",
    "preferences": "object"
  }
}
```

Response:
```json
{
  "conversationId": "string",
  "title": "string",
  "createdAt": "string",
  "message": {
    "id": "string",
    "role": "assistant",
    "content": "string",
    "timestamp": "string"
  }
}
```

## E-Library

### GET /api/elibrary/resources
**Frontend**: ELibrary.tsx (lines 105-138)
**Purpose**: Get list of e-library resources
**Auth**: Sanctum Bearer Token (all authenticated users)

Query Parameters:
- `keyword`: string (search in title/description)
- `author`: string (filter by author)
- `year`: number (filter by year)
- `type`: string (book/journal)
- `page`: number
- `limit`: number

Response:
```json
{
  "resources": [
    {
      "id": "1",
      "title": "Sejarah Islam",
      "author": "Dr. Ahmad",
      "year": 2020,
      "type": "book",
      "description": "Buku tentang sejarah perkembangan Islam",
      "coverUrl": "https://example.com/cover.jpg",
      "sourceType": "upload",
      "sourceUrl": "https://example.com/book.pdf",
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "total": 25
  }
}
```

### GET /api/elibrary/resources/{id}
**Frontend**: ELibrary.tsx
**Purpose**: Get specific e-library resource
**Auth**: Sanctum Bearer Token (all authenticated users)

Response:
```json
{
  "resource": {
    "id": "1",
    "title": "Sejarah Islam",
    "author": "Dr. Ahmad",
    "year": 2020,
    "type": "book",
    "description": "Buku tentang sejarah perkembangan Islam",
    "coverUrl": "https://example.com/cover.jpg",
    "sourceType": "upload",
    "sourceUrl": "https://example.com/book.pdf",
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
  }
}
```

### POST /api/elibrary/resources
**Frontend**: ResourceForm.tsx (lines 60-93)
**Purpose**: Create new e-library resource
**Auth**: Sanctum Bearer Token (admin/lecturer roles)

Request:
```json
{
  "title": "Sejarah Islam",
  "author": "Dr. Ahmad",
  "year": 2020,
  "type": "book",
  "description": "Buku tentang sejarah perkembangan Islam",
  "coverUrl": "https://example.com/cover.jpg",
  "sourceType": "upload",
  "sourceUrl": "https://example.com/book.pdf"
}
```

Response:
```json
{
  "resource": {
    "id": "1",
    "title": "Sejarah Islam",
    "author": "Dr. Ahmad",
    "year": 2020,
    "type": "book",
    "description": "Buku tentang sejarah perkembangan Islam",
    "coverUrl": "https://example.com/cover.jpg",
    "sourceType": "upload",
    "sourceUrl": "https://example.com/book.pdf",
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
  }
}
```### PUT /api/elibrary/resources/{id}
**Frontend**: ResourceForm.tsx (lines 60-93)
**Purpose**: Update existing e-library resource
**Auth**: Sanctum Bearer Token (admin/lecturer roles)

Request:
```json
{
  "title": "Sejarah Islam",
  "author": "Dr. Ahmad",
  "year": 2020,
  "type": "book",
  "description": "Buku tentang sejarah perkembangan Islam",
  "coverUrl": "https://example.com/cover.jpg",
  "sourceType": "upload",
  "sourceUrl": "https://example.com/book.pdf"
}
```

Response:
```json
{
  "resource": {
    "id": "1",
    "title": "Sejarah Islam",
    "author": "Dr. Ahmad",
    "year": 2020,
    "type": "book",
    "description": "Buku tentang sejarah perkembangan Islam",
    "coverUrl": "https://example.com/cover.jpg",
    "sourceType": "upload",
    "sourceUrl": "https://example.com/book.pdf",
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
  }
}
```

### DELETE /api/elibrary/resources/{id}
**Frontend**: ManageELibrary.tsx (lines 81-90)
**Purpose**: Delete e-library resource
**Auth**: Sanctum Bearer Token (admin roles)

Response:
```json
{
  "status": "success",
  "message": "Resource deleted successfully"
}
```

### POST /api/elibrary/resources/{id}/toggle-bookmark
**Frontend**: ELibrary.tsx (lines 105-138)
**Purpose**: Add/remove resource to/from user's personal library
**Auth**: Sanctum Bearer Token (authenticated users)

Response:
```json
{
  "status": "success",
  "isBookmarked": true
}
```

### GET /api/elibrary/my-library
**Frontend**: ELibrary.tsx
**Purpose**: Get user's personal library resources
**Auth**: Sanctum Bearer Token (authenticated users)

Response:
```json
{
  "resources": [
    {
      "id": "1",
      "title": "Sejarah Islam",
      "author": "Dr. Ahmad",
      "year": 2020,
      "type": "book",
      "description": "Buku tentang sejarah perkembangan Islam",
      "coverUrl": "https://example.com/cover.jpg",
      "sourceType": "upload",
      "sourceUrl": "https://example.com/book.pdf"
    }
  ]
}
```

## Announcements

### GET /api/announcements
**Frontend**: AnnouncementsPage.tsx (lines 33-191)
**Purpose**: Get paginated list of announcements
**Auth**: Sanctum Bearer Token (all authenticated users)

Query Parameters:
- `page`: number
- `limit`: number (default: 10)
- `category`: string (Akademik/Kampus/Mata Kuliah)
- `search`: string (search in title/content)

Response:
```json
{
  "announcements": [
    {
      "id": "1",
      "title": "Pengumuman Libur Akhir Tahun",
      "content": "Kampus akan libur pada tanggal 28-31 Desember 2024...",
      "authorName": "Admin Kampus",
      "author_id": "admin123",
      "timestamp": "2024-12-01T10:00:00Z",
      "category": "Kampus",
      "course_id": null,
      "created_at": "2024-12-01T10:00:00Z",
      "updated_at": "2024-12-01T10:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "total": 25
  }
}
```

### POST /api/announcements
**Frontend**: AnnouncementsPage.tsx (lines 64-82)
**Purpose**: Create new announcement
**Auth**: Sanctum Bearer Token (Dosen/Prodi Admin/Manajemen Kampus/Super Admin)

Request:
```json
{
  "title": "Pengumuman Libur Akhir Tahun",
  "content": "Kampus akan libur pada tanggal 28-31 Desember 2024...",
  "category": "Kampus"
}
```

Response:
```json
{
  "announcement": {
    "id": "1",
    "title": "Pengumuman Libur Akhir Tahun",
    "content": "Kampus akan libur pada tanggal 28-31 Desember 2024...",
    "authorName": "Admin Kampus",
    "author_id": "admin123",
    "timestamp": "2024-12-01T10:00:00Z",
    "category": "Kampus",
    "course_id": null,
    "created_at": "2024-12-01T10:00:00Z",
    "updated_at": "2024-12-01T10:00:00Z"
  }
}
```

### PUT /api/announcements/{id}
**Frontend**: AnnouncementsPage.tsx
**Purpose**: Update existing announcement
**Auth**: Sanctum Bearer Token (announcement author or admin roles)

Request:
```json
{
  "title": "Pengumuman Libur Akhir Tahun",
  "content": "Kampus akan libur pada tanggal 28-31 Desember 2024...",
  "category": "Kampus"
}
```

Response:
```json
{
  "announcement": {
    "id": "1",
    "title": "Pengumuman Libur Akhir Tahun",
    "content": "Kampus akan libur pada tanggal 28-31 Desember 2024...",
    "authorName": "Admin Kampus",
    "author_id": "admin123",
    "timestamp": "2024-12-01T10:00:00Z",
    "category": "Kampus",
    "course_id": null,
    "created_at": "2024-12-01T10:00:00Z",
    "updated_at": "2024-12-01T10:00:00Z"
  }
}
```

### DELETE /api/announcements/{id}
**Frontend**: AnnouncementsPage.tsx
**Purpose**: Delete announcement
**Auth**: Sanctum Bearer Token (announcement author or admin roles)

Response:
```json
{
  "status": "success",
  "message": "Announcement deleted successfully"
}
```

## Discussion Forum

### GET /api/forum/threads
**Frontend**: DiscussionForum.tsx (lines 14-196)
**Purpose**: Get list of discussion threads for a course
**Auth**: Sanctum Bearer Token (all authenticated users)

Query Parameters:
- `courseId`: string
- `search`: string (search in thread title)

Response:
```json
{
  "threads": [
    {
      "id": "1",
      "courseId": "course123",
      "title": "Diskusi materi tajwid",
      "authorId": "student123",
      "createdAt": "2024-12-01T10:00:00Z",
      "isPinned": false,
      "isClosed": false,
      "posts": [
        {
          "id": "1",
          "authorId": "student123",
          "createdAt": "2024-12-01T10:00:00Z",
          "content": "Bagaimana hukum tajwid untuk huruf mim sukun?"
        }
      ]
    }
  ]
}
```

### POST /api/forum/threads
**Frontend**: DiscussionForum.tsx (lines 45-70)
**Purpose**: Create new discussion thread
**Auth**: Sanctum Bearer Token (all authenticated users)

Request:
```json
{
  "courseId": "course123",
  "title": "Diskusi materi tajwid",
  "content": "Bagaimana hukum tajwid untuk huruf mim sukun?"
}
```

Response:
```json
{
  "thread": {
    "id": "1",
    "courseId": "course123",
    "title": "Diskusi materi tajwid",
    "authorId": "student123",
    "createdAt": "2024-12-01T10:00:00Z",
    "isPinned": false,
    "isClosed": false,
    "posts": [
      {
        "id": "1",
        "authorId": "student123",
        "createdAt": "2024-12-01T10:00:00Z",
        "content": "Bagaimana hukum tajwid untuk huruf mim sukun?"
      }
    ]
  }
}
```

### POST /api/forum/threads/{threadId}/posts
**Frontend**: DiscussionForum.tsx (lines 72-93)
**Purpose**: Add reply to existing discussion thread
**Auth**: Sanctum Bearer Token (all authenticated users)

Request:
```json
{
  "content": "Menurut aturan tajwid, mim sukun memiliki hukum..."
}
```

Response:
```json
{
  "post": {
    "id": "2",
    "authorId": "lecturer123",
    "createdAt": "2024-12-01T11:00:00Z",
    "content": "Menurut aturan tajwid, mim sukun memiliki hukum..."
  }
}
```

### PUT /api/forum/threads/{threadId}/pin
**Frontend**: DiscussionForum.tsx
**Purpose**: Pin/unpin discussion thread
**Auth**: Sanctum Bearer Token (Dosen/Prodi Admin/Super Admin)

Request:
```json
{
  "isPinned": true
}
```

Response:
```json
{
  "status": "success",
  "thread": {
    "id": "1",
    "courseId": "course123",
    "title": "Diskusi materi tajwid",
    "authorId": "student123",
    "createdAt": "2024-12-01T10:00:00Z",
    "isPinned": true,
    "isClosed": false
  }
}
```

### PUT /api/forum/threads/{threadId}/close
**Frontend**: DiscussionForum.tsx
**Purpose**: Close/open discussion thread
**Auth**: Sanctum Bearer Token (Dosen/Prodi Admin/Super Admin)

Request:
```json
{
  "isClosed": true
}
```

Response:
```json
{
  "status": "success",
  "thread": {
    "id": "1",
    "courseId": "course123",
    "title": "Diskusi materi tajwid",
    "authorId": "student123",
    "createdAt": "2024-12-01T10:00:00Z",
    "isPinned": false,
    "isClosed": true
  }
}
```

## Calendar

### GET /api/calendar/events
**Frontend**: Calendar.tsx (lines 15-71)
**Purpose**: Get calendar events for user
**Auth**: Sanctum Bearer Token (authenticated users)

Query Parameters:
- `startDate`: string (ISO date format)
- `endDate`: string (ISO date format)
- `types`: string (comma-separated: assignment, live-class, academic)

Response:
```json
{
  "events": [
    {
      "id": "1",
      "type": "assignment",
      "date": "2025-01-15T00:00:00Z",
      "title": "Tugas Hadits",
      "courseTitle": "Ilmu Hadits",
      "courseId": "course123",
      "assignmentId": "assignment123"
    },
    {
      "id": "2",
      "type": "live-class",
      "date": "2025-01-16T09:00:00Z",
      "title": "Kelas Live Tajwid",
      "courseTitle": "Tajwid",
      "courseId": "course456",
      "startTime": "2025-01-16T09:00:00Z",
      "endTime": "2025-01-16T11:00:00Z",
      "liveUrl": "https://example.com/class"
    },
    {
      "id": "3",
      "type": "academic",
      "date": "2025-01-20T00:00:00Z",
      "title": "Libur Akhir Tahun",
      "category": "holiday"
    }
  ]
}
```

### POST /api/calendar/events
**Frontend**: Calendar.tsx
**Purpose**: Create new calendar event (admin only)
**Auth**: Sanctum Bearer Token (admin roles)

Request:
```json
{
  "titleKey": "calendar_holiday_new_year",
  "startDate": "2025-01-01",
  "endDate": "2025-01-01",
  "category": "holiday"
}
```

Response:
```json
{
  "event": {
    "id": "1",
    "titleKey": "calendar_holiday_new_year",
    "startDate": "2025-01-01",
    "endDate": "2025-01-01",
    "category": "holiday"
  }
}
```

### PUT /api/calendar/events/{id}
**Frontend**: Calendar.tsx
**Purpose**: Update existing calendar event
**Auth**: Sanctum Bearer Token (admin roles)

Request:
```json
{
  "titleKey": "calendar_holiday_new_year",
  "startDate": "2025-01-01",
  "endDate": "2025-01-01",
  "category": "holiday"
}
```

Response:
```json
{
  "event": {
    "id": "1",
    "titleKey": "calendar_holiday_new_year",
    "startDate": "2025-01-01",
    "endDate": "2025-01-01",
    "category": "holiday"
  }
}
```

### DELETE /api/calendar/events/{id}
**Frontend**: Calendar.tsx
**Purpose**: Delete calendar event
**Auth**: Sanctum Bearer Token (admin roles)

Response:
```json
{
  "status": "success",
  "message": "Event deleted successfully"
}
```

## Hafalan Recorder (Tajwid Analysis)

### POST /api/tajwid/analyze
**Frontend**: HafalanRecorder.tsx (lines 69-81)
**Purpose**: Analyze recorded audio for tajwid feedback
**Auth**: Sanctum Bearer Token (authenticated users)

Request (multipart/form-data):
- `audio`: file (audio file)
- `assignmentId`: string

Response:
```json
{
  "feedback": {
    "overallScore": 85,
    "feedback": [
      {
        "type": "error",
        "rule": "Idgham Mutamathilain",
        "comment": "Pronunciation of mim needs improvement"
      }
    ]
  }
}
```

### POST /api/submissions
**Frontend**: HafalanRecorder.tsx (lines 83-95)
**Purpose**: Submit hafalan recording for assignment
**Auth**: Sanctum Bearer Token (authenticated users)

Request (multipart/form-data):
- `file`: file (audio file)
- `assignmentId`: string
- `notes`: string (optional)

Response:
```json
{
  "submission": {
    "id": "1",
    "studentId": "student123",
    "submittedAt": "2024-12-01T10:00:00Z",
    "file": {
      "name": "hafalan_assignment123.wav",
      "url": "https://example.com/submission/1"
    },
    "gradeLetter": null,
    "gradeNumeric": null,
    "feedback": null
  }
}
```

## FAQ Section

### GET /api/faq
**Frontend**: FaqContent.tsx (lines 152-196)
**Purpose**: Get FAQ items filtered by user role
**Auth**: Sanctum Bearer Token (authenticated users)

Query Parameters:
- `role`: string (user role to filter FAQs for)
- `search`: string (search in questions/answers)

Response:
```json
{
  "faqs": [
    {
      "q": "faq_q_general_theme",
      "a": "faq_a_general_theme",
      "roles": ["Mahasiswa", "Dosen", "Prodi Admin", "Manajemen Kampus", "Super Admin"]
    }
  ]
}
```

## Contact Section

### POST /api/contact
**Frontend**: ContactSection.tsx
**Purpose**: Send contact message to support team
**Auth**: Sanctum Bearer Token (authenticated users) or Public

Request:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "subject": "Technical Issue",
  "message": "I'm having trouble accessing the video lectures..."
}
```

Response:
```json
{
  "status": "success",
  "message": "Thank you for contacting us. We'll respond to your inquiry soon."
}
```

## File Upload Endpoints

### POST /api/upload
**Frontend**: ResourceForm.tsx, HafalanRecorder.tsx
**Purpose**: Upload files (documents, audio recordings)
**Auth**: Sanctum Bearer Token (authenticated users)

Request (multipart/form-data):
- `file`: file (any supported file type)

Response:
```json
{
  "file": {
    "id": "1",
    "name": "document.pdf",
    "url": "https://example.com/uploads/document.pdf",
    "type": "application/pdf",
    "size": 123456
  }
}
```

## Frontend Notes

- Video player component uses direct URLs for video playback - backend needed for managing video resources
- User-generated content (announcements, forum posts, submissions) requires backend endpoints
- E-library system requires full CRUD operations for managing resources
- Calendar events require backend for academic events, but assignments and classes are derived from existing data