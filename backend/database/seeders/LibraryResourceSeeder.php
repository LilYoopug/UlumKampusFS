<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LibraryResource;
use App\Models\User;
use Illuminate\Support\Str;

class LibraryResourceSeeder extends Seeder
{
    private $bookCategories = [
        'Aqidah' => [
            ['title' => 'Kitab At-Tauhid', 'author' => 'Syaikh Muhammad bin Abdul Wahhab'],
            ['title' => 'Al-Ushul Ats-Tsalatsah', 'author' => 'Syaikh Muhammad bin Abdul Wahhab'],
            ['title' => 'Syarh Aqidah Wasithiyah', 'author' => 'Syaikhul Islam Ibn Taimiyah'],
            ['title' => 'Aqidah Tahawiyah', 'author' => 'Imam At-Thahawi'],
            ['title' => 'Lumatul I\'tiqad', 'author' => 'Imam Ibn Qudamah'],
            ['title' => 'Syarh Asma\' wa Sifat', 'author' => 'Imam Al-Baihaqi'],
        ],
        'Fiqh' => [
            ['title' => 'Al-Fiqh Al-Islami wa Adillatuhu', 'author' => 'Prof. Dr. Wahbah Az-Zuhaili'],
            ['title' => 'Bidayatul Mujtahid', 'author' => 'Ibn Rusyd'],
            ['title' => 'Al-Mughni', 'author' => 'Imam Ibn Qudamah'],
            ['title' => 'Fiqh Sunnah', 'author' => 'Sayyid Sabiq'],
            ['title' => 'Al-Umm', 'author' => 'Imam Asy-Syafi\'i'],
            ['title' => 'Bulughul Maram', 'author' => 'Al-Hafidz Ibn Hajar Al-Asqalani'],
            ['title' => 'Subulus Salam', 'author' => 'Imam Ash-Shan\'ani'],
            ['title' => 'Nail Al-Authar', 'author' => 'Imam Asy-Syaukani'],
        ],
        'Hadis' => [
            ['title' => 'Shahih Al-Bukhari', 'author' => 'Imam Al-Bukhari'],
            ['title' => 'Shahih Muslim', 'author' => 'Imam Muslim'],
            ['title' => 'Sunan Abu Dawud', 'author' => 'Imam Abu Dawud'],
            ['title' => 'Sunan At-Tirmidzi', 'author' => 'Imam At-Tirmidzi'],
            ['title' => 'Sunan An-Nasa\'i', 'author' => 'Imam An-Nasa\'i'],
            ['title' => 'Sunan Ibn Majah', 'author' => 'Imam Ibn Majah'],
            ['title' => 'Riyadhus Shalihin', 'author' => 'Imam An-Nawawi'],
            ['title' => 'Al-Arba\'in An-Nawawiyah', 'author' => 'Imam An-Nawawi'],
            ['title' => 'Fathul Bari', 'author' => 'Al-Hafidz Ibn Hajar Al-Asqalani'],
        ],
        'Tafsir' => [
            ['title' => 'Tafsir Al-Mishbah', 'author' => 'Prof. Dr. M. Quraish Shihab'],
            ['title' => 'Tafsir Ibn Katsir', 'author' => 'Imam Ibn Katsir'],
            ['title' => 'Tafsir Al-Qurthubi', 'author' => 'Imam Al-Qurthubi'],
            ['title' => 'Tafsir Ath-Thabari', 'author' => 'Imam Ath-Thabari'],
            ['title' => 'Tafsir As-Sa\'di', 'author' => 'Syaikh Abdurrahman As-Sa\'di'],
            ['title' => 'Fi Dzilalil Quran', 'author' => 'Sayyid Quthb'],
            ['title' => 'Zubdatut Tafsir', 'author' => 'Dr. Muhammad Sulaiman Al-Asyqar'],
        ],
        'Sirah' => [
            ['title' => 'Ar-Rahiq Al-Makhtum', 'author' => 'Syaikh Shafiyyurrahman Al-Mubarakfuri'],
            ['title' => 'Sirah Nabawiyah', 'author' => 'Ibn Hisyam'],
            ['title' => 'Fiqh As-Sirah', 'author' => 'Dr. Muhammad Said Ramadhan Al-Buthi'],
            ['title' => 'Hayatus Shahabah', 'author' => 'Syaikh Muhammad Yusuf Al-Kandahlawi'],
            ['title' => 'Al-Bidayah wan Nihayah', 'author' => 'Imam Ibn Katsir'],
        ],
        'Ekonomi Islam' => [
            ['title' => 'Fiqh Al-Muamalat Al-Maliyah', 'author' => 'Prof. Dr. Wahbah Az-Zuhaili'],
            ['title' => 'Islamic Finance: Law, Economics, and Practice', 'author' => 'Mahmoud A. El-Gamal'],
            ['title' => 'An Introduction to Islamic Finance', 'author' => 'Mufti Muhammad Taqi Usmani'],
            ['title' => 'Ekonomi Islam', 'author' => 'Dr. Muhammad Syafi\'i Antonio'],
            ['title' => 'Bank Syariah: Dari Teori ke Praktik', 'author' => 'Dr. Muhammad Syafi\'i Antonio'],
            ['title' => 'Riba dan Bunga Bank dalam Islam', 'author' => 'Dr. Abdullah Saeed'],
        ],
        'Akhlak & Tasawuf' => [
            ['title' => 'Ihya Ulumuddin', 'author' => 'Imam Al-Ghazali'],
            ['title' => 'Minhajul Qashidin', 'author' => 'Imam Ibn Qudamah'],
            ['title' => 'Madarij As-Salikin', 'author' => 'Imam Ibn Qayyim Al-Jauziyah'],
            ['title' => 'Adabul Mufrad', 'author' => 'Imam Al-Bukhari'],
            ['title' => 'Akhlak Muslim', 'author' => 'Syaikh Abu Bakar Jabir Al-Jaza\'iri'],
        ],
        'Ushul Fiqh' => [
            ['title' => 'Ushul Al-Fiqh Al-Islami', 'author' => 'Prof. Dr. Wahbah Az-Zuhaili'],
            ['title' => 'Al-Muwafaqat', 'author' => 'Imam Asy-Syathibi'],
            ['title' => 'Ilmu Ushul Fiqh', 'author' => 'Prof. Dr. Abdul Wahhab Khallaf'],
            ['title' => 'Al-Waraqat', 'author' => 'Imam Al-Juwaini'],
        ],
        'Pendidikan Islam' => [
            ['title' => 'Tarbiyatul Aulad fil Islam', 'author' => 'Abdullah Nashih Ulwan'],
            ['title' => 'Filsafat Pendidikan Islam', 'author' => 'Prof. Dr. Abuddin Nata'],
            ['title' => 'Ilmu Pendidikan dalam Perspektif Islam', 'author' => 'Prof. Dr. Ahmad Tafsir'],
            ['title' => 'Manhaj Pendidikan Anak Muslim', 'author' => 'Muhammad Nur Abdul Hafidz'],
        ],
    ];

