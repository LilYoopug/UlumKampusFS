<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create users based on frontend constants
        // Note: Use role names for backend compatibility
        // Mapping: Mahasiswa -> student, Dosen -> dosen, Prodi Admin -> prodi_admin, etc.
        $frontendUsers = [
            [
                'name' => 'Ahmad Faris',
                'avatar' => 'https://picsum.photos/seed/ahmad/100/100',
                'role' => 'student',
                'student_id' => 'UC2024001',
                'email' => 'ahmad.faris@student.ulumcampus.com',
                'phone' => '081234567890',
                'bio' => 'Penuntut ilmu syar\'i dari Jakarta yang bersemangat mempelajari Fiqh Muamalat dan Sejarah Peradaban Islam untuk berkontribusi pada kemajuan umat.',
                'student_status' => 'Aktif',
                'gpa' => 3.85,
                'total_sks' => 42,
                'faculty_id' => 'syariah',
                'major_id' => 'hes',
                'badges' => json_encode(['learner', 'fiqh', 'muamalat_expert', 'historian']),
                'password' => Hash::make('mahasiswa123'),
                'enrollment_year' => 2023,
            ],
            [
                'name' => 'Dr. Yusuf Al-Fatih',
                'avatar' => 'https://picsum.photos/seed/yusuf/100/100',
                'role' => 'dosen',
                'student_id' => 'DSN202001',
                'email' => 'yusuf.alfatih@dosen.ulumcampus.com',
                'phone' => '081234567891',
                'bio' => 'Akademisi dan da\'i yang fokus pada studi Aqidah dan Manhaj. Meraih gelar Doktor dari Universitas Islam Madinah.',
                'faculty_id' => 'syariah',
                'password' => Hash::make('dosen123'),
            ],
            [
                'name' => 'Dr. Aisyah Hasanah',
                'avatar' => 'https://picsum.photos/seed/aisyah/100/100',
                'role' => 'prodi_admin',
                'student_id' => 'PRODI01',
                'email' => 'aisyah.h@staff.ulumcampus.com',
                'phone' => '081234567892',
                'bio' => 'Kepala Program Studi Syariah & Hukum. Pakar Fiqh Muamalat dan Ekonomi Syariah. Lulusan S1 Fiqh dari Universitas Al-Azhar, Kairo.',
                'faculty_id' => 'syariah',
                'password' => Hash::make('prodi123'),
            ],
            [
                'name' => 'Dr. Ahmad Syafiq',
                'avatar' => 'https://picsum.photos/seed/syafiq/100/100',
                'role' => 'dosen',
                'student_id' => 'DSN202006',
                'email' => 'ahmad.syafiq@dosen.ulumcampus.com',
                'phone' => '081234567903',
                'bio' => 'Pakar Fiqh Muamalat dan Hukum Ekonomi Syariah. Lulusan S3 dari Al-Azhar University.',
                'faculty_id' => 'syariah',
                'password' => Hash::make('dosen123'),
            ],
            [
                'name' => 'Prof. Dr. Ibrahim Malik',
                'avatar' => 'https://picsum.photos/seed/ibrahim/100/100',
                'role' => 'admin',
                'student_id' => 'REKTOR01',
                'email' => 'rektor@ulumcampus.com',
                'phone' => '081234567893',
                'bio' => 'Rektor UlumCampus. Guru besar di bidang Keuangan Syariah dengan pengalaman lebih dari 20 tahun.',
                'password' => Hash::make('manajemen123'),
            ],
            [
                'name' => 'Budi Santoso',
                'avatar' => 'https://picsum.photos/seed/budi/100/100',
                'role' => 'maba',
                'student_id' => 'MABA2025001',
                'email' => 'budi.santoso@maba.ulumcampus.com',
                'phone' => '081234567899',
                'bio' => 'Mahasiswa baru yang antusias belajar di UlumCampus. Tertarik dengan studi Fiqh Muamalat dan Ekonomi Syariah.',
                'student_status' => 'Pendaftaran',
                'gpa' => 0,
                'total_sks' => 0,
                'faculty_id' => 'syariah',
                'major_id' => 'hes',
                'password' => Hash::make('maba123'),
                'enrollment_year' => 2025,
            ],
            [
                'name' => 'Admin Sistem',
                'avatar' => 'https://picsum.photos/seed/admin/100/100',
                'role' => 'super_admin',
                'student_id' => 'SYSADMIN',
                'email' => 'admin@ulumcampus.com',
                'phone' => '081234567894',
                'bio' => 'Administrator sistem utama UlumCampus. Bertanggung jawab atas infrastruktur teknis dan manajemen pengguna.',
                'password' => Hash::make('admin123'),
            ],
            [
                'name' => 'Dr. Eng. Faiz Rabbani',
                'avatar' => 'https://picsum.photos/seed/faiz/100/100',
                'role' => 'dosen',
                'student_id' => 'DSN202105',
                'email' => 'faiz.rabbani@dosen.ulumcampus.com',
                'phone' => '081234567895',
                'bio' => 'Insinyur dan peneliti yang menjembatani dunia teknologi dan studi Islam. Lulusan doktor teknik dari Jepang ini memimpin sebuah lab riset yang fokus mengembangkan aplikasi AI untuk kemaslahatan umat.',
                'faculty_id' => 'sains',
                'password' => Hash::make('dosen123'),
            ],
            [
                'name' => 'Siti Maryam',
                'avatar' => 'https://picsum.photos/seed/maryam/100/100',
                'role' => 'student',
                'student_id' => 'UC2024002',
                'email' => 'siti.m@student.ulumcampus.com',
                'phone' => '081234567896',
                'bio' => '',
                'student_status' => 'Aktif',
                'gpa' => 3.92,
                'total_sks' => 42,
                'faculty_id' => 'syariah',
                'major_id' => 'ahwal-syakhshiyyah',
                'badges' => json_encode(['learner', 'fiqh']),
                'password' => Hash::make('mahasiswa123'),
                'enrollment_year' => 2023,
            ],
            [
                'name' => 'Abdullah',
                'avatar' => 'https://picsum.photos/seed/abdullah/100/100',
                'role' => 'student',
                'student_id' => 'UC2024003',
                'email' => 'abdullah@student.ulumcampus.com',
                'phone' => '081234567897',
                'bio' => '',
                'student_status' => 'Cuti',
                'gpa' => 3.50,
                'total_sks' => 28,
                'faculty_id' => 'syariah',
                'major_id' => 'hes',
                'badges' => json_encode([]),
                'password' => Hash::make('mahasiswa123'),
                'enrollment_year' => 2023,
            ],
            [
                'name' => 'Dr. Abdullah Musnad',
                'avatar' => 'https://picsum.photos/seed/abdullah/100/100',
                'role' => 'dosen',
                'student_id' => 'DSN202002',
                'email' => 'abdullah.musnad@dosen.ulumcampus.com',
                'phone' => '081234567898',
                'bio' => 'Pakar ilmu hadis dan rijal al-hadis. Meraih gelar doktor dari Universitas Islam Madinah dengan spesialisasi kritik sanad dan matan.',
                'faculty_id' => 'ushuluddin',
                'password' => Hash::make('dosen123'),
            ],
            [
                'name' => 'Dr. Halimah Sa\'diyah, M.E.I',
                'avatar' => 'https://picsum.photos/seed/halimah/100/100',
                'role' => 'dosen',
                'student_id' => 'DSN202003',
                'email' => 'halimah.sadiyah@dosen.ulumcampus.com',
                'phone' => '081234567900',
                'bio' => 'Pakar perbankan syariah dan ekonomi Islam. Lulusan S2 Ekonomi Islam dari Universitas Al-Azhar dan S3 dari International Islamic University Malaysia.',
                'faculty_id' => 'ekonomi',
                'password' => Hash::make('dosen123'),
            ],
            [
                'name' => 'Prof. Dr. Tariq An-Nawawi',
                'avatar' => 'https://picsum.photos/seed/tariq/100/100',
                'role' => 'dosen',
                'student_id' => 'DSN202004',
                'email' => 'tariq.annawawi@dosen.ulumcampus.com',
                'phone' => '081234567901',
                'bio' => 'Sejarawan Islam dan pakar peradaban Islam. Guru besar sejarah dengan pengalaman penelitian lebih dari 25 tahun.',
                'faculty_id' => 'adab',
                'password' => Hash::make('dosen123'),
            ],
            [
                'name' => 'Dr. Hana Al-Ghazali, M.Psi.',
                'avatar' => 'https://picsum.photos/seed/hana/100/100',
                'role' => 'dosen',
                'student_id' => 'DSN202005',
                'email' => 'hana.alghazali@dosen.ulumcampus.com',
                'phone' => '081234567902',
                'bio' => 'Psikolog klinis dan peneliti psikologi Islam. Menggabungkan pendekatan psikologi modern dengan nilai-nilai Islam.',
                'faculty_id' => 'psikologi',
                'password' => Hash::make('dosen123'),
            ]
        ];

        foreach ($frontendUsers as $userData) {
            // Create the user or update if already exists
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
