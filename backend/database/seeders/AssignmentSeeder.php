<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;

class AssignmentSeeder extends Seeder
{
    private $assignmentTypes = [
        'file' => ['pdf', 'doc,docx', 'pdf,doc,docx', 'pptx,pdf'],
        'hafalan' => null,
        'text' => null,
        'link' => null,
        'mixed' => ['pdf,doc,docx'],
    ];

    private $assignmentTitles = [
        'Esai Reflektif',
        'Analisis Studi Kasus',
        'Presentasi Materi',
        'Setoran Hafalan',
        'Laporan Penelitian',
        'Proyek Akhir',
        'Kritik Literatur',
        'Makalah Ilmiah',
        'Resume Bab',
        'Diskusi Kelompok',
        'Ujian Tengah Semester',
        'Ujian Akhir Semester',
        'Praktikum',
        'Observasi Lapangan',
        'Proposal Penelitian',
    ];

    private $topics = [
        'Aqidah' => ['Pilar Keimanan', 'Tauhid Uluhiyah', 'Asma wa Sifat', 'Iman kepada Malaikat', 'Iman kepada Kitab', 'Iman kepada Rasul', 'Hari Akhir', 'Qada dan Qadar'],
        'Fiqh' => ['Thaharah', 'Shalat', 'Zakat', 'Puasa', 'Haji', 'Muamalah', 'Nikah', 'Jinayat', 'Mawaris'],
        'Ekonomi' => ['Riba', 'Bank Syariah', 'Asuransi Syariah', 'Pasar Modal Syariah', 'Zakat Produktif', 'Wakaf', 'Murabahah', 'Mudharabah', 'Musyarakah'],
        'Hadis' => ['Sanad', 'Matan', 'Rawi', 'Jarh wa Tadil', 'Shahih Bukhari', 'Shahih Muslim', 'Arbaein Nawawi', 'Bulughul Maram'],
        'Tafsir' => ['Ulumul Quran', 'Asbabun Nuzul', 'Nasikh Mansukh', 'Makki Madani', 'Tafsir Maudui', 'Tafsir Tahlili'],
        'Sejarah' => ['Sirah Nabawiyah', 'Khulafaur Rasyidin', 'Dinasti Umayyah', 'Dinasti Abbasiyah', 'Peradaban Islam', 'Ilmuwan Muslim'],
        'Teknologi' => ['AI dalam Islam', 'Fintech Syariah', 'Blockchain Halal', 'E-Learning Islami', 'Aplikasi Quran', 'Sistem Informasi Masjid'],
        'Pendidikan' => ['Kurikulum PAI', 'Metode Pembelajaran', 'Evaluasi Pendidikan', 'Psikologi Pendidikan', 'Manajemen Kelas', 'Media Pembelajaran'],
    ];

