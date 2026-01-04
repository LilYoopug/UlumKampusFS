<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CourseEnrollment;
use App\Models\User;

class AssignmentSubmissionSeeder extends Seeder
{
    private $feedbackTemplates = [
        'excellent' => [
            'Kerja yang sangat luar biasa! Analisis Anda mendalam dan argumen sangat kuat.',
            'Mashaa Allah, pekerjaan yang sangat baik! Referensi dalil yang digunakan sangat tepat.',
            'Luar biasa! Struktur tulisan rapi dan pemahaman konsep sangat komprehensif.',
            'Excellent work! Presentasi visual menarik dan penjelasan sangat jelas.',
            'Barakallahu fiik! Hafalan sangat lancar dengan tajwid yang baik.',
        ],
        'good' => [
            'Pekerjaan yang baik. Beberapa poin sudah tepat, namun masih bisa ditingkatkan.',
            'Bagus, namun analisis bisa lebih mendalam. Perhatikan referensi yang digunakan.',
            'Kerja yang cukup baik. Pertahankan dan tingkatkan di tugas berikutnya.',
            'Konsep sudah dipahami dengan baik, namun aplikasinya perlu diperdalam.',
            'Hafalan cukup lancar, namun perlu memperbaiki beberapa makhraj huruf.',
        ],
        'average' => [
            'Pekerjaan cukup memadai. Perlu lebih banyak latihan dan pendalaman materi.',
            'Jawaban sudah sesuai namun kurang lengkap. Tambahkan lebih banyak referensi.',
            'Tugas diterima. Mohon perhatikan format dan struktur penulisan.',
            'Cukup baik, namun argumen perlu lebih kuat dengan dalil yang jelas.',
            'Hafalan perlu diulang-ulang lagi untuk memperkuat kelancaran.',
        ],
        'needs_improvement' => [
            'Perlu perbaikan signifikan. Silakan konsultasi untuk penjelasan lebih lanjut.',
            'Tugas belum memenuhi standar. Mohon direvisi sesuai instruksi.',
            'Banyak bagian yang perlu diperbaiki. Perhatikan feedback yang diberikan.',
            'Pemahaman konsep masih kurang. Pelajari ulang materi yang diberikan.',
            'Hafalan belum lancar. Harap latihan lebih intensif dan setorkan ulang.',
        ],
    ];

    public function run(): void
    {
        $assignments = Assignment::where('is_published', true)->get();
        
        if ($assignments->isEmpty()) {
            return;
        }

        $submissionCount = 0;

        foreach ($assignments as $assignment) {
            // Get enrolled students for this course
            $enrolledStudents = CourseEnrollment::where('course_id', $assignment->course_id)
                ->whereIn('status', ['enrolled', 'completed'])
                ->with('student')
                ->get()
                ->pluck('student')
                ->filter();
            
            if ($enrolledStudents->isEmpty()) {
                continue;
            }

            // 60-90% of enrolled students submit
            $submissionRate = rand(60, 90) / 100;
            $numSubmissions = max(1, (int)($enrolledStudents->count() * $submissionRate));
            $submittingStudents = $enrolledStudents->random(min($numSubmissions, $enrolledStudents->count()));

            foreach ($submittingStudents as $student) {
                $submission = $this->createSubmission($assignment, $student);
                if ($submission) {
                    $submissionCount++;
                }
            }
        }
        
        $this->command->info("Created {$submissionCount} assignment submissions!");
    }

