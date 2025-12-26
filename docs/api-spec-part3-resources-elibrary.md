# API Specification Part 3 - Resources, E-Library & Islamic Content

## Quran Reader

### GET /api/resources/quran/surah
**Frontend**: QuranReader.tsx (lines 46-65)
**Purpose**: Get list of all Quran surahs
**Auth**: Public

Response:
```json
{
  "code": 200,
  "data": [
    {
      "nomor": 1,
      "nama": "الفاتحة",
      "namaLatin": "Al-Fatihah",
      "jumlahAyat": 7,
      "tempatTurun": "Mekah",
      "arti": "Pembukaan",
      "deskripsi": "Surah Al-Fatihah adalah surah pertama dalam Al-Qur'an...",
      "audioFull": {
        "01": "https://..."
      }
    }
  ]
}
```

### GET /api/resources/quran/surah/{surahNumber}
**Frontend**: QuranReader.tsx (lines 73-93)
**Purpose**: Get specific surah with all its ayahs
**Auth**: Public

Response:
```json
{
  "code": 200,
  "data": {
    "nomor": 1,
    "nama": "الفاتحة",
    "namaLatin": "Al-Fatihah",
    "jumlahAyat": 7,
    "tempatTurun": "Mekah",
    "arti": "Pembukaan",
    "deskripsi": "Surah Al-Fatihah adalah surah pertama dalam Al-Qur'an...",
    "ayat": [
      {
        "nomorAyat": 1,
        "teksArab": "بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ",
        "teksLatin": "bismillāhir-raḥmānir-raḥīm(i)",
        "teksIndonesia": "Dengan nama Allah, Yang Maha Pengasih, Maha Penyayang.",
        "audio": {
          "01": "https://..."
        }
      }
    ],
    "audioFull": {
      "01": "https://..."
    }
  }
}
```

### GET /api/resources/quran/tafsir/{surahNumber}
**Frontend**: QuranReader.tsx (lines 73-93)
**Purpose**: Get tafsir for specific surah
**Auth**: Public

Response:
```json
{
  "code": 200,
  "data": {
    "tafsir": [
      {
        "ayat": 1,
        "teks": "Tafsir ayat pertama..."
      }
    ]
  }
}
```

### GET /api/resources/quran/random
**Frontend**: QuranReader.tsx (lines 28-44)
**Purpose**: Get random ayah from Quran
**Auth**: Public

Response:
```json
{
  "status": true,
  "data": {
    "ayat": {
      "id": "1:1",
      "surat": "Al-Fatihah",
      "ayah": "1",
      "text": "Dengan nama Allah, Yang Maha Pengasih, Maha Penyayang.",
      "arab": "بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ",
      "latin": "bismillāhir-raḥmānir-raḥīm(i)"
    },
    "info": {
      "surat": {
        "id": 1,
        "nama": {
          "id": "Al-Fatihah",
          "ar": "الفاتحة",
          "en": "The Opening"
        },
        "arti": {
          "id": "Pembukaan",
          "en": "The Opening"
        },
        "ayat": 7
      }
    }
  }
}
```

## Hadith Reader

### GET /api/resources/hadith/arbain/random
**Frontend**: HadithReader.tsx (lines 28-40)
**Purpose**: Get random hadith from Arbain Nawawi collection
**Auth**: Public

