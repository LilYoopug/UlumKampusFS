<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Create assignments based on frontend constants
        $assignments = [
            [
                'course_id' => $this->getCourseIdByCode('AQ101'),
                'created_by' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'title' => 'Esai Reflektif Pilar Keimanan',
                'description' => 'Tulis esai 2 halaman yang merefleksikan pemahaman Anda tentang salah satu dari enam pilar keimanan. Gunakan minimal 3 referensi dari Al-Qur\'an atau Hadis Shahih. Format file: PDF.',
                'instructions' => 'Tulis esai 2 halaman yang merefleksikan pemahaman Anda tentang salah satu dari enam pilar keimanan. Gunakan minimal 3 referensi dari Al-Qur\'an atau Hadis Shahih. Format file: PDF.',
                'due_date' => now()->addDays(7)->setTime(23, 59),
                'max_points' => 100.00,
                'submission_type' => 'file',
                'allowed_file_types' => 'pdf',
                'max_file_size' => 5242880, // 5MB
                'attempts_allowed' => 1,
                'is_published' => true,
                'published_at' => now()->subDays(1),
                'allow_late_submission' => true,
                'late_penalty' => 10.00,
                'order' => 1,
            ],
            [
                'course_id' => $this->getCourseIdByCode('AQ101'),
                'created_by' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'title' => 'Setoran Hafalan: Tiga Landasan Utama',
                'description' => 'Hafalkan matan Tiga Landasan Utama (Al-Ushul Ats-Tsalatsah) karya Syaikh Muhammad bin Abdul Wahhab. Rekam setoran Anda dengan pelafalan yang jelas dan tartil.',
                'instructions' => 'Hafalkan matan Tiga Landasan Utama (Al-Ushul Ats-Tsalatsah) karya Syaikh Muhammad bin Abdul Wahhab. Rekam setoran Anda dengan pelafalan yang jelas dan tartil.',
                'due_date' => now()->addDays(10)->setTime(23, 59),
                'max_points' => 100.00,
                'submission_type' => 'hafalan',
                'attempts_allowed' => 3,
                'is_published' => true,
                'published_at' => now()->subDays(1),
                'allow_late_submission' => true,
                'late_penalty' => 5.00,
                'order' => 2,
            ],
            [
                'course_id' => $this->getCourseIdByCode('FQ201'),
                'created_by' => $this->getUserIdByEmail('aisyah.h@staff.ulumcampus.com'),
                'title' => 'Analisis Studi Kasus Riba',
                'description' => 'Analisis studi kasus terlampir mengenai transaksi di lembaga keuangan. Identifikasi potensi riba, gharar, atau maysir dan berikan solusi syar\'i alternatif.',
                'instructions' => 'Analisis studi kasus terlampir mengenai transaksi di lembaga keuangan. Identifikasi potensi riba, gharar, atau maysir dan berikan solusi syar\'i alternatif.',
                'due_date' => now()->subDays(2)->setTime(23, 59), // Overdue
                'max_points' => 100.00,
                'submission_type' => 'file',
                'allowed_file_types' => 'pdf,doc,docx',
                'max_file_size' => 10485760, // 10MB
                'attempts_allowed' => 1,
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'allow_late_submission' => true,
                'late_penalty' => 25.00,
                'order' => 1,
            ],
            [
                'course_id' => $this->getCourseIdByCode('AD501'),
                'created_by' => $this->getUserIdByEmail('tariq.annawawi@dosen.ulumcampus.com'),
                'title' => 'Presentasi Kontribusi Ilmuwan Muslim',
                'description' => 'Buat presentasi 10 slide mengenai kontribusi salah satu ilmuwan Muslim (pilih dari daftar yang disediakan) pada peradaban dunia. Kumpulkan dalam format PPTX atau PDF.',
                'instructions' => 'Buat presentasi 10 slide mengenai kontribusi salah satu ilmuwan Muslim (pilih dari daftar yang disediakan) pada peradaban dunia. Kumpulkan dalam format PPTX atau PDF.',
                'due_date' => now()->subDays(20)->setTime(23, 59), // Due 20 days ago
                'max_points' => 100.00,
                'submission_type' => 'file',
                'allowed_file_types' => 'pptx,pdf',
                'max_file_size' => 20971520, // 20MB
                'attempts_allowed' => 1,
                'is_published' => true,
                'published_at' => now()->subDays(21),
                'allow_late_submission' => false,
                'late_penalty' => 0.00,
                'order' => 1,
            ],
            [
                'course_id' => $this->getCourseIdByCode('TR401'),
                'created_by' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'title' => 'Rancangan RPP Inovatif',
                'description' => 'Rancang satu Rencana Pelaksanaan Pembelajaran (RPP) untuk materi PAI tingkat SMA dengan mengintegrasikan teknologi atau model pembelajaran aktif.',
                'instructions' => 'Rancang satu Rencana Pelaksanaan Pembelajaran (RPP) untuk materi PAI tingkat SMA dengan mengintegrasikan teknologi atau model pembelajaran aktif.',
                'due_date' => now()->addDays(15)->setTime(23, 59),
                'max_points' => 100.00,
                'submission_type' => 'file',
                'allowed_file_types' => 'pdf,doc,docx',
                'max_file_size' => 5242880, // 5MB
                'attempts_allowed' => 3,
                'is_published' => true,
                'published_at' => now()->subDays(2),
                'allow_late_submission' => true,
                'late_penalty' => 5.00,
                'order' => 1,
            ],
            [
                'course_id' => $this->getCourseIdByCode('HD202'),
                'created_by' => $this->getUserIdByEmail('abdullah.musnad@dosen.ulumcampus.com'),
                'title' => 'Kritik Sanad Hadis',
                'description' => 'Pilih satu hadis dari lampiran dan lakukan kritik sanad dasar berdasarkan metodologi yang telah dipelajari.',
                'instructions' => 'Pilih satu hadis dari lampiran dan lakukan kritik sanad dasar berdasarkan metodologi yang telah dipelajari.',
                'due_date' => now()->addDays(5)->setTime(23, 59),
                'max_points' => 100.00,
                'submission_type' => 'file',
                'allowed_file_types' => 'pdf,doc,docx',
                'max_file_size' => 5242880, // 5MB
                'attempts_allowed' => 1,
                'is_published' => true,
                'published_at' => now()->subDays(1),
                'allow_late_submission' => true,
                'late_penalty' => 15.00,
                'order' => 1,
            ],
            [
                'course_id' => $this->getCourseIdByCode('HD202'),
                'created_by' => $this->getUserIdByEmail('abdullah.musnad@dosen.ulumcampus.com'),
                'title' => 'Setoran Hafalan: Hadits Pertama Arba\'in',
                'description' => 'Hafalkan matan dan sanad hadits pertama dari kitab Arba\'in An-Nawawi tentang niat. Pastikan makhraj dan harakat diucapkan dengan benar.',
                'instructions' => 'Hafalkan matan dan sanad hadits pertama dari kitab Arba\'in An-Nawawi tentang niat. Pastikan makhraj dan harakat diucapkan dengan benar.',
                'due_date' => now()->addDays(12)->setTime(23, 59),
                'max_points' => 100.00,
                'submission_type' => 'hafalan',
                'attempts_allowed' => 5,
                'is_published' => true,
                'published_at' => now()->subDays(2),
                'allow_late_submission' => true,
                'late_penalty' => 5.00,
                'order' => 2,
            ],
            [
                'course_id' => $this->getCourseIdByCode('EK305'),
                'created_by' => $this->getUserIdByEmail('halimah.sadiyah@dosen.ulumcampus.com'),
                'title' => 'Analisis Produk Bank Syariah',
                'description' => 'Pilih satu produk pembiayaan dari bank syariah di Indonesia. Analisis akad yang digunakan, skema, serta potensi risikonya. Buat laporan 3 halaman.',
                'instructions' => 'Pilih satu produk pembiayaan dari bank syariah di Indonesia. Analisis akad yang digunakan, skema, serta potensi risikonya. Buat laporan 3 halaman.',
                'due_date' => now()->addDays(20)->setTime(23, 59),
                'max_points' => 100.00,
                'submission_type' => 'file',
                'allowed_file_types' => 'pdf,doc,docx',
                'max_file_size' => 5242880, // 5MB
                'attempts_allowed' => 1,
                'is_published' => true,
                'published_at' => now()->subDays(1),
                'allow_late_submission' => true,
                'late_penalty' => 10.00,
                'order' => 1,
            ],
            [
                'course_id' => $this->getCourseIdByCode('SN701'),
                'created_by' => $this->getUserIdByEmail('faiz.rabbani@dosen.ulumcampus.com'),
                'title' => 'Proyek Akhir: Proposal Aplikasi Islami berbasis AI',
                'description' => 'Buat proposal (5-7 halaman) untuk sebuah aplikasi Islami yang memanfaatkan teknologi AI. Jelaskan masalah yang ingin diselesaikan, teknologi AI yang akan digunakan, dan pertimbangan etika syariahnya.',
                'instructions' => 'Buat proposal (5-7 halaman) untuk sebuah aplikasi Islami yang memanfaatkan teknologi AI. Jelaskan masalah yang ingin diselesaikan, teknologi AI yang akan digunakan, dan pertimbangan etika syariahnya.',
                'due_date' => now()->addDays(45)->setTime(23, 59),
                'max_points' => 100.00,
                'submission_type' => 'file',
                'allowed_file_types' => 'pdf,doc,docx',
                'max_file_size' => 10485760, // 10MB
                'attempts_allowed' => 1,
                'is_published' => true,
                'published_at' => now()->subDays(1),
                'allow_late_submission' => false,
                'late_penalty' => 0.00,
                'order' => 2,
            ]
        ];

        foreach ($assignments as $assignmentData) {
            // Only create assignment if course and instructor exist
            if ($assignmentData['course_id'] && $assignmentData['created_by']) {
                Assignment::updateOrCreate(
                    [
                        'course_id' => $assignmentData['course_id'],
                        'title' => $assignmentData['title']
                    ],
                    $assignmentData
                );
            }
        }
    }

    private function getCourseIdByCode($code)
    {
        $course = Course::where('code', $code)->first();
        return $course ? $course->id : null;
    }

    private function getUserIdByEmail($email)
    {
        $user = User::where('email', $email)->first();
        return $user ? $user->id : null;
    }
}
