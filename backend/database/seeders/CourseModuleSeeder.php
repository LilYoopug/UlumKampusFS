<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseModule;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Str;

class CourseModuleSeeder extends Seeder
{
    private $moduleTypes = ['video', 'pdf', 'quiz', 'live'];
    private $moduleTypeWeights = [40, 30, 15, 15];

    private $moduleTopics = [
        'Aqidah' => [
            'Pengantar Ilmu Aqidah',
            'Rukun Iman dan Penjabarannya',
            'Tauhid Rububiyah',
            'Tauhid Uluhiyah',
            'Tauhid Asma wa Sifat',
            'Pembatal-pembatal Keimanan',
            'Syirik dan Macam-macamnya',
            'Bid\'ah dalam Agama',
            'Kufur dan Nifaq',
            'Wala\' dan Bara\'',
        ],
        'Fiqh' => [
            'Pengantar Ilmu Fiqh',
            'Thaharah (Bersuci)',
            'Shalat Wajib dan Sunnah',
            'Shalat Jamaah dan Jumat',
            'Puasa Ramadhan',
            'Zakat dan Macam-macamnya',
            'Ibadah Haji dan Umrah',
            'Muamalah dalam Islam',
            'Jual Beli yang Dilarang',
            'Nikah dan Perceraian',
        ],
        'Hadis' => [
            'Pengantar Ilmu Hadis',
            'Sejarah Kodifikasi Hadis',
            'Pembagian Hadis',
            'Kritik Sanad',
            'Kritik Matan',
            'Jarh wa Ta\'dil',
            'Rijal Al-Hadis',
            'Hadis Shahih',
            'Hadis Dhaif',
            'Hadis Maudhu\'',
        ],
        'Tafsir' => [
            'Pengantar Ulumul Quran',
            'Sejarah Turunnya Al-Quran',
            'Asbabun Nuzul',
            'Nasikh dan Mansukh',
            'Muhkam dan Mutasyabih',
            'Metodologi Tafsir',
            'Tafsir Bil Ma\'tsur',
            'Tafsir Bil Ra\'yi',
            'I\'jaz Al-Quran',
            'Qira\'at Al-Quran',
        ],
        'Ekonomi' => [
            'Pengantar Ekonomi Islam',
            'Prinsip Dasar Ekonomi Syariah',
            'Larangan Riba',
            'Akad-akad Muamalah',
            'Perbankan Syariah',
            'Asuransi Syariah (Takaful)',
            'Pasar Modal Syariah',
            'Zakat dan Wakaf Produktif',
            'Fintech Syariah',
            'Ekonomi Digital Islami',
        ],
        'Sejarah' => [
            'Periode Makkah',
            'Hijrah ke Madinah',
            'Perjanjian Hudaibiyah',
            'Fathu Makkah',
            'Khulafaur Rasyidin',
            'Dinasti Umayyah',
            'Dinasti Abbasiyah',
            'Peradaban Islam di Andalusia',
            'Kerajaan Islam di Nusantara',
            'Islam di Era Modern',
        ],
        'Teknologi' => [
            'Pengantar AI dan Machine Learning',
            'Etika Teknologi dalam Islam',
            'Aplikasi Al-Quran Digital',
            'Fintech Syariah',
            'Blockchain untuk Industri Halal',
            'E-Learning Platform',
            'Smart Mosque',
            'Islamic Chatbot',
            'Digitalisasi Wakaf',
            'Cyber Security Islam',
        ],
        'Pendidikan' => [
            'Filosofi Pendidikan Islam',
            'Kurikulum Pendidikan Agama',
            'Metode Pembelajaran Aktif',
            'Media Pembelajaran Modern',
            'Evaluasi Pembelajaran',
            'Psikologi Pendidikan Islam',
            'Manajemen Kelas',
            'Pendidikan Karakter',
            'Pendidikan Anak Usia Dini',
            'Pendidikan Tinggi Islam',
        ],
    ];

    public function run(): void
    {
        $courses = Course::where('status', 'Published')->get();
        $lecturers = User::where('role', 'dosen')->get();
        
        if ($courses->isEmpty() || $lecturers->isEmpty()) {
            return;
        }

        $moduleCount = 0;

        foreach ($courses as $course) {
            // Each course has 8-16 modules
            $numModules = rand(8, 16);
            $topicCategory = $this->determineTopicCategory($course->name);
            $topics = $this->moduleTopics[$topicCategory] ?? $this->moduleTopics['Aqidah'];
            
            for ($order = 1; $order <= $numModules; $order++) {
                $type = $this->weightedRandom($this->moduleTypes, $this->moduleTypeWeights);
                $topic = $topics[($order - 1) % count($topics)];
                
                $title = "Modul {$order}: {$topic}";
                $isPublished = $order <= ($numModules * 0.8); // 80% of modules are published
                
                $duration = match($type) {
                    'video' => rand(15, 90),
                    'reading' => rand(10, 30),
                    'quiz' => rand(15, 45),
                    'live' => rand(60, 120),
                    'discussion' => null,
                    'assignment' => null,
                    default => rand(20, 60),
                };

                $videoUrl = null;
                $documentUrl = null;
                $liveUrl = null;
                $startTime = null;
                
                if ($type === 'video') {
                    $videoUrl = 'https://example.com/videos/' . Str::slug($title) . '.mp4';
                } elseif ($type === 'pdf') {
                    $documentUrl = 'https://example.com/docs/' . Str::slug($title) . '.pdf';
                } elseif ($type === 'live') {
                    $liveUrl = 'https://zoom.us/j/' . rand(100000000, 999999999);
                    $startTime = now()->addDays(rand(1, 30))->setTime(rand(8, 16), 0);
                }

                CourseModule::create([
                    'course_id' => $course->id,
                    'type' => $type,
                    'title' => $title,
                    'description' => $this->generateDescription($topic, $type),
                    'content' => $this->generateContent($topic, $type),
                    'order' => $order,
                    'is_published' => $isPublished,
                    'published_at' => $isPublished ? now()->subDays(rand(1, 60)) : null,
                    'video_url' => $videoUrl,
                    'document_url' => $documentUrl,
                    'live_url' => $liveUrl,
                    'start_time' => $startTime,
                ]);
                
                $moduleCount++;
            }
        }
        
        $this->command->info("Created {$moduleCount} course modules!");
    }

