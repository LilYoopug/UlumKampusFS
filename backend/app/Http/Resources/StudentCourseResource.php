<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\CourseEnrollment;
use App\Models\Grade;

/**
 * Student Course Resource - includes student-specific enrollment data
 * 
 * This resource transforms course data with the authenticated student's
 * enrollment information including progress, grades, and completion status.
 */
class StudentCourseResource extends JsonResource
{
    protected $enrollment;
    protected $studentId;
    
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  CourseEnrollment|null  $enrollment
     * @param  int|null  $studentId
     * @return void
     */
    public function __construct($resource, $enrollment = null, $studentId = null)
    {
        parent::__construct($resource);
        $this->enrollment = $enrollment;
        $this->studentId = $studentId;
    }
    
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get enrollment data if not provided
        if (!$this->enrollment && $this->studentId) {
            $this->enrollment = CourseEnrollment::where('course_id', $this->id)
                ->where('student_id', $this->studentId)
                ->first();
        }
        
        // Calculate progress from enrollment
        $progress = 0;
        if ($this->enrollment) {
            $progress = $this->enrollment->progress_percentage ?? 0;
        }
        
        // Get grade data for this course and student
        $gradeLetter = null;
        $gradeNumeric = null;
        $completionDate = null;
        
        if ($this->enrollment) {
            // Check if course is completed
            if ($this->enrollment->status === 'completed') {
                $progress = 100;
                $completionDate = $this->enrollment->completed_at ? 
                    $this->enrollment->completed_at->format('Y-m-d') : null;
            }
            
            // Get course grade from grades table
            $grades = Grade::where('course_id', $this->id)
                ->where('user_id', $this->enrollment->student_id)
                ->get();
            
            if ($grades->isNotEmpty()) {
                // Calculate average grade for the course
                $avgGrade = $grades->avg('grade');
                $gradeNumeric = round($avgGrade);
                
                // Convert to letter grade
                $gradeLetter = $this->calculateGradeLetter($avgGrade);
            }
        }

        // Transform syllabus_data to frontend format
        $syllabus = [];
        if (!empty($this->syllabus_data) && is_array($this->syllabus_data)) {
            foreach ($this->syllabus_data as $item) {
                if (isset($item['week']) && isset($item['topic'])) {
                    $syllabus[] = [
                        'week' => (int) $item['week'],
                        'topic' => $item['topic'],
                        'description' => $item['description'] ?? '',
                    ];
                }
            }
        }

        return [
            'id' => $this->code ?? $this->id, // Use code as ID to match frontend
            'title' => $this->name,
            'instructor' => $this->instructor?->name ?? '',
            'instructorId' => $this->instructor_id,
            'instructorAvatarUrl' => $this->instructor?->avatar_url ?? 'https://picsum.photos/seed/' . ($this->instructor?->id ?? 1) . '/100/100',
            'instructorBioKey' => $this->instructor_bio_key ?? null,
            'faculty' => $this->whenLoaded('faculty', fn() => $this->faculty),
            'facultyId' => $this->faculty_id,
            'major' => $this->whenLoaded('major', fn() => $this->major),
            'majorId' => $this->major_id,
            'sks' => $this->credit_hours,
            'description' => $this->description,
            'imageUrl' => $this->image_url,
            'progress' => $progress,
            'gradeLetter' => $gradeLetter,
            'gradeNumeric' => $gradeNumeric,
            'completionDate' => $completionDate,
            'mode' => $this->mode,
            'status' => $this->status ?? ($this->is_active ? 'Published' : 'Draft'),
            'learningObjectives' => $this->learning_objectives ?? [],
            'syllabus' => $syllabus,
            'modules' => $this->whenLoaded('modules', fn() => CourseModuleResource::collection($this->modules), []),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'students_count' => $this->current_enrollment,
            'code' => $this->code,
            'semester' => $this->semester,
            'year' => $this->year,
            'schedule' => $this->schedule,
            'room' => $this->room,
            'capacity' => $this->capacity,
        ];
    }
    
    /**
     * Calculate letter grade from numeric grade.
     */
    private function calculateGradeLetter(float $grade): string
    {
        return match (true) {
            $grade >= 95 => 'A+',
            $grade >= 90 => 'A',
            $grade >= 85 => 'A-',
            $grade >= 80 => 'B+',
            $grade >= 75 => 'B',
            $grade >= 70 => 'B-',
            $grade >= 65 => 'C+',
            $grade >= 60 => 'C',
            $grade >= 55 => 'D',
            default => 'E',
        };
    }
}
