<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Create courses based on frontend constants
        $courses = [
            [
                'id' => 'AQ101',
                'faculty_id' => 'ushuluddin',
                'major_id' => 'aqidah',
                'instructor_id' => User::where('email', 'yusuf.alfatih@dosen.ulumcampus.com')->first()->id ?? null,
                'code' => 'AQ101',
                'name' => 'Pengantar Aqidah Islamiyah',
                'description' => 'Membahas pilar-pilar fundamental keimanan dalam Islam berdasarkan Al-Qur\'an dan Sunnah dengan pemahaman salaful ummah. Kursus ini mencakup tauhid, kenabian, hari akhir, dan takdir.',
                'credit_hours' => 3,
                'capacity' => 50,
                'current_enrollment' => 0,
                'semester' => 'Fall',
                'year' => 2024,
                'schedule' => 'Mon/Wed 10:00-11:30',
                'room' => 'Ushuluddin Building 101',
                'image_url' => 'https://picsum.photos/seed/aqidah/600/400',
                'is_active' => true,
                'status' => 'Published',
                'mode' => 'VOD',
            ],
            [
                'id' => 'FQ201',
                'faculty_id' => 'syariah',
                'major_id' => 'hes',
                'instructor_id' => User::where('email', 'aisyah.h@staff.ulumcampus.com')->first()->id ?? null,
                'code' => 'FQ201',
                'name' => 'Fiqh Muamalat Kontemporer',
                'description' => 'Analisis transaksi keuangan modern dari perspektif fiqh. Meliputi pembahasan perbankan syariah, asuransi, pasar modal, dan fintech sesuai prinsip-prinsip syariah.',
                'credit_hours' => 4,
                'capacity' => 40,
                'current_enrollment' => 0,
                'semester' => 'Fall',
                'year' => 2024,
                'schedule' => 'Tue/Thu 14:00-15:30',
                'room' => 'Syariah Building 201',
                'image_url' => 'https://picsum.photos/seed/muamalat/600/400',
                'is_active' => true,
                'status' => 'Published',
                'mode' => 'VOD',
            ],
            [
                'id' => 'EK301',
                'faculty_id' => 'ekonomi',
                'major_id' => 'keuangan-investasi-syariah',
                'instructor_id' => User::where('email', 'rektor@ulumcampus.com')->first()->id ?? null,
                'code' => 'EK301',
                'name' => 'Manajemen Keuangan Syariah',
                'description' => 'Mempelajari prinsip dan praktik manajemen keuangan pada lembaga keuangan syariah, termasuk manajemen likuiditas, risiko, dan investasi halal.',
                'credit_hours' => 3,
                'capacity' => 35,
                'current_enrollment' => 0,
                'semester' => 'Spring',
                'year' => 2024,
                'schedule' => 'Fri 09:00-12:00',
                'room' => 'Economy Building 301',
                'image_url' => 'https://picsum.photos/seed/keuangan/600/400',
                'is_active' => true,
                'status' => 'Published',
                'mode' => 'Live',
            ],
            [
                'id' => 'TR401',
                'faculty_id' => 'tarbiyah',
                'major_id' => 'pai',
                'instructor_id' => User::where('email', 'yusuf.alfatih@dosen.ulumcampus.com')->first()->id ?? null,
                'code' => 'TR401',
                'name' => 'Metodologi Pengajaran PAI',
                'description' => 'Kursus ini membekali calon pendidik dengan berbagai metode dan strategi pengajaran Pendidikan Agama Islam (PAI) yang efektif dan relevan untuk generasi milenial dan Z.',
                'credit_hours' => 3,
                'capacity' => 45,
                'current_enrollment' => 0,
                'semester' => 'Fall',
                'year' => 2024,
                'schedule' => 'Mon/Wed/Fri 13:00-14:00',
                'room' => 'Tarbiyah Building 102',
                'image_url' => 'https://picsum.photos/seed/tarbiyah/600/400',
                'is_active' => true,
                'status' => 'Draft',
                'mode' => 'VOD',
            ],
            [
                'id' => 'HD202',
                'faculty_id' => 'ushuluddin',
                'major_id' => 'hadis',
                'instructor_id' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'code' => 'HD202',
                'name' => 'Kritik Sanad dan Matan Hadis',
                'description' => 'Mempelajari metodologi ulama hadis dalam melakukan kritik (naqd) terhadap sanad (rantai perawi) dan matan (isi) hadis untuk menentukan otentisitasnya.',
                'credit_hours' => 3,
                'capacity' => 30,
                'current_enrollment' => 0,
                'semester' => 'Fall',
                'year' => 2024,
                'schedule' => 'Tue/Thu 10:00-11:30',
                'room' => 'Ushuluddin Building 202',
                'image_url' => 'https://picsum.photos/seed/hadis/600/400',
                'is_active' => true,
                'status' => 'Published',
                'mode' => 'VOD',
            ],
            [
                'id' => 'EK305',
                'faculty_id' => 'ekonomi',
                'major_id' => 'perbankan-syariah',
                'instructor_id' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'code' => 'EK305',
                'name' => 'Akad dan Produk Perbankan Syariah',
                'description' => 'Mendalami berbagai jenis akad (mudharabah, musyarakah, murabahah, ijarah) dan implementasinya dalam produk-produk perbankan syariah modern.',
                'credit_hours' => 3,
                'capacity' => 40,
                'current_enrollment' => 0,
                'semester' => 'Spring',
                'year' => 2024,
                'schedule' => 'Mon/Wed 14:00-15:30',
                'room' => 'Economy Building 205',
                'image_url' => 'https://picsum.photos/seed/perbankan/600/400',
                'is_active' => true,
                'status' => 'Published',
                'mode' => 'Live',
            ],
            [
                'id' => 'AD501',
                'faculty_id' => 'adab',
                'major_id' => 'spi',
                'instructor_id' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'code' => 'AD501',
                'name' => 'Sejarah Peradaban Islam',
                'description' => 'Menelusuri jejak kegemilangan peradaban Islam dari masa Khulafaur Rasyidin, Bani Umayyah, Abbasiyah, hingga Andalusia, serta kontribusinya bagi dunia.',
                'credit_hours' => 3,
                'capacity' => 50,
                'current_enrollment' => 0,
                'semester' => 'Fall',
                'year' => 2024,
                'schedule' => 'Mon/Wed 09:00-10:30',
                'room' => 'Adab Building 101',
                'image_url' => 'https://picsum.photos/seed/sejarah/600/400',
                'is_active' => true,
                'status' => 'Archived',
                'mode' => 'VOD',
            ],
            [
                'id' => 'PS601',
                'faculty_id' => 'psikologi',
                'major_id' => 'psikologi-islam',
                'instructor_id' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                'code' => 'PS601',
                'name' => 'Pengantar Psikologi Islam',
                'description' => 'Mengintegrasikan konsep-konsep psikologi modern dengan pandangan Islam tentang jiwa (nafs), hati (qalb), dan akal, serta metode tazkiyatun nafs.',
                'credit_hours' => 2,
                'capacity' => 35,
                'current_enrollment' => 0,
                'semester' => 'Fall',
                'year' => 2024,
                'schedule' => 'Fri 10:00-12:00',
                'room' => 'Psychology Building 101',
                'image_url' => 'https://picsum.photos/seed/psikologi/600/400',
                'is_active' => true,
                'status' => 'Published',
                'mode' => 'VOD',
            ],
            [
                'id' => 'SN701',
                'faculty_id' => 'sains',
                'major_id' => 'ti-islami',
                'instructor_id' => $this->getUserIdByEmail('faiz.rabbani@dosen.ulumcampus.com'),
                'code' => 'SN701',
                'name' => 'AI & Etika Digital Islami',
                'description' => 'Membahas penerapan Kecerdasan Buatan (AI) dalam aplikasi Islami (seperti deteksi tajwid, chatbot fatwa) serta meninjaunya dari sudut pandang etika dan maqashid syariah.',
                'credit_hours' => 3,
                'capacity' => 30,
                'current_enrollment' => 0,
                'semester' => 'Spring',
                'year' => 2024,
                'schedule' => 'Tue/Thu 13:00-14:30',
                'room' => 'Science Building 201',
                'image_url' => 'https://picsum.photos/seed/ai-islam/600/400',
                'is_active' => true,
                'status' => 'Published',
                'mode' => 'Live',
            ]
        ];

        foreach ($courses as $courseData) {
            // Ensure we have a valid instructor ID
            if (isset($courseData['instructor_id']) && $courseData['instructor_id']) {
                Course::updateOrCreate(
                    ['code' => $courseData['code']],
                    $courseData
                );
            } else {
                // If no instructor found, create course without instructor for now
                unset($courseData['instructor_id']);
                Course::updateOrCreate(
                    ['code' => $courseData['code']],
                    $courseData
                );
            }
        }
    }

    private function getUserIdByEmail($email)
    {
        $user = User::where('email', $email)->first();
        return $user ? $user->id : null;
    }
}