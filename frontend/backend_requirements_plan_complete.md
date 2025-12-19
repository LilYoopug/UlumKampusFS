# Complete Backend Requirements Plan for UlumCampus Platform

## Project Overview
UlumCampus is a comprehensive Islamic educational platform featuring multiple user roles, course management, assignment systems, Islamic resources (Quran, Hadith, Doa), and specialized academic management features for Islamic studies education.

## Database Schema Requirements

### 1. Users Table
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('student_id')->nullable()->unique();
    $table->enum('role', ['Mahasiswa', 'Dosen', 'Prodi Admin', 'Manajemen Kampus', 'Super Admin']);
    $table->string('avatar_url')->nullable();
    $table->string('phone_number')->nullable();
    $table->string('faculty_id')->nullable();
    $table->string('major_id')->nullable();
    $table->text('bio')->nullable();
    $table->enum('student_status', ['Aktif', 'Cuti', 'Lulus', 'DO'])->default('Aktif');
    $table->decimal('gpa', 3, 2)->default(0.00);
    $table->integer('total_sks')->default(0);
    $table->json('badges')->nullable();
    $table->rememberToken();
    $table->timestamps();
});
```

### 2. Faculties Table
```php
Schema::create('faculties', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->string('name');
    $table->text('description');
    $table->timestamps();
});
```

### 3. Majors Table
```php
Schema::create('majors', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->string('name');
    $table->string('faculty_id');
    $table->foreign('faculty_id')->references('id')->on('faculties');
    $table->timestamps();
});
```

### 4. Courses Table
```php
Schema::create('courses', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->string('title');
    $table->string('instructor');
    $table->string('instructor_id')->nullable();
    $table->string('faculty_id');
    $table->string('major_id')->nullable();
    $table->integer('sks');
    $table->text('description');
    $table->string('image_url')->nullable();
    $table->decimal('progress', 5, 2)->nullable();
    $table->string('grade_letter')->nullable();
    $table->decimal('grade_numeric', 5, 2)->nullable();
    $table->date('completion_date')->nullable();
    $table->enum('mode', ['Live', 'VOD']);
    $table->enum('status', ['Published', 'Draft', 'Archived']);
    $table->json('learning_objectives')->nullable();
    $table->json('syllabus')->nullable();
    $table->json('modules')->nullable();
    $table->string('instructor_avatar_url')->nullable();
    $table->timestamps();
});
```

### 5. Course Enrollments Table
```php
Schema::create('course_enrollments', function (Blueprint $table) {
    $table->id();
    $table->string('course_id');
    $table->string('user_id');
    $table->decimal('progress', 5, 2)->default(0);
    $table->timestamps();
    
    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
```

### 6. Assignments Table
```php
Schema::create('assignments', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->string('course_id');
    $table->string('title');
    $table->text('description');
    $table->timestamp('due_date');
    $table->json('files')->nullable();
    $table->string('type')->default('file'); // file, hafalan, quiz
    $table->string('category')->default('Tugas'); // Tugas, Ujian
    $table->integer('max_score')->nullable();
    $table->text('instructions')->nullable();
    $table->json('attachments')->nullable();
    $table->timestamps();
    
    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
});
```

### 7. Assignment Submissions Table
```php
Schema::create('assignment_submissions', function (Blueprint $table) {
    $table->id();
    $table->string('assignment_id');
    $table->string('user_id');
    $table->timestamp('submitted_at')->nullable();
    $table->json('file')->nullable(); // {name, url}
    $table->string('grade_letter')->nullable();
    $table->decimal('grade_numeric', 5, 2)->nullable();
    $table->text('feedback')->nullable();
    $table->json('hafalan_data')->nullable(); // For memorization submissions
    $table->timestamps();
    
    $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
```

### 8. Library Resources Table
```php
Schema::create('library_resources', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->string('title');
    $table->string('author');
    $table->integer('year');
    $table->enum('type', ['book', 'journal']);
    $table->text('description');
    $table->string('cover_url');
    $table->enum('source_type', ['upload', 'link', 'embed'])->nullable();
    $table->string('source_url')->nullable();
    $table->timestamps();
});
```

### 9. User Library Table
```php
Schema::create('user_library', function (Blueprint $table) {
    $table->id();
    $table->string('user_id');
    $table->string('resource_id');
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('resource_id')->references('id')->on('library_resources')->onDelete('cascade');
});
```

### 10. Announcements Table
```php
Schema::create('announcements', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->string('title');
    $table->text('content');
    $table->string('author_name');
    $table->string('author_id')->nullable();
    $table->string('course_id')->nullable();
    $table->enum('category', ['Kampus', 'Akademik', 'Mata Kuliah']);
    $table->timestamps();
});
```

### 11. Notifications Table
```php
Schema::create('notifications', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->string('user_id');
    $table->string('type'); // forum, grade, assignment, announcement
    $table->string('message_key');
    $table->string('context');
    $table->json('link'); // {page, params}
    $table->boolean('is_read')->default(false);
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
```

### 12. Discussion Threads Table
```php
Schema::create('discussion_threads', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->string('course_id');
    $table->string('title');
    $table->string('author_id');
    $table->boolean('is_pinned')->default(false);
    $table->boolean('is_closed')->default(false);
    $table->timestamps();
    
    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
    $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
});
```

### 13. Discussion Posts Table
```php
Schema::create('discussion_posts', function (Blueprint $table) {
    $table->id();
    $table->string('thread_id');
    $table->string('author_id');
    $table->text('content');
    $table->timestamps();
    
    $table->foreign('thread_id')->references('id')->on('discussion_threads')->onDelete('cascade');
    $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
});
```

### 14. Grades Table
```php
Schema::create('grades', function (Blueprint $table) {
    $table->id();
    $table->string('user_id');
    $table->string('course_id');
    $table->string('assignment_id')->nullable();
    $table->decimal('grade', 5, 2);
    $table->string('grade_letter')->nullable();
    $table->text('comments')->nullable();
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
    $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
});
```

### 15. Calendar Events Table
```php
Schema::create('calendar_events', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->string('title_key');
    $table->timestamp('start_date');
    $table->timestamp('end_date')->nullable();
    $table->enum('category', ['holiday', 'exam', 'registration', 'academic']);
    $table->timestamps();
});
```

### 16. Course Modules Table
```php
Schema::create('course_modules', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->string('course_id');
    $table->string('title');
    $table->enum('type', ['video', 'pdf', 'quiz', 'hafalan', 'live']);
    $table->text('description')->nullable();
    $table->string('duration')->nullable();
    $table->string('resource_url')->nullable();
    $table->string('captions_url')->nullable();
    $table->string('attachment_url')->nullable();
    $table->timestamp('start_time')->nullable();
    $table->string('live_url')->nullable();
    $table->integer('order')->default(0);
    $table->timestamps();
    
    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
});
```

## API Endpoints Requirements

### Authentication Endpoints

#### POST /api/login
**Request:**
```json
{
  "email": "ahmad.faris@student.ulumcampus.com",
  "password": "mahasiswa123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "name": "Ahmad Faris",
      "email": "ahmad.faris@student.ulumcampus.com",
      "student_id": "UC2024001",
      "role": "Mahasiswa",
      "avatar_url": "https://picsum.photos/seed/ahmad/100/100",
      "faculty_id": "syariah",
      "major_id": "hes"
    }
  }
}
```

#### POST /api/register
**Request:**
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "securepassword",
  "password_confirmation": "securepassword"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 2,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "student_id": "UC2025001",
      "role": "Mahasiswa",
      "avatar_url": "https://picsum.photos/seed/john/100/100",
      "created_at": "2024-12-09T19:00:00.000000Z"
    }
  }
}
```

