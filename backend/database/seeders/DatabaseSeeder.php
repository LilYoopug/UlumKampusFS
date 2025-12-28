<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FacultyMajorSeeder::class,
            UserSeeder::class,
            PaymentSeeder::class,
            CourseSeeder::class,
            AssignmentSeeder::class,
            DiscussionSeeder::class,
            AnnouncementSeeder::class,
            NotificationSeeder::class,
            LibraryResourceSeeder::class,
            AcademicCalendarSeeder::class,
        ]);
    }
}
