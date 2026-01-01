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
            StudentRegistrationSeeder::class,
            PaymentMethodSeeder::class,
            PaymentItemSeeder::class,
            PaymentStatusSeeder::class,
            PaymentHistorySeeder::class,
            ManagementAdministrationSeeder::class,
            CourseSeeder::class,
            CourseModuleSeeder::class,
            AssignmentSeeder::class,
            AssignmentSubmissionSeeder::class,
            CourseEnrollmentSeeder::class,
            GradeSeeder::class,
            DiscussionSeeder::class,
            AnnouncementSeeder::class,
            NotificationSeeder::class,
            LibraryResourceSeeder::class,
            AcademicCalendarSeeder::class,
        ]);
    }
}