Response:
```json
{
  "status": true,
  "data": {
    "id": 1,
    "number": 1,
    "arab": "حَدَّثَنَا أَبُو بَكْرٍ مُحَمَّدُ بْنُ الْعَلَاءِ، حَدَّثَنَا أَبُو أُسَامَةَ، حَدَّثَنَا بُرَيْدٌ، عَنِ الأَعْمَشِ، عَنْ أَبِي سُفْيَانَ، عَنْ جَابِرٍ، قَالَ قَالَ رَسُولُ اللَّهِ صلى الله عليه وسلم: إِنَّ اللَّهَ وَفَّى لَكُمْ حَجَّكُمْ، وَعُمْرَتَكُمْ، وَغَزْوَتَكُمْ، وَمَحَّلَكُمْ، فَاحْفَظُوا أَنْفُسَكُمْ، وَأَهْلِيكُمْ، وَمَوَالِيكُمْ، وَإِخْوَانَكُمْ، وَجِيرَانَكُمْ، وَصَدِيقَكُمْ، وَمَنْ صَلَحَ مِنَ الْمُسْلِمِينَ، فَإِنَّ لَكُمْ فِي كُلِّ ذَلِكَ أَجْرًا.",
    "indo": "Sesungguhnya Allah telah menyempurnakan haji kalian, umroh kalian, peperangan kalian, dan tempat kalian. Maka peliharalah diri kalian, istri kalian, hamba sahaya kalian, saudara kalian, tetangga kalian, teman kalian, dan orang-orang baik dari kaum muslimin, karena sesungguhnya kalian mendapatkan pahala di setiap hal itu.",
    "judul": "Kewajiban menjaga diri dan orang lain"
  }
}
```

### GET /api/resources/hadith/arbain/all
**Frontend**: HadithReader.tsx (lines 42-50)
**Purpose**: Get all hadith from Arbain Nawawi collection
**Auth**: Public

