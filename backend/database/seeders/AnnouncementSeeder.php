<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;
use App\Models\Course;
use App\Models\User;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        // Create announcements based on frontend constants
        $announcements = [
            [
                'id' => 'AN001',
                'title' => 'Perubahan Jadwal Ujian Tengah Semester',
                'content' => 'Assalamu\'alaikum Warahmatullahi Wabarakatuh.

Diberitahukan kepada seluruh mahasiswa bahwa jadwal Ujian Tengah Semester (UTS) untuk beberapa mata kuliah mengalami perubahan. Mohon untuk memeriksa jadwal terbaru di kalender akademik Anda.

Perubahan ini dilakukan untuk mengakomodasi jadwal dosen dan memastikan kelancaran pelaksanaan ujian. Terima kasih atas perhatiannya.

Wassalamu\'alaikum Warahmatullahi Wabarakatuh.',
                'created_by' => $this->getUserIdByEmail('aisyah.h@staff.ulumcampus.com'),
                'category' => 'Akademik',
                'course_id' => null,
                'is_published' => true,
                'target_audience' => 'all',
                'priority' => 'normal',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'id' => 'AN002',
                'title' => 'Pelatihan Penggunaan E-Library Terbaru',
                'content' => 'Dalam rangka meningkatkan literasi digital, kami akan mengadakan sesi pelatihan online tentang cara efektif menggunakan fitur-fitur terbaru di E-Library UlumCampus. Sesi akan diadakan pada hari Sabtu pekan ini pukul 10:00 WIB. Link Zoom akan dibagikan melalui email.',
                'created_by' => $this->getUserIdByEmail('admin@ulumcampus.com'),
                'category' => 'Akademik',
                'course_id' => null,
                'is_published' => true,
                'target_audience' => 'all',
                'priority' => 'normal',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'id' => 'AN003',
                'title' => 'Pembaruan Sistem & Maintenance',
                'content' => 'Akan dilakukan pemeliharaan sistem pada hari Ahad dini hari, mulai pukul 01:00 hingga 04:00 WIB. Selama periode tersebut, akses ke platform UlumCampus mungkin akan terganggu. Mohon maaf atas ketidaknyamanannya.',
                'created_by' => $this->getUserIdByEmail('admin@ulumcampus.com'),
                'category' => 'Kampus',
                'course_id' => null,
                'is_published' => true,
                'target_audience' => 'all',
                'priority' => 'high',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(8),
            ]
        ];

        foreach ($announcements as $announcementData) {
            if ($announcementData['created_by']) {
                Announcement::updateOrCreate(
                    ['id' => $announcementData['id']],
                    $announcementData
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
