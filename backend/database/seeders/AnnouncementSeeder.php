<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Str;

class AnnouncementSeeder extends Seeder
{
    private $categories = ['Akademik', 'Kampus', 'Kegiatan', 'Beasiswa', 'Kemahasiswaan', 'Umum', 'Penting'];
    
    private $priorities = ['low', 'normal', 'high', 'urgent'];
    private $priorityWeights = [10, 50, 30, 10];
    
    private $targetAudiences = ['all', 'students', 'lecturers', 'staff', 'maba'];
    
    private $announcementTemplates = [
        'Akademik' => [
            ['title' => 'Perubahan Jadwal Ujian {semester}', 'content' => 'Diberitahukan kepada seluruh mahasiswa bahwa jadwal Ujian {semester} mengalami perubahan. Mohon periksa jadwal terbaru di kalender akademik.'],
            ['title' => 'Pendaftaran Mata Kuliah Semester {semester}', 'content' => 'Pendaftaran mata kuliah untuk semester {semester} telah dibuka. Silakan akses SIAKAD untuk melakukan pengisian KRS.'],
            ['title' => 'Batas Akhir Pembayaran SPP', 'content' => 'Diingatkan bahwa batas akhir pembayaran SPP semester ini adalah tanggal {date}. Keterlambatan akan dikenakan denda.'],
            ['title' => 'Pengumuman Hasil Ujian {course}', 'content' => 'Hasil ujian mata kuliah {course} telah dipublikasikan. Silakan cek nilai Anda di portal akademik.'],
            ['title' => 'Jadwal Kuliah Pengganti', 'content' => 'Kuliah pengganti untuk mata kuliah {course} akan dilaksanakan pada {date}. Harap hadir tepat waktu.'],
            ['title' => 'Perubahan Ruang Kelas', 'content' => 'Terdapat perubahan ruang kelas untuk beberapa mata kuliah. Silakan periksa jadwal terbaru.'],
            ['title' => 'Pengumpulan Berkas Wisuda', 'content' => 'Bagi mahasiswa yang akan wisuda, harap segera mengumpulkan berkas yang diperlukan ke bagian akademik.'],
            ['title' => 'Kalender Akademik Semester Baru', 'content' => 'Kalender akademik semester baru telah diterbitkan. Silakan unduh dari portal kampus.'],
        ],
        'Kampus' => [
            ['title' => 'Pemeliharaan Sistem LMS', 'content' => 'Akan dilakukan pemeliharaan sistem LMS pada {date}. Akses mungkin terganggu selama 2-4 jam.'],
            ['title' => 'Renovasi Gedung {building}', 'content' => 'Gedung {building} sedang dalam renovasi. Perkuliahan sementara dipindahkan ke gedung lain.'],
            ['title' => 'Jam Operasional Perpustakaan', 'content' => 'Perpustakaan kampus akan beroperasi dengan jadwal khusus selama periode ujian. Buka 07:00 - 22:00.'],
            ['title' => 'Parkir Kampus Ditutup Sementara', 'content' => 'Area parkir utama ditutup sementara untuk perbaikan. Gunakan area parkir alternatif.'],
            ['title' => 'Kebersihan Kampus', 'content' => 'Mari jaga kebersihan kampus bersama. Buang sampah pada tempatnya dan hemat penggunaan listrik.'],
            ['title' => 'WiFi Kampus Diperluas', 'content' => 'Jangkauan WiFi kampus telah diperluas ke seluruh area. Gunakan ID mahasiswa untuk login.'],
        ],
        'Kegiatan' => [
            ['title' => 'Seminar Nasional {topic}', 'content' => 'Akan diadakan Seminar Nasional dengan tema "{topic}". Pendaftaran sudah dibuka.'],
            ['title' => 'Pelatihan {skill}', 'content' => 'Pelatihan {skill} akan diadakan pada {date}. Terbatas untuk {limit} peserta.'],
            ['title' => 'Kajian Rutin Mingguan', 'content' => 'Kajian rutin mingguan akan membahas tema "{topic}". Hadir tepat waktu ba\'da Maghrib.'],
            ['title' => 'Lomba {competition}', 'content' => 'Pendaftaran lomba {competition} telah dibuka. Ayo tunjukkan bakatmu!'],
            ['title' => 'Bakti Sosial Ramadhan', 'content' => 'Ikuti kegiatan bakti sosial dalam rangka Ramadhan. Kontribusi Anda sangat berarti.'],
            ['title' => 'Workshop {topic}', 'content' => 'Workshop "{topic}" akan diadakan oleh HMJ. Daftarkan diri Anda segera.'],
        ],
        'Beasiswa' => [
            ['title' => 'Beasiswa {scholarship} Dibuka', 'content' => 'Pendaftaran Beasiswa {scholarship} telah dibuka. Syarat dan ketentuan dapat dilihat di portal.'],
            ['title' => 'Pengumuman Penerima Beasiswa', 'content' => 'Daftar penerima beasiswa periode ini telah diumumkan. Cek nama Anda di portal kemahasiswaan.'],
            ['title' => 'Batas Akhir Pendaftaran Beasiswa', 'content' => 'Batas akhir pendaftaran beasiswa adalah {date}. Segera lengkapi berkas Anda.'],
            ['title' => 'Informasi Beasiswa Luar Negeri', 'content' => 'Terdapat informasi beasiswa studi lanjut ke luar negeri. Kunjungi kantor kemahasiswaan.'],
        ],
        'Kemahasiswaan' => [
            ['title' => 'Pemilihan Ketua BEM', 'content' => 'Pemilihan Ketua BEM periode baru akan dilaksanakan pada {date}. Gunakan hak pilih Anda!'],
            ['title' => 'Rekrutmen Anggota UKM', 'content' => 'UKM {ukm} membuka rekrutmen anggota baru. Daftarkan diri Anda segera.'],
            ['title' => 'Kegiatan Ospek Mahasiswa Baru', 'content' => 'Kegiatan Ospek untuk mahasiswa baru akan dimulai pada {date}. Persiapkan diri Anda.'],
            ['title' => 'Pengumuman Beasiswa KIP-K', 'content' => 'Mahasiswa penerima KIP-K harap segera mengurus administrasi di bagian kemahasiswaan.'],
        ],
        'Umum' => [
            ['title' => 'Libur Hari Raya {holiday}', 'content' => 'Kampus akan libur dalam rangka Hari Raya {holiday}. Selamat merayakan!'],
            ['title' => 'Cuaca Ekstrem', 'content' => 'Diperkirakan akan terjadi cuaca ekstrem. Harap berhati-hati dalam perjalanan ke kampus.'],
            ['title' => 'Vaksinasi di Kampus', 'content' => 'Kegiatan vaksinasi akan diadakan di kampus pada {date}. Daftarkan diri Anda.'],
        ],
        'Penting' => [
            ['title' => 'URGENT: Evakuasi Gedung', 'content' => 'Latihan evakuasi gedung akan dilaksanakan pada {date}. Ikuti instruksi petugas.'],
            ['title' => 'Peringatan Keamanan Cyber', 'content' => 'Waspada terhadap email phishing. Jangan bagikan password Anda kepada siapapun.'],
            ['title' => 'Perubahan Kebijakan Akademik', 'content' => 'Terdapat perubahan kebijakan akademik yang berlaku mulai semester depan. Baca detail di portal.'],
        ],
    ];