    private function determineTopicCategory(string $courseName): string
    {
        $courseName = strtolower($courseName);
        
        if (str_contains($courseName, 'aqidah') || str_contains($courseName, 'tauhid')) return 'Aqidah';
        if (str_contains($courseName, 'fiqh') || str_contains($courseName, 'fikih')) return 'Fiqh';
        if (str_contains($courseName, 'hadis') || str_contains($courseName, 'hadits')) return 'Hadis';
        if (str_contains($courseName, 'tafsir') || str_contains($courseName, 'quran')) return 'Tafsir';
        if (str_contains($courseName, 'ekonomi') || str_contains($courseName, 'muamalah') || str_contains($courseName, 'bank')) return 'Ekonomi';
        if (str_contains($courseName, 'sejarah') || str_contains($courseName, 'peradaban')) return 'Sejarah';
        if (str_contains($courseName, 'teknologi') || str_contains($courseName, 'sistem') || str_contains($courseName, 'informasi')) return 'Teknologi';
        if (str_contains($courseName, 'pendidikan') || str_contains($courseName, 'tarbiyah') || str_contains($courseName, 'pembelajaran')) return 'Pendidikan';
        
        return array_keys($this->moduleTopics)[array_rand(array_keys($this->moduleTopics))];
    }

    private function generateDescription(string $topic, string $type): string
    {
        $typeDesc = match($type) {
            'video' => 'Video pembelajaran',
            'reading' => 'Materi bacaan',
            'quiz' => 'Kuis evaluasi',
            'live' => 'Sesi live interaktif',
            'discussion' => 'Forum diskusi',
            'assignment' => 'Tugas praktik',
            default => 'Modul pembelajaran',
        };
        
        return "{$typeDesc} tentang {$topic}. Pelajari dengan seksama dan catat poin-poin penting untuk pemahaman yang lebih baik.";
    }

    private function generateContent(string $topic, string $type): string
    {
        return match($type) {
            'video' => "# {$topic}\n\nTonton video pembelajaran ini untuk memahami konsep dasar tentang {$topic}. Durasi video disesuaikan dengan kompleksitas materi.\n\n## Poin Pembelajaran\n1. Definisi dan konsep dasar\n2. Dalil-dalil yang berkaitan\n3. Penerapan dalam kehidupan\n4. Kesimpulan dan refleksi",
            'reading' => "# {$topic}\n\n## Pendahuluan\nMateri ini membahas tentang {$topic} secara komprehensif.\n\n## Pembahasan\nBacalah materi berikut dengan seksama...\n\n## Referensi\n- Kitab rujukan utama\n- Jurnal ilmiah terkait",
            'quiz' => "# Kuis: {$topic}\n\nKuis ini bertujuan untuk mengukur pemahaman Anda tentang {$topic}.\n\n## Petunjuk\n- Baca setiap soal dengan teliti\n- Waktu pengerjaan terbatas\n- Nilai minimum kelulusan: 70",
            'live' => "# Sesi Live: {$topic}\n\nSesi pembelajaran langsung dengan dosen. Siapkan pertanyaan Anda.\n\n## Agenda\n1. Review materi sebelumnya\n2. Pembahasan materi baru\n3. Tanya jawab\n4. Kesimpulan",
            'discussion' => "# Diskusi: {$topic}\n\nBagikan pemikiran dan pertanyaan Anda tentang {$topic} di forum ini.\n\n## Panduan Diskusi\n- Hormati pendapat orang lain\n- Sertakan dalil dalam argumen\n- Gunakan bahasa yang baik",
            'assignment' => "# Tugas: {$topic}\n\nKerjakan tugas berikut untuk memperdalam pemahaman tentang {$topic}.\n\n## Instruksi\nBaca instruksi dengan seksama sebelum mengerjakan.",
            default => "# {$topic}\n\nMateri pembelajaran tentang {$topic}.",
        };
    }

    private function generateResources(string $topic): array
    {
        $resources = [];
        $numResources = rand(1, 4);
        
        $resourceTypes = [
            ['type' => 'pdf', 'name' => 'Materi PDF', 'url' => '#'],
            ['type' => 'link', 'name' => 'Referensi Tambahan', 'url' => '#'],
            ['type' => 'video', 'name' => 'Video Pendukung', 'url' => '#'],
            ['type' => 'document', 'name' => 'Dokumen Latihan', 'url' => '#'],
        ];
        
        for ($i = 0; $i < $numResources; $i++) {
            $resources[] = $resourceTypes[array_rand($resourceTypes)];
        }
        
        return $resources;
    }

    private function weightedRandom(array $items, array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $current = 0;
        foreach ($items as $index => $item) {
            $current += $weights[$index];
            if ($random <= $current) {
                return $item;
            }
        }
        
        return $items[0];
    }
}