    private $journalTopics = [
        'Journal of Islamic Economic Studies',
        'Islamic Economic Studies Review',
        'Journal of Islamic Finance',
        'International Journal of Islamic and Middle Eastern Finance',
        'Journal of King Abdulaziz University: Islamic Economics',
        'ISRA International Journal of Islamic Finance',
        'Journal of Islamic Banking and Finance',
        'Intellectual Discourse Journal',
        'Al-Shajarah: Journal of Islamic Thought',
        'Journal of Islamic Monetary Economics and Finance',
        'Jurnal Ekonomi Islam Indonesia',
        'Al-Iqtishad: Journal of Islamic Economics',
        'Jurnal Ilmu Syariah',
        'Jurnal Studi Al-Quran',
        'Jurnal Pendidikan Islam',
        'Jurnal Kajian Islam Kontemporer',
    ];

    private $articleTitles = [
        'Maqasid al-Shariah dan Inovasi Keuangan Islam',
        'Peran Wakaf dalam Pembangunan Ekonomi',
        'Implementasi Zakat Produktif di Era Modern',
        'Fintech Syariah: Peluang dan Tantangan',
        'Akad Murabahah dalam Praktik Perbankan',
        'Sukuk sebagai Instrumen Pembiayaan',
        'Takaful vs Asuransi Konvensional',
        'Etika Bisnis dalam Islam',
        'Blockchain dan Keuangan Syariah',
        'Artificial Intelligence dalam Fatwa Digital',
        'Halal Supply Chain Management',
        'Islamic Social Finance Framework',
        'Waqf-based Microfinance Model',
        'Green Sukuk untuk Pembangunan Berkelanjutan',
        'Digitalisasi Layanan Keuangan Syariah',
    ];

