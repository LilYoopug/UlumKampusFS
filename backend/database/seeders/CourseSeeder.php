<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Get all lecturers
        $lecturers = User::where('role', 'dosen')->get();
        
        // Define course templates for each faculty
        $courseTemplates = [
            'ushuluddin' => [
                ['code' => 'AQ', 'prefix' => 'Aqidah', 'topics' => ['Pengantar Aqidah Islamiyah', 'Tauhid Rububiyah', 'Tauhid Uluhiyah', 'Asma wa Sifat', 'Iman kepada Malaikat', 'Iman kepada Kitab-Kitab', 'Iman kepada Rasul', 'Iman kepada Hari Akhir', 'Iman kepada Qadha dan Qadar', 'Manhaj Salaf dalam Aqidah']],
                ['code' => 'TF', 'prefix' => 'Tafsir', 'topics' => ['Pengantar Ulumul Quran', 'Metodologi Tafsir', 'Tafsir Juz Amma', 'Tafsir Ayat Ahkam', 'Tafsir Maudhu\'i', 'Sejarah Kodifikasi Al-Quran', 'Ilmu Asbabun Nuzul', 'Ilmu Nasikh Mansukh', 'Tafsir Surah Al-Baqarah', 'Tafsir Surah Ali Imran']],
                ['code' => 'HD', 'prefix' => 'Hadis', 'topics' => ['Pengantar Ilmu Hadis', 'Musthalah al-Hadis', 'Kritik Sanad dan Matan', 'Ilmu Rijal al-Hadis', 'Hadis Ahkam', 'Takhrij al-Hadis', 'Kutub al-Sittah', 'Hadis Qudsi', 'Metodologi Ulama Hadis', 'Studi Kitab Shahih Bukhari']],
            ],
            'syariah' => [
                ['code' => 'FQ', 'prefix' => 'Fiqh', 'topics' => ['Fiqh Ibadah', 'Fiqh Muamalat Kontemporer', 'Fiqh Munakahat', 'Fiqh Mawaris', 'Fiqh Jinayat', 'Fiqh Siyasah', 'Qawaid Fiqhiyyah', 'Ushul Fiqh', 'Fiqh Zakat dan Wakaf', 'Perbandingan Mazhab']],
                ['code' => 'HK', 'prefix' => 'Hukum', 'topics' => ['Hukum Ekonomi Syariah', 'Hukum Perbankan Syariah', 'Hukum Keluarga Islam', 'Hukum Waris Islam', 'Hukum Acara Peradilan Agama', 'Hukum Pidana Islam', 'Hukum Tata Negara Islam', 'Filsafat Hukum Islam', 'Metodologi Ijtihad', 'Fatwa Kontemporer']],
            ],
            'tarbiyah' => [
                ['code' => 'TR', 'prefix' => 'Pendidikan', 'topics' => ['Metodologi Pengajaran PAI', 'Psikologi Pendidikan Islam', 'Manajemen Pendidikan Islam', 'Evaluasi Pembelajaran PAI', 'Kurikulum PAI', 'Media Pembelajaran PAI', 'Micro Teaching', 'Praktik Mengajar', 'Pendidikan Karakter Islami', 'Teknologi Pendidikan']],
                ['code' => 'BA', 'prefix' => 'Bahasa Arab', 'topics' => ['Nahwu Dasar', 'Nahwu Lanjut', 'Sharaf', 'Balaghah', 'Muhadatsah', 'Kitabah', 'Qiraah', 'Tarjamah', 'Insya', 'Adab Arab']],
            ],
            'ekonomi' => [
                ['code' => 'EK', 'prefix' => 'Ekonomi', 'topics' => ['Manajemen Keuangan Syariah', 'Akad Perbankan Syariah', 'Akuntansi Syariah', 'Audit Lembaga Keuangan Syariah', 'Pasar Modal Syariah', 'Asuransi Syariah', 'Fintech Syariah', 'Manajemen Risiko Syariah', 'Investasi Syariah', 'Ekonomi Makro Islam']],
                ['code' => 'BS', 'prefix' => 'Bisnis', 'topics' => ['Kewirausahaan Islam', 'Manajemen Bisnis Syariah', 'Pemasaran Syariah', 'Etika Bisnis Islam', 'Manajemen SDM Syariah', 'Studi Kelayakan Bisnis', 'Manajemen Operasi Syariah', 'E-Commerce Syariah', 'Manajemen Rantai Pasok Halal', 'Bisnis Internasional Syariah']],
            ],
            'adab' => [
                ['code' => 'AD', 'prefix' => 'Sejarah', 'topics' => ['Sejarah Peradaban Islam', 'Sejarah Islam Indonesia', 'Sejarah Khulafaur Rasyidin', 'Sejarah Dinasti Umayyah', 'Sejarah Dinasti Abbasiyah', 'Sejarah Islam Andalusia', 'Sejarah Islam Modern', 'Historiografi Islam', 'Sejarah Pemikiran Islam', 'Arkeologi Islam']],
                ['code' => 'SS', 'prefix' => 'Sastra', 'topics' => ['Sastra Arab Klasik', 'Sastra Arab Modern', 'Kritik Sastra Arab', 'Syair Arab Jahiliyah', 'Syair Arab Islami', 'Prosa Arab', 'Maqamat', 'Linguistik Arab', 'Semantik Arab', 'Stilistika Arab']],
            ],
            'psikologi' => [
                ['code' => 'PS', 'prefix' => 'Psikologi', 'topics' => ['Pengantar Psikologi Islam', 'Psikologi Perkembangan Islami', 'Psikologi Kepribadian Islam', 'Psikologi Sosial Islam', 'Konseling Islami', 'Psikoterapi Islam', 'Psikologi Klinis Islam', 'Psikologi Keluarga Islam', 'Kesehatan Mental Islam', 'Tazkiyatun Nafs']],
            ],
            'sains' => [
                ['code' => 'SN', 'prefix' => 'Teknologi', 'topics' => ['AI & Etika Digital Islami', 'Pemrograman Dasar', 'Basis Data', 'Jaringan Komputer', 'Sistem Informasi Islam', 'Web Development', 'Mobile App Development', 'Keamanan Siber', 'Machine Learning', 'Big Data Analytics']],
                ['code' => 'BH', 'prefix' => 'Biologi', 'topics' => ['Biologi Halal', 'Mikrobiologi Pangan', 'Bioteknologi Islam', 'Sains Halal', 'Teknologi Pangan Halal', 'Farmasi Halal', 'Kosmetik Halal', 'Pengelolaan Limbah Islami', 'Lingkungan Hidup Islam', 'Bioetika Islam']],
            ],
            'pascasarjana' => [
                ['code' => 'PG', 'prefix' => 'Pascasarjana', 'topics' => ['Kajian Islam Kontemporer', 'Fiqh Minoritas', 'Kepemimpinan Islam', 'Metodologi Penelitian Islam', 'Seminar Tesis', 'Studi Islam Interdisipliner', 'Pemikiran Islam Modern', 'Ekonomi Politik Islam', 'Hukum Islam Internasional', 'Manajemen Dakwah Strategis']],
            ],
        ];

        $statuses = ['Published', 'Published', 'Published', 'Published', 'Draft', 'Archived'];
        $modes = ['VOD', 'VOD', 'VOD', 'Live', 'Hybrid'];
        $semesters = ['Fall', 'Spring'];
        $years = [2023, 2024, 2025];
        
        $courses = [];
        $courseId = 100;
        
        foreach ($courseTemplates as $facultyId => $prefixes) {
            $facultyLecturers = $lecturers->where('faculty_id', $facultyId)->values();
            
            foreach ($prefixes as $prefixData) {
                foreach ($prefixData['topics'] as $index => $topic) {
                    $level = ($index % 5) + 1; // 1-5 level
                    $code = $prefixData['code'] . $level . str_pad($courseId % 100, 2, '0', STR_PAD_LEFT);
                    $lecturer = $facultyLecturers->count() > 0 
                        ? $facultyLecturers[$index % $facultyLecturers->count()]
                        : $lecturers->random();
                    
                    $status = $statuses[array_rand($statuses)];
                    $mode = $modes[array_rand($modes)];
                    $semester = $semesters[array_rand($semesters)];
                    $year = $years[array_rand($years)];
                    $credits = rand(2, 4);
                    $capacity = rand(25, 60);
                    
                    $courses[] = [
                        'id' => $code,
                        'faculty_id' => $facultyId,
                        'major_id' => $this->getMajorForFaculty($facultyId),
                        'instructor_id' => $lecturer->id,
                        'code' => $code,
                        'name' => $topic,
                        'description' => 'Mata kuliah ' . $topic . ' membahas secara komprehensif tentang ' . strtolower($topic) . ' dalam perspektif Islam dan aplikasinya dalam kehidupan modern.',
                        'credit_hours' => $credits,
                        'capacity' => $capacity,
                        'current_enrollment' => rand(0, $capacity),
                        'semester' => $semester,
                        'year' => $year,
                        'schedule' => $this->generateSchedule(),
                        'room' => ucfirst($facultyId) . ' Building ' . rand(101, 305),
                        'image_url' => 'https://picsum.photos/seed/' . strtolower(str_replace(' ', '', $topic)) . '/600/400',
                        'is_active' => $status === 'Published',
                        'status' => $status,
                        'mode' => $mode,
                        'learning_objectives' => $this->generateLearningObjectives($topic),
                        'syllabus_data' => $this->generateSyllabus($topic),
                    ];
                    
                    $courseId++;
                }
            }
        }
        
        // Insert all courses
        foreach ($courses as $courseData) {
            Course::updateOrCreate(
                ['code' => $courseData['code']],
                $courseData
            );
        }
        
        $this->command->info('Created ' . count($courses) . ' courses successfully!');
    }
    
    private function getMajorForFaculty($facultyId)
    {
        $majors = [
            'syariah' => ['hes', 'ahwal-syakhshiyyah', 'siyasah', 'peradilan-agama'],
            'ushuluddin' => ['aqidah', 'tafsir', 'hadis', 'perbandingan-agama', 'kpi'],
            'tarbiyah' => ['pai', 'pba', 'pgmi', 'mpi', 'tekpen-islami'],
            'ekonomi' => ['ekonomi-islam', 'perbankan-syariah', 'akuntansi-syariah', 'manajemen-syariah', 'keuangan-investasi-syariah'],
            'adab' => ['spi', 'bsa', 'english-islamic', 'islamic-civ'],
            'psikologi' => ['psikologi-islam', 'bk-islami', 'sosiologi-islam', 'studi-gender'],
            'sains' => ['sains-etika', 'ti-islami', 'industri-halal', 'farmasi-halal', 'kesehatan-syariah'],
            'pascasarjana' => ['kajian-kontemporer', 'fiqh-aqalliyat', 'islamic-leadership'],
        ];
        
        return $majors[$facultyId][array_rand($majors[$facultyId])];
    }
    
    private function generateSchedule()
    {
        $days = [
            ['Mon', 'Wed'],
            ['Tue', 'Thu'],
            ['Mon', 'Wed', 'Fri'],
            ['Tue', 'Thu'],
            ['Fri'],
            ['Sat'],
        ];
        $times = ['08:00-09:30', '09:00-10:30', '10:00-11:30', '13:00-14:30', '14:00-15:30', '15:00-16:30'];
        
        $selectedDays = $days[array_rand($days)];
        $selectedTime = $times[array_rand($times)];
        
        return implode('/', $selectedDays) . ' ' . $selectedTime;
    }
    
    private function generateLearningObjectives($topic)
    {
        return [
            'Memahami konsep dasar dan fundamental ' . strtolower($topic) . '.',
            'Menganalisis teori dan praktik ' . strtolower($topic) . ' dalam konteks Islam.',
            'Menerapkan prinsip-prinsip ' . strtolower($topic) . ' dalam studi kasus.',
            'Mengevaluasi perkembangan ' . strtolower($topic) . ' kontemporer.',
        ];
    }
    
    private function generateSyllabus($topic)
    {
        return [
            ['week' => 1, 'topic' => 'Pengantar ' . $topic, 'description' => 'Memahami definisi, ruang lingkup, dan urgensi mempelajari ' . strtolower($topic) . '.'],
            ['week' => 2, 'topic' => 'Sejarah dan Perkembangan', 'description' => 'Menelusuri sejarah perkembangan ' . strtolower($topic) . ' dari masa klasik hingga kontemporer.'],
            ['week' => 3, 'topic' => 'Metodologi dan Pendekatan', 'description' => 'Mempelajari berbagai metodologi dan pendekatan dalam ' . strtolower($topic) . '.'],
            ['week' => 4, 'topic' => 'Aplikasi Praktis', 'description' => 'Menerapkan teori ' . strtolower($topic) . ' dalam studi kasus dan praktik.'],
        ];
    }
}
