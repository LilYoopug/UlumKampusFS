<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LibraryResource;
use App\Models\User;

class LibraryResourceSeeder extends Seeder
{
    public function run(): void
    {
        // Create library resources based on frontend constants
        $libraryResources = [
            [
                'id' => 'lib001',
                'title' => 'Fiqh Al-Muamalat Al-Maliyah Al-Muashirah',
                'author' => 'Prof. Dr. Wahbah Az-Zuhaili',
                'publication_year' => 2002,
                'type' => 'book',
                'description' => 'Buku komprehensif yang membahas transaksi keuangan kontemporer dari perspektif fiqh, mencakup perbankan, asuransi, dan pasar modal.',
                'cover_url' => 'https://picsum.photos/seed/fiqh-book/300/400',
                'source_type' => 'link',
                'source_url' => '#',
                'is_published' => true,
                'published_at' => now(),
                'created_by' => $this->getUserIdByEmail('admin@ulumcampus.com'),
            ],
            [
                'id' => 'lib002',
                'title' => 'Tafsir Al-Mishbah',
                'author' => 'Prof. Dr. M. Quraish Shihab',
                'publication_year' => 2002,
                'type' => 'book',
                'description' => 'Tafsir Al-Qur\'an lengkap 30 juz dengan pendekatan tematik dan kontekstual yang relevan dengan kondisi masyarakat Indonesia.',
                'cover_url' => 'https://picsum.photos/seed/tafsir-book/300/400',
                'source_type' => 'link',
                'source_url' => '#',
                'is_published' => true,
                'published_at' => now(),
                'created_by' => $this->getUserIdByEmail('admin@ulumcampus.com'),
            ],
            [
                'id' => 'lib003',
                'title' => 'Ar-Rahiq Al-Makhtum',
                'author' => 'Syaikh Shafiyyurrahman Al-Mubarakfuri',
                'publication_year' => 1976,
                'type' => 'book',
                'description' => 'Karya sirah Nabawiyah (biografi Nabi Muhammad ﷺ) yang memenangkan penghargaan internasional, disajikan secara kronologis dan detail.',
                'cover_url' => 'https://picsum.photos/seed/sirah-book/300/400',
                'source_type' => 'link',
                'source_url' => '#',
                'is_published' => true,
                'published_at' => now(),
                'created_by' => $this->getUserIdByEmail('admin@ulumcampus.com'),
            ],
            [
                'id' => 'lib004',
                'title' => 'Journal of Islamic Economic Studies',
                'author' => 'Islamic Research and Training Institute (IRTI)',
                'publication_year' => 2023,
                'type' => 'journal',
                'description' => 'Jurnal ilmiah yang memuat penelitian terbaru tentang ekonomi, keuangan, dan perbankan Islam dari para akademisi di seluruh dunia.',
                'cover_url' => 'https://picsum.photos/seed/journal-eco/300/400',
                'source_type' => 'link',
                'source_url' => '#',
                'is_published' => true,
                'published_at' => now(),
                'created_by' => $this->getUserIdByEmail('admin@ulumcampus.com'),
            ],
            [
                'id' => 'lib005',
                'title' => 'Minhaj Al-Muslim',
                'author' => 'Syaikh Abu Bakar Jabir Al-Jaza\'iri',
                'publication_year' => 1964,
                'type' => 'book',
                'description' => 'Panduan lengkap bagi setiap Muslim yang mencakup aqidah, ibadah, adab, akhlak, dan muamalat berdasarkan Al-Qur\'an dan Sunnah.',
                'cover_url' => 'https://picsum.photos/seed/minhaj-book/300/400',
                'source_type' => 'link',
                'source_url' => '#',
                'is_published' => true,
                'published_at' => now(),
                'created_by' => $this->getUserIdByEmail('admin@ulumcampus.com'),
            ],
            [
                'id' => 'lib006',
                'title' => 'The Role of Maqasid al-Sharia in Islamic Finance',
                'author' => 'Dr. Asyraf Wajdi Dusuki',
                'publication_year' => 2011,
                'type' => 'journal',
                'description' => 'Artikel jurnal yang menganalisis pentingnya Maqashid Syariah (tujuan-tujuan syariat) sebagai landasan filosofis dalam pengembangan produk keuangan syariah.',
                'cover_url' => 'https://picsum.photos/seed/journal-maqasid/300/400',
                'source_type' => 'link',
                'source_url' => '#',
                'is_published' => true,
                'published_at' => now(),
                'created_by' => $this->getUserIdByEmail('admin@ulumcampus.com'),
            ]
        ];

        foreach ($libraryResources as $resourceData) {
            if ($resourceData['created_by']) {
                LibraryResource::updateOrCreate(
                    ['id' => $resourceData['id']],
                    $resourceData
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
