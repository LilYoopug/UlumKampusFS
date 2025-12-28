<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;
use App\Models\Major;

class FacultyMajorSeeder extends Seeder
{
    public function run(): void
    {
        // Create faculties based on frontend constants
        $faculties = [
            [
                'id' => 'ushuluddin',
                'name' => 'Ushuluddin & Dakwah',
                'description' => 'Studi fundamental keimanan dan metode dakwah.',
                'dean_name' => 'Dr. Hasan Al-Banna',
                'email' => 'dean.ushuluddin@ulumcampus.com',
                'phone' => '081234567801',
                'is_active' => true,
            ],
            [
                'id' => 'syariah',
                'name' => 'Syariah & Hukum',
                'description' => 'Kajian hukum Islam dan aplikasinya dalam kehidupan.',
                'dean_name' => 'Dr. Aisyah Hasanah',
                'email' => 'dean.syariah@ulumcampus.com',
                'phone' => '081234567802',
                'is_active' => true,
            ],
            [
                'id' => 'ekonomi',
                'name' => 'Ekonomi & Manajemen Syariah',
                'description' => 'Prinsip ekonomi dan bisnis berbasis syariah.',
                'dean_name' => 'Prof. Dr. Ibrahim Malik',
                'email' => 'dean.ekonomi@ulumcampus.com',
                'phone' => '081234567803',
                'is_active' => true,
            ],
            [
                'id' => 'tarbiyah',
                'name' => 'Tarbiyah & Pendidikan Islam',
                'description' => 'Ilmu mendidik dan membentuk generasi Islami.',
                'dean_name' => 'Dr. Yusuf Al-Fatih',
                'email' => 'dean.tarbiyah@ulumcampus.com',
                'phone' => '081234567804',
                'is_active' => true,
            ],
            [
                'id' => 'adab',
                'name' => 'Adab, Humaniora & Bahasa',
                'description' => 'Studi peradaban, sastra, dan bahasa dalam konteks Islam.',
                'dean_name' => 'Dr. Tariq An-Nawawi',
                'email' => 'dean.adab@ulumcampus.com',
                'phone' => '081234567805',
                'is_active' => true,
            ],
            [
                'id' => 'sains',
                'name' => 'Sains & Inovasi Islami',
                'description' => 'Integrasi sains dan teknologi dengan etika dan nilai-nilai Islam.',
                'dean_name' => 'Dr. Eng. Faiz Rabbani',
                'email' => 'dean.sains@ulumcampus.com',
                'phone' => '081234567806',
                'is_active' => true,
            ],
            [
                'id' => 'psikologi',
                'name' => 'Psikologi & Sosial',
                'description' => 'Kajian perilaku manusia dan masyarakat dari perspektif Islam.',
                'dean_name' => 'Dr. Hana Al-Ghazali',
                'email' => 'dean.psikologi@ulumcampus.com',
                'phone' => '081234567807',
                'is_active' => true,
            ],
            [
                'id' => 'pascasarjana',
                'name' => 'Sekolah Pascasarjana',
                'description' => 'Studi lanjutan untuk kajian Islam kontemporer dan kepemimpinan.',
                'dean_name' => 'Prof. Dr. Tariq An-Nawawi',
                'email' => 'dean.pascasarjana@ulumcampus.com',
                'phone' => '081234567808',
                'is_active' => true,
            ]
        ];

        foreach ($faculties as $facultyData) {
            // Create or update faculty with string ID
            $faculty = Faculty::updateOrCreate(
                ['id' => $facultyData['id']], // Use the id as primary key
                [
                    'id' => $facultyData['id'],
                    'name' => $facultyData['name'],
                    'description' => $facultyData['description'],
                    'dean_name' => $facultyData['dean_name'],
                    'email' => $facultyData['email'],
                    'phone' => $facultyData['phone'],
                    'is_active' => $facultyData['is_active'],
                    'code' => $facultyData['id'], // Store the original id as code too
                ]
            );

            // Create majors for each faculty based on frontend constants
            $majors = $this->getMajorsForFaculty($facultyData['id']);
            foreach ($majors as $majorData) {
                Major::updateOrCreate(
                    [
                        'faculty_id' => $facultyData['id'], // Use the string ID
                        'name' => $majorData['name']
                    ],
                    [
                        'faculty_id' => $facultyData['id'], // Use the string ID
                        'name' => $majorData['name'],
                        'code' => $majorData['code'] ?? $this->generateMajorCode($majorData['name']),
                        'description' => $majorData['description'] ?? $majorData['name'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function getMajorsForFaculty($facultyId)
    {
        switch ($facultyId) {
            case 'ushuluddin':
                return [
                    ['name' => 'Aqidah & Filsafat', 'code' => 'aqidah'],
                    ['name' => 'Ilmu Al-Qur\'an & Tafsir', 'code' => 'tafsir'],
                    ['name' => 'Ilmu Hadis', 'code' => 'hadis'],
                    ['name' => 'Perbandingan Agama', 'code' => 'perbandingan-agama'],
                    ['name' => 'KPI (Dakwah Digital)', 'code' => 'kpi'],
                ];
            case 'syariah':
                return [
                    ['name' => 'HES (Muamalat)', 'code' => 'hes'],
                    ['name' => 'Ahwal Syakhshiyyah', 'code' => 'ahwal-syakhshiyyah'],
                    ['name' => 'Siyasah', 'code' => 'siyasah'],
                    ['name' => 'Peradilan Agama/Arbitrase Syariah', 'code' => 'peradilan-agama'],
                ];
            case 'ekonomi':
                return [
                    ['name' => 'Ekonomi Islam', 'code' => 'ekonomi-islam'],
                    ['name' => 'Perbankan Syariah', 'code' => 'perbankan-syariah'],
                    ['name' => 'Akuntansi Syariah', 'code' => 'akuntansi-syariah'],
                    ['name' => 'Manajemen Syariah', 'code' => 'manajemen-syariah'],
                    ['name' => 'Keuangan & Investasi Syariah', 'code' => 'keuangan-investasi-syariah'],
                ];
            case 'tarbiyah':
                return [
                    ['name' => 'PAI', 'code' => 'pai'],
                    ['name' => 'PBA', 'code' => 'pba'],
                    ['name' => 'PGMI', 'code' => 'pgmi'],
                    ['name' => 'MPI', 'code' => 'mpi'],
                    ['name' => 'TekPen Islami', 'code' => 'tekpen-islami'],
                ];
            case 'adab':
                return [
                    ['name' => 'SPI', 'code' => 'spi'],
                    ['name' => 'BSA', 'code' => 'bsa'],
                    ['name' => 'English for Islamic Studies', 'code' => 'english-islamic'],
                    ['name' => 'Islamic Civilization', 'code' => 'islamic-civ'],
                ];
            case 'sains':
                return [
                    ['name' => 'Sains & Etika Syariah', 'code' => 'sains-etika'],
                    ['name' => 'TI Islami (AI/Apps)', 'code' => 'ti-islami'],
                    ['name' => 'Teknik Industri Halal & SC', 'code' => 'industri-halal'],
                    ['name' => 'Farmasi Halal', 'code' => 'farmasi-halal'],
                    ['name' => 'Kesehatan Syariah', 'code' => 'kesehatan-syariah'],
                ];
            case 'psikologi':
                return [
                    ['name' => 'Psikologi Islam', 'code' => 'psikologi-islam'],
                    ['name' => 'BK Islami', 'code' => 'bk-islami'],
                    ['name' => 'Sosiologi Islam', 'code' => 'sosiologi-islam'],
                    ['name' => 'Studi Gender dalam Islam', 'code' => 'studi-gender'],
                ];
            case 'pascasarjana':
                return [
                    ['name' => 'Kajian Islam Kontemporer', 'code' => 'kajian-kontemporer'],
                    ['name' => 'Fiqh al-Aqalliyat', 'code' => 'fiqh-aqalliyat'],
                    ['name' => 'Islamic Leadership & Da\'wah Management', 'code' => 'islamic-leadership'],
                ];
            default:
                return [];
        }
    }

    private function generateMajorCode($name)
    {
        // Generate a code from the major name if not provided
        $code = strtolower(str_replace([' ', '(', ')', '&', '/'], ['-', '', '', '', ''], $name));
        $code = preg_replace('/[^a-z0-9-]/', '', $code);
        return $code;
    }
}