### Course Management Endpoints

#### GET /api/courses
**Query Parameters:**
- facultyId (optional)
- status (optional)
- search (optional)
- majorId (optional)

**Response:**
```json
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": "AQ101",
        "title": "Pengantar Aqidah Islamiyah",
        "instructor": "Dr. Yusuf Al-Fatih",
        "faculty_id": "ushuluddin",
        "major_id": "aqidah",
        "sks": 3,
        "description": "Membahas pilar-pilar fundamental keimanan dalam Islam...",
        "image_url": "https://picsum.photos/seed/aqidah/600/400",
        "progress": 75,
        "grade_letter": "A-",
        "grade_numeric": 3.7,
        "mode": "VOD",
        "status": "Published",
        "learning_objectives": [
          "Mampu menjelaskan pilar-pilar fundamental keimanan dalam Islam.",
          "Memahami konsep Tauhid dan pembagiannya secara komprehensif."
        ],
        "syllabus": [
          {
            "week": 1,
            "topic": "Pengantar Ilmu Aqidah",
            "description": "Definisi, urgensi, dan sumber-sumber utama dalam mempelajari aqidah Islamiyah."
          }
        ],
        "modules": [
          {
            "id": "m1",
            "title": "Makna Syahadatain",
            "type": "video",
            "duration": "45min",
            "description": "Membedah makna dan konsekuensi dari dua kalimat syahadat...",
            "resource_url": "https://example.com/video.mp4",
            "captions_url": "https://example.com/captions.vtt"
          }
        ],
        "instructor_avatar_url": "https://picsum.photos/seed/yusuf/100/100"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 10,
      "total": 25
    }
  }
}
```

