<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiscussionThread;
use App\Models\DiscussionPost;
use App\Models\Course;
use App\Models\User;

class DiscussionSeeder extends Seeder
{
    public function run(): void
    {
        // Create discussion threads based on frontend constants
        $discussionThreads = [
            [
                'id' => 'DT001',
                'course_id' => $this->getCourseIdByCode('AQ101'),
                'title' => 'Pertanyaan tentang Batasan Sifat Istiwa',
                'created_by' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'type' => 'question',
                'status' => 'open',
                'is_pinned' => true,
                'is_closed' => false,
                'is_locked' => false,
                'view_count' => 45,
                'reply_count' => 2,
            ],
            [
                'id' => 'DT002',
                'course_id' => $this->getCourseIdByCode('AQ101'),
                'title' => 'Dalil-dalil Tauhid Uluhiyah',
                'created_by' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'type' => 'discussion',
                'status' => 'open',
                'is_pinned' => false,
                'is_closed' => false,
                'is_locked' => false,
                'view_count' => 32,
                'reply_count' => 2,
            ],
            [
                'id' => 'DT003',
                'course_id' => $this->getCourseIdByCode('FQ201'),
                'title' => 'Diskusi: Hukum Dropshipping',
                'created_by' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'type' => 'discussion',
                'status' => 'closed',
                'is_pinned' => false,
                'is_closed' => true,
                'is_locked' => false,
                'view_count' => 28,
                'reply_count' => 2,
            ],
            [
                'id' => 'DT004',
                'course_id' => $this->getCourseIdByCode('FQ201'),
                'title' => 'Perbedaan Murabahah dan Musyarakah Mutanaqisah?',
                'created_by' => $this->getUserIdByEmail('abdullah@student.ulumcampus.com'),
                'type' => 'question',
                'status' => 'open',
                'is_pinned' => false,
                'is_closed' => false,
                'is_locked' => false,
                'view_count' => 18,
                'reply_count' => 2,
            ],
            [
                'id' => 'DT005',
                'course_id' => $this->getCourseIdByCode('SN701'),
                'title' => 'Potensi Bias pada Chatbot Fatwa Berbasis AI',
                'created_by' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'type' => 'question',
                'status' => 'open',
                'is_pinned' => true,
                'is_closed' => false,
                'is_locked' => false,
                'view_count' => 67,
                'reply_count' => 3,
            ],
            [
                'id' => 'DT006',
                'course_id' => $this->getCourseIdByCode('SN701'),
                'title' => 'Halal-chain: Penerapan Blockchain untuk Industri Halal',
                'created_by' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'type' => 'discussion',
                'status' => 'open',
                'is_pinned' => false,
                'is_closed' => false,
                'is_locked' => false,
                'view_count' => 41,
                'reply_count' => 2,
            ]
        ];

        foreach ($discussionThreads as $threadData) {
            if ($threadData['course_id'] && $threadData['created_by']) {
                $thread = DiscussionThread::updateOrCreate(
                    ['id' => $threadData['id']],
                    $threadData
                );

                // Create posts for each thread based on frontend constants
                $posts = $this->createDiscussionPosts($thread->id, $threadData['title']);
                
                // Update last post info
                if (!empty($posts)) {
                    $lastPost = $posts[count($posts) - 1];
                    $thread->update([
                        'last_post_by' => $lastPost['user_id'],
                        'last_post_at' => $lastPost['created_at'],
                    ]);
                }
            }
        }
    }

