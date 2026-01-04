<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicCalendarEvent;
use Carbon\Carbon;

class AcademicCalendarSeeder extends Seeder
{
    private $academicEvents = [
        // Semester Ganjil 2025/2026
        ['title' => 'Awal Semester Ganjil 2025/2026', 'category' => 'academic', 'start' => '2025-09-01', 'end' => '2025-09-01'],
        ['title' => 'Masa Orientasi Mahasiswa Baru (OSPEK)', 'category' => 'activity', 'start' => '2025-09-01', 'end' => '2025-09-05'],
        ['title' => 'Perkuliahan Dimulai', 'category' => 'academic', 'start' => '2025-09-08', 'end' => '2025-09-08'],
        ['title' => 'Batas Akhir Pengisian KRS', 'category' => 'deadline', 'start' => '2025-09-15', 'end' => '2025-09-15'],
        ['title' => 'Batas Akhir Pembatalan Mata Kuliah', 'category' => 'deadline', 'start' => '2025-09-22', 'end' => '2025-09-22'],
        ['title' => 'Ujian Tengah Semester Ganjil', 'category' => 'exam', 'start' => '2025-10-20', 'end' => '2025-10-31'],
        ['title' => 'Libur Maulid Nabi Muhammad SAW', 'category' => 'holiday', 'start' => '2025-09-15', 'end' => '2025-09-15'],
        ['title' => 'Wisuda Periode Oktober', 'category' => 'ceremony', 'start' => '2025-10-15', 'end' => '2025-10-15'],
        ['title' => 'Ujian Akhir Semester Ganjil', 'category' => 'exam', 'start' => '2025-12-15', 'end' => '2025-12-26'],
        ['title' => 'Libur Semester Ganjil', 'category' => 'holiday', 'start' => '2025-12-27', 'end' => '2026-01-10'],
        ['title' => 'Libur Tahun Baru Masehi', 'category' => 'holiday', 'start' => '2026-01-01', 'end' => '2026-01-01'],
        
        // Semester Genap 2025/2026
        ['title' => 'Awal Semester Genap 2025/2026', 'category' => 'academic', 'start' => '2026-01-12', 'end' => '2026-01-12'],
        ['title' => 'Perkuliahan Semester Genap Dimulai', 'category' => 'academic', 'start' => '2026-01-12', 'end' => '2026-01-12'],
        ['title' => 'Batas Akhir Pengisian KRS Genap', 'category' => 'deadline', 'start' => '2026-01-19', 'end' => '2026-01-19'],
        ['title' => 'Libur Isra Miraj', 'category' => 'holiday', 'start' => '2026-02-07', 'end' => '2026-02-07'],
        ['title' => 'Ujian Tengah Semester Genap', 'category' => 'exam', 'start' => '2026-03-16', 'end' => '2026-03-27'],
        ['title' => 'Awal Ramadhan 1447 H', 'category' => 'holiday', 'start' => '2026-03-01', 'end' => '2026-03-01'],
        ['title' => 'Libur Ramadhan', 'category' => 'holiday', 'start' => '2026-03-20', 'end' => '2026-04-05'],
        ['title' => 'Hari Raya Idul Fitri 1447 H', 'category' => 'holiday', 'start' => '2026-03-30', 'end' => '2026-04-05'],
        ['title' => 'Perkuliahan Pasca Idul Fitri', 'category' => 'academic', 'start' => '2026-04-06', 'end' => '2026-04-06'],
        ['title' => 'Ujian Akhir Semester Genap', 'category' => 'exam', 'start' => '2026-06-01', 'end' => '2026-06-12'],
        ['title' => 'Wisuda Periode Juni', 'category' => 'ceremony', 'start' => '2026-06-20', 'end' => '2026-06-20'],
        ['title' => 'Libur Semester Genap', 'category' => 'holiday', 'start' => '2026-06-22', 'end' => '2026-08-31'],
        ['title' => 'Hari Raya Idul Adha 1447 H', 'category' => 'holiday', 'start' => '2026-06-06', 'end' => '2026-06-08'],
        ['title' => 'Tahun Baru Hijriyah 1448 H', 'category' => 'holiday', 'start' => '2026-06-26', 'end' => '2026-06-26'],
        
        // Pendaftaran Mahasiswa Baru
        ['title' => 'Pendaftaran Mahasiswa Baru Gelombang 1', 'category' => 'registration', 'start' => '2026-01-05', 'end' => '2026-02-28'],
        ['title' => 'Tes Masuk Gelombang 1', 'category' => 'exam', 'start' => '2026-03-07', 'end' => '2026-03-07'],
        ['title' => 'Pengumuman Hasil Seleksi Gelombang 1', 'category' => 'academic', 'start' => '2026-03-14', 'end' => '2026-03-14'],
        ['title' => 'Pendaftaran Mahasiswa Baru Gelombang 2', 'category' => 'registration', 'start' => '2026-04-01', 'end' => '2026-05-31'],
        ['title' => 'Tes Masuk Gelombang 2', 'category' => 'exam', 'start' => '2026-06-07', 'end' => '2026-06-07'],
        ['title' => 'Pengumuman Hasil Seleksi Gelombang 2', 'category' => 'academic', 'start' => '2026-06-14', 'end' => '2026-06-14'],
        ['title' => 'Daftar Ulang Mahasiswa Baru', 'category' => 'registration', 'start' => '2026-07-01', 'end' => '2026-08-15'],
    ];

