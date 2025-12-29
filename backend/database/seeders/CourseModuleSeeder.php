<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\CourseModule;

class CourseModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Course modules based on frontend constants
        $modules = [
            // AQ101 - Pengantar Aqidah Islamiyah
            [
                'id' => 'm1',
                'course_id' => $this->getCourseIdByCode('AQ101'),
                'title' => 'Makna Syahadatain',
                'type' => 'video',
                'description' => 'Membedah makna dan konsekuensi dari dua kalimat syahadat sebagai fondasi utama keislaman.',
                'duration' => '45min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'captions_url' => 'https://gist.githubusercontent.com/samdutton/ca37f3adaf4e23679957b8083e061177/raw/e19399addb3b8b548c7c71f085185a06065b7a39/sintel-en.vtt',
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 1,
            ],
            [
                'id' => 'm2',
                'course_id' => $this->getCourseIdByCode('AQ101'),
                'title' => 'Pembagian Tauhid',
                'type' => 'video',
                'description' => 'Penjelasan rinci mengenai Tauhid Rububiyah, Uluhiyah, dan Asma wa Sifat beserta dalil-dalilnya.',
                'duration' => '55min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 2,
            ],
            [
                'id' => 'm3',
                'course_id' => $this->getCourseIdByCode('AQ101'),
                'title' => 'Rukun Iman',
                'type' => 'pdf',
                'description' => 'Dokumen ini berisi ringkasan komprehensif dari enam rukun iman, lengkap dengan dalil-dalil utama dari Al-Qur\'an dan Sunnah.',
                'duration' => null,
                'resource_url' => '#',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 3,
            ],
            
            // FQ201 - Fiqh Muamalat Kontemporer
            [
                'id' => 'm1',
                'course_id' => $this->getCourseIdByCode('FQ201'),
                'title' => 'Pengantar Fiqh Muamalat',
                'type' => 'video',
                'description' => 'Memahami kaidah-kaidah dasar dan prinsip umum dalam transaksi maliyah Islam.',
                'duration' => '50min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 1,
            ],
            [
                'id' => 'm2',
                'course_id' => $this->getCourseIdByCode('FQ201'),
                'title' => 'Akad-akad dalam Transaksi',
                'type' => 'pdf',
                'description' => 'Materi bacaan mendalam yang membahas berbagai jenis akad dalam transaksi maliyah, termasuk syarat, rukun, dan contoh aplikasinya.',
                'duration' => null,
                'resource_url' => '#',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 2,
            ],
            [
                'id' => 'm3',
                'course_id' => $this->getCourseIdByCode('FQ201'),
                'title' => 'Studi Kasus: Fintech Syariah',
                'type' => 'video',
                'description' => 'Analisis studi kasus mengenai aplikasi dan tantangan fiqh muamalat pada platform fintech syariah modern.',
                'duration' => '60min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 3,
            ],
            
            // EK301 - Manajemen Keuangan Syariah
            [
                'id' => 'm1',
                'course_id' => $this->getCourseIdByCode('EK301'),
                'title' => 'Dasar-dasar Keuangan Islam',
                'type' => 'video',
                'description' => 'Pengenalan prinsip dasar, larangan, dan tujuan (maqashid) dari sistem keuangan Islam.',
                'duration' => '48min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 1,
            ],
            [
                'id' => 'm2',
                'course_id' => $this->getCourseIdByCode('EK301'),
                'title' => 'Manajemen Aset & Liabilitas',
                'type' => 'pdf',
                'description' => 'Penjelasan rinci mengenai teknik-teknik manajemen aset dan liabilitas pada Lembaga Keuangan Syariah (LKS) untuk menjaga likuiditas dan profitabilitas.',
                'duration' => null,
                'resource_url' => '#',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 2,
            ],
            [
                'id' => 'm_live_1',
                'course_id' => $this->getCourseIdByCode('EK301'),
                'title' => 'Sesi Live: Manajemen Likuiditas',
                'type' => 'live',
                'description' => 'Sesi tanya jawab dan diskusi mendalam tentang manajemen likuiditas pada lembaga keuangan syariah.',
                'duration' => null,
                'resource_url' => null,
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => now()->addDays(3)->setTime(10, 0),
                'live_url' => 'https://meet.google.com/abc-defg-hij',
                'is_published' => true,
                'order' => 3,
            ],
            
            // TR401 - Metodologi Pengajaran PAI
            [
                'id' => 'm1',
                'course_id' => $this->getCourseIdByCode('TR401'),
                'title' => 'Filosofi Pendidikan Islam',
                'type' => 'video',
                'description' => 'Kajian mendalam tentang landasan filosofis dan tujuan akhir dari pendidikan dalam Islam (Tarbiyah Islamiyah).',
                'duration' => '52min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 1,
            ],
            [
                'id' => 'm2',
                'course_id' => $this->getCourseIdByCode('TR401'),
                'title' => 'Model Pembelajaran Aktif',
                'type' => 'pdf',
                'description' => 'Panduan praktis mengenai penerapan model pembelajaran aktif seperti Problem-Based Learning dan Project-Based Learning dalam konteks PAI.',
                'duration' => null,
                'resource_url' => '#',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 2,
            ],
            
            // HD202 - Kritik Sanad dan Matan Hadis
            [
                'id' => 'm1',
                'course_id' => $this->getCourseIdByCode('HD202'),
                'title' => 'Pengantar Ilmu Rijal al-Hadis',
                'type' => 'video',
                'description' => 'Video ini menjelaskan urgensi ilmu rijal (biografi perawi) dan konsep al-jarh wa at-ta\'dil (kritik dan pujian) sebagai fondasi kritik sanad.',
                'duration' => '60min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 1,
            ],
            [
                'id' => 'm2',
                'course_id' => $this->getCourseIdByCode('HD202'),
                'title' => 'Syarat-syarat Sanad yang Sahih',
                'type' => 'pdf',
                'description' => 'Dokumen yang merinci lima syarat utama kesahihan sanad sebuah hadis, yaitu: bersambungnya sanad, keadilan perawi, kedhabitan perawi, tidak adanya syadz (kejanggalan), dan tidak adanya \'illah (cacat tersembunyi).',
                'duration' => null,
                'resource_url' => '#',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 2,
            ],
            [
                'id' => 'm3',
                'course_id' => $this->getCourseIdByCode('HD202'),
                'title' => 'Metode Kritik Matan',
                'type' => 'video',
                'description' => 'Pembahasan mengenai bagaimana ulama hadis mengkritik isi (matan) hadis dengan membandingkannya dengan Al-Qur\'an, hadis lain yang lebih kuat, dan fakta sejarah.',
                'duration' => '50min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackOnStreetAndDirt.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 3,
            ],
            
            // EK305 - Akad dan Produk Perbankan Syariah
            [
                'id' => 'm1',
                'course_id' => $this->getCourseIdByCode('EK305'),
                'title' => 'Filosofi Akad dalam Islam',
                'type' => 'pdf',
                'description' => 'Materi ini membahas filosofi, rukun, dan syarat-syarat sahnya sebuah akad dalam perspektif hukum Islam, sebagai landasan untuk semua transaksi.',
                'duration' => null,
                'resource_url' => '#',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 1,
            ],
            [
                'id' => 'm2',
                'course_id' => $this->getCourseIdByCode('EK305'),
                'title' => 'Akad-akad Tabarru\' dan Tijarah',
                'type' => 'video',
                'description' => 'Perbedaan mendasar antara akad sosial (non-profit) seperti qardh dan wakalah, dengan akad komersial (profit-oriented) seperti murabahah dan ijarah.',
                'duration' => '75min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 2,
            ],
            [
                'id' => 'm3',
                'course_id' => $this->getCourseIdByCode('EK305'),
                'title' => 'Analisis Produk Funding & Financing',
                'type' => 'video',
                'description' => 'Video ini membedah skema produk-produk utama di bank syariah, dari sisi penghimpunan dana (funding) dan penyaluran dana (financing).',
                'duration' => '80min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/VolkswagenGTIReview.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 3,
            ],
            
            // AD501 - Sejarah Peradaban Islam
            [
                'id' => 'm1',
                'course_id' => $this->getCourseIdByCode('AD501'),
                'title' => 'Era Kenabian dan Khulafaur Rasyidin',
                'type' => 'video',
                'description' => 'Pembahasan mengenai periode fondasi peradaban Islam, mulai dari era kenabian di Madinah hingga masa kepemimpinan empat khalifah pertama.',
                'duration' => '60min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 1,
            ],
            [
                'id' => 'm2',
                'course_id' => $this->getCourseIdByCode('AD501'),
                'title' => 'Puncak Keemasan di Baghdad',
                'type' => 'pdf',
                'description' => 'Ringkasan sejarah Dinasti Abbasiyah, fokus pada perkembangan ilmu pengetahuan di Baitul Hikmah, Baghdad, serta kontribusi para ilmuwan pada masa itu.',
                'duration' => null,
                'resource_url' => '#',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 2,
            ],
            [
                'id' => 'm3',
                'course_id' => $this->getCourseIdByCode('AD501'),
                'title' => 'Sains dan Filsafat di Andalusia',
                'type' => 'video',
                'description' => 'Menelusuri jejak kemajuan ilmu pengetahuan, seni, dan filsafat di Cordoba, Spanyol, serta bagaimana interaksi antar peradaban terjadi.',
                'duration' => '65min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WhatCarCanYouGetForAGrand.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 3,
            ],
            
            // PS601 - Pengantar Psikologi Islam
            [
                'id' => 'm1',
                'course_id' => $this->getCourseIdByCode('PS601'),
                'title' => 'Konsep Manusia dalam Al-Qur\'an',
                'type' => 'video',
                'description' => 'Analisis terminologi kunci dalam Al-Qur\'an yang berkaitan dengan jiwa manusia, seperti Nafs, Qalb, Aql, dan Ruh.',
                'duration' => '55min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'captions_url' => 'https://gist.githubusercontent.com/samdutton/ca37f3adaf4e23679957b8083e061177/raw/e19399addb3b8b548c7c71f085185a06065b7a39/sintel-en.vtt',
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 1,
            ],
            [
                'id' => 'm2',
                'course_id' => $this->getCourseIdByCode('PS601'),
                'title' => 'Teori Kepribadian Islam',
                'type' => 'pdf',
                'description' => 'Pembahasan tentang struktur kepribadian dalam Psikologi Islam yang meliputi konsep Nafs (jiwa), Qalb (hati), Aql (akal), dan Ruh, serta interaksi dinamis di antara keempatnya.',
                'duration' => null,
                'resource_url' => '#',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 2,
            ],
            
            // SN701 - AI & Etika Digital Islami
            [
                'id' => 'm1',
                'course_id' => $this->getCourseIdByCode('SN701'),
                'title' => 'Dasar-dasar Machine Learning',
                'type' => 'video',
                'description' => 'Pengenalan konsep-konsep inti Kecerdasan Buatan seperti supervised/unsupervised learning, neural networks, dan natural language processing (NLP).',
                'duration' => '70min',
                'resource_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 1,
            ],
            [
                'id' => 'm2',
                'course_id' => $this->getCourseIdByCode('SN701'),
                'title' => 'Maqashid Syariah dalam Teknologi',
                'type' => 'pdf',
                'description' => 'Kajian tentang bagaimana lima tujuan utama syariat (hifdz ad-din, an-nafs, al-aql, an-nasl, al-mal) dapat dijadikan sebagai kerangka kerja etis dalam merancang dan mengevaluasi teknologi kecerdasan buatan.',
                'duration' => null,
                'resource_url' => '#',
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => null,
                'live_url' => null,
                'is_published' => true,
                'order' => 2,
            ],
            [
                'id' => 'm_live_sn701',
                'course_id' => $this->getCourseIdByCode('SN701'),
                'title' => 'Sesi Live: Etika & Fiqh Digital',
                'type' => 'live',
                'description' => 'Diskusi interaktif dan Q&A seputar tantangan fiqh dalam era digital dan kecerdasan buatan.',
                'duration' => null,
                'resource_url' => null,
                'captions_url' => null,
                'attachment_url' => null,
                'start_time' => now()->addDays(5)->setTime(14, 0),
                'live_url' => 'https://meet.google.com/abc-defg-hij',
                'is_published' => true,
                'order' => 3,
            ],
        ];

        foreach ($modules as $moduleData) {
            if ($moduleData['course_id']) {
                // Remove 'id' and 'duration' from moduleData as they don't exist in schema
                $moduleDataToInsert = array_diff_key($moduleData, ['id' => '', 'duration' => '']);
                
                // Map field names to match database schema
                if (isset($moduleData['resource_url'])) {
                    $moduleDataToInsert['video_url'] = $moduleData['resource_url'];
                    unset($moduleDataToInsert['resource_url']);
                }
                
                if (isset($moduleData['captions_url'])) {
                    // captions_url doesn't exist in schema, store in content field
                    if (isset($moduleDataToInsert['content'])) {
                        $moduleDataToInsert['content'] .= "\n\nCaptions: " . $moduleData['captions_url'];
                    } else {
                        $moduleDataToInsert['content'] = "Captions: " . $moduleData['captions_url'];
                    }
                    unset($moduleDataToInsert['captions_url']);
                }
                
                if (isset($moduleData['attachment_url'])) {
                    // attachment_url maps to document_url
                    $moduleDataToInsert['document_url'] = $moduleData['attachment_url'];
                    unset($moduleDataToInsert['attachment_url']);
                }
                
                CourseModule::updateOrCreate(
                    [
                        'course_id' => $moduleDataToInsert['course_id'],
                        'title' => $moduleDataToInsert['title']
                    ],
                    $moduleDataToInsert
                );
            }
        }
    }

    private function getCourseIdByCode($code)
    {
        $course = Course::where('code', $code)->first();
        return $course ? $course->id : null;
    }
}
