<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\User;

class AssignmentSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create assignment submissions based on frontend constants
        $submissions = [
            // AQ101 - Esai Reflektif Pilar Keimanan - GRADED
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Esai Reflektif Pilar Keimanan'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'file_url' => 'assignments/ahmad_faris_pilar_keimanan.pdf',
                'file_name' => 'Refleksi_Pilar_Keimanan_AhmadFaris.pdf',
                'content' => null,
                'submitted_at' => now()->subDays(2)->setTime(14, 30),
                'is_late' => false,
                'attempt_number' => 1,
                'status' => 'graded',
                'grade' => 88,
                'feedback' => 'Esai yang sangat reflektif dan mendalam. Ahmad berhasil menghubungkan konsep keimanan dengan pengalaman pribadi dengan baik. Referensi Al-Qur\'an yang digunakan relevan dan tepat.',
            ],
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Esai Reflektif Pilar Keimanan'),
                'student_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'file_url' => 'assignments/siti_maryam_pilar_keimanan.pdf',
                'file_name' => 'Refleksi_Pilar_Keimanan_SitiMaryam.pdf',
                'content' => null,
                'submitted_at' => now()->subDays(1)->setTime(10, 15),
                'is_late' => false,
                'attempt_number' => 1,
                'status' => 'graded',
                'grade' => 92,
                'feedback' => 'Penulisan yang sangat sistematis dan terstruktur. Siti menunjukkan pemahaman yang komprehensif tentang pilar-pilar keimanan. Analisis yang disajikan sangat kuat dan argumentatif.',
            ],
            
            // AQ101 - Setoran Hafalan: Tiga Landasan Utama
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Setoran Hafalan: Tiga Landasan Utama'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'file_url' => 'assignments/ahmad_faris_hafalan_tiga_landasan.mp3',
                'file_name' => 'Hafalan_Tiga_Landasan_AhmadFaris.mp3',
                'content' => 'Alhamdulillah, saya telah menghafalkan matan Al-Ushul Ats-Tsalatsah dengan lancar. Mohon dikoreksi pelafalannya.',
                'submitted_at' => now()->subDays(3)->setTime(16, 45),
                'is_late' => false,
                'attempt_number' => 2,
                'status' => 'submitted',
                'grade' => null,
                'feedback' => null,
            ],
            
            // FQ201 - Analisis Studi Kasus Riba (Overdue)
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Analisis Studi Kasus Riba'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'file_url' => 'assignments/ahmad_faris_studi_kasus_riba.pdf',
                'file_name' => 'Analisis_Riba_BankKonvensional.pdf',
                'content' => null,
                'submitted_at' => now()->subDays(5)->setTime(23, 59),
                'is_late' => true,
                'attempt_number' => 1,
                'status' => 'late',
                'grade' => null,
                'feedback' => null,
            ],
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Analisis Studi Kasus Riba'),
                'student_id' => $this->getUserIdByEmail('abdullah@student.ulumcampus.com'),
                'file_url' => 'assignments/abdullah_studi_kasus_riba.pdf',
                'file_name' => 'Analisis_Riba_LembagaKeuangan.pdf',
                'content' => null,
                'submitted_at' => now()->subDays(3)->setTime(20, 30),
                'is_late' => true,
                'attempt_number' => 1,
                'status' => 'late',
                'grade' => null,
                'feedback' => null,
            ],
            
            // AD501 - Presentasi Kontribusi Ilmuwan Muslim (Due 20 days ago) - GRADED
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Presentasi Kontribusi Ilmuwan Muslim'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'file_url' => 'assignments/ahmad_faris_kontribusi_ilmuwan.pptx',
                'file_name' => 'Kontribusi_AlKhwarizmi_AhmadFaris.pptx',
                'content' => 'Saya memilih Al-Khwarizmi sebagai tokoh yang dianalisis, mengingat kontribusinya yang monumental dalam bidang matematika dan astronomi.',
                'submitted_at' => now()->subDays(19)->setTime(12, 0),
                'is_late' => false,
                'attempt_number' => 1,
                'status' => 'graded',
                'grade' => 95,
                'feedback' => 'Kerja yang sangat baik, Ahmad! Analisis Anda tentang kontribusi Ibn Al-Haytham dalam bidang optik sangat mendalam dan presentasinya visualnya menarik. Pertahankan!',
            ],
            
            // TR401 - Rancangan RPP Inovatif - GRADED
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Rancangan RPP Inovatif'),
                'student_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'file_url' => 'assignments/siti_maryam_rpp_pai.pdf',
                'file_name' => 'RPP_PAI_SMASitiMaryam.pdf',
                'content' => null,
                'submitted_at' => now()->subDays(4)->setTime(15, 20),
                'is_late' => false,
                'attempt_number' => 1,
                'status' => 'graded',
                'grade' => 81,
                'feedback' => 'Konsepnya sudah bagus, namun mohon perjelas lagi bagian asesmen formatifnya. Pastikan terukur dan relevan dengan tujuan pembelajaran. Silakan direvisi.',
            ],
            
            // HD202 - Kritik Sanad Hadis - GRADED
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Kritik Sanad Hadis'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'file_url' => 'assignments/ahmad_faris_kritik_sanad.pdf',
                'file_name' => 'Kritik_Sanad_Hadis_AhmadFaris.pdf',
                'content' => null,
                'submitted_at' => now()->subDays(1)->setTime(18, 45),
                'is_late' => false,
                'attempt_number' => 1,
                'status' => 'graded',
                'grade' => 85,
                'feedback' => 'Analisis sanad yang baik dan teliti. Ahmad menunjukkan pemahaman yang baik tentang metodologi kritik sanad dasar. Perlu ditingkatkan lagi pada bagian kritik matan.',
            ],
            
            // HD202 - Setoran Hafalan: Hadits Pertama Arba'in
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Setoran Hafalan: Hadits Pertama Arba\'in'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'file_url' => 'assignments/ahmad_faris_hafalan_arbain.mp3',
                'file_name' => 'Hafalan_Hadits_Pertama_Arbaein_AhmadFaris.mp3',
                'content' => 'Inilah hafalan saya untuk hadits pertama kitab Arba\'in An-Nawawi tentang niat. Mohon koreksi makhraj dan harakatnya.',
                'submitted_at' => now()->subHours(12),
                'is_late' => false,
                'attempt_number' => 3,
                'status' => 'submitted',
                'grade' => null,
                'feedback' => null,
            ],
            
            // EK305 - Analisis Produk Bank Syariah - GRADED
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Analisis Produk Bank Syariah'),
                'student_id' => $this->getUserIdByEmail('abdullah@student.ulumcampus.com'),
                'file_url' => 'assignments/abdullah_produk_bank_syariah.pdf',
                'file_name' => 'Analisis_KPR_Murabahah_Abdullah.pdf',
                'content' => null,
                'submitted_at' => now()->subDays(10)->setTime(11, 30),
                'is_late' => false,
                'attempt_number' => 1,
                'status' => 'graded',
                'grade' => 90,
                'feedback' => 'Laporan analisis yang sangat komprehensif. Abdullah berhasil menjelaskan akad Murabahah dengan jelas dan mengidentifikasi potensi risiko dengan baik. Analisis solusi alternatif juga sangat tepat.',
            ],
            
            // SN701 - Proyek Akhir: Proposal Aplikasi Islami berbasis AI
            [
                'assignment_id' => $this->getAssignmentIdByTitle('Proyek Akhir: Proposal Aplikasi Islami berbasis AI'),
                'student_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'file_url' => 'assignments/siti_maryam_proposal_ai.pdf',
                'file_name' => 'Proposal_Aplikasi_QuranChatAI_SitiMaryam.pdf',
                'content' => 'Proposal untuk aplikasi chatbot berbasis AI yang dapat menjawab pertanyaan seputar Al-Qur\'an dengan referensi tafsir yang terpercaya.',
                'submitted_at' => now()->subDays(5)->setTime(14, 0),
                'is_late' => false,
                'attempt_number' => 1,
                'status' => 'submitted',
                'grade' => null,
                'feedback' => null,
            ],
        ];

        foreach ($submissions as $submissionData) {
            if ($submissionData['assignment_id'] && $submissionData['student_id']) {
                AssignmentSubmission::updateOrCreate(
                    [
                        'assignment_id' => $submissionData['assignment_id'],
                        'student_id' => $submissionData['student_id']
                    ],
                    $submissionData
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
