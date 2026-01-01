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
                'learning_objectives' => [
                    'Mampu menjelaskan pilar-pilar fundamental keimanan dalam Islam.',
                    'Memahami konsep Tauhid dan pembagiannya secara komprehensif.',
                    'Dapat membedakan antara keyakinan yang lurus dengan penyimpangan.',
                    'Menginternalisasi konsekuensi dari syahadatain dalam kehidupan sehari-hari.'
                ],
                'syllabus_data' => [
                    ['week' => 1, 'topic' => 'Pengantar Ilmu Aqidah', 'description' => 'Definisi, urgensi, dan sumber-sumber utama dalam mempelajari aqidah Islamiyah.'],
                    ['week' => 2, 'topic' => 'Makna dan Konsekuensi Syahadatain', 'description' => 'Analisis mendalam tentang rukun, syarat, dan pembatal dua kalimat syahadat.'],
                    ['week' => 3, 'topic' => 'Konsep Tauhid dan Pembagiannya', 'description' => 'Pembahasan Tauhid Rububiyah, Uluhiyah, dan Asma wa Sifat beserta dalil-dalilnya.'],
                    ['week' => 4, 'topic' => 'Keimanan kepada Malaikat, Kitab, dan Rasul', 'description' => 'Mempelajari hakikat, nama, dan tugas-tugas malaikat serta kewajiban beriman kepada kitab-kitab dan para rasul Allah.']
                ],
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
                'learning_objectives' => [
                    'Mampu mengidentifikasi unsur Riba, Gharar, dan Maysir dalam transaksi modern.',
                    'Memahami skema akad-akad utama dalam produk perbankan syariah.',
                    'Menganalisis isu-isu kontemporer dalam fintech syariah.',
                    'Menerapkan kaidah-kaidah fiqh muamalat dalam studi kasus.'
                ],
                    'syllabus_data' => [
                    ['week' => 1, 'topic' => 'Kaidah Fiqh Muamalat', 'description' => 'Mempelajari kaidah-kaidah kunci seperti "Al-ashlu fil mu\'amalah al-ibahah" (Hukum asal dalam muamalah adalah boleh).'],
                    ['week' => 2, 'topic' => 'Riba dan Gharar', 'description' => 'Definisi, jenis-jenis, dan bahaya Riba serta ketidakpastian (Gharar) dalam transaksi modern.'],
                    ['week' => 3, 'topic' => 'Akad Jual Beli (Al-Bai\')', 'description' => 'Pembahasan berbagai jenis akad jual beli seperti Murabahah, Salam, dan Istisna\''],
                    ['week' => 4, 'topic' => 'Akad Kemitraan (Syirkah)', 'description' => 'Mempelajari konsep Mudharabah dan Musyarakah serta aplikasinya dalam bisnis dan investasi.']
                ],
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
                'learning_objectives' => [
                    'Memahami perbedaan fundamental antara manajemen keuangan syariah dan konvensional.',
                    'Mampu menganalisis laporan keuangan lembaga keuangan syariah.',
                    'Mengidentifikasi dan mengelola risiko-risiko spesifik dalam keuangan syariah.',
                    'Menyusun perencanaan investasi yang sesuai dengan prinsip syariah.'
                ],
                    'syllabus_data' => [
                    ['week' => 1, 'topic' => 'Prinsip Dasar Keuangan Islam', 'description' => 'Membedah filosofi dan tujuan (Maqashid Syariah) dari sistem keuangan Islam.'],
                    ['week' => 2, 'topic' => 'Manajemen Aset dan Liabilitas LKS', 'description' => 'Teknik mengelola aset dan liabilitas pada Lembaga Keuangan Syariah (LKS) untuk menjaga likuiditas dan profitabilitas.'],
                    ['week' => 3, 'topic' => 'Manajemen Risiko Keuangan Syariah', 'description' => 'Identifikasi dan mitigasi berbagai jenis risiko, termasuk risiko kredit, pasar, dan operasional yang spesifik bagi LKS.']
                ],
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
                'learning_objectives' => [
                    'Merancang Rencana Pelaksanaan Pembelajaran (RPP) PAI yang inovatif.',
                    'Menerapkan berbagai model pembelajaran aktif dalam kelas PAI.',
                    'Mengembangkan media pembelajaran PAI berbasis teknologi.',
                    'Melakukan evaluasi pembelajaran yang otentik dan bermakna.'
                ],
                    'syllabus_data' => [
                    ['week' => 1, 'topic' => 'Filosofi dan Tujuan Pendidikan Islam', 'description' => 'Memahami landasan filosofis pendidikan dalam Islam untuk membentuk insan kamil.'],
                    ['week' => 2, 'topic' => 'Desain Kurikulum dan Pembelajaran PAI', 'description' => 'Praktik merancang silabus, RPP, dan materi ajar PAI yang sesuai dengan perkembangan peserta didik.']
                ],
            ],
            [
                'id' => 'HD202',
                'faculty_id' => 'ushuluddin',
                'major_id' => 'hadis',
                'instructor_id' => $this->getUserIdByEmail('abdullah.musnad@dosen.ulumcampus.com'),
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
                'learning_objectives' => [
                    'Memahami syarat-syarat kesahihan sanad hadis.',
                    'Mengenal kitab-kitab rujukan utama dalam ilmu rijal al-hadis.',
                    'Mampu melakukan kritik eksternal (sanad) dan internal (matan) pada tingkat dasar.',
                    'Mengidentifikasi sebab-sebab cacatnya sebuah hadis.'
                ],
                    'syllabus_data' => [
                    ['week' => 1, 'topic' => 'Pengantar Ilmu Musthalah al-Hadis', 'description' => 'Mengenal istilah-istilah kunci dalam ilmu hadis seperti sanad, matan, shahih, hasan, dan dhaif.'],
                    ['week' => 2, 'topic' => 'Ilmu Rijal al-Hadis (Kritik Perawi)', 'description' => 'Mempelajari metodologi untuk menilai kredibilitas dan kapasitas seorang perawi hadis (al-jarh wa at-ta\'dil).'],
                    ['week' => 3, 'topic' => 'Ilal al-Hadis (Cacat Tersembunyi)', 'description' => 'Mendeteksi cacat-cacat tersembunyi dalam sanad atau matan yang hanya dapat diidentifikasi oleh para ahli hadis.']
                ],
            ],
            [
                'id' => 'EK305',
                'faculty_id' => 'ekonomi',
                'major_id' => 'perbankan-syariah',
                'instructor_id' => $this->getUserIdByEmail('halimah.sadiyah@dosen.ulumcampus.com'),
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
                'learning_objectives' => [
                    'Membedakan antara akad tabarru\' dan tijarah.',
                    'Menjelaskan mekanisme operasional produk funding berbasis akad wadiah dan mudharabah.',
                    'Menjelaskan mekanisme operasional produk financing berbasis jual beli, sewa, dan bagi hasil.',
                    'Menganalisis inovasi produk perbankan syariah dari sisi kesesuaian akad.'
                ],
                    'syllabus_data' => [
                    ['week' => 1, 'topic' => 'Filosofi dan Rukun Akad', 'description' => 'Memahami pentingnya akad dalam muamalat Islam serta syarat dan rukun yang harus dipenuhi.'],
                    ['week' => 2, 'topic' => 'Akad Pendanaan (Funding)', 'description' => 'Studi mendalam tentang akad Wadiah dan Mudharabah serta aplikasinya pada produk giro, tabungan, dan deposito syariah.'],
                    ['week' => 3, 'topic' => 'Akad Pembiayaan (Financing)', 'description' => 'Analisis akad Murabahah, Ijarah, Musyarakah, dan implementasinya pada produk pembiayaan modal kerja, investasi, dan konsumtif.']
                ],
            ],
            [
                'id' => 'AD501',
                'faculty_id' => 'adab',
                'major_id' => 'spi',
                'instructor_id' => $this->getUserIdByEmail('tariq.annawawi@dosen.ulumcampus.com'),
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
                'learning_objectives' => [
                    'Mendeskripsikan periodisasi sejarah peradaban Islam.',
                    'Menganalisis faktor-faktor kemajuan dan kemunduran peradaban Islam di berbagai era.',
                    'Mengidentifikasi kontribusi ilmuwan Muslim dalam berbagai bidang ilmu pengetahuan.',
                    'Mengambil ibrah (pelajaran) dari sejarah untuk konteks kekinian.'
                ],
                    'syllabus_data' => [
                    ['week' => 1, 'topic' => 'Era Khulafaur Rasyidin', 'description' => 'Kajian tentang model kepemimpinan, ekspansi wilayah, dan peletakan dasar-dasar administrasi negara Islam.'],
                    ['week' => 2, 'topic' => 'Dinasti Umayyah dan Abbasiyah', 'description' => 'Perbandingan sistem pemerintahan, perkembangan ilmu pengetahuan, dan pusat-pusat peradaban di Damaskus dan Baghdad.'],
                    ['week' => 3, 'topic' => 'Keemasan Islam di Andalusia', 'description' => 'Menelusuri jejak kemajuan sains, seni, dan arsitektur di Cordoba serta interaksi antar peradaban di Semenanjung Iberia.']
                ],
            ],
            [
                'id' => 'PS601',
                'faculty_id' => 'psikologi',
                'major_id' => 'psikologi-islam',
                'instructor_id' => $this->getUserIdByEmail('hana.alghazali@dosen.ulumcampus.com'),
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
                'learning_objectives' => [
                    'Memahami konsep manusia (insan) dalam perspektif Al-Qur\'an dan Sunnah.',
                    'Membedakan struktur kepribadian Islam (nafs, qalb, aql, ruh).',
                    'Mengenal konsep kesehatan mental dan metode terapetik dalam Islam.',
                    'Menganalisis fenomena psikologis modern dari kacamata Islam.'
                ],
                    'syllabus_data' => [
                    ['week' => 1, 'topic' => 'Konsep Manusia dalam Psikologi Islam', 'description' => 'Analisis terminologi kunci: Nafs, Qalb, Aql, dan Ruh serta hubungannya dengan perilaku manusia.'],
                    ['week' => 2, 'topic' => 'Kesehatan dan Gangguan Mental Perspektif Islam', 'description' => 'Membahas konsep tazkiyatun nafs (penyucian jiwa) sebagai fondasi kesehatan mental dan pendekatan spiritual terhadap gangguan seperti waswas dan kesedihan.']
                ],
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
                'learning_objectives' => [
                    'Memahami dasar-dasar teknologi Kecerdasan Buatan (AI).',
                    'Menganalisis potensi dan tantangan penerapan AI dalam konteks keislaman.',
                    'Merumuskan panduan etis (digital ethics) berdasarkan Maqashid Syariah.',
                    'Mengevaluasi aplikasi-aplikasi Islami berbasis AI yang ada saat ini.'
                ],
                'syllabus_data' => [
                    ['week' => 1, 'topic' => 'Dasar-dasar AI dan Machine Learning', 'description' => 'Pengenalan konsep-konsep inti AI seperti supervised/unsupervised learning, neural networks, dan natural language processing (NLP).'],
                    ['week' => 2, 'topic' => 'Maqashid Syariah sebagai Landasan Etika AI', 'description' => 'Menerapkan lima tujuan utama syariat (hifdz ad-din, an-nafs, al-aql, an-nasl, al-mal) dalam merancang dan mengevaluasi teknologi AI.']
                ],
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