#### POST /api/courses
**Request:**
```json
{
  "title": "Fiqh Muamalat Kontemporer",
  "instructor": "Dr. Aisyah Hasanah",
  "faculty_id": "syariah",
  "major_id": "hes",
  "sks": 4,
  "description": "Analisis transaksi keuangan modern dari perspektif fiqh...",
  "image_url": "https://example.com/course-image.jpg",
  "mode": "Live",
  "status": "Draft",
  "learning_objectives": [
    "Mampu mengidentifikasi unsur Riba, Gharar, dan Maysir dalam transaksi modern."
  ],
  "syllabus": [
    {
      "week": 1,
      "topic": "Kaidah Fiqh Muamalat",
      "description": "Mempelajari kaidah-kaidah kunci seperti 'Al-ashlu fil mu'amalah al-ibahah'"
    }
  ],
  "modules": [
    {
      "title": "Pengantar Fiqh Muamalat",
      "type": "video",
      "duration": "50min",
      "description": "Memahami kaidah-kaidah dasar dan prinsip umum dalam transaksi maliyah Islam.",
      "resource_url": "https://example.com/intro-video.mp4"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "FQ201",
    "title": "Fiqh Muamalat Kontemporer",
    "instructor": "Dr. Aisyah Hasanah",
    "faculty_id": "syariah",
    "major_id": "hes",
    "sks": 4,
    "description": "Analisis transaksi keuangan modern dari perspektif fiqh...",
    "image_url": "https://example.com/course-image.jpg",
    "progress": null,
    "grade_letter": null,
    "grade_numeric": null,
    "mode": "Live",
    "status": "Draft",
    "learning_objectives": [
      "Mampu mengidentifikasi unsur Riba, Gharar, dan Maysir dalam transaksi modern."
    ],
    "syllabus": [
      {
        "week": 1,
        "topic": "Kaidah Fiqh Muamalat",
        "description": "Mempelajari kaidah-kaidah kunci seperti 'Al-ashlu fil mu'amalah al-ibahah'"
      }
    ],
    "modules": [
      {
        "id": "MOD001",
        "title": "Pengantar Fiqh Muamalat",
        "type": "video",
        "duration": "50min",
        "description": "Memahami kaidah-kaidah dasar dan prinsip umum dalam transaksi maliyah Islam.",
        "resource_url": "https://example.com/intro-video.mp4",
        "order": 0
      }
    ],
    "instructor_avatar_url": "https://picsum.photos/seed/aisyah/100",
    "created_at": "2024-12-09T19:00:00.000000Z",
    "updated_at": "2024-12-09T19:00:00.000000Z"
  }
}
```

### Assignment Management Endpoints

#### GET /api/assignments
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "ASG001",
      "course_id": "AQ101",
      "title": "Esai Reflektif Pilar Keimanan",
      "description": "Tulis esai 2 halaman yang merefleksikan pemahaman Anda tentang salah satu dari enam pilar keimanan...",
      "due_date": "2024-12-16T23:59:00.000000Z",
      "files": [
        {
          "name": "Panduan Penulisan Esai.pdf",
          "url": "https://example.com/panduan-esai.pdf"
        }
      ],
      "type": "file",
      "category": "Tugas",
      "max_score": 100,
      "instructions": "Gunakan minimal 3 referensi dari Al-Qur'an atau Hadis Shahih.",
      "attachments": [],
      "submissions": [
        {
          "id": 1,
          "user_id": 1,
          "submitted_at": "2024-12-02T10:30:00.000000Z",
          "file": {
            "name": "Esai_Pilar_Keimanan_Ahmad_Faris.pdf",
            "url": "https://example.com/submission-123.pdf"
          },
          "grade_letter": "A",
          "grade_numeric": 95,
          "feedback": "Kerja yang sangat baik, Ahmad! ..."
        }
      ],
      "created_at": "2024-12-02T09:00:00.000000Z",
      "updated_at": "2024-12-02T10:30:00.000000Z"
    }
  ]
}
```

#### POST /api/assignments/{assignmentId}/submissions
**Request:**
```json
{
  "file": {
    "name": "Assignment_Submission.pdf",
    "url": "https://storage.example.com/uploads/assignment_123.pdf"
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "assignment_id": "ASG001",
    "user_id": 1,
    "submitted_at": "2024-12-09T19:30:00.000000Z",
    "file": {
      "name": "Assignment_Submission.pdf",
      "url": "https://storage.example.com/uploads/assignment_123.pdf"
    },
    "grade_letter": null,
    "grade_numeric": null,
    "feedback": null
  }
}
```

### Library Resource Endpoints

#### GET /api/library-resources
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "lib001",
      "title": "Fiqh Al-Muamalat Al-Maliyah Al-Muashirah",
      "author": "Prof. Dr. Wahbah Az-Zuhaili",
      "year": 2002,
      "type": "book",
      "description": "Buku komprehensif yang membahas transaksi keuangan modern dari perspektif fiqh...",
      "cover_url": "https://picsum.photos/seed/fiqh-book/300/400",
      "source_type": "link",
      "source_url": "https://example.com/book-link",
      "created_at": "2024-12-01T10:00:00.000000Z",
      "updated_at": "2024-12-01T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 10,
    "total": 15
  }
}
```