    public function run(): void
    {
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        
        if ($admins->isEmpty()) {
            return;
        }

        $resourceCount = 0;

        // Create books
        foreach ($this->bookCategories as $category => $books) {
            foreach ($books as $book) {
                $yearRange = match($category) {
                    'Hadis', 'Tafsir', 'Sirah' => rand(1970, 2020),
                    'Ekonomi Islam' => rand(2000, 2024),
                    default => rand(1980, 2023),
                };

                LibraryResource::create([
                    'id' => 'lib' . str_pad($resourceCount + 1, 3, '0', STR_PAD_LEFT),
                    'title' => $book['title'],
                    'author' => $book['author'],
                    'publication_year' => $yearRange,
                    'type' => 'book',
                    'tags' => $category,
                    'description' => "Kitab klasik dalam bidang {$category}. {$book['title']} karya {$book['author']} merupakan referensi penting bagi para pengkaji ilmu keislaman.",
                    'cover_url' => "https://picsum.photos/seed/{$resourceCount}/300/400",
                    'source_type' => rand(1, 100) <= 70 ? 'file' : 'link',
                    'source_url' => '#',
                    'is_published' => true,
                    'published_at' => now()->subDays(rand(1, 365)),
                    'created_by' => $admins->random()->id,
                    'download_count' => rand(10, 500),
                    'view_count' => rand(50, 2000),
                ]);
                
                $resourceCount++;
            }
        }

        // Create journals
        foreach ($this->journalTopics as $journal) {
            for ($vol = 1; $vol <= rand(2, 5); $vol++) {
                LibraryResource::create([
                    'id' => 'lib' . str_pad($resourceCount + 1, 3, '0', STR_PAD_LEFT),
                    'title' => "{$journal} - Volume {$vol}",
                    'author' => 'Various Authors',
                    'publication_year' => 2020 + $vol,
                    'type' => 'journal',
                    'tags' => 'Ekonomi Islam',
                    'description' => "Jurnal ilmiah yang memuat penelitian terbaru dalam bidang ekonomi dan keuangan Islam. Volume {$vol} mencakup berbagai topik kontemporer.",
                    'cover_url' => "https://picsum.photos/seed/journal{$resourceCount}/300/400",
                    'source_type' => 'link',
                    'source_url' => '#',
                    'is_published' => true,
                    'published_at' => now()->subDays(rand(1, 180)),
                    'created_by' => $admins->random()->id,
                    'download_count' => rand(5, 200),
                    'view_count' => rand(20, 800),
                ]);
                
                $resourceCount++;
            }
        }

        // Create articles
        foreach ($this->articleTitles as $title) {
            LibraryResource::create([
                'id' => 'lib' . str_pad($resourceCount + 1, 3, '0', STR_PAD_LEFT),
                'title' => $title,
                'author' => $this->generateAuthorName(),
                'publication_year' => rand(2020, 2025),
                'type' => 'article',
                'tags' => 'Ekonomi Islam',
                'description' => "Artikel ilmiah yang membahas {$title}. Memberikan analisis mendalam dan perspektif kontemporer.",
                'cover_url' => "https://picsum.photos/seed/article{$resourceCount}/300/400",
                'source_type' => 'link',
                'source_url' => '#',
                'is_published' => true,
                'published_at' => now()->subDays(rand(1, 90)),
                'created_by' => $admins->random()->id,
                'download_count' => rand(2, 100),
                'view_count' => rand(10, 400),
            ]);
            
            $resourceCount++;
        }

        // Create video lectures
        $videoTopics = [
            'Pengantar Aqidah Islam',
            'Dasar-dasar Fiqh Ibadah',
            'Metodologi Studi Hadis',
            'Sejarah Peradaban Islam',
            'Ekonomi Syariah untuk Pemula',
            'Tafsir Juz Amma',
            'Sirah Nabawiyah Series',
            'Bahasa Arab untuk Pemula',
            'Ulumul Quran',
            'Kajian Kitab Kuning',
        ];

        foreach ($videoTopics as $topic) {
            for ($ep = 1; $ep <= rand(5, 15); $ep++) {
                LibraryResource::create([
                    'id' => 'lib' . str_pad($resourceCount + 1, 3, '0', STR_PAD_LEFT),
                    'title' => "{$topic} - Episode {$ep}",
                    'author' => $this->generateLecturerName(),
                    'publication_year' => rand(2022, 2025),
                    'type' => 'video',
                    'tags' => 'Video Pembelajaran',
                    'description' => "Video pembelajaran episode {$ep} dari seri {$topic}. Durasi sekitar 45-60 menit.",
                    'cover_url' => "https://picsum.photos/seed/video{$resourceCount}/300/400",
                    'source_type' => 'link',
                    'source_url' => '#',
                    'is_published' => rand(1, 100) <= 90,
                    'published_at' => now()->subDays(rand(1, 200)),
                    'created_by' => $admins->random()->id,
                    'download_count' => 0,
                    'view_count' => rand(100, 5000),
                ]);
                
                $resourceCount++;
            }
        }
        
        $this->command->info("Created {$resourceCount} library resources!");
    }

    private function generateAuthorName(): string
    {
        $firstNames = ['Dr.', 'Prof. Dr.', 'Ustadz', 'Ustadzah', 'H.', 'Hj.'];
        $names = ['Ahmad', 'Muhammad', 'Abdullah', 'Abdul Rahman', 'Fatimah', 'Aisyah', 'Ibrahim', 'Ismail', 'Yusuf', 'Maryam'];
        $lastNames = ['Al-Farisi', 'Al-Qurthubi', 'Hidayat', 'Ramadhan', 'Hakim', 'Nugroho', 'Wijaya', 'Rahman'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . 
               $names[array_rand($names)] . ' ' . 
               $lastNames[array_rand($lastNames)];
    }

    private function generateLecturerName(): string
    {
        $titles = ['Ustadz', 'Ustadzah', 'Dr.', 'Prof.'];
        $names = ['Ahmad Zainuddin', 'Aisyah Fathimah', 'Abdul Malik', 'Nur Hidayah', 'Hamzah Al-Farisi', 'Khadijah Sari'];
        
        return $titles[array_rand($titles)] . ' ' . $names[array_rand($names)];
    }
}
