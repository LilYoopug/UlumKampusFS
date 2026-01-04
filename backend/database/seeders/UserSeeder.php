<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [];
        
        // ============================================
        // SUPER ADMIN & MANAGEMENT (5 users)
        // ============================================
        $users[] = [
            'name' => 'Admin Sistem',
            'avatar' => 'https://picsum.photos/seed/admin/100/100',
            'role' => 'super_admin',
            'student_id' => 'SYSADMIN',
            'email' => 'admin@ulumcampus.com',
            'phone' => '081234567894',
            'bio' => 'Administrator sistem utama UlumCampus. Bertanggung jawab atas infrastruktur teknis dan manajemen pengguna.',
            'password' => Hash::make('admin123'),
        ];
        
        $users[] = [
            'name' => 'Prof. Dr. Ibrahim Malik',
            'avatar' => 'https://picsum.photos/seed/ibrahim/100/100',
            'role' => 'admin',
            'student_id' => 'REKTOR01',
            'email' => 'rektor@ulumcampus.com',
            'phone' => '081234567893',
            'bio' => 'Rektor UlumCampus. Guru besar di bidang Keuangan Syariah dengan pengalaman lebih dari 20 tahun.',
            'password' => Hash::make('manajemen123'),
        ];
        
        $users[] = [
            'name' => 'Dr. Hj. Fatimah Azzahra',
            'avatar' => 'https://picsum.photos/seed/fatimah/100/100',
            'role' => 'admin',
            'student_id' => 'WAREK01',
            'email' => 'warek1@ulumcampus.com',
            'phone' => '081234567950',
            'bio' => 'Wakil Rektor I Bidang Akademik. Ahli dalam pengembangan kurikulum pendidikan Islam.',
            'password' => Hash::make('manajemen123'),
        ];

        $users[] = [
            'name' => 'H. Muhammad Ridwan, M.M.',
            'avatar' => 'https://picsum.photos/seed/ridwan/100/100',
            'role' => 'admin',
            'student_id' => 'WAREK02',
            'email' => 'warek2@ulumcampus.com',
            'phone' => '081234567951',
            'bio' => 'Wakil Rektor II Bidang Administrasi dan Keuangan.',
            'password' => Hash::make('manajemen123'),
        ];

        $users[] = [
            'name' => 'Dr. Abdul Karim',
            'avatar' => 'https://picsum.photos/seed/karim/100/100',
            'role' => 'admin',
            'student_id' => 'WAREK03',
            'email' => 'warek3@ulumcampus.com',
            'phone' => '081234567952',
            'bio' => 'Wakil Rektor III Bidang Kemahasiswaan dan Alumni.',
            'password' => Hash::make('manajemen123'),
        ];

        // ============================================
        // PRODI ADMINS (8 users - one per faculty)
        // ============================================
        $prodiAdmins = [
            ['name' => 'Dr. Aisyah Hasanah', 'faculty_id' => 'syariah', 'email' => 'aisyah.h@staff.ulumcampus.com', 'seed' => 'aisyah'],
            ['name' => 'Ustadz Ahmad Zaki, M.A.', 'faculty_id' => 'ushuluddin', 'email' => 'ahmad.zaki@staff.ulumcampus.com', 'seed' => 'zaki'],
            ['name' => 'Dr. Hj. Khadijah', 'faculty_id' => 'tarbiyah', 'email' => 'khadijah@staff.ulumcampus.com', 'seed' => 'khadijah'],
            ['name' => 'Dr. Ir. Mahmud Hasan', 'faculty_id' => 'sains', 'email' => 'mahmud.hasan@staff.ulumcampus.com', 'seed' => 'mahmud'],
            ['name' => 'Prof. Dr. Siti Aminah', 'faculty_id' => 'ekonomi', 'email' => 'siti.aminah@staff.ulumcampus.com', 'seed' => 'aminah'],
            ['name' => 'Dr. Harun Al-Rasyid', 'faculty_id' => 'adab', 'email' => 'harun@staff.ulumcampus.com', 'seed' => 'harun'],
            ['name' => 'Dr. Ruqayyah, M.Psi.', 'faculty_id' => 'psikologi', 'email' => 'ruqayyah@staff.ulumcampus.com', 'seed' => 'ruqayyah'],
            ['name' => 'Prof. Dr. Umar Farooq', 'faculty_id' => 'pascasarjana', 'email' => 'umar.farooq@staff.ulumcampus.com', 'seed' => 'umarfarooq'],
        ];

        foreach ($prodiAdmins as $i => $admin) {
            $users[] = [
                'name' => $admin['name'],
                'avatar' => 'https://picsum.photos/seed/' . $admin['seed'] . '/100/100',
                'role' => 'prodi_admin',
                'student_id' => 'PRODI0' . ($i + 1),
                'email' => $admin['email'],
                'phone' => '08123456790' . $i,
                'bio' => 'Kepala Program Studi Fakultas ' . ucfirst($admin['faculty_id']) . '.',
                'faculty_id' => $admin['faculty_id'],
                'password' => Hash::make('prodi123'),
            ];
        }

        // ============================================
        // DOSEN / LECTURERS (30 users)
        // ============================================
        $lecturers = [
            ['name' => 'Dr. Yusuf Al-Fatih', 'faculty_id' => 'ushuluddin', 'email' => 'yusuf.alfatih@dosen.ulumcampus.com', 'bio' => 'Akademisi dan da\'i yang fokus pada studi Aqidah dan Manhaj. Meraih gelar Doktor dari Universitas Islam Madinah.'],
            ['name' => 'Dr. Abdullah Musnad', 'faculty_id' => 'ushuluddin', 'email' => 'abdullah.musnad@dosen.ulumcampus.com', 'bio' => 'Pakar ilmu hadis dan rijal al-hadis. Meraih gelar doktor dari Universitas Islam Madinah dengan spesialisasi kritik sanad dan matan.'],
            ['name' => 'Dr. Ahmad Syafiq', 'faculty_id' => 'syariah', 'email' => 'ahmad.syafiq@dosen.ulumcampus.com', 'bio' => 'Pakar Fiqh Muamalat dan Hukum Ekonomi Syariah. Lulusan S3 dari Al-Azhar University.'],
            ['name' => 'Dr. Halimah Sa\'diyah, M.E.I', 'faculty_id' => 'ekonomi', 'email' => 'halimah.sadiyah@dosen.ulumcampus.com', 'bio' => 'Pakar perbankan syariah dan ekonomi Islam. Lulusan S2 Ekonomi Islam dari Universitas Al-Azhar dan S3 dari IIUM.'],
            ['name' => 'Prof. Dr. Tariq An-Nawawi', 'faculty_id' => 'adab', 'email' => 'tariq.annawawi@dosen.ulumcampus.com', 'bio' => 'Sejarawan Islam dan pakar peradaban Islam. Guru besar sejarah dengan pengalaman lebih dari 25 tahun.'],
            ['name' => 'Dr. Hana Al-Ghazali, M.Psi.', 'faculty_id' => 'psikologi', 'email' => 'hana.alghazali@dosen.ulumcampus.com', 'bio' => 'Psikolog klinis dan peneliti psikologi Islam. Menggabungkan pendekatan psikologi modern dengan nilai-nilai Islam.'],
            ['name' => 'Dr. Eng. Faiz Rabbani', 'faculty_id' => 'sains', 'email' => 'faiz.rabbani@dosen.ulumcampus.com', 'bio' => 'Insinyur dan peneliti yang menjembatani dunia teknologi dan studi Islam. Lulusan doktor teknik dari Jepang.'],
            ['name' => 'Dr. Muhammad Al-Bukhari', 'faculty_id' => 'ushuluddin', 'email' => 'albukhari@dosen.ulumcampus.com', 'bio' => 'Spesialis ilmu tafsir dan ulumul Quran. Lulusan Al-Azhar University.'],
            ['name' => 'Dr. Zaynab Al-Husna', 'faculty_id' => 'tarbiyah', 'email' => 'zaynab@dosen.ulumcampus.com', 'bio' => 'Pakar pendidikan anak usia dini dalam perspektif Islam.'],
            ['name' => 'Prof. Dr. Sulaiman Ibn Dawud', 'faculty_id' => 'syariah', 'email' => 'sulaiman@dosen.ulumcampus.com', 'bio' => 'Guru besar hukum Islam dan fatwa kontemporer.'],
            ['name' => 'Dr. Maryam Binti Imran', 'faculty_id' => 'ekonomi', 'email' => 'maryam.imran@dosen.ulumcampus.com', 'bio' => 'Ahli akuntansi syariah dan audit lembaga keuangan Islam.'],
            ['name' => 'Dr. Usman Al-Affan', 'faculty_id' => 'tarbiyah', 'email' => 'usman.affan@dosen.ulumcampus.com', 'bio' => 'Pakar komunikasi dakwah dan media Islam.'],
            ['name' => 'Dr. Bilal Ibn Rabah', 'faculty_id' => 'adab', 'email' => 'bilal@dosen.ulumcampus.com', 'bio' => 'Spesialis sastra Arab klasik dan kontemporer.'],
            ['name' => 'Dr. Asma Binti Abu Bakar', 'faculty_id' => 'psikologi', 'email' => 'asma.abubakar@dosen.ulumcampus.com', 'bio' => 'Psikolog pendidikan dan konselor keluarga Islami.'],
            ['name' => 'Dr. Eng. Khalid Al-Walid', 'faculty_id' => 'sains', 'email' => 'khalid.walid@dosen.ulumcampus.com', 'bio' => 'Pakar sistem informasi dan keamanan siber.'],
            ['name' => 'Dr. Salman Al-Farisi', 'faculty_id' => 'ushuluddin', 'email' => 'salman@dosen.ulumcampus.com', 'bio' => 'Ahli perbandingan agama dan studi keislaman.'],
            ['name' => 'Dr. Hafshah Binti Umar', 'faculty_id' => 'tarbiyah', 'email' => 'hafshah@dosen.ulumcampus.com', 'bio' => 'Pakar metodologi pembelajaran Al-Quran.'],
            ['name' => 'Dr. Anas Ibn Malik', 'faculty_id' => 'syariah', 'email' => 'anas.malik@dosen.ulumcampus.com', 'bio' => 'Spesialis hukum keluarga Islam dan waris.'],
            ['name' => 'Dr. Zaid Ibn Tsabit', 'faculty_id' => 'ekonomi', 'email' => 'zaid.tsabit@dosen.ulumcampus.com', 'bio' => 'Ahli fintech syariah dan ekonomi digital.'],
            ['name' => 'Dr. Hamzah Ibn Abdul Muthalib', 'faculty_id' => 'tarbiyah', 'email' => 'hamzah@dosen.ulumcampus.com', 'bio' => 'Pakar manajemen dakwah dan pengembangan masyarakat.'],
            ['name' => 'Dr. Safiyyah Binti Huyay', 'faculty_id' => 'adab', 'email' => 'safiyyah@dosen.ulumcampus.com', 'bio' => 'Spesialis bahasa Arab dan linguistik.'],
            ['name' => 'Dr. Abu Hurairah', 'faculty_id' => 'ushuluddin', 'email' => 'abuhurairah@dosen.ulumcampus.com', 'bio' => 'Pakar hadis dan sirah nabawiyah.'],
            ['name' => 'Dr. Muadz Ibn Jabal', 'faculty_id' => 'tarbiyah', 'email' => 'muadz@dosen.ulumcampus.com', 'bio' => 'Ahli kurikulum pendidikan Islam.'],
            ['name' => 'Dr. Ubay Ibn Kaab', 'faculty_id' => 'syariah', 'email' => 'ubay@dosen.ulumcampus.com', 'bio' => 'Spesialis ushul fiqh dan qawaid fiqhiyyah.'],
            ['name' => 'Dr. Said Ibn Zaid', 'faculty_id' => 'ekonomi', 'email' => 'said.zaid@dosen.ulumcampus.com', 'bio' => 'Pakar manajemen bisnis syariah.'],
            ['name' => 'Dr. Thalhah Ibn Ubaidillah', 'faculty_id' => 'tarbiyah', 'email' => 'thalhah@dosen.ulumcampus.com', 'bio' => 'Ahli public speaking dan retorika dakwah.'],
            ['name' => 'Dr. Zubair Ibn Awwam', 'faculty_id' => 'sains', 'email' => 'zubair@dosen.ulumcampus.com', 'bio' => 'Pakar bioteknologi dan bioetika Islam.'],
            ['name' => 'Dr. Abdurrahman Ibn Auf', 'faculty_id' => 'ekonomi', 'email' => 'abdurrahman@dosen.ulumcampus.com', 'bio' => 'Ahli investasi syariah dan wealth management.'],
            ['name' => 'Dr. Ummu Salamah', 'faculty_id' => 'psikologi', 'email' => 'ummu.salamah@dosen.ulumcampus.com', 'bio' => 'Spesialis kesehatan mental dan terapi Islam.'],
            ['name' => 'Dr. Abu Dzar Al-Ghifari', 'faculty_id' => 'adab', 'email' => 'abudzar@dosen.ulumcampus.com', 'bio' => 'Pakar kritik sosial dalam perspektif Islam.'],
        ];

        foreach ($lecturers as $i => $lecturer) {
            $users[] = [
                'name' => $lecturer['name'],
                'avatar' => 'https://picsum.photos/seed/dosen' . ($i + 1) . '/100/100',
                'role' => 'dosen',
                'student_id' => 'DSN20200' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'email' => $lecturer['email'],
                'phone' => '08123456' . str_pad(800 + $i, 4, '0', STR_PAD_LEFT),
                'bio' => $lecturer['bio'],
                'faculty_id' => $lecturer['faculty_id'],
                'password' => Hash::make('dosen123'),
            ];
        }

        // ============================================
        // ACTIVE STUDENTS (100 users)
        // ============================================
        $firstNames = ['Ahmad', 'Muhammad', 'Abdullah', 'Umar', 'Ali', 'Hasan', 'Husein', 'Ibrahim', 'Ismail', 'Yusuf', 'Zakariya', 'Yahya', 'Isa', 'Musa', 'Daud', 'Sulaiman', 'Ayyub', 'Yunus', 'Ilyas', 'Idris', 'Nuh', 'Hud', 'Saleh', 'Syu\'aib', 'Lut', 'Harun', 'Dzulkifli', 'Zulkarnaen', 'Luqman', 'Khidir'];
        $lastNames = ['Al-Farisi', 'Al-Ansari', 'Al-Muhajir', 'Al-Quraisyi', 'Al-Madani', 'Al-Makki', 'Al-Baghdadi', 'Al-Dimasyqi', 'Al-Misri', 'Al-Andalusi', 'Al-Bukhari', 'Al-Naisaburi', 'Al-Tirmidzi', 'Al-Nasai', 'Ibn Majah', 'Ibn Hanbal', 'Al-Syafii', 'Al-Maliki', 'Al-Hanafi', 'Al-Hanbali'];
        $femaleFirstNames = ['Fatimah', 'Aisyah', 'Khadijah', 'Maryam', 'Asma', 'Hafshah', 'Zainab', 'Ummu Kultsum', 'Ruqayyah', 'Safiyyah', 'Maimunah', 'Juwairiyyah', 'Saudah', 'Hindun', 'Sumayya', 'Nusaibah', 'Khaulah', 'Laila', 'Sarah', 'Hagar'];
        
        $faculties = ['syariah', 'ushuluddin', 'tarbiyah', 'ekonomi', 'adab', 'psikologi', 'sains', 'pascasarjana'];
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
        $statuses = ['Aktif', 'Aktif', 'Aktif', 'Aktif', 'Aktif', 'Aktif', 'Aktif', 'Cuti', 'Aktif', 'Aktif']; // 80% active, 10% cuti

        for ($i = 1; $i <= 100; $i++) {
            $isFemale = $i % 3 === 0;
            $firstName = $isFemale 
                ? $femaleFirstNames[array_rand($femaleFirstNames)]
                : $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $fullName = $firstName . ' ' . $lastName;
            
            $faculty = $faculties[array_rand($faculties)];
            $major = $majors[$faculty][array_rand($majors[$faculty])];
            $enrollmentYear = rand(2020, 2024);
            $gpa = round(rand(250, 400) / 100, 2);
            $totalSks = rand(12, 144);
            $status = $statuses[array_rand($statuses)];
            
            $badgeOptions = [[], ['learner'], ['learner', 'fiqh'], ['learner', 'historian'], ['learner', 'fiqh', 'muamalat_expert'], ['learner', 'fiqh', 'historian', 'aqidah_foundations']];
            $badges = $badgeOptions[array_rand($badgeOptions)];
            
            $users[] = [
                'name' => $fullName,
                'avatar' => 'https://picsum.photos/seed/student' . $i . '/100/100',
                'role' => 'student',
                'student_id' => 'UC' . $enrollmentYear . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email' => strtolower(str_replace([' ', '\''], ['', ''], $firstName)) . '.' . strtolower(str_replace(['-', '\''], '', $lastName)) . $i . '@student.ulumcampus.com',
                'phone' => '0812' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'bio' => 'Mahasiswa ' . ucfirst($faculty) . ' yang bersemangat menuntut ilmu.',
                'student_status' => $status,
                'gpa' => $gpa,
                'total_sks' => $totalSks,
                'faculty_id' => $faculty,
                'major_id' => $major,
                'badges' => json_encode($badges),
                'password' => Hash::make('mahasiswa123'),
                'enrollment_year' => $enrollmentYear,
            ];
        }

        // ============================================
        // MABA - NEW STUDENTS (50 users with different registration statuses)
        // ============================================
        $registrationStatuses = ['pending', 'pending', 'pending', 'accepted', 'accepted', 'rejected', 'pending'];
        
        for ($i = 1; $i <= 50; $i++) {
            $isFemale = $i % 2 === 0;
            $firstName = $isFemale 
                ? $femaleFirstNames[array_rand($femaleFirstNames)]
                : $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $fullName = $firstName . ' Bin/Binti ' . $lastName;
            
            $faculty = $faculties[array_rand($faculties)];
            $major = $majors[$faculty][array_rand($majors[$faculty])];
            
            $users[] = [
                'name' => $fullName,
                'avatar' => 'https://picsum.photos/seed/maba' . $i . '/100/100',
                'role' => 'maba',
                'student_id' => 'MABA2025' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email' => strtolower(str_replace([' ', '\'', '/'], ['', '', ''], $firstName)) . '.maba' . $i . '@maba.ulumcampus.com',
                'phone' => '0813' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'bio' => 'Calon mahasiswa baru yang antusias belajar di UlumCampus.',
                'student_status' => 'Pendaftaran',
                'gpa' => 0,
                'total_sks' => 0,
                'faculty_id' => $faculty,
                'major_id' => $major,
                'password' => Hash::make('maba123'),
                'enrollment_year' => 2025,
            ];
        }

        // ============================================
        // ALUMNI STUDENTS (20 users)
        // ============================================
        for ($i = 1; $i <= 20; $i++) {
            $isFemale = $i % 3 === 0;
            $firstName = $isFemale 
                ? $femaleFirstNames[array_rand($femaleFirstNames)]
                : $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $fullName = $firstName . ' ' . $lastName;
            
            $faculty = $faculties[array_rand($faculties)];
            $major = $majors[$faculty][array_rand($majors[$faculty])];
            $enrollmentYear = rand(2016, 2020);
            $gpa = round(rand(300, 400) / 100, 2);
            
            $users[] = [
                'name' => $fullName,
                'avatar' => 'https://picsum.photos/seed/alumni' . $i . '/100/100',
                'role' => 'student',
                'student_id' => 'ALM' . $enrollmentYear . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email' => 'alumni.' . strtolower(str_replace([' ', '\''], ['', ''], $firstName)) . $i . '@alumni.ulumcampus.com',
                'phone' => '0815' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'bio' => 'Alumni UlumCampus angkatan ' . $enrollmentYear . '.',
                'student_status' => 'Lulus',
                'gpa' => $gpa,
                'total_sks' => 144,
                'faculty_id' => $faculty,
                'major_id' => $major,
                'badges' => json_encode(['learner', 'fiqh', 'historian']),
                'password' => Hash::make('alumni123'),
                'enrollment_year' => $enrollmentYear,
            ];
        }

        // Insert all users
        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Created ' . count($users) . ' users successfully!');
    }
}