    public function run(): void
    {
        $courses = Course::where('status', 'Published')->get();
        $lecturers = User::where('role', 'dosen')->get();
        
        if ($courses->isEmpty() || $lecturers->isEmpty()) {
            return;
        }

        $assignmentCount = 0;

        foreach ($courses as $course) {
            // Each course has 3-8 assignments
            $numAssignments = rand(3, 8);
            $lecturer = $lecturers->random();
            
            // Determine topic category based on course name
            $topicCategory = $this->determineTopicCategory($course->name);
            $topics = $this->topics[$topicCategory] ?? $this->topics['Aqidah'];

            for ($i = 1; $i <= $numAssignments; $i++) {
                $titleType = $this->assignmentTitles[array_rand($this->assignmentTitles)];
                $topic = $topics[array_rand($topics)];
                $title = "{$titleType}: {$topic}";
                
                $submissionType = array_keys($this->assignmentTypes)[array_rand(array_keys($this->assignmentTypes))];
                $fileTypeOptions = $this->assignmentTypes[$submissionType];
                $allowedFileTypes = is_array($fileTypeOptions) ? $fileTypeOptions[array_rand($fileTypeOptions)] : $fileTypeOptions;
                
                // Randomize due dates: some past, some future
                $daysOffset = rand(-30, 60);
                $dueDate = now()->addDays($daysOffset)->setTime(23, 59);
                
                $isPublished = rand(1, 100) <= 85; // 85% published
                $publishedAt = $isPublished ? now()->subDays(rand(1, 30)) : null;
                
                $allowLate = rand(1, 100) <= 70; // 70% allow late
                $latePenalty = $allowLate ? (float)(rand(5, 25)) : 0;
                
                $maxPoints = [100.00, 100.00, 100.00, 50.00, 25.00][array_rand([100.00, 100.00, 100.00, 50.00, 25.00])];
                
                Assignment::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'title' => $title,
                    ],
                    [
                        'created_by' => $lecturer->id,
                        'description' => $this->generateDescription($titleType, $topic, $topicCategory),
                        'instructions' => $this->generateInstructions($titleType, $submissionType),
                        'due_date' => $dueDate,
                        'max_points' => $maxPoints,
                        'submission_type' => $submissionType,
                        'allowed_file_types' => $allowedFileTypes,
                        'max_file_size' => $submissionType === 'file' ? rand(5, 20) * 1048576 : null,
                        'attempts_allowed' => $submissionType === 'hafalan' ? rand(3, 5) : 1,
                        'is_published' => $isPublished,
                        'published_at' => $publishedAt,
                        'allow_late_submission' => $allowLate,
                        'late_penalty' => $latePenalty,
                        'order' => $i,
                    ]
                );
                
                $assignmentCount++;
            }
        }
        
        $this->command->info("Created {$assignmentCount} assignments!");
    }

    private function determineTopicCategory(string $courseName): string
    {
        $courseName = strtolower($courseName);
        
        if (str_contains($courseName, 'aqidah') || str_contains($courseName, 'tauhid')) return 'Aqidah';
        if (str_contains($courseName, 'fiqh') || str_contains($courseName, 'fikih')) return 'Fiqh';
        if (str_contains($courseName, 'ekonomi') || str_contains($courseName, 'muamalah') || str_contains($courseName, 'bank')) return 'Ekonomi';
        if (str_contains($courseName, 'hadis') || str_contains($courseName, 'hadits')) return 'Hadis';
        if (str_contains($courseName, 'tafsir') || str_contains($courseName, 'quran')) return 'Tafsir';
        if (str_contains($courseName, 'sejarah') || str_contains($courseName, 'peradaban')) return 'Sejarah';
        if (str_contains($courseName, 'teknologi') || str_contains($courseName, 'sistem') || str_contains($courseName, 'informasi')) return 'Teknologi';
        if (str_contains($courseName, 'pendidikan') || str_contains($courseName, 'tarbiyah') || str_contains($courseName, 'pembelajaran')) return 'Pendidikan';
        
        return array_keys($this->topics)[array_rand(array_keys($this->topics))];
    }

    private function generateDescription(string $titleType, string $topic, string $category): string
    {
        $descriptions = [
            'Esai Reflektif' => "Tulis esai reflektif 2-3 halaman tentang {$topic}. Hubungkan konsep dengan pengalaman pribadi dan kehidupan sehari-hari. Gunakan minimal 3 referensi dari Al-Qur'an atau hadis shahih.",
            'Analisis Studi Kasus' => "Analisis studi kasus yang diberikan tentang {$topic}. Identifikasi permasalahan, analisis berdasarkan perspektif Islam, dan berikan solusi yang sesuai dengan syariat.",
            'Presentasi Materi' => "Buat presentasi 10-15 slide tentang {$topic}. Sajikan dengan visualisasi yang menarik dan mudah dipahami. Siapkan poin-poin diskusi untuk sesi tanya jawab.",
            'Setoran Hafalan' => "Hafalkan materi {$topic} sesuai dengan ketentuan yang telah dijelaskan. Pastikan pelafalan yang baik dengan makhraj dan tajwid yang benar.",
            'Laporan Penelitian' => "Buat laporan penelitian mini tentang {$topic}. Gunakan metodologi yang tepat dan sertakan analisis data serta kesimpulan yang berbasis dalil.",
            'Proyek Akhir' => "Kerjakan proyek akhir tentang {$topic}. Proyek harus menunjukkan penguasaan materi selama satu semester dan memiliki kontribusi praktis.",
            'Kritik Literatur' => "Lakukan kritik terhadap satu karya ilmiah tentang {$topic}. Evaluasi metodologi, argumen, dan relevansi dengan kondisi kontemporer.",
            'Makalah Ilmiah' => "Tulis makalah ilmiah tentang {$topic} dengan format jurnal standar. Minimal 5 halaman dengan referensi dari sumber primer dan sekunder.",
            'Resume Bab' => "Buat resume dari bab yang membahas {$topic}. Identifikasi poin-poin utama dan buatlah mind map atau infografis pendukung.",
            'Diskusi Kelompok' => "Ikuti diskusi kelompok tentang {$topic}. Setiap anggota harus menyampaikan pendapat dan merespon argumen anggota lain.",
            'Ujian Tengah Semester' => "Ujian tengah semester mencakup materi {$topic} dan materi sebelumnya. Pelajari semua catatan kuliah dan referensi yang diberikan.",
            'Ujian Akhir Semester' => "Ujian akhir semester bersifat komprehensif, termasuk {$topic}. Persiapkan diri dengan baik dan berdoa sebelum ujian.",
            'Praktikum' => "Lakukan praktikum tentang {$topic}. Dokumentasikan proses dan hasil dalam laporan praktikum sesuai format yang ditentukan.",
            'Observasi Lapangan' => "Lakukan observasi lapangan terkait {$topic}. Catat temuan dan analisis relevansinya dengan teori yang dipelajari.",
            'Proposal Penelitian' => "Susun proposal penelitian tentang {$topic}. Jelaskan latar belakang, tujuan, metodologi, dan expected outcome.",
        ];
        
        return $descriptions[$titleType] ?? "Kerjakan tugas tentang {$topic} sesuai dengan instruksi yang diberikan.";
    }

    private function generateInstructions(string $titleType, string $submissionType): string
    {
        $formatInstructions = match($submissionType) {
            'file' => 'Kumpulkan dalam format file yang ditentukan. Pastikan nama file sesuai format: NIM_NamaLengkap_JudulTugas.',
            'hafalan' => 'Rekam hafalan Anda dengan audio yang jelas. Pastikan pelafalan tartil dan sesuai kaidah tajwid.',
            'text' => 'Ketik jawaban langsung di form yang disediakan. Perhatikan ejaan dan tata bahasa.',
            'quiz' => 'Jawab semua pertanyaan dalam waktu yang ditentukan. Bacalah soal dengan teliti sebelum menjawab.',
            default => 'Ikuti instruksi yang diberikan dengan seksama.',
        };
        
        $generalInstructions = match($titleType) {
            'Esai Reflektif' => 'Gunakan gaya bahasa formal akademik. Cantumkan daftar pustaka di akhir esai.',
            'Analisis Studi Kasus' => 'Analisis harus sistematis dengan kerangka yang jelas. Sertakan diagram alur jika diperlukan.',
            'Presentasi Materi' => 'Slide harus informatif namun tidak terlalu padat. Siapkan catatan pembicara.',
            'Setoran Hafalan' => 'Boleh diulang maksimal sesuai ketentuan attempts. Nilai terbaik yang akan diambil.',
            default => 'Kerjakan dengan jujur dan penuh tanggung jawab.',
        };
        
        return "{$formatInstructions}\n\n{$generalInstructions}";
    }
}
