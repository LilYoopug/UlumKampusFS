// FIX: import React to use React.ReactNode
import React from 'react';
// FIX: Re-export TranslationKey so it can be imported from types.ts in other files.
import { TranslationKey } from "./translations";
export type { TranslationKey };

export type Language = 'id' | 'en' | 'ar';
export type UserRole = 'Mahasiswa' | 'Dosen' | 'Prodi Admin' | 'Manajemen Kampus' | 'Super Admin' | 'MABA';
export type Page = 'dashboard' | 'calendar' | 'courses' | 'course-detail' | 'create-course' | 'edit-course' | 'grades' | 'gradebook' | 'assignments' | 'video-lectures' | 'elibrary' | 'manage-elibrary' | 'profile' | 'settings' | 'worship' | 'help' | 'notifications' | 'announcements' | 'prodi-courses' | 'prodi-students' | 'prodi-lecturers' | 'administrasi' | 'management-administration' | 'student-registration' | 'user-management' | 'manajemen-fakultas';
export type CourseStatus = 'Published' | 'Draft' | 'Archived';

export interface User {
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

export interface Badge {
  id: string;
  icon: React.ReactNode;
  titleKey: TranslationKey;
  descriptionKey: TranslationKey;
}

export interface Major {
    id: string;
    name: string;
}

export interface Faculty {
    id: string;
    name: string;
    description: string;
    majors: Major[];
    createdAt?: string;
}

export interface CourseModule {
  id: string;
  title: string;
  // FIX: Added 'hafalan' to support memorization assignments as modules, which are then converted to assignments.
  type: 'video' | 'pdf' | 'quiz' | 'hafalan' | 'live';
 description?: string;
 // FIX: Added optional 'duration' property to support video modules with a specified duration.
  duration?: string;
  resourceUrl?: string;
  captionsUrl?: string;
  attachmentUrl?: string; // For optional link or file
  startTime?: string;
  liveUrl?: string;
}

export interface SyllabusWeek {
  week: number;
  topic: string;
 description: string;
}

export interface Course {
  id: string;
  title: string;
 instructor: string;
 instructorId?: string;
  facultyId: string;
  majorId?: string;
  sks: number;
  description: string;
 imageUrl?: string;
 progress?: number;
 gradeLetter?: string;
  gradeNumeric?: number;
  completionDate?: string;
  mode?: string; // 'Live' | 'VOD' - from backend
  status: CourseStatus;
  learningObjectives?: string[];
  syllabus?: SyllabusWeek[];
  modules?: CourseModule[];
  created_at?: string;
  updated_at?: string;
  instructorAvatarUrl?: string;
  instructorBioKey?: TranslationKey;
}

export interface Submission {
  studentId: string;
  submittedAt: string;
  file: { name: string; url: string };
  gradeLetter?: string;
  gradeNumeric?: number;
  feedback?: string;
}

export interface Assignment {
  id: string;
  courseId: string;
 title: string;
 description: string;
 dueDate: string;
 files?: { name: string; url: string }[];
  submissions?: Submission[];
  type: string; // Backend has type field
  category?: string; // 'Tugas' | 'Ujian' - from backend
  maxScore?: number;
 instructions?: string;
 attachments?: any[];
  created_at?: string;
  updated_at?: string;
}

export interface AcademicCalendarEvent {
    id: string;
    titleKey: TranslationKey;
    startDate: string;
    endDate?: string;
    category: 'holiday' | 'exam' | 'registration' | 'academic';
}

// Type alias for CalendarEvent
export type CalendarEvent = AcademicCalendarEvent;

export interface DiscussionPost {
  id: string;
  authorId: string; // studentId or equivalent
  createdAt: string;
  content: string;
}

export interface DiscussionThread {
 id: string;
  courseId: string;
  title: string;
  authorId: string;
  createdAt: string;
  isPinned: boolean;
  isClosed: boolean;
  posts?: DiscussionPost[];
}

export interface NotificationLink {
  page: Page;
  params: any;
}

export interface Notification {
  id: string;
  type: string; // Backend has type field
  messageKey: TranslationKey;
  context: string;
  timestamp: string;
  isRead: boolean;
  link: NotificationLink;
  user_id?: string;
}

export enum HafalanSubmissionStatus {
  NotSubmitted,
  Recording,
  Submitting, // After recording stopped, before analyzing or submitting
 Analyzing,
  FeedbackReady, // After analysis, before submitting
  Submitted,
}

export interface TajwidFeedbackItem {
  type: 'error' | 'info';
  rule: string;
 comment: string;
}

export interface TajwidFeedback {
  overallScore: number;
 feedback: TajwidFeedbackItem[];
}

// Quran Reader Types
export interface EQuranSurah {
  nomor: number;
  nama: string;
  namaLatin: string;
 jumlahAyat: number;
  tempatTurun: string;
 arti: string;
  deskripsi: string;
  audioFull: Record<string, string>;
}

export interface EQuranAyah {
  nomorAyat: number;
 teksArab: string;
  teksLatin: string;
  teksIndonesia: string;
 audio: Record<string, string>;
}

export interface EQuranSurahDetail extends EQuranSurah {
  ayat: EQuranAyah[];
}

export interface TafsirAyah {
    ayat: number;
    teks: string;
}

export interface MyQuranRandomAyahData {
  ayat: {
    id: string;
    surat: string;
    ayah: string;
    text: string;
    arab: string;
    latin: string;
 };
  info: {
    surat: {
      id: number;
      nama: {
        id: string;
        ar: string;
        en: string;
      };
      arti: {
        id: string;
        en: string;
      };
      ayat: number;
    };
  };
}

// Hadith Types
// FIX: Resolved duplicate 'id' property by merging into a union type 'number | string'.
// Made 'indo' optional as some API responses use 'id' (as a string) for translation.
export interface Hadith {
  id: number | string;
  number: number;
 no?: number;
 arab: string;
 indo?: string;
  judul?: string;
}

export interface Perawi {
  name: string;
  slug: string;
 total: number;
}

// Doa Types
export interface Doa {
  id: string;
  judul: string;
  arab: string;
 latin: string;
 indo: string;
}

// Library Types
export interface LibraryResource {
  id: string;
  title: string;
  author: string;
  year: number;
 type: 'book' | 'journal';
  description: string;
  coverUrl: string;
  sourceType?: 'upload' | 'link' | 'embed'; // For books and journals
 sourceUrl?: string;
  created_at?: string;
  updated_at?: string;
}

// Help Types
export interface FaqItem {
    q: TranslationKey;
    a: TranslationKey;
    roles: UserRole[];
}

export interface GuideSection {
    title: TranslationKey;
    content: TranslationKey;
    roles: UserRole[];
}

// Announcements
export type AnnouncementCategory = 'Kampus' | 'Akademik' | 'Mata Kuliah';

export interface Announcement {
  id: string;
  title: string;
 content: string;
  authorName: string;
  timestamp: string;
  category: AnnouncementCategory;
  author_id?: string; // Backend has author_id field
  course_id?: string; // Backend has course_id field
  created_at?: string;
  updated_at?: string;
}

// Grade Types
export interface Grade {
  id?: string;
  user_id: string;
  course_id: string;
  assignment_id?: string;
  grade: number;
  grade_letter?: string;
  comments?: string;
 created_at?: string;
  updated_at?: string;
  user?: User;
 course?: Course;
 assignment?: Assignment;
}