    private function createDiscussionPosts($threadId, $threadTitle)
    {
        $posts = [];

        switch ($threadId) {
            case 'DT001':
                $posts = [
                    [
                        'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                        'content' => 'Assalamu\'alaikum, Ustadz. Saya masih bingung bagaimana kita harus meyakini sifat Istiwa Allah tanpa terjerumus ke dalam tasybih (menyerupakan dengan makhluk). Mohon penjelasannya, jazakallah.',
                        'created_at' => now()->subDays(2),
                    ],
                    [
                        'user_id' => $this->getUserIdByEmail('yusuf.alfatih@dosen.ulumcampus.com'),
                        'content' => 'Wa\'alaikumussalam. Pertanyaan yang bagus. Ahlussunnah meyakini Allah ber-istiwa di atas \'Arsy sesuai dengan keagungan-Nya, tanpa menanyakan "bagaimana" (bila kaif), tanpa menyerupakan dengan makhluk, tanpa menolak, dan tanpa mengubah maknanya. Kita tetapkan sesuai yang Allah kabarkan dalam Al-Qur\'an.',
                        'created_at' => now()->subDays(1),
                    ],
                ];
                break;
            case 'DT002':
                $posts = [
                    [
                        'user_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                        'content' => 'Apakah ada yang bisa berbagi dalil-dalil paling kuat dari Al-Qur\'an tentang Tauhid Uluhiyah selain surat Al-Ikhlas?',
                        'created_at' => now()->subDays(5),
                    ],
                    [
                        'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                        'content' => 'Selain Al-Ikhlas, ayat kursi (Al-Baqarah: 255) adalah ayat yang sangat agung yang menjelaskan tentang keesaan dan kekuasaan Allah. Juga awal surat Al-Hadid banyak menjelaskan tentang Asma wa Sifat.',
                        'created_at' => now()->subDays(4),
                    ],
                ];
                break;
            case 'DT003':
                $posts = [
                    [
                        'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                        'content' => 'Apakah skema dropshipping diperbolehkan? Karena penjual menjual barang yang belum ia miliki.',
                        'created_at' => now()->subDays(10),
                    ],
                    [
                        'user_id' => $this->getUserIdByEmail('aisyah.h@staff.ulumcampus.com'),
                        'content' => 'Ini masuk dalam pembahasan menjual apa yang tidak dimiliki. Ulama berbeda pendapat. Sebagian membolehkan jika skemanya diubah menjadi akad salam atau wakalah (perwakilan). Diskusi yang menarik. Thread ini saya tutup ya, akan dibahas lebih lanjut di sesi live pekan depan.',
                        'created_at' => now()->subDays(9),
                    ],
                ];
                break;
            case 'DT004':
                $posts = [
                    [
                        'user_id' => $this->getUserIdByEmail('abdullah@student.ulumcampus.com'),
                        'content' => 'Assalamu\'alaikum. Saya masih belum paham betul perbedaan mendasar antara pembiayaan KPR dengan akad Murabahah dan MMQ. Keduanya kan sama-sama untuk kepemilikan rumah. Mohon pencerahannya.',
                        'created_at' => now()->subDays(1),
                    ],
                    [
                        'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                        'content' => 'Wa\'alaikumussalam. Setahu saya, kalau Murabahah itu jual-beli dengan margin keuntungan yang disepakati di awal, jadi cicilannya flat. Kalau MMQ itu kemitraan, porsi kepemilikan bank berkurang seiring kita mencicil, jadi ada bagi hasil dari sewa juga. Mungkin ustadzah bisa koreksi.',
                        'created_at' => now()->subHours(12),
                    ],
                ];
                break;
            case 'DT005':
                $posts = [
                    [
                        'user_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                        'content' => 'Assalamu\'alaikum, Ustadz. Saya tertarik dengan konsep chatbot fatwa, tapi khawatir, bagaimana kita memastikan AI tidak memberikan jawaban yang bias atau salah, terutama jika data training-nya terbatas pada satu mazhab saja?',
                        'created_at' => now()->subDays(3),
                    ],
                    [
                        'user_id' => $this->getUserIdByEmail('faiz.rabbani@dosen.ulumcampus.com'),
                        'content' => 'Wa\'alaikumussalam. Pertanyaan kritis, Siti. Ini adalah tantangan utama dalam etika AI Islami. Untuk mitigasi bias, pertama, sumber data harus komprehensif dan merepresentasikan berbagai pandangan ulama mu\'tabar. Kedua, transparansi model AI sangat penting; kita harus tahu *mengapa* AI memberikan jawaban tertentu. Ketiga, harus selalu ada mekanisme supervisi oleh dewan syariah manusia. AI di sini berperan sebagai asisten, bukan mufti independen.',
                        'created_at' => now()->subDays(2)->subHours(12),
                    ],
                    [
                        'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                        'content' => 'Terima kasih atas penjelasannya, Ustadz. Berarti peran manusia sebagai verifikator akhir tetap tidak tergantikan ya.',
                        'created_at' => now()->subDays(1),
                    ],
                ];
                break;
            case 'DT006':
                $posts = [
                    [
                        'user_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                        'content' => 'Saya baca tentang konsep "halal-chain" yang menggunakan blockchain untuk menjamin kehalalan produk dari hulu ke hilir. Apakah ini sudah banyak diterapkan di Indonesia dan apa tantangan terbesarnya?',
                        'created_at' => now()->subDays(7),
                    ],
                    [
                        'user_id' => $this->getUserIdByEmail('faiz.rabbani@dosen.ulumcampus.com'),
                        'content' => 'Betul sekali, konsepnya sangat menjanjikan. Di Indonesia sudah ada beberapa startup yang merintis, tapi tantangannya masih besar. Terutama pada adopsi teknologi di seluruh rantai pasok (supply chain), standardisasi data, dan biaya implementasi awal. Namun potensinya untuk meningkatkan kepercayaan konsumen sangat besar.',
                        'created_at' => now()->subDays(6),
                    ],
                ];
                break;
        }

        foreach ($posts as $postData) {
            if ($postData['user_id']) {
                DiscussionPost::updateOrCreate(
                    [
                        'thread_id' => $threadId,
                        'user_id' => $postData['user_id'],
                        'content' => $postData['content'],
                    ],
                    [
                        'thread_id' => $threadId,
                        'user_id' => $postData['user_id'],
                        'content' => $postData['content'],
                        'created_at' => $postData['created_at'],
                        'updated_at' => $postData['created_at'],
                        'likes_count' => rand(0, 15), // Random likes for variety
                        'is_edited' => false,
                    ]
                );
            }
        }
        
        return $posts;
    }

    private function getCourseIdByCode($code)
    {
        $course = Course::where('code', $code)->first();
        return $course ? $course->id : null;
    }

    private function getUserIdByEmail($email)
    {
        $user = User::where('email', $email)->first();
        return $user ? $user->id : null;
    }
}
