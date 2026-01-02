<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Assignment;
use App\Models\Announcement;
use App\Models\Course;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing notifications
        Notification::truncate();

        // Get users by role
        $students = User::where('role', 'student')->get();
        $lecturers = User::where('role', 'dosen')->get();
        $prodiAdmins = User::where('role', 'prodi_admin')->get();
        $management = User::where('role', 'admin')->get();
        $superAdmins = User::where('role', 'super_admin')->get();

        // Student notifications (based on frontend NOTIFICATIONS_DATA)
        foreach ($students as $student) {
            $this->createStudentNotifications($student);
        }

        // Dosen notifications
        foreach ($lecturers as $lecturer) {
            $this->createDosenNotifications($lecturer);
        }

        // Prodi Admin notifications
        foreach ($prodiAdmins as $admin) {
            $this->createProdiAdminNotifications($admin);
        }

        // Management notifications
        foreach ($management as $manager) {
            $this->createManagementNotifications($manager);
        }

        // Super Admin notifications
        foreach ($superAdmins as $superAdmin) {
            $this->createSuperAdminNotifications($superAdmin);
        }
    }

    /**
     * Create notifications for students (matching frontend constants.tsx)
     */
    private function createStudentNotifications(User $student): void
    {
        // Get real IDs from database
        $assignment1 = Assignment::where('title', 'like', '%Presentasi Kontribusi%')->first();
        $assignment2 = Assignment::where('title', 'like', '%Analisis Produk Bank%')->first();
        $announcement1 = Announcement::where('id', 'AN001')->first();
        $course1 = Course::where('code', 'AQ101')->first();

        $notifications = [
            [
                'id' => 'N001_' . $student->id,
                'user_id' => $student->id,
                'type' => 'forum',
                'title' => 'notification_forum_reply',
                'message' => 'Dr. Yusuf Al-Fatih membalas diskusi Anda di forum.',
                'context' => 'Dr. Yusuf Al-Fatih',
                'link' => ['page' => 'course-detail', 'params' => ['courseId' => $course1?->id ?? 'AQ101', 'initialTab' => 'discussion', 'threadId' => 'DT001']],
                'is_read' => false,
                'created_at' => now()->subHours(5),
            ],
            [
                'id' => 'N002_' . $student->id,
                'user_id' => $student->id,
                'type' => 'grade',
                'title' => 'notification_grade_update',
                'message' => 'Nilai untuk tugas "Presentasi Kontribusi Ilmuwan Muslim" telah diperbarui.',
                'context' => 'Presentasi Kontribusi Ilmuwan Muslim',
                'link' => ['page' => 'assignments', 'params' => ['assignmentId' => (string)($assignment1?->id ?? 1)]],
                'is_read' => false,
                'created_at' => now()->subDays(1),
            ],
            [
                'id' => 'N003_' . $student->id,
                'user_id' => $student->id,
                'type' => 'assignment',
                'title' => 'notification_new_assignment',
                'message' => 'Tugas baru "Analisis Produk Bank Syariah" telah ditambahkan.',
                'context' => 'Analisis Produk Bank Syariah',
                'link' => ['page' => 'assignments', 'params' => ['assignmentId' => (string)($assignment2?->id ?? 2)]],
                'is_read' => true,
                'created_at' => now()->subDays(2),
            ],
            [
                'id' => 'N004_' . $student->id,
                'user_id' => $student->id,
                'type' => 'announcement',
                'title' => 'notification_announcement',
                'message' => 'Dr. Aisyah Hasanah membuat pengumuman baru tentang Perubahan Jadwal Ujian.',
                'context' => 'Dr. Aisyah Hasanah',
                'link' => ['page' => 'announcements', 'params' => ['announcementId' => $announcement1?->id ?? 'AN001']],
                'is_read' => true,
                'created_at' => now()->subDays(3),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }

    /**
     * Create notifications for lecturers (Dosen)
     */
    private function createDosenNotifications(User $lecturer): void
    {
        // Get real IDs from database
        $assignment1 = Assignment::where('title', 'like', '%Esai Reflektif%')->first();
        $assignment2 = Assignment::where('title', 'like', '%Kritik Sanad%')->first();
        $announcement1 = Announcement::where('id', 'AN001')->first();
        $course1 = Course::where('code', 'AQ101')->first();

        $notifications = [
            [
                'id' => 'ND001_' . $lecturer->id,
                'user_id' => $lecturer->id,
                'type' => 'forum',
                'title' => 'notification_forum_reply',
                'message' => 'Ahmad Faris membalas diskusi di mata kuliah Anda.',
                'context' => 'Ahmad Faris',
                'link' => ['page' => 'course-detail', 'params' => ['courseId' => $course1?->id ?? 'AQ101', 'initialTab' => 'discussion', 'threadId' => 'DT001']],
                'is_read' => false,
                'created_at' => now()->subHours(3),
            ],
            [
                'id' => 'ND002_' . $lecturer->id,
                'user_id' => $lecturer->id,
                'type' => 'assignment',
                'title' => 'notification_submission_received',
                'message' => 'Mahasiswa telah mengumpulkan tugas "Esai Reflektif Pilar Keimanan".',
                'context' => 'Esai Reflektif Pilar Keimanan',
                'link' => ['page' => 'gradebook', 'params' => ['assignmentId' => (string)($assignment1?->id ?? 1)]],
                'is_read' => false,
                'created_at' => now()->subDays(1),
            ],
            [
                'id' => 'ND003_' . $lecturer->id,
                'user_id' => $lecturer->id,
                'type' => 'announcement',
                'title' => 'notification_announcement',
                'message' => 'Admin Prodi membuat pengumuman baru tentang jadwal rapat dosen.',
                'context' => 'Rapat Dosen',
                'link' => ['page' => 'announcements', 'params' => ['announcementId' => $announcement1?->id ?? 'AN001']],
                'is_read' => true,
                'created_at' => now()->subDays(2),
            ],
            [
                'id' => 'ND004_' . $lecturer->id,
                'user_id' => $lecturer->id,
                'type' => 'grade',
                'title' => 'notification_grading_deadline',
                'message' => 'Deadline penilaian tugas "Kritik Sanad Hadis" akan berakhir dalam 2 hari.',
                'context' => 'Kritik Sanad Hadis',
                'link' => ['page' => 'gradebook', 'params' => ['assignmentId' => (string)($assignment2?->id ?? 2)]],
                'is_read' => false,
                'created_at' => now()->subHours(12),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }

    /**
     * Create notifications for Prodi Admin
     */
    private function createProdiAdminNotifications(User $admin): void
    {
        $notifications = [
            [
                'id' => 'NP001_' . $admin->id,
                'user_id' => $admin->id,
                'type' => 'announcement',
                'title' => 'notification_enrollment_request',
                'message' => 'Mahasiswa baru mendaftar ke program studi Anda.',
                'context' => 'Pendaftaran Mahasiswa Baru',
                'link' => ['page' => 'prodi-students', 'params' => []],
                'is_read' => false,
                'created_at' => now()->subHours(2),
            ],
            [
                'id' => 'NP002_' . $admin->id,
                'user_id' => $admin->id,
                'type' => 'assignment',
                'title' => 'notification_course_review',
                'message' => 'Mata kuliah baru menunggu persetujuan kurikulum.',
                'context' => 'Review Kurikulum',
                'link' => ['page' => 'prodi-courses', 'params' => []],
                'is_read' => false,
                'created_at' => now()->subDays(1),
            ],
            [
                'id' => 'NP003_' . $admin->id,
                'user_id' => $admin->id,
                'type' => 'grade',
                'title' => 'notification_grade_report',
                'message' => 'Laporan nilai semester telah tersedia.',
                'context' => 'Laporan Nilai',
                'link' => ['page' => 'grades', 'params' => []],
                'is_read' => true,
                'created_at' => now()->subDays(3),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }

    /**
     * Create notifications for Management (Manajemen Kampus)
     */
    private function createManagementNotifications(User $manager): void
    {
        $notifications = [
            [
                'id' => 'NM001_' . $manager->id,
                'user_id' => $manager->id,
                'type' => 'announcement',
                'title' => 'notification_system_update',
                'message' => 'Pembaruan sistem LMS telah berhasil dilakukan.',
                'context' => 'Pembaruan Sistem',
                'link' => ['page' => 'dashboard', 'params' => []],
                'is_read' => false,
                'created_at' => now()->subHours(1),
            ],
            [
                'id' => 'NM002_' . $manager->id,
                'user_id' => $manager->id,
                'type' => 'grade',
                'title' => 'notification_academic_report',
                'message' => 'Laporan akademik semester ganjil telah tersedia.',
                'context' => 'Laporan Akademik',
                'link' => ['page' => 'management-administration', 'params' => []],
                'is_read' => false,
                'created_at' => now()->subDays(1),
            ],
            [
                'id' => 'NM003_' . $manager->id,
                'user_id' => $manager->id,
                'type' => 'assignment',
                'title' => 'notification_payment_received',
                'message' => 'Pembayaran SPP telah diterima dari 25 mahasiswa baru.',
                'context' => 'Pembayaran SPP',
                'link' => ['page' => 'management-administration', 'params' => []],
                'is_read' => true,
                'created_at' => now()->subDays(2),
            ],
            [
                'id' => 'NM004_' . $manager->id,
                'user_id' => $manager->id,
                'type' => 'forum',
                'title' => 'notification_feedback_received',
                'message' => 'Feedback baru dari mahasiswa tentang fasilitas kampus.',
                'context' => 'Feedback Mahasiswa',
                'link' => ['page' => 'announcements', 'params' => []],
                'is_read' => true,
                'created_at' => now()->subDays(4),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }

    /**
     * Create notifications for Super Admin
     */
    private function createSuperAdminNotifications(User $superAdmin): void
    {
        $notifications = [
            [
                'id' => 'NS001_' . $superAdmin->id,
                'user_id' => $superAdmin->id,
                'type' => 'announcement',
                'title' => 'notification_security_alert',
                'message' => 'Tidak ada ancaman keamanan yang terdeteksi dalam 24 jam terakhir.',
                'context' => 'Laporan Keamanan',
                'link' => ['page' => 'dashboard', 'params' => []],
                'is_read' => false,
                'created_at' => now()->subHours(2),
            ],
            [
                'id' => 'NS002_' . $superAdmin->id,
                'user_id' => $superAdmin->id,
                'type' => 'assignment',
                'title' => 'notification_user_registration',
                'message' => '5 pengguna baru telah terdaftar dan menunggu verifikasi.',
                'context' => 'Registrasi Pengguna',
                'link' => ['page' => 'user-management', 'params' => []],
                'is_read' => false,
                'created_at' => now()->subDays(1),
            ],
            [
                'id' => 'NS003_' . $superAdmin->id,
                'user_id' => $superAdmin->id,
                'type' => 'grade',
                'title' => 'notification_backup_complete',
                'message' => 'Backup database harian telah berhasil dilakukan.',
                'context' => 'Backup Database',
                'link' => ['page' => 'dashboard', 'params' => []],
                'is_read' => true,
                'created_at' => now()->subDays(1),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }
}
