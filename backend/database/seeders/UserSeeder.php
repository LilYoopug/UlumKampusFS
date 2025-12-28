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
        $frontendUsers = [
            [
                'name' => 'Ahmad Faris',
                'avatar' => 'https://picsum.photos/seed/ahmad/100/100',
                'role' => 'Mahasiswa',
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
                'role' => 'Dosen',
                'student_id' => 'DSN202001',
                'email' => 'yusuf.alfatih@dosen.ulumcampus.com',
                'phone' => '081234567891',
                'bio' => 'Akademisi dan da\'i yang fokus pada studi Aqidah dan Manhaj. Meraih gelar Doktor dari Universitas Islam Madinah.',
                'password' => Hash::make('dosen123'),
            ],
            [
                'name' => 'Dr. Aisyah Hasanah',
                'avatar' => 'https://picsum.photos/seed/aisyah/100/100',
                'role' => 'Prodi Admin',
                'student_id' => 'PRODI01',
                'email' => 'aisyah.h@staff.ulumcampus.com',
                'phone' => '081234567892',
                'bio' => 'Kepala Program Studi Syariah & Hukum. Pakar Fiqh Muamalat dan Ekonomi Syariah. Lulusan S1 Fiqh dari Universitas Al-Azhar, Kairo.',
                'faculty_id' => 'syariah',
                'password' => Hash::make('prodi123'),
            ],
            [
                'name' => 'Prof. Dr. Ibrahim Malik',
                'avatar' => 'https://picsum.photos/seed/ibrahim/100/100',
                'role' => 'Manajemen Kampus',
                'student_id' => 'REKTOR01',
                'email' => 'rektor@ulumcampus.com',
                'phone' => '081234567893',
                'bio' => 'Rektor UlumCampus. Guru besar di bidang Keuangan Syariah dengan pengalaman lebih dari 20 tahun.',
                'password' => Hash::make('manajemen123'),
            ],
            [
                'name' => 'Budi Santoso',
                'avatar' => 'https://picsum.photos/seed/budi/100/100',
                'role' => 'MABA',
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
                'role' => 'Super Admin',
                'student_id' => 'SYSADMIN',
                'email' => 'admin@ulumcampus.com',
                'phone' => '081234567894',
                'bio' => 'Administrator sistem utama UlumCampus. Bertanggung jawab atas infrastruktur teknis dan manajemen pengguna.',
                'password' => Hash::make('admin123'),
            ],
            [
                'name' => 'Dr. Eng. Faiz Rabbani',
                'avatar' => 'https://picsum.photos/seed/faiz/100/100',
                'role' => 'Dosen',
                'student_id' => 'DSN202105',
                'email' => 'faiz.rabbani@dosen.ulumcampus.com',
                'phone' => '081234567895',
                'bio' => 'Insinyur dan peneliti yang menjembatani dunia teknologi dan studi Islam. Lulusan doktor teknik dari Jepang ini memimpin sebuah lab riset yang fokus mengembangkan aplikasi AI untuk kemaslahatan umat.',
                'password' => Hash::make('dosen123'),
            ],
            [
                'name' => 'Siti Maryam',
                'avatar' => 'https://picsum.photos/seed/maryam/100/100',
                'role' => 'Mahasiswa',
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
                'role' => 'Mahasiswa',
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