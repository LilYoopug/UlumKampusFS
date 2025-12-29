<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;

class CourseEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        // Create course enrollments based on frontend constants
        $enrollments = [
            // Ahmad Faris - enrolled in multiple courses
            [
                'course_id' => $this->getCourseIdByCode('AQ101'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'enrollment_date' => now()->subDays(30),
                'status' => 'enrolled',
                'progress_percentage' => 67,
                'completed_modules' => 2,
                'total_modules' => 3,
            ],
            [
                'course_id' => $this->getCourseIdByCode('FQ201'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'enrollment_date' => now()->subDays(25),
                'status' => 'enrolled',
                'progress_percentage' => 33,
                'completed_modules' => 1,
                'total_modules' => 3,
            ],
            [
                'course_id' => $this->getCourseIdByCode('AD501'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'enrollment_date' => now()->subDays(20),
                'status' => 'enrolled',
                'progress_percentage' => 100,
                'completed_modules' => 3,
                'total_modules' => 3,
            ],
            [
                'course_id' => $this->getCourseIdByCode('HD202'),
                'student_id' => $this->getUserIdByEmail('ahmad.faris@student.ulumcampus.com'),
                'enrollment_date' => now()->subDays(15),
                'status' => 'enrolled',
                'progress_percentage' => 67,
                'completed_modules' => 2,
                'total_modules' => 3,
            ],
            
            // Siti Maryam - enrolled in courses
            [
                'course_id' => $this->getCourseIdByCode('AQ101'),
                'student_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'enrollment_date' => now()->subDays(30),
                'status' => 'completed',
                'progress_percentage' => 100,
                'completed_modules' => 3,
                'total_modules' => 3,
            ],
            [
                'course_id' => $this->getCourseIdByCode('TR401'),
                'student_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'enrollment_date' => now()->subDays(20),
                'status' => 'enrolled',
                'progress_percentage' => 50,
                'completed_modules' => 1,
                'total_modules' => 2,
            ],
            [
                'course_id' => $this->getCourseIdByCode('SN701'),
                'student_id' => $this->getUserIdByEmail('siti.m@student.ulumcampus.com'),
                'enrollment_date' => now()->subDays(10),
                'status' => 'enrolled',
                'progress_percentage' => 67,
                'completed_modules' => 2,
                'total_modules' => 3,
            ],
            
            // Abdullah - enrolled in courses
            [
                'course_id' => $this->getCourseIdByCode('FQ201'),
                'student_id' => $this->getUserIdByEmail('abdullah@student.ulumcampus.com'),
                'enrollment_date' => now()->subDays(25),
                'status' => 'enrolled',
                'progress_percentage' => 33,
                'completed_modules' => 1,
                'total_modules' => 3,
            ],
            [
                'course_id' => $this->getCourseIdByCode('EK305'),
                'student_id' => $this->getUserIdByEmail('abdullah@student.ulumcampus.com'),
                'enrollment_date' => now()->subDays(15),
                'status' => 'enrolled',
                'progress_percentage' => 33,
                'completed_modules' => 1,
                'total_modules' => 3,
            ],
        ];

        foreach ($enrollments as $enrollmentData) {
            if ($enrollmentData['course_id'] && $enrollmentData['student_id']) {
                CourseEnrollment::updateOrCreate(
                    [
                        'course_id' => $enrollmentData['course_id'],
                        'student_id' => $enrollmentData['student_id']
                    ],
                    $enrollmentData
                );
            }
        }
        
        // Update current_enrollment count for each course
        $this->updateCourseEnrollmentCounts();
    }
    
    private function updateCourseEnrollmentCounts()
    {
        $courses = Course::all();
        foreach ($courses as $course) {
            $count = CourseEnrollment::where('course_id', $course->id)->count();
            $course->update(['current_enrollment' => $count]);
        }
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
