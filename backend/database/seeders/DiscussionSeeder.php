<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiscussionThread;
use App\Models\DiscussionPost;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Support\Str;

class DiscussionSeeder extends Seeder
{
    private $threadTypes = ['question', 'discussion', 'announcement', 'help'];
    private $threadTypeWeights = [40, 35, 15, 10];

    private $discussionTopics = [
        'Aqidah' => [
            'Batasan Sifat Istiwa',
            'Dalil-dalil Tauhid Uluhiyah',
            'Perbedaan Syirik Akbar dan Asghar',
            'Makna La Ilaha Illallah',
            'Rukun Iman dalam Kehidupan',
            'Konsep Tawakkal yang Benar',
            'Bid\'ah dan Batasan-batasannya',
            'Wala\' dan Bara\' dalam Konteks Modern',
        ],
        'Fiqh' => [
            'Hukum Dropshipping dalam Islam',
            'Perbedaan Murabahah dan MMQ',
            'Zakat Profesi: Pro dan Kontra',
            'Shalat Jamak Qashar untuk Mahasiswa Kos',
            'Hukum Asuransi Konvensional',
            'Jual Beli Online dan Gharar',
            'Wakaf Uang: Implementasi dan Tantangan',
            'Nikah Siri dalam Perspektif Fiqh',
        ],
        'Hadis' => [
            'Kriteria Hadis Shahih',
            'Perbedaan Hadis dan Atsar',
            'Metode Kritik Sanad',
            'Hadis Mutawatir vs Ahad',
            'Kedudukan Hadis Mursal',
            'Tadlis dalam Periwayatan',
            'Jarh wa Ta\'dil Praktis',
            'Memahami Hadis Maudhu\'',
        ],
        'Ekonomi' => [
            'Fintech Syariah: Peluang atau Tantangan?',
            'Blockchain untuk Industri Halal',
            'Akad dalam Transaksi Digital',
            'Saham Syariah vs Konvensional',
            'Sukuk sebagai Instrumen Investasi',
            'Crowdfunding dalam Perspektif Islam',
            'Cryptocurrency: Halal atau Haram?',
            'Ekonomi Circular dan Maqashid Syariah',
        ],
        'Teknologi' => [
            'AI Chatbot Fatwa: Etika dan Tantangan',
            'Blockchain untuk Sertifikasi Halal',
            'Smart Mosque: Inovasi Ibadah',
            'E-Learning dalam Pendidikan Islam',
            'Aplikasi Quran: Fitur yang Dibutuhkan',
            'Big Data untuk Analisis Zakat',
            'VR untuk Simulasi Haji',
            'Cybersecurity dalam Perspektif Islam',
        ],
        'Pendidikan' => [
            'Metode Pembelajaran Aktif di Pesantren',
            'Integrasi Kurikulum Umum dan Agama',
            'Evaluasi Pembelajaran Berbasis Kompetensi',
            'Pendidikan Karakter Islami',
            'Penggunaan Teknologi di Madrasah',
            'Peran Orang Tua dalam Pendidikan',
            'Kurikulum Tahfidz yang Efektif',
            'Motivasi Belajar Santri Modern',
        ],
    ];

    private $questionStarters = [
        'Bagaimana pendapat teman-teman tentang',
        'Apakah ada yang bisa menjelaskan',
        'Saya masih bingung tentang',
        'Mohon penjelasan lebih lanjut mengenai',
        'Apa dalil yang mendukung',
        'Bagaimana aplikasi praktis dari',
        'Siapa yang punya referensi tentang',
        'Tolong bantu jelaskan',
    ];

    private $responseTemplates = [
        'agreement' => [
            'Saya setuju dengan pendapat di atas.',
            'Benar sekali, jazakallah atas penjelasannya.',
            'Mashaa Allah, penjelasan yang sangat membantu.',
            'Alhamdulillah, sekarang saya lebih paham.',
        ],
        'addition' => [
            'Saya ingin menambahkan bahwa',
            'Selain itu, perlu juga diperhatikan',
            'Ada satu poin lagi yang menarik yaitu',
            'Untuk melengkapi, berikut referensi tambahan:',
        ],
        'question' => [
            'Bagaimana jika kasusnya berbeda?',
            'Apakah ada pengecualian dalam hal ini?',
            'Bagaimana pendapat ulama lain tentang ini?',
            'Bisakah diberikan contoh konkret?',
        ],
        'expert' => [
            'Berdasarkan kajian yang lebih mendalam,',
            'Menurut pendapat mayoritas ulama,',
            'Jika merujuk pada kitab klasik,',
            'Dalam konteks maqashid syariah,',
        ],
    ];

