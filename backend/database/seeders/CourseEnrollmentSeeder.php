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
        // Get all students and courses
        $students = User::whereIn('role', ['student', 'maba'])->get();
        $courses = Course::where('status', 'Published')->get();
        
        if ($students->isEmpty() || $courses->isEmpty()) {
            return;
        }

        $statuses = ['enrolled', 'completed', 'dropped', 'pending'];
        $statusWeights = [60, 25, 10, 5]; // 60% enrolled, 25% completed, 10% dropped, 5% pending
        
        $enrollmentCount = 0;
        
        // Each student enrolls in 3-8 courses
        foreach ($students as $student) {
            $numCourses = rand(3, 8);
            $selectedCourses = $courses->random(min($numCourses, $courses->count()));
            
            foreach ($selectedCourses as $course) {
                $status = $this->weightedRandom($statuses, $statusWeights);
                $enrollmentDate = now()->subDays(rand(1, 365));
                
                $progressPercentage = match($status) {
                    'completed' => 100,
                    'dropped' => rand(5, 40),
                    'pending' => 0,
                    default => rand(10, 95),
                };
                
                $totalModules = rand(3, 12);
                $completedModules = (int)(($progressPercentage / 100) * $totalModules);
                
                $completedAt = null;
                if ($status === 'completed') {
                    $completedAt = $enrollmentDate->copy()->addDays(rand(30, 120));
                }
                
                CourseEnrollment::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'enrollment_date' => $enrollmentDate,
                        'enrolled_at' => $enrollmentDate,
                        'status' => $status,
                        'progress_percentage' => $progressPercentage,
                        'completed_modules' => $completedModules,
                        'total_modules' => $totalModules,
                        'completed_at' => $completedAt,
                    ]
                );
                
                $enrollmentCount++;
            }
        }
        
        // Update current_enrollment count for each course
        $this->updateCourseEnrollmentCounts();
        
        $this->command->info("Created {$enrollmentCount} course enrollments!");
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
    
    private function updateCourseEnrollmentCounts()
    {
        $courses = Course::all();
        foreach ($courses as $course) {
            $count = CourseEnrollment::where('course_id', $course->id)->count();
            $course->update(['current_enrollment' => $count]);
        }
    }
}
