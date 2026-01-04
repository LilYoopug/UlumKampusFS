<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\AssignmentSubmission;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;

class GradeSeeder extends Seeder
{
    private $gradeComments = [
        'A+' => [
            'Luar biasa! Kerja yang sangat memuaskan dengan pemahaman mendalam.',
            'Exceptional work! Menunjukkan penguasaan materi yang sempurna.',
            'Mashaa Allah, hasil yang sangat membanggakan. Pertahankan!',
            'Outstanding performance. Contoh teladan bagi mahasiswa lain.',
        ],
        'A' => [
            'Sangat baik! Pemahaman konsep yang kuat dan aplikasi yang tepat.',
            'Excellent! Menunjukkan kemampuan analisis yang baik.',
            'Kerja yang sangat memuaskan. Terus tingkatkan!',
            'Barakallahu fiik! Hasil yang sangat baik.',
        ],
        'A-' => [
            'Bagus sekali! Sedikit perbaikan dan bisa lebih sempurna.',
            'Hasil yang sangat baik dengan beberapa catatan kecil.',
            'Pemahaman yang baik, terus asah kemampuan analisis.',
        ],
        'B+' => [
            'Baik! Pemahaman cukup solid, perlu pendalaman di beberapa area.',
            'Kerja yang baik. Perhatikan detail untuk hasil lebih optimal.',
            'Sudah di jalur yang benar, tingkatkan konsistensi.',
        ],
        'B' => [
            'Cukup baik. Perlu lebih banyak latihan dan pendalaman materi.',
            'Pemahaman dasar sudah ada, perlu dikembangkan lebih lanjut.',
            'Hasil memadai, namun masih banyak ruang untuk perbaikan.',
        ],
        'B-' => [
            'Cukup. Perlu usaha lebih untuk memahami konsep secara mendalam.',
            'Hasil bisa lebih baik dengan persiapan yang lebih matang.',
        ],
        'C+' => [
            'Perlu perbaikan. Banyak konsep yang belum dipahami dengan baik.',
            'Hasil di bawah harapan. Konsultasikan kesulitan yang dihadapi.',
        ],
        'C' => [
            'Minimum passing. Perlu kerja keras untuk meningkatkan pemahaman.',
            'Batas lulus. Sangat disarankan untuk mengikuti remedial.',
        ],
        'D' => [
            'Tidak memenuhi standar. Perlu mengulang atau remedial.',
            'Pemahaman sangat kurang. Harap konsultasi dengan dosen.',
        ],
        'E' => [
            'Tidak lulus. Wajib mengulang mata kuliah ini.',
            'Gagal memenuhi kompetensi minimum. Perlu persiapan lebih baik.',
        ],
    ];

    public function run(): void
    {
        // Get graded submissions and create grades based on them
        $gradedSubmissions = AssignmentSubmission::where('status', 'graded')
            ->whereNotNull('grade')
            ->with(['assignment', 'student'])
            ->get();
        
        $gradeCount = 0;

        foreach ($gradedSubmissions as $submission) {
            if (!$submission->assignment || !$submission->student) {
                continue;
            }

            $gradeValue = $submission->grade;
            $gradeLetter = $this->getGradeLetter($gradeValue);
            
            Grade::updateOrCreate(
                [
                    'assignment_id' => $submission->assignment_id,
                    'user_id' => $submission->student_id,
                ],
                [
                    'course_id' => $submission->assignment->course_id,
                    'grade' => $gradeValue,
                    'grade_letter' => $gradeLetter,
                    'comments' => $this->getRandomComment($gradeLetter),
                    'graded_at' => $submission->submitted_at?->addDays(rand(1, 7)),
                    'graded_by' => $submission->assignment->created_by,
                ]
            );
            
            $gradeCount++;
        }

        // Also create course final grades for completed enrollments
        $completedEnrollments = CourseEnrollment::where('status', 'completed')
            ->with(['course', 'student'])
            ->get();

        foreach ($completedEnrollments as $enrollment) {
            if (!$enrollment->course || !$enrollment->student) {
                continue;
            }

            // Calculate average from assignment grades for this course
            $avgGrade = Grade::where('course_id', $enrollment->course_id)
                ->where('user_id', $enrollment->student_id)
                ->avg('grade');
            
            if (!$avgGrade) {
                $avgGrade = rand(60, 100); // Random if no assignment grades
            }

            $gradeLetter = $this->getGradeLetter($avgGrade);

            // Create final course grade (no assignment_id means it's a final grade)
            Grade::updateOrCreate(
                [
                    'course_id' => $enrollment->course_id,
                    'user_id' => $enrollment->student_id,
                    'assignment_id' => null,
                ],
                [
                    'grade' => round($avgGrade, 2),
                    'grade_letter' => $gradeLetter,
                    'comments' => "Nilai akhir mata kuliah. {$this->getRandomComment($gradeLetter)}",
                    'graded_at' => $enrollment->completed_at ?? now()->subDays(rand(1, 30)),
                ]
            );
            
            $gradeCount++;
        }
        
        $this->command->info("Created {$gradeCount} grades!");
    }

    private function getGradeLetter(float $grade): string
    {
        return match(true) {
            $grade >= 95 => 'A+',
            $grade >= 90 => 'A',
            $grade >= 85 => 'A-',
            $grade >= 80 => 'B+',
            $grade >= 75 => 'B',
            $grade >= 70 => 'B-',
            $grade >= 65 => 'C+',
            $grade >= 60 => 'C',
            $grade >= 50 => 'D',
            default => 'E',
        };
    }

    private function getRandomComment(string $gradeLetter): string
    {
        $comments = $this->gradeComments[$gradeLetter] ?? ['Silakan konsultasi dengan dosen.'];
        return $comments[array_rand($comments)];
    }
}
