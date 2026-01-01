<?php

namespace Database\Seeders;

use App\Models\StudentRegistration;
use App\Models\User;
use App\Models\Major;
use Illuminate\Database\Seeder;

class StudentRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get MABA users (new students) who haven't registered yet
        $mabaUsers = User::where('role', 'MABA')->orWhere('role', 'maba')->get();

        $registrations = [
            [
                'user_email' => 'budi.santoso@maba.ulumcampus.com',
                'nisn' => '0012345678',
                'nik' => '3201234567890001',
                'date_of_birth' => '2006-05-15',
                'place_of_birth' => 'Jakarta',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Jl. Sudirman No. 123, Jakarta Selatan',
                'city' => 'Jakarta Selatan',
                'postal_code' => '12000',
                'citizenship' => 'Indonesia',
                'parent_name' => 'Ahmad Santoso',
                'parent_phone' => '081234567890',
                'parent_job' => 'Wiraswasta',
                'school_name' => 'SMAN 1 Jakarta',
                'school_address' => 'Jl. Sudirman No. 45, Jakarta',
                'graduation_year_school' => 2024,
                'school_type' => 'SMA',
                'school_major' => 'IPA',
                'average_grade' => 85.50,
                'first_choice_id' => 'spi',
                'second_choice_id' => 'islamic-civ',
                'status' => 'submitted',
            ],
            [
                'user_email' => 'bibbhh@fff.cv',
                'nisn' => '0023456789',
                'nik' => '3201234567890002',
                'date_of_birth' => '2006-08-22',
                'place_of_birth' => 'Bandung',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Jl. Asia Afrika No. 56, Bandung',
                'city' => 'Bandung',
                'postal_code' => '40111',
                'citizenship' => 'Indonesia',
                'parent_name' => 'Rudi Hartono',
                'parent_phone' => '081234567891',
                'parent_job' => 'PNS',
                'school_name' => 'SMAN 3 Bandung',
                'school_address' => 'Jl. Belitung No. 8, Bandung',
                'graduation_year_school' => 2024,
                'school_type' => 'SMA',
                'school_major' => 'IPA',
                'average_grade' => 88.75,
                'first_choice_id' => 'ekonomi-islam',
                'second_choice_id' => null,
                'status' => 'under_review',
            ],
            [
                'user_email' => 'biji@jojo@jawa.com',
                'nisn' => '0034567890',
                'nik' => '3201234567890003',
                'date_of_birth' => '2006-11-10',
                'place_of_birth' => 'Surabaya',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Jl. Tunjungan No. 78, Surabaya',
                'city' => 'Surabaya',
                'postal_code' => '60111',
                'citizenship' => 'Indonesia',
                'parent_name' => 'Siti Aminah',
                'parent_phone' => '081234567892',
                'parent_job' => 'Guru',
                'school_name' => 'SMAN 5 Surabaya',
                'school_address' => 'Jl. Raya Darmo No. 12, Surabaya',
                'graduation_year_school' => 2024,
                'school_type' => 'SMA',
                'school_major' => 'IPS',
                'average_grade' => 82.00,
                'first_choice_id' => 'pai',
                'second_choice_id' => 'ekonomi-islam',
                'status' => 'accepted',
            ],
            [
                'user_email' => 'tytyd@hytam@cm.com',
                'nisn' => '0045678901',
                'nik' => '3201234567890004',
                'date_of_birth' => '2007-02-28',
                'place_of_birth' => 'Yogyakarta',
                'gender' => 'male',
                'religion' => 'Islam',
                'address' => 'Jl. Malioboro No. 90, Yogyakarta',
                'city' => 'Yogyakarta',
                'postal_code' => '55000',
                'citizenship' => 'Indonesia',
                'parent_name' => 'Hendra Wijaya',
                'parent_phone' => '081234567893',
                'parent_job' => 'Pedagang',
                'school_name' => 'SMAN 1 Yogyakarta',
                'school_address' => 'Jl. Cendrawasih No. 5, Yogyakarta',
                'graduation_year_school' => 2024,
                'school_type' => 'SMA',
                'school_major' => 'IPA',
                'average_grade' => 79.50,
                'first_choice_id' => 'aqidah',
                'second_choice_id' => 'psikologi-islam',
                'status' => 'rejected',
                'rejection_reason' => 'Nilai rata-rata di bawah syarat minimal (80.00)',
            ],
            [
                'user_email' => 'bool@ejjsj.com',
                'nisn' => '0056789012',
                'nik' => '3201234567890005',
                'date_of_birth' => '2006-07-18',
                'place_of_birth' => 'Semarang',
                'gender' => 'female',
                'religion' => 'Islam',
                'address' => 'Jl. Pemuda No. 112, Semarang',
                'city' => 'Semarang',
                'postal_code' => '50132',
                'citizenship' => 'Indonesia',
                'parent_name' => 'Bambang Sutrisno',
                'parent_phone' => '081234567894',
                'parent_job' => 'Wiraswasta',
                'school_name' => 'SMAN 2 Semarang',
                'school_address' => 'Jl. Pandanaran No. 15, Semarang',
                'graduation_year_school' => 2024,
                'school_type' => 'SMA',
                'school_major' => 'IPA',
                'average_grade' => 91.25,
                'first_choice_id' => 'sains-etika',
                'second_choice_id' => null,
                'status' => 'submitted',
            ],
            [
                // Additional dummy registration for a non-existent user
                'user_email' => 'dummy.student@test.com',
                'nisn' => '9998887776',
                'nik' => '3209998887770001',
                'date_of_birth' => '2006-04-12',
                'place_of_birth' => 'Medan',
                'gender' => 'male',
                'religion' => 'Kristen',
                'address' => 'Jl. Gatot Subroto No. 55, Medan',
                'city' => 'Medan',
                'postal_code' => '20111',
                'citizenship' => 'Indonesia',
                'parent_name' => 'Robert Siregar',
                'parent_phone' => '081234567895',
                'parent_job' => 'Petani',
                'school_name' => 'SMAN 1 Medan',
                'school_address' => 'Jl. Pangeran Diponegoro No. 22, Medan',
                'graduation_year_school' => 2024,
                'school_type' => 'SMA',
                'school_major' => 'IPA',
                'average_grade' => 87.00,
                'first_choice_id' => 'peradilan-agama',
                'second_choice_id' => 'ekonomi-islam',
                'status' => 'under_review',
            ],
        ];

        $adminUser = User::where('role', 'admin')->first();

        // Get available majors
        $availableMajors = Major::pluck('code')->toArray();
        $this->command->info('Available majors: ' . implode(', ', $availableMajors));

        foreach ($registrations as $regData) {
            // Find user by email
            $user = User::where('email', $regData['user_email'])->first();
            
            if (!$user) {
                // Create a dummy user if not found
                $user = User::create([
                    'name' => 'Student ' . substr($regData['nisn'], -4),
                    'email' => $regData['user_email'],
                    'password' => bcrypt('password123'),
                    'role' => 'maba',
                    'student_id' => 'MABA' . substr($regData['nisn'], -4),
                ]);
                $this->command->info("Created user: {$user->email}");
            }

            // Validate major codes exist
            $firstChoiceId = in_array($regData['first_choice_id'], $availableMajors) ? $regData['first_choice_id'] : null;
            $secondChoiceId = ($regData['second_choice_id'] && in_array($regData['second_choice_id'], $availableMajors)) ? $regData['second_choice_id'] : null;

            if (!$firstChoiceId) {
                $this->command->warn("Skipping registration for {$user->email} - major code {$regData['first_choice_id']} not found");
                continue;
            }

            // Create registration
            $registration = StudentRegistration::create([
                'user_id' => $user->id,
                'nisn' => $regData['nisn'],
                'nik' => $regData['nik'],
                'date_of_birth' => $regData['date_of_birth'],
                'place_of_birth' => $regData['place_of_birth'],
                'gender' => $regData['gender'],
                'religion' => $regData['religion'],
                'address' => $regData['address'],
                'city' => $regData['city'],
                'postal_code' => $regData['postal_code'],
                'citizenship' => $regData['citizenship'],
                'parent_name' => $regData['parent_name'],
                'parent_phone' => $regData['parent_phone'],
                'parent_job' => $regData['parent_job'],
                'school_name' => $regData['school_name'],
                'school_address' => $regData['school_address'],
                'graduation_year_school' => $regData['graduation_year_school'],
                'school_type' => $regData['school_type'],
                'school_major' => $regData['school_major'],
                'average_grade' => $regData['average_grade'],
                'first_choice_id' => $firstChoiceId,
                'second_choice_id' => $secondChoiceId,
                'status' => $regData['status'],
                'submitted_at' => now()->subDays(rand(1, 30)),
                'documents' => [
                    'ijazah.pdf',
                    'transkrip_nilai.pdf',
                    'kk.pdf',
                    'foto_3x4.jpg',
                ],
                'rejection_reason' => $regData['rejection_reason'] ?? null,
                'reviewed_by' => in_array($regData['status'], ['accepted', 'rejected']) ? ($adminUser?->id ?? null) : null,
                'reviewed_at' => in_array($regData['status'], ['accepted', 'rejected']) ? now()->subDays(rand(1, 15)) : null,
            ]);

            $this->command->info("Created registration for {$user->name} ({$regData['status']})");
        }

        $this->command->info('Student registrations seeded successfully!');
    }
}
