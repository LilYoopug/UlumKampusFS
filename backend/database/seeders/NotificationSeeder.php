<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Create notifications based on frontend constants
        $notifications = [
            [
                'id' => 'N001',
                'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'type' => 'forum',
                'title' => 'Dr. Yusuf Al-Fatih membalas diskusi Anda',
                'message' => 'Dr. Yusuf Al-Fatih telah membalas diskusi Anda di mata kuliah Pengantar Aqidah Islamiyah',
                'is_read' => false,
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
            [
                'id' => 'N002',
                'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'type' => 'grade',
                'title' => 'Nilai telah diperbarui',
                'message' => 'Nilai untuk tugas "Presentasi Kontribusi Ilmuwan Muslim" telah diperbarui',
                'is_read' => false,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'id' => 'N003',
                'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'type' => 'assignment',
                'title' => 'Tugas baru telah ditambahkan',
                'message' => 'Tugas baru "Analisis Produk Bank Syariah" telah ditambahkan ke mata kuliah Akad dan Produk Perbankan Syariah',
                'is_read' => true,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => 'N004',
                'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'type' => 'announcement',
                'title' => 'Pengumuman baru dari Dr. Aisyah Hasanah',
                'message' => 'Dr. Aisyah Hasanah membuat pengumuman baru tentang Perubahan Jadwal Ujian Tengah Semester',
                'is_read' => true,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ]
        ];

        foreach ($notifications as $notificationData) {
            if ($notificationData['user_id']) {
                Notification::updateOrCreate(
                    ['id' => $notificationData['id']],
                    $notificationData
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