    private function createSubmission(Assignment $assignment, User $student): ?AssignmentSubmission
    {
        $dueDate = $assignment->due_date;
        $isPastDue = $dueDate < now();
        
        // Determine submission timing
        $isLate = false;
        $submittedAt = null;
        
        if ($isPastDue) {
            // Assignment is past due - submission was in the past
            $daysBeforeDue = rand(-5, 3); // -5 to 3 days relative to due date
            $submittedAt = $dueDate->copy()->addDays($daysBeforeDue);
            $isLate = $daysBeforeDue > 0;
        } else {
            // Assignment still open - some submitted, some will submit later
            if (rand(1, 100) <= 70) {
                // 70% already submitted
                $daysAgo = rand(1, 14);
                $submittedAt = now()->subDays($daysAgo);
                $isLate = false;
            } else {
                // 30% haven't submitted yet (no submission record)
                return null;
            }
        }

        // Determine status
        $statuses = ['submitted', 'graded', 'returned'];
        $statusWeights = [30, 60, 10];
        
        if ($isLate) {
            $status = 'late';
        } else {
            $status = $this->weightedRandom($statuses, $statusWeights);
        }

        // Generate grade if graded
        $grade = null;
        $feedback = null;
        
        if ($status === 'graded' || $status === 'returned') {
            $gradeRange = rand(1, 100);
            if ($gradeRange <= 15) {
                $grade = rand(60, 69);
                $feedback = $this->feedbackTemplates['needs_improvement'][array_rand($this->feedbackTemplates['needs_improvement'])];
            } elseif ($gradeRange <= 35) {
                $grade = rand(70, 79);
                $feedback = $this->feedbackTemplates['average'][array_rand($this->feedbackTemplates['average'])];
            } elseif ($gradeRange <= 70) {
                $grade = rand(80, 89);
                $feedback = $this->feedbackTemplates['good'][array_rand($this->feedbackTemplates['good'])];
            } else {
                $grade = rand(90, 100);
                $feedback = $this->feedbackTemplates['excellent'][array_rand($this->feedbackTemplates['excellent'])];
            }
        }

        // Generate file info based on submission type
        $fileUrl = null;
        $fileName = null;
        $content = null;
        
        $studentNameSlug = str_replace(' ', '_', strtolower($student->name));
        $assignmentSlug = str_replace([' ', ':', ','], ['_', '', ''], strtolower($assignment->title));
        
        switch ($assignment->submission_type) {
            case 'file':
                $extensions = explode(',', $assignment->allowed_file_types ?? 'pdf');
                $ext = trim($extensions[0]);
                $fileName = "{$student->nim}_{$studentNameSlug}_{$assignmentSlug}.{$ext}";
                $fileUrl = "assignments/{$fileName}";
                break;
            case 'hafalan':
                $fileName = "hafalan_{$student->nim}_{$assignmentSlug}.mp3";
                $fileUrl = "hafalan/{$fileName}";
                $content = "Rekaman hafalan untuk tugas: {$assignment->title}";
                break;
            case 'text':
                $content = $this->generateTextContent($assignment->title);
                break;
            case 'quiz':
                $content = json_encode(['answers' => $this->generateQuizAnswers()]);
                break;
        }

        return AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'content' => $content,
                'submitted_at' => $submittedAt,
                'is_late' => $isLate,
                'attempt_number' => $assignment->submission_type === 'hafalan' ? rand(1, $assignment->attempts_allowed) : 1,
                'status' => $status,
                'grade' => $grade,
                'feedback' => $feedback,
            ]
        );
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

    private function generateTextContent(string $title): string
    {
        $paragraphs = [
            "Bismillahirrahmanirrahim. Dalam menjawab tugas tentang {$title}, saya akan menjelaskan berdasarkan pemahaman yang telah saya pelajari dari berbagai referensi.",
            "Menurut pandangan ulama, konsep ini memiliki dasar yang kuat dalam Al-Qur'an dan As-Sunnah. Dalil yang mendukung hal ini antara lain...",
            "Dari analisis yang saya lakukan, dapat disimpulkan bahwa pemahaman yang benar tentang topik ini sangat penting untuk diamalkan dalam kehidupan sehari-hari.",
            "Wallahu a'lam bishawab. Semoga jawaban ini dapat memberikan pemahaman yang bermanfaat.",
        ];
        
        return implode("\n\n", $paragraphs);
    }

    private function generateQuizAnswers(): array
    {
        $answers = [];
        for ($i = 1; $i <= rand(5, 10); $i++) {
            $answers["q{$i}"] = ['A', 'B', 'C', 'D'][array_rand(['A', 'B', 'C', 'D'])];
        }
        return $answers;
    }
}