Response:
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "number": 1,
      "arab": "...",
      "indo": "...",
      "judul": "..."
    }
  ]
}
```

### GET /api/resources/hadith/perawi
**Frontend**: HadithReader.tsx (lines 50-55)
**Purpose**: Get list of hadith perawi/authors
**Auth**: Public

Response:
```json
{
  "status": true,
  "data": [
    {
      "name": "Imam Bukhari",
      "slug": "bukhari",
      "total": 7275
    }
  ]
}
```

### GET /api/resources/hadith/{perawi}/{number}
**Frontend**: HadithReader.tsx (lines 63-85)
**Purpose**: Get specific hadith from specific perawi
**Auth**: Public

Response:
```json
{
  "status": true,
  "data": {
    "id": 1,
    "number": 1,
    "arab": "...",
    "indo": "...",
    "judul": "..."
  }
}
```

## Doa Reader

### GET /api/resources/doa/random
**Frontend**: DoaReader.tsx (lines 24-36)
**Purpose**: Get random doa
**Auth**: Public

Response:
```json
{
  "status": true,
  "data": {
    "id": "1",
    "judul": "Doa Sebelum Makan",
    "arab": "اَللَّهُمَّ بَارِكْ لَنَا فِيْمَا رَزَقْتَنَا وَقِنَا عَذَابَ النَّارِ",
    "indo": "Ya Allah, berkahilah kami dalam rezeki yang telah Engkau berikan kepada kami dan peliharalah kami dari siksa api neraka"
  }
}
```

### GET /api/resources/doa/sources
**Frontend**: DoaReader.tsx (lines 38-54)
**Purpose**: Get list of doa sources/categories
**Auth**: Public

Response:
```json
{
  "status": true,
  "data": [
    "sehari-hari",
    "ibadah",
    "makan-minum",
    "tidur",
    "perjalanan"
  ]
}
```

### GET /api/resources/doa/sources/{source}
**Frontend**: DoaReader.tsx (lines 64-76)
**Purpose**: Get doa from specific source
**Auth**: Public

Response:
```json
{
  "status": true,
  "data": [
    {
      "id": "1",
      "judul": "Doa Sebelum Makan",
      "arab": "اَللَّهُمَّ بَارِكْ لَنَا فِيْمَا رَزَقْتَنَا وَقِنَا عَذَابَ النَّارِ",
      "indo": "Ya Allah, berkahilah kami dalam rezeki yang telah Engkau berikan kepada kami dan peliharalah kami dari siksa api neraka"
    }
  ]
}
```

## Prayer Times

### GET /api/prayer-times/locations
**Frontend**: PrayerTimes.tsx (lines 172-188)
**Purpose**: Get list of available prayer time locations
**Auth**: Public

Response:
```json
{
  "status": true,
  "data": [
    {
      "id": "1",
      "lokasi": "Jakarta"
    }
  ]
}
```

### GET /api/prayer-times/{locationId}/{date}
**Frontend**: PrayerTimes.tsx (lines 190-214)
**Purpose**: Get prayer times for specific location and date
**Auth**: Public

Query Parameters:
- `date`: string (ISO date format)

Response:
```json
{
  "status": true,
  "data": {
    "jadwal": {
      "tanggal": "01 Januari 2025",
      "imsak": "04:25",
      "subuh": "04:35",
      "terbit": "05:50",
      "dhuha": "06:15",
      "dzuhur": "12:00",
      "ashar": "15:15",
      "maghrib": "18:00",
      "isya": "19:15",
      "date": "2025-01-01"
    }
  }
}
```

## AI Chatbot (Ustadz AI)

### POST /api/ai/chat
**Frontend**: UstadzAI.tsx (lines 31-49)
**Purpose**: Send message to Islamic AI chatbot and get response
**Auth**: JWT (authenticated users)

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

## E-Library

### GET /api/elibrary/resources
**Frontend**: ELibrary.tsx (lines 105-138)
**Purpose**: Get list of e-library resources
**Auth**: JWT (all authenticated users)

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
**Auth**: JWT (all authenticated users)

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
**Auth**: JWT (admin/lecturer roles)

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

### PUT /api/elibrary/resources/{id}
**Frontend**: ResourceForm.tsx (lines 60-93)
**Purpose**: Update existing e-library resource
**Auth**: JWT (admin/lecturer roles)

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
**Auth**: JWT (admin roles)

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
**Auth**: JWT (authenticated users)

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
**Auth**: JWT (authenticated users)

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
**Auth**: JWT (all authenticated users)

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
**Auth**: JWT (Dosen/Prodi Admin/Manajemen Kampus/Super Admin)

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
**Auth**: JWT (announcement author or admin roles)

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
**Auth**: JWT (announcement author or admin roles)

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
**Auth**: JWT (all authenticated users)

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
**Auth**: JWT (all authenticated users)

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
**Auth**: JWT (all authenticated users)

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
**Auth**: JWT (Dosen/Prodi Admin/Super Admin)

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
**Auth**: JWT (Dosen/Prodi Admin/Super Admin)

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
**Auth**: JWT (authenticated users)

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
**Auth**: JWT (admin roles)

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
**Auth**: JWT (admin roles)

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
**Auth**: JWT (admin roles)

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
**Auth**: JWT (authenticated users)

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
**Auth**: JWT (authenticated users)

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
**Auth**: JWT (authenticated users)

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
**Auth**: JWT (authenticated users) or Public

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

## Static Content (No API needed)

The following components use static content or external APIs that don't require backend endpoints:

- **QuranReader.tsx**: Uses external APIs from api.myquran.com and equran.id
- **HadithReader.tsx**: Uses external API from api.myquran.com
- **DoaReader.tsx**: Uses external API from api.myquran.com
- **PrayerTimes.tsx**: Uses external API from api.myquran.com
- **FaqSection.tsx**: Uses hardcoded FAQ content
- **ContactSection.tsx**: Uses hardcoded contact information

## File Upload Endpoints

### POST /api/upload
**Frontend**: ResourceForm.tsx, HafalanRecorder.tsx
**Purpose**: Upload files (documents, audio recordings)
**Auth**: JWT (authenticated users)

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

- Quran, Hadith, and Doa readers use external APIs from api.myquran.com and equran.id - no backend needed for these
- Prayer times use external API from api.myquran.com - no backend needed for this
- Video player component uses direct URLs for video playback - backend needed for managing video resources
- Most Islamic content is fetched from external APIs, but the application provides UI and caching mechanisms
- User-generated content (announcements, forum posts, submissions) requires backend endpoints
- E-library system requires full CRUD operations for managing resources
- Calendar events require backend for academic events, but assignments and classes are derived from existing data