#### POST /api/library-resources
**Request:**
```json
{
  "title": "Islamic Finance Principles",
  "author": "Dr. Muhammad Ayub",
  "year": 2020,
  "type": "book",
  "description": "Comprehensive guide to Islamic finance principles and applications.",
  "cover_url": "https://example.com/cover.jpg",
  "source_type": "upload",
  "source_url": "https://storage.example.com/books/islamic_finance.pdf"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "lib002",
    "title": "Islamic Finance Principles",
    "author": "Dr. Muhammad Ayub",
    "year": 2020,
    "type": "book",
    "description": "Comprehensive guide to Islamic finance principles and applications.",
    "cover_url": "https://example.com/cover.jpg",
    "source_type": "upload",
    "source_url": "https://storage.example.com/books/islamic_finance.pdf",
    "created_at": "2024-12-09T19:45:00.000000Z",
    "updated_at": "2024-12-09T19:45:00.000000Z"
  }
}
```

### Announcement Endpoints

#### GET /api/announcements
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "AN001",
      "title": "Perubahan Jadwal Ujian Tengah Semester",
      "content": "Assalamu'alaikum Warahmatullahi Wabarakatuh...",
      "author_name": "Dr. Aisyah Hasanah",
      "author_id": "PRODI01",
      "course_id": null,
      "category": "Akademik",
      "created_at": "2024-12-06T10:00:00.000000Z",
      "updated_at": "2024-12-06T10:00:00.000000Z"
    }
  ]
}
```

### Discussion Forum Endpoints

#### GET /api/discussion-threads
**Query Parameters:**
- course_id (optional)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "DT001",
      "course_id": "AQ101",
      "title": "Pertanyaan tentang Batasan Sifat Istiwa",
      "author_id": 1,
      "is_pinned": true,
      "is_closed": false,
      "posts_count": 3,
      "created_at": "2024-12-07T14:30:00.000000Z",
      "updated_at": "2024-12-08T09:15:00.000000Z",
      "author": {
        "name": "Ahmad Faris",
        "avatar_url": "https://picsum.photos/seed/ahmad/100/100"
      }
    }
  ]
}
```

#### POST /api/discussion-threads
**Request:**
```json
{
  "course_id": "AQ101",
  "title": "Diskusi tentang Hukum Menggunakan Produk Haram",
  "content": "Assalamu'alaikum, saya ingin bertanya tentang hukum menggunakan produk yang berasal dari perusahaan yang tidak syariah..."
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "DT002",
    "course_id": "AQ101",
    "title": "Diskusi tentang Hukum Menggunakan Produk Haram",
    "author_id": 1,
    "is_pinned": false,
    "is_closed": false,
    "created_at": "2024-12-09T20:00:00.000000Z",
    "updated_at": "2024-12-09T20:00:00.000000Z"
  }
}
```

### Notification Endpoints