    public function run(): void
    {
        $courses = Course::where('status', 'Published')->get();
        $lecturers = User::where('role', 'dosen')->get();
        $students = User::whereIn('role', ['student', 'maba'])->get();
        
        if ($courses->isEmpty() || $students->isEmpty()) {
            return;
        }

        $threadCount = 0;
        $postCount = 0;

        foreach ($courses as $course) {
            // Get enrolled students for this course
            $enrolledStudents = CourseEnrollment::where('course_id', $course->id)
                ->whereIn('status', ['enrolled', 'completed'])
                ->pluck('student_id')
                ->toArray();
            
            $courseStudents = $students->whereIn('id', $enrolledStudents);
            if ($courseStudents->isEmpty()) {
                $courseStudents = $students->random(min(10, $students->count()));
            }

            // Each course has 5-15 discussion threads
            $numThreads = rand(5, 15);
            $topicCategory = $this->determineTopicCategory($course->name);
            $topics = $this->discussionTopics[$topicCategory] ?? $this->discussionTopics['Aqidah'];

            for ($i = 0; $i < $numThreads; $i++) {
                $topic = $topics[array_rand($topics)];
                $type = $this->weightedRandom($this->threadTypes, $this->threadTypeWeights);
                $creator = $courseStudents->random();
                
                $title = $this->generateTitle($topic, $type);
                $daysAgo = rand(1, 90);
                $createdAt = now()->subDays($daysAgo);

                $thread = DiscussionThread::create([
                    'id' => 'DT' . Str::random(8),
                    'course_id' => $course->id,
                    'title' => $title,
                    'created_by' => $creator->id,
                    'type' => $type,
                    'status' => rand(1, 100) <= 80 ? 'open' : 'closed',
                    'is_pinned' => rand(1, 100) <= 10,
                    'is_closed' => rand(1, 100) <= 15,
                    'is_locked' => rand(1, 100) <= 5,
                    'view_count' => rand(10, 200),
                    'reply_count' => 0,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                
                $threadCount++;

                // Create initial post (the thread content)
                $initialPost = DiscussionPost::create([
                    'thread_id' => $thread->id,
                    'user_id' => $creator->id,
                    'content' => $this->generateInitialPost($topic, $type),
                    'likes_count' => rand(0, 20),
                    'is_edited' => false,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $postCount++;

                // Create replies (3-15 per thread)
                $numReplies = rand(3, 15);
                $lastPostAt = $createdAt;
                $lastPostBy = $creator->id;

                for ($j = 0; $j < $numReplies; $j++) {
                    $replyDaysAfter = rand(0, min(30, $daysAgo));
                    $replyAt = $createdAt->copy()->addDays($replyDaysAfter)->addHours(rand(1, 23));
                    
                    // Mix of student and lecturer replies
                    $replier = rand(1, 100) <= 20 && $lecturers->isNotEmpty() 
                        ? $lecturers->random() 
                        : $courseStudents->random();
                    
                    $isExpert = $replier->role === 'dosen';
                    
                    $post = DiscussionPost::create([
                        'thread_id' => $thread->id,
                        'user_id' => $replier->id,
                        'content' => $this->generateReply($topic, $isExpert),
                        'likes_count' => rand(0, 15),
                        'is_edited' => rand(1, 100) <= 10,
                        'created_at' => $replyAt,
                        'updated_at' => $replyAt,
                    ]);
                    $postCount++;
                    
                    if ($replyAt > $lastPostAt) {
                        $lastPostAt = $replyAt;
                        $lastPostBy = $replier->id;
                    }
                }

                // Update thread with reply count and last post info
                $thread->update([
                    'reply_count' => $numReplies,
                    'last_post_by' => $lastPostBy,
                    'last_post_at' => $lastPostAt,
                ]);
            }
        }
        
        $this->command->info("Created {$threadCount} discussion threads with {$postCount} posts!");
    }

    private function determineTopicCategory(string $courseName): string
    {
        $courseName = strtolower($courseName);
        
        if (str_contains($courseName, 'aqidah') || str_contains($courseName, 'tauhid')) return 'Aqidah';
        if (str_contains($courseName, 'fiqh') || str_contains($courseName, 'fikih') || str_contains($courseName, 'muamalah')) return 'Fiqh';
        if (str_contains($courseName, 'hadis') || str_contains($courseName, 'hadits')) return 'Hadis';
        if (str_contains($courseName, 'ekonomi') || str_contains($courseName, 'bank') || str_contains($courseName, 'keuangan')) return 'Ekonomi';
        if (str_contains($courseName, 'teknologi') || str_contains($courseName, 'sistem') || str_contains($courseName, 'informasi')) return 'Teknologi';
        if (str_contains($courseName, 'pendidikan') || str_contains($courseName, 'tarbiyah') || str_contains($courseName, 'pembelajaran')) return 'Pendidikan';
        
        return array_keys($this->discussionTopics)[array_rand(array_keys($this->discussionTopics))];
    }

    private function generateTitle(string $topic, string $type): string
    {
        return match($type) {
            'question' => "Pertanyaan tentang {$topic}",
            'discussion' => "Diskusi: {$topic}",
            'announcement' => "Pengumuman: {$topic}",
            'help' => "Butuh Bantuan: {$topic}",
            default => $topic,
        };
    }

    private function generateInitialPost(string $topic, string $type): string
    {
        $starter = $this->questionStarters[array_rand($this->questionStarters)];
        
        return match($type) {
            'question' => "Assalamu'alaikum warahmatullahi wabarakatuh.\n\n{$starter} {$topic}? Saya sudah membaca materi yang diberikan, namun masih ada beberapa hal yang belum saya pahami.\n\nMohon penjelasan dari teman-teman atau ustadz/ustadzah. Jazakumullahu khairan.",
            'discussion' => "Assalamu'alaikum.\n\nSaya ingin membuka diskusi tentang {$topic}. Topik ini menurut saya sangat menarik dan relevan dengan kondisi saat ini.\n\nAyo kita diskusikan bersama. Silakan berbagi pandangan dan referensi yang relevan.",
            'announcement' => "Bismillah.\n\nDiberitahukan kepada seluruh mahasiswa terkait {$topic}.\n\nMohon perhatian dan tindak lanjut dari semua pihak. Wassalam.",
            'help' => "Assalamu'alaikum.\n\nSaya butuh bantuan tentang {$topic}. Apakah ada yang bisa membantu menjelaskan?\n\nJazakumullahu khairan.",
            default => "Mari kita bahas tentang {$topic} bersama-sama.",
        };
    }

    private function generateReply(string $topic, bool $isExpert = false): string
    {
        if ($isExpert) {
            $expertStarter = $this->responseTemplates['expert'][array_rand($this->responseTemplates['expert'])];
            return "Wa'alaikumussalam warahmatullahi wabarakatuh.\n\n{$expertStarter} {$topic} perlu dipahami dengan komprehensif. Dalil yang mendukung hal ini cukup jelas dalam nash.\n\nSilakan merujuk ke kitab-kitab mu'tabar untuk pendalaman lebih lanjut. Wallahu a'lam.";
        }

        $responseType = ['agreement', 'addition', 'question'][array_rand(['agreement', 'addition', 'question'])];
        $template = $this->responseTemplates[$responseType][array_rand($this->responseTemplates[$responseType])];
        
        return match($responseType) {
            'agreement' => "{$template}\n\nTerima kasih atas penjelasannya tentang {$topic}. Sangat membantu pemahaman saya.",
            'addition' => "{$template} dalam konteks {$topic}, kita juga perlu memperhatikan aspek lain yang tidak kalah penting.",
            'question' => "Jazakallah atas penjelasannya. {$template}\n\nKhususnya dalam hal {$topic}, apakah ada pandangan lain yang berbeda?",
            default => "Terima kasih. Penjelasan tentang {$topic} sangat bermanfaat.",
        };
    }

    private function weightedRandom(array $items, array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $current = 0;
        foreach ($items as $index => $item) {
            $current += $weights[$index];
            if ($random <= $current) {
                return $item;
            }
        }
        
        return $items[0];
    }
}
