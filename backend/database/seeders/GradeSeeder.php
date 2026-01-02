<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        // Create assignment-level grades based on frontend constants
        $grades = [
            // Ahmad Faris - AQ101 Esai Reflektif Pilar Keimanan
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Esai Reflektif Pilar Keimanan'),
                'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'grade' => 92.00,
                'grade_letter' => 'A',
                'comments' => 'Tulisan yang sangat baik dengan analisis mendalam. Referensi Al-Qur\'an dan hadis yang digunakan relevan dan kuat. Pertahankan kualitas ini.',
            ],
            
            // Siti Maryam - AQ101 Esai Reflektif Pilar Keimanan
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Esai Reflektif Pilar Keimanan'),
                'user_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'grade' => 95.00,
                'grade_letter' => 'A+',
                'comments' => 'Luar biasa! Struktur tulisan sangat rapi dengan argumen yang logis dan berbasis dalil yang kuat. Contoh yang sangat baik.',
            ],
            
            // Ahmad Faris - AD501 Presentasi Kontribusi Ilmuwan Muslim
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Presentasi Kontribusi Ilmuwan Muslim'),
                'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'grade' => 88.00,
                'grade_letter' => 'A-',
                'comments' => 'Presentasi yang informatif tentang Al-Khwarizmi. Visualisasi yang bagus dan penjelasan yang jelas. Bisa ditambahkan lebih banyak detail tentang dampak karya-karyanya.',
            ],
            
            // Ahmad Faris - FQ201 Analisis Studi Kasus Riba (Late)
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Analisis Studi Kasus Riba'),
                'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'grade' => 85.00,
                'grade_letter' => 'B+',
                'comments' => 'Analisis yang baik, namun karena pengumpulan terlambat, ada penalti 25 poin. Jika dikumpulkan tepat waktu, skor Anda akan sekitar A-. Selalu perhatikan deadline.',
            ],
            
            // Abdullah - FQ201 Analisis Studi Kasus Riba (Late)
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Analisis Studi Kasus Riba'),
                'user_id' => $this->getUserIdByEmail('abdullah@student.ulumcampus.com'),
                'grade' => 82.00,
                'grade_letter' => 'B',
                'comments' => 'Analisis cukup baik dengan identifikasi potensi riba yang tepat. Namun, solusi syar\'i yang ditawarkan bisa lebih detail. Tepat waktu, jadi tidak ada penalti.',
            ],
            
            // Siti Maryam - TR401 Rancangan RPP Inovatif
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Rancangan RPP Inovatif'),
                'user_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'grade' => 90.00,
                'grade_letter' => 'A',
                'comments' => 'RPP yang sangat kreatif dengan integrasi teknologi yang relevan. Langkah-langkah pembelajaran jelas dan terstruktur. Sangat baik!',
            ],
            
            // Ahmad Faris - HD202 Kritik Sanad Hadis
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Kritik Sanad Hadis'),
                'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'grade' => 87.00,
                'grade_letter' => 'A-',
                'comments' => 'Kritik sanad yang tepat dengan penggunaan terminologi yang benar. Perlu lebih memperdalam analisis tentang \'adalah perawi.',
            ],
            
            // Ahmad Faris - HD202 Setoran Hafalan: Hadits Pertama Arba'in
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Setoran Hafalan: Hadits Pertama Arba\'in'),
                'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'grade' => 93.00,
                'grade_letter' => 'A',
                'comments' => 'Hafalan yang sangat lancar dengan pelafalan yang baik. Makhraj dan harakat diucapkan dengan benar. Pertahankan hafalan ini!',
            ],
            
            // Abdullah - EK305 Analisis Produk Bank Syariah
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Analisis Produk Bank Syariah'),
                'user_id' => $this->getUserIdByEmail('abdullah@student.ulumcampus.com'),
                'grade' => 86.00,
                'grade_letter' => 'A-',
                'comments' => 'Analisis KPR Murabahah yang komprehensif. Penjelasan skema dan risiko cukup baik. Bisa ditambahkan contoh perhitungan untuk lebih jelas.',
            ],
            
            // Siti Maryam - SN701 Proyek Akhir: Proposal Aplikasi Islami berbasis AI
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Proyek Akhir: Proposal Aplikasi Islami berbasis AI'),
                'user_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'grade' => 91.00,
                'grade_letter' => 'A',
                'comments' => 'Proposal yang sangat menjanjikan! Konsep QuranChatAI sangat relevan dan pertimbangan etika syariah dijelaskan dengan baik. Kerja bagus!',
            ],
        ];

        foreach ($grades as $gradeData) {
            if ($gradeData['assignment_id'] && $gradeData['user_id']) {
                // Get course_id from assignment
                $assignment = Assignment::find($gradeData['assignment_id']);
                if ($assignment) {
                    $gradeData['course_id'] = $assignment->course_id;
                    
                    Grade::updateOrCreate(
                        [
                            'assignment_id' => $gradeData['assignment_id'],
                            'user_id' => $gradeData['user_id']
                        ],
                        $gradeData
                    );
                }
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
