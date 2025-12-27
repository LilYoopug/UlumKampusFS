<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssignmentSubmission>
 */
class AssignmentSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => \App\Models\Assignment::factory(),
            'student_id' => \App\Models\User::factory()->create(['role' => 'student']),
            'content' => fake()->optional(70)->paragraphs(2, true),
            'file_url' => fake()->optional(30)->url(),
            'file_name' => fake()->optional(30)->word() . '.' . fake()->fileExtension(),
            'file_size' => fake()->optional(30)->numberBetween(1024, 10485760),
            'link_url' => fake()->optional(20)->url(),
            'status' => fake()->randomElement(['draft', 'submitted', 'late', 'graded']),
            'submitted_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'graded_at' => fake()->optional(30)->dateTimeBetween('-2 weeks', 'now'),
            'graded_by' => fake()->optional(30)->create(\App\Models\User::class, ['role' => 'faculty'])->id,
            'grade' => fake()->optional(30)->randomFloat(2, 0, 100),
            'feedback' => fake()->optional(30)->paragraphs(2, true),
            'instructor_notes' => fake()->optional(20)->paragraphs(1, true),
            'is_late' => fake()->boolean(20),
            'late_submission_at' => fake()->optional(20)->dateTimeBetween('-1 month', 'now'),
            'attempt_number' => fake()->numberBetween(1, 3),
        ];
    }

    /**
     * Indicate that the submission is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'submitted_at' => null,
        ]);
    }

    /**
     * Indicate that the submission has been submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'submitted_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'is_late' => false,
        ]);
    }

    /**
     * Indicate that the submission is late.
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
            'submitted_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'is_late' => true,
            'late_submission_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the submission has been graded.
     */
    public function graded(): static
    {
        $grader = \App\Models\User::factory()->create(['role' => 'faculty']);

        return $this->state(fn (array $attributes) => [
            'status' => 'graded',
            'submitted_at' => fake()->dateTimeBetween('-1 month', '-1 week'),
            'graded_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'graded_by' => $grader->id,
            'grade' => fake()->randomFloat(2, 50, 100),
            'feedback' => fake()->paragraphs(2, true),
        ]);
    }

    /**
     * Indicate that the submission is for a specific assignment.
     */
    public function forAssignment(int $assignmentId): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_id' => $assignmentId,
        ]);
    }

    /**
     * Indicate that the submission is from a specific student.
     */
    public function fromStudent(int $studentId): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $studentId,
        ]);
    }

    /**
     * Indicate that the submission has text content.
     */
    public function withContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->paragraphs(3, true),
        ]);
    }

    /**
     * Indicate that the submission has a file attachment.
     */
    public function withFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_url' => fake()->url(),
            'file_name' => fake()->word() . '.' . fake()->fileExtension(),
            'file_size' => fake()->numberBetween(1024, 10485760),
        ]);
    }

    /**
     * Indicate that the submission has a link.
     */
    public function withLink(): static
    {
        return $this->state(fn (array $attributes) => [
            'link_url' => fake()->url(),
        ]);
    }

    /**
     * Indicate the attempt number.
     */
    public function attempt(int $number): static
    {
        return $this->state(fn (array $attributes) => [
            'attempt_number' => $number,
        ]);
    }
}