    private $seminarTopics = [
        'Ekonomi Syariah di Era Digital',
        'Fiqh Kontemporer dan Tantangan Zaman',
        'Pendidikan Islam Abad 21',
        'Peran Ulama dalam Masyarakat Modern',
        'Dakwah di Era Media Sosial',
        'Keluarga Sakinah dalam Islam',
        'Entrepreneurship Islami',
        'Kepemimpinan dalam Islam',
        'Kesehatan Mental Perspektif Islam',
        'Lingkungan Hidup dan Fiqh Ekologi',
    ];

    private $workshopTopics = [
        'Penulisan Karya Ilmiah',
        'Public Speaking untuk Dakwah',
        'Desain Grafis untuk Media Dakwah',
        'Kewirausahaan Mahasiswa',
        'Digital Marketing Halal',
        'Manajemen Waktu Islami',
        'Tahsin dan Tajwid Praktis',
        'Bahasa Arab Komunikatif',
        'Kepemimpinan Organisasi',
        'Fotografi dan Videografi',
    ];

    public function run(): void
    {
        $eventCount = 0;

        // Create main academic calendar events
        foreach ($this->academicEvents as $event) {
            AcademicCalendarEvent::create([
                'id' => 'evt' . str_pad($eventCount + 1, 4, '0', STR_PAD_LEFT),
                'title' => $event['title'],
                'description' => $this->generateDescription($event['title'], $event['category']),
                'category' => $event['category'],
                'start_date' => Carbon::parse($event['start']),
                'end_date' => Carbon::parse($event['end']),
            ]);
            
            $eventCount++;
        }

        // Create seminar events throughout the year
        for ($month = 1; $month <= 12; $month++) {
            $numSeminars = rand(1, 3);
            for ($i = 0; $i < $numSeminars; $i++) {
                $topic = $this->seminarTopics[array_rand($this->seminarTopics)];
                $day = rand(1, 28);
                $year = $month <= 8 ? 2026 : 2025;
                
                AcademicCalendarEvent::create([
                    'id' => 'evt' . str_pad($eventCount + 1, 4, '0', STR_PAD_LEFT),
                    'title' => "Seminar: {$topic}",
                    'description' => "Seminar nasional dengan tema \"{$topic}\". Pembicara dari berbagai kalangan akademisi dan praktisi.",
                    'category' => 'activity',
                    'start_date' => Carbon::create($year, $month, $day),
                    'end_date' => Carbon::create($year, $month, $day),
                ]);
                
                $eventCount++;
            }
        }

        // Create workshop events
        for ($month = 1; $month <= 12; $month++) {
            $numWorkshops = rand(1, 2);
            for ($i = 0; $i < $numWorkshops; $i++) {
                $topic = $this->workshopTopics[array_rand($this->workshopTopics)];
                $day = rand(1, 28);
                $year = $month <= 8 ? 2026 : 2025;
                
                AcademicCalendarEvent::create([
                    'id' => 'evt' . str_pad($eventCount + 1, 4, '0', STR_PAD_LEFT),
                    'title' => "Workshop: {$topic}",
                    'description' => "Workshop praktis tentang {$topic}. Peserta akan mendapatkan sertifikat.",
                    'category' => 'activity',
                    'start_date' => Carbon::create($year, $month, $day),
                    'end_date' => Carbon::create($year, $month, $day),
                ]);
                
                $eventCount++;
            }
        }

        // Create deadline reminders
        $deadlines = [
            'Batas Akhir Pengumpulan Proposal Skripsi',
            'Batas Akhir Pendaftaran Wisuda',
            'Batas Akhir Pembayaran SPP',
            'Batas Akhir Pendaftaran Beasiswa',
            'Batas Akhir Pengumpulan Laporan PKL',
            'Batas Akhir Revisi Skripsi',
            'Batas Akhir Upload Jurnal',
        ];

        foreach ($deadlines as $deadline) {
            for ($semester = 1; $semester <= 2; $semester++) {
                $month = $semester == 1 ? rand(10, 12) : rand(4, 6);
                $year = $semester == 1 ? 2025 : 2026;
                $day = rand(15, 28);
                
                AcademicCalendarEvent::create([
                    'id' => 'evt' . str_pad($eventCount + 1, 4, '0', STR_PAD_LEFT),
                    'title' => $deadline,
                    'description' => "Harap perhatikan {$deadline}. Keterlambatan tidak dapat ditoleransi.",
                    'category' => 'deadline',
                    'start_date' => Carbon::create($year, $month, $day),
                    'end_date' => Carbon::create($year, $month, $day),
                ]);
                
                $eventCount++;
            }
        }

        // Create faculty-specific events
        $faculties = ['Ushuluddin', 'Syariah', 'Tarbiyah', 'Ekonomi', 'Adab', 'Psikologi', 'Sains', 'Pascasarjana'];
        foreach ($faculties as $faculty) {
            // Rapat Dosen
            $month1 = rand(1, 6);
            $day1 = rand(1, 28);
            AcademicCalendarEvent::create([
                'id' => 'evt' . str_pad($eventCount + 1, 4, '0', STR_PAD_LEFT),
                'title' => "Rapat Dosen Fakultas {$faculty}",
                'description' => "Rapat koordinasi dosen Fakultas {$faculty}. Lokasi: Ruang Rapat Fakultas {$faculty}",
                'category' => 'academic',
                'start_date' => Carbon::create(2026, $month1, $day1),
                'end_date' => Carbon::create(2026, $month1, $day1),
            ]);
            $eventCount++;
            
            // Dies Natalis Fakultas
            $month2 = rand(1, 12);
            $day2 = rand(1, 28);
            AcademicCalendarEvent::create([
                'id' => 'evt' . str_pad($eventCount + 1, 4, '0', STR_PAD_LEFT),
                'title' => "Dies Natalis Fakultas {$faculty}",
                'description' => "Peringatan hari jadi Fakultas {$faculty} dengan berbagai kegiatan. Lokasi: Gedung Fakultas {$faculty}",
                'category' => 'ceremony',
                'start_date' => Carbon::create(2026, $month2, $day2),
                'end_date' => Carbon::create(2026, $month2, $day2),
            ]);
            $eventCount++;
        }

        // Create more recurring activities
        $activities = [
            ['title' => 'Kajian Rutin Mingguan', 'desc' => 'Kajian rutin setiap Jumat ba\'da Maghrib di Masjid Kampus'],
            ['title' => 'Tahsin Al-Quran', 'desc' => 'Kelas Tahsin setiap Sabtu pagi jam 08:00-10:00'],
            ['title' => 'Halaqah Tahfidz', 'desc' => 'Halaqah hafalan Al-Quran setiap Ahad pagi'],
            ['title' => 'Diskusi Ilmiah Mahasiswa', 'desc' => 'Forum diskusi ilmiah antar mahasiswa'],
            ['title' => 'Pelatihan Bahasa Arab', 'desc' => 'Pelatihan intensif bahasa Arab untuk mahasiswa'],
            ['title' => 'Pelatihan Bahasa Inggris', 'desc' => 'Pelatihan intensif bahasa Inggris untuk mahasiswa'],
            ['title' => 'Olahraga Bersama', 'desc' => 'Kegiatan olahraga rutin untuk civitas akademika'],
            ['title' => 'Bakti Sosial', 'desc' => 'Kegiatan bakti sosial ke masyarakat sekitar kampus'],
        ];

        foreach ($activities as $activity) {
            // Create multiple instances throughout the year
            for ($i = 0; $i < rand(10, 20); $i++) {
                $month = rand(1, 12);
                $year = $month <= 8 ? 2026 : 2025;
                $day = rand(1, 28);
                
                AcademicCalendarEvent::create([
                    'id' => 'evt' . str_pad($eventCount + 1, 4, '0', STR_PAD_LEFT),
                    'title' => $activity['title'],
                    'description' => $activity['desc'],
                    'category' => 'activity',
                    'start_date' => Carbon::create($year, $month, $day),
                    'end_date' => Carbon::create($year, $month, $day),
                ]);
                
                $eventCount++;
            }
        }

        // Create more exam schedules
        $examTypes = [
            'Ujian Komprehensif',
            'Sidang Skripsi',
            'Sidang Tesis',
            'Ujian Proposal',
            'Ujian Praktikum',
            'Ujian Hafalan',
            'Tes TOEFL Kampus',
            'Tes TOAFL Kampus',
        ];

        foreach ($examTypes as $exam) {
            for ($i = 0; $i < rand(5, 10); $i++) {
                $month = rand(1, 12);
                $year = $month <= 8 ? 2026 : 2025;
                $day = rand(1, 28);
                
                AcademicCalendarEvent::create([
                    'id' => 'evt' . str_pad($eventCount + 1, 4, '0', STR_PAD_LEFT),
                    'title' => $exam,
                    'description' => "Jadwal {$exam} untuk mahasiswa. Harap mempersiapkan diri dengan baik.",
                    'category' => 'exam',
                    'start_date' => Carbon::create($year, $month, $day),
                    'end_date' => Carbon::create($year, $month, $day),
                ]);
                
                $eventCount++;
            }
        }
        
        $this->command->info("Created {$eventCount} academic calendar events!");
    }

    private function generateDescription(string $title, string $category): string
    {
        return match($category) {
            'academic' => "Kegiatan akademik: {$title}. Pastikan Anda memperhatikan jadwal dan mempersiapkan diri dengan baik.",
            'exam' => "Periode ujian: {$title}. Pelajari materi dengan baik dan berdoa sebelum ujian.",
            'holiday' => "Libur: {$title}. Gunakan waktu libur untuk beristirahat dan beribadah.",
            'registration' => "Periode pendaftaran: {$title}. Siapkan dokumen yang diperlukan.",
            'activity' => "Kegiatan: {$title}. Ikuti kegiatan ini untuk pengembangan diri.",
            'deadline' => "Batas waktu: {$title}. Pastikan mengumpulkan tepat waktu.",
            'ceremony' => "Upacara: {$title}. Hadir dengan pakaian formal yang rapi.",
            default => $title,
        };
    }
}
