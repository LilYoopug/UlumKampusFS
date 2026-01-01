<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\Assignment;
use App\Models\User;
use App\Models\AssignmentSubmission;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        // Create grades based on frontend constants
        $grades = [
            // Ahmad Faris - AQ101 Esai Reflektif Pilar Keimanan
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Esai Reflektif Pilar Keimanan'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'graded_by' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'grade' => 92.00,
                'grade_letter' => 'A',
                'feedback' => 'Tulisan yang sangat baik dengan analisis mendalam. Referensi Al-Qur\'an dan hadis yang digunakan relevan dan kuat. Pertahankan kualitas ini.',
                'graded_at' => now()->subDays(1)->setTime(16, 0),
            ],
            
            // Siti Maryam - AQ101 Esai Reflektif Pilar Keimanan
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Esai Reflektif Pilar Keimanan'),
                'student_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'graded_by' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'grade' => 95.00,
                'grade_letter' => 'A+',
                'feedback' => 'Luar biasa! Struktur tulisan sangat rapi dengan argumen yang logis dan berbasis dalil yang kuat. Contoh yang sangat baik.',
                'graded_at' => now()->subHours(12),
            ],
            
            // Ahmad Faris - AD501 Presentasi Kontribusi Ilmuwan Muslim
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Presentasi Kontribusi Ilmuwan Muslim'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'graded_by' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'grade' => 88.00,
                'grade_letter' => 'A-',
                'feedback' => 'Presentasi yang informatif tentang Al-Khwarizmi. Visualisasi yang bagus dan penjelasan yang jelas. Bisa ditambahkan lebih banyak detail tentang dampak karya-karyanya.',
                'graded_at' => now()->subDays(15)->setTime(10, 30),
            ],
            
            // Ahmad Faris - FQ201 Analisis Studi Kasus Riba (Late)
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Analisis Studi Kasus Riba'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'graded_by' => $this->getUserIdByEmail('aisyah.h@staff.ulumcampus.com'),
                'grade' => 85.00,
                'grade_letter' => 'B+',
                'feedback' => 'Analisis yang baik, namun karena pengumpulan terlambat, ada penalti 25 poin. Jika dikumpulkan tepat waktu, skor Anda akan sekitar A-. Selalu perhatikan deadline.',
                'graded_at' => now()->subDays(3)->setTime(14, 0),
            ],
            
            // Abdullah - FQ201 Analisis Studi Kasus Riba (Late)
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Analisis Studi Kasus Riba'),
                'student_id' => $this->getUserIdByEmail('abdullah@student.ulumcampus.com'),
                'graded_by' => $this->getUserIdByEmail('aisyah.h@staff.ulumcampus.com'),
                'grade' => 82.00,
                'grade_letter' => 'B',
                'feedback' => 'Analisis cukup baik dengan identifikasi potensi riba yang tepat. Namun, solusi syar\'i yang ditawarkan bisa lebih detail. Tepat waktu, jadi tidak ada penalti.',
                'graded_at' => now()->subDays(2)->setTime(11, 0),
            ],
            
            // Siti Maryam - TR401 Rancangan RPP Inovatif
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Rancangan RPP Inovatif'),
                'student_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'graded_by' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'grade' => 90.00,
                'grade_letter' => 'A',
                'feedback' => 'RPP yang sangat kreatif dengan integrasi teknologi yang relevan. Langkah-langkah pembelajaran jelas dan terstruktur. Sangat baik!',
                'graded_at' => now()->subDays(2)->setTime(17, 30),
            ],
            
            // Ahmad Faris - HD202 Kritik Sanad Hadis
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Kritik Sanad Hadis'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'graded_by' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'grade' => 87.00,
                'grade_letter' => 'A-',
                'feedback' => 'Kritik sanad yang tepat dengan penggunaan terminologi yang benar. Perlu lebih memperdalam analisis tentang \'adalah perawi.',
                'graded_at' => now()->subHours(6),
            ],
            
            // Ahmad Faris - HD202 Setoran Hafalan: Hadits Pertama Arba'in
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Setoran Hafalan: Hadits Pertama Arba\'in'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'graded_by' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'grade' => 93.00,
                'grade_letter' => 'A',
                'feedback' => 'Hafalan yang sangat lancar dengan pelafalan yang baik. Makhraj dan harakat diucapkan dengan benar. Pertahankan hafalan ini!',
                'graded_at' => now()->subHours(4),
            ],
            
            // Abdullah - EK305 Analisis Produk Bank Syariah
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Analisis Produk Bank Syariah'),
                'student_id' => $this->getUserIdByEmail('abdullah@student.ulumcampus.com'),
                'graded_by' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'grade' => 86.00,
                'grade_letter' => 'A-',
                'feedback' => 'Analisis KPR Murabahah yang komprehensif. Penjelasan skema dan risiko cukup baik. Bisa ditambahkan contoh perhitungan untuk lebih jelas.',
                'graded_at' => now()->subDays(8)->setTime(15, 0),
            ],
            
            // Siti Maryam - SN701 Proyek Akhir: Proposal Aplikasi Islami berbasis AI
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Proyek Akhir: Proposal Aplikasi Islami berbasis AI'),
                'student_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'graded_by' => $this->getUserIdByEmail('faiz.rabbani@dosen.ulumcampus.com'),
                'grade' => 91.00,
                'grade_letter' => 'A',
                'feedback' => 'Proposal yang sangat menjanjikan! Konsep QuranChatAI sangat relevan dan pertimbangan etika syariah dijelaskan dengan baik. Kerja bagus!',
                'graded_at' => now()->subDays(4)->setTime(12, 0),
            ],
        ];

        foreach ($grades as $gradeData) {
            if ($gradeData['assignment_id'] && $gradeData['student_id']) {
                // Add user_id (same as student_id for grades table requirement)
                $gradeData['user_id'] = $gradeData['student_id'];
                
                // Get course_id from assignment
                $assignment = Assignment::find($gradeData['assignment_id']);
                if ($assignment) {
                    $gradeData['course_id'] = $assignment->course_id;
                }
                
                Grade::updateOrCreate(
                    [
                        'assignment_id' => $gradeData['assignment_id'],
                        'student_id' => $gradeData['student_id']
                    ],
                    $gradeData
                );
            }
        }
    }

    private function getAssignmentIdByTitle($title)
    {
        $assignment = Assignment::where('title', $title)->first();
        return $assignment ? $assignment->id : null;
    }

    private function getUserIdByEmail($email)
    {
        $user = User::where('email', $email)->first();
        return $user ? $user->id : null;
    }
}