#### GET /api/notifications
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "N001",
      "user_id": 1,
      "type": "forum",
      "message_key": "notification_forum_reply",
      "context": "Dr. Yusuf Al-Fatih",
      "link": {
        "page": "course-detail",
        "params": {
          "courseId": "AQ101",
          "initialTab": "discussion",
          "threadId": "DT001"
        }
      },
      "is_read": false,
      "created_at": "2024-12-09T18:30:00.000000Z",
      "updated_at": "2024-12-09T18:30:00.000000Z"
    },
    {
      "id": "N002",
      "user_id": 1,
      "type": "grade",
      "message_key": "notification_grade_update",
      "context": "Presentasi Kontribusi Ilmuwan Muslim",
      "link": {
        "page": "assignments",
        "params": {
          "assignmentId": "ASG003"
        }
      },
      "is_read": false,
      "created_at": "2024-12-08T15:45:00.000000Z",
      "updated_at": "2024-12-08T15:45:00.000000Z"
    }
  ]
}
```

### Grade Management Endpoints

#### GET /api/grades
**Query Parameters:**
- user_id (optional)
- course_id (optional)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "course_id": "AQ101",
      "assignment_id": "ASG001",
      "grade": 95,
      "grade_letter": "A",
      "comments": "Excellent work on the essay. Well researched and clearly articulated.",
      "created_at": "2024-12-02T11:00:00.000000Z",
      "updated_at": "2024-12-02T11:00:00.000000Z",
      "user": {
        "name": "Ahmad Faris"
      },
      "course": {
        "title": "Pengantar Aqidah Islamiyah"
      },
      "assignment": {
        "title": "Esai Reflektif Pilar Keimanan"
      }
    }
  ]
}
```

### Calendar Events Endpoints

#### GET /api/calendar-events
**Query Parameters:**
- category (optional)
- start_date (optional)
- end_date (optional)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "ACE001",
      "title_key": "event_semester_start",
      "start_date": "2024-09-02T00:00:00.000000Z",
      "end_date": null,
      "category": "academic",
      "created_at": "2024-08-01T00:00:00.000000Z",
      "updated_at": "2024-08-01T00:00:00.000000Z"
    },
    {
      "id": "ACE002",
      "title_key": "event_mid_terms",
      "start_date": "2024-10-21T00:00:00.000000Z",
      "end_date": "2024-10-25T23:59:59.000000Z",
      "category": "exam",
      "created_at": "2024-08-01T00:00:00.000000Z",
      "updated_at": "2024-08-01T00:00:00.000000Z"
    }
  ]
}
```

### Course Module Management Endpoints

#### GET /api/courses/{courseId}/modules
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "MOD001",
      "course_id": "AQ101",
      "title": "Makna Syahadatain",
      "type": "video",
      "description": "Membedah makna dan konsekuensi dari dua kalimat syahadat sebagai fondasi utama keislaman.",
      "duration": "45min",
      "resource_url": "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4",
      "captions_url": "https://example.com/syahadatain-captions.vtt",
      "attachment_url": "https://example.com/syahadatain-notes.pdf",
      "order": 0,
      "created_at": "2024-12-01T10:00:00.000000Z",
      "updated_at": "2024-12-01T10:00:00.000000Z"
    },
    {
      "id": "MOD002",
      "course_id": "AQ101",
      "title": "Pembagian Tauhid",
      "type": "pdf",
      "description": "Penjelasan rinci mengenai Tauhid Rububiyah, Uluhiyah, dan Asma wa Sifat beserta dalil-dalilnya.",
      "duration": null,
      "resource_url": "https://example.com/tauhid-breakdown.pdf",
      "captions_url": null,
      "attachment_url": null,
      "order": 1,
      "created_at": "2024-12-01T10:05:00.000000Z",
      "updated_at": "2024-12-01T10:05:00.000000Z"
    }
  ]
}
```

#### POST /api/courses/{courseId}/modules
**Request:**
```json
{
  "title": "Live Session: Tauhid Rububiyah",
  "type": "live",
  "description": "Sesi live interaktif membahas konsep Tauhid Rububiyah",
  "duration": "60min",
  "start_time": "2024-12-15T10:00:00.000000Z",
  "live_url": "https://meet.google.com/abc-defg-hij",
  "order": 2
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "MOD003",
    "course_id": "AQ101",
    "title": "Live Session: Tauhid Rububiyah",
    "type": "live",
    "description": "Sesi live interaktif membahas konsep Tauhid Rububiyah",
    "duration": "60min",
    "resource_url": null,
    "captions_url": null,
    "attachment_url": null,
    "start_time": "2024-12-15T10:00:00.000000Z",
    "live_url": "https://meet.google.com/abc-defg-hij",
    "order": 2,
    "created_at": "2024-12-09T20:30:00.000000Z",
    "updated_at": "2024-12-09T20:30:00.000000Z"
  }
}
```