    public function run(): void
    {
        $admins = User::whereIn('role', ['admin', 'super_admin', 'prodi_admin'])->get();
        $lecturers = User::where('role', 'dosen')->get();
        $courses = Course::where('status', 'Published')->get();
        
        if ($admins->isEmpty()) {
            return;
        }

        $announcementCount = 0;

        // Create global announcements (not course-specific)
        foreach ($this->categories as $category) {
            $templates = $this->announcementTemplates[$category] ?? [];
            $numAnnouncements = rand(3, 6);
            
            for ($i = 0; $i < min($numAnnouncements, count($templates)); $i++) {
                $template = $templates[$i];
                $creator = $admins->random();
                
                $title = $this->processTemplate($template['title']);
                $content = $this->processTemplate($template['content']);
                
                $daysAgo = rand(0, 90);
                $createdAt = now()->subDays($daysAgo);
                
                Announcement::create([
                    'id' => 'AN' . str_pad($announcementCount + 1, 3, '0', STR_PAD_LEFT),
                    'title' => $title,
                    'content' => $content,
                    'created_by' => $creator->id,
                    'category' => $category,
                    'course_id' => null,
                    'is_published' => rand(1, 100) <= 90,
                    'target_audience' => $this->targetAudiences[array_rand($this->targetAudiences)],
                    'priority' => $this->weightedRandom($this->priorities, $this->priorityWeights),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                
                $announcementCount++;
            }
        }

        // Create course-specific announcements
        foreach ($courses->take(50) as $course) {
            $numAnnouncements = rand(1, 3);
            $lecturer = $lecturers->isNotEmpty() ? $lecturers->random() : $admins->random();
            
            for ($i = 0; $i < $numAnnouncements; $i++) {
                $daysAgo = rand(0, 60);
                $createdAt = now()->subDays($daysAgo);
                
                $courseAnnouncements = [
                    "Perubahan jadwal kuliah {$course->name}",
                    "Materi tambahan untuk {$course->name}",
                    "Tugas baru telah ditambahkan",
                    "Pengumuman UTS {$course->name}",
                    "Reminder: Deadline tugas minggu ini",
                    "Sesi konsultasi mata kuliah",
                    "Link rekaman kuliah tersedia",
                    "Persiapan UAS {$course->name}",
                ];
                
                $courseContents = [
                    "Mohon perhatikan perubahan jadwal untuk mata kuliah ini. Cek kalender akademik untuk detail.",
                    "Materi tambahan telah diunggah di modul. Silakan pelajari sebelum pertemuan berikutnya.",
                    "Tugas baru telah ditambahkan. Perhatikan deadline dan instruksi yang diberikan.",
                    "UTS akan dilaksanakan sesuai jadwal. Pelajari semua materi yang telah dibahas.",
                    "Diingatkan bahwa deadline tugas adalah minggu ini. Kumpulkan tepat waktu.",
                    "Sesi konsultasi dibuka setiap Rabu pukul 14:00-16:00. Silakan manfaatkan.",
                    "Link rekaman kuliah minggu lalu telah tersedia di modul. Bagi yang berhalangan hadir, silakan tonton.",
                    "Persiapkan diri untuk UAS. Materi mencakup seluruh pertemuan semester ini.",
                ];
                
                $idx = array_rand($courseAnnouncements);
                
                Announcement::create([
                    'id' => 'AN' . str_pad($announcementCount + 1, 3, '0', STR_PAD_LEFT),
                    'title' => $courseAnnouncements[$idx],
                    'content' => $courseContents[$idx],
                    'created_by' => $lecturer->id,
                    'category' => 'Akademik',
                    'course_id' => $course->id,
                    'is_published' => true,
                    'target_audience' => 'students',
                    'priority' => 'normal',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                
                $announcementCount++;
            }
        }
        
        $this->command->info("Created {$announcementCount} announcements!");
    }

    private function processTemplate(string $template): string
    {
        $replacements = [
            '{semester}' => ['Ganjil 2025/2026', 'Genap 2024/2025', 'Ganjil 2024/2025'][array_rand(['Ganjil 2025/2026', 'Genap 2024/2025', 'Ganjil 2024/2025'])],
            '{date}' => now()->addDays(rand(1, 30))->format('d F Y'),
            '{course}' => ['Aqidah Islam', 'Fiqh Muamalah', 'Hadis', 'Tafsir', 'Ekonomi Syariah'][array_rand(['Aqidah Islam', 'Fiqh Muamalah', 'Hadis', 'Tafsir', 'Ekonomi Syariah'])],
            '{building}' => ['A', 'B', 'C', 'D', 'Rektorat'][array_rand(['A', 'B', 'C', 'D', 'Rektorat'])],
            '{topic}' => ['Ekonomi Syariah di Era Digital', 'Peradaban Islam', 'Fiqh Kontemporer', 'AI dan Etika Islam', 'Keuangan Syariah'][array_rand(['Ekonomi Syariah di Era Digital', 'Peradaban Islam', 'Fiqh Kontemporer', 'AI dan Etika Islam', 'Keuangan Syariah'])],
            '{skill}' => ['Public Speaking', 'Microsoft Office', 'Desain Grafis', 'Penulisan Ilmiah', 'Tahsin Al-Quran'][array_rand(['Public Speaking', 'Microsoft Office', 'Desain Grafis', 'Penulisan Ilmiah', 'Tahsin Al-Quran'])],
            '{limit}' => rand(30, 100),
            '{competition}' => ['Debat Islami', 'MTQ', 'Kaligrafi', 'Karya Tulis Ilmiah', 'Tilawah'][array_rand(['Debat Islami', 'MTQ', 'Kaligrafi', 'Karya Tulis Ilmiah', 'Tilawah'])],
            '{scholarship}' => ['Bidikmisi', 'KIP-K', 'Tahfidz', 'Prestasi', 'Dhuafa'][array_rand(['Bidikmisi', 'KIP-K', 'Tahfidz', 'Prestasi', 'Dhuafa'])],
            '{ukm}' => ['Tilawah', 'Tahfidz', 'Dakwah', 'Olahraga', 'Kewirausahaan'][array_rand(['Tilawah', 'Tahfidz', 'Dakwah', 'Olahraga', 'Kewirausahaan'])],
            '{holiday}' => ['Idul Fitri', 'Idul Adha', 'Maulid Nabi', 'Isra Miraj', 'Tahun Baru Hijriyah'][array_rand(['Idul Fitri', 'Idul Adha', 'Maulid Nabi', 'Isra Miraj', 'Tahun Baru Hijriyah'])],
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
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
