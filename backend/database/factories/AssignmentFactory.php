<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => \App\Models\Course::factory(),
            'module_id' => fake()->optional(50)->create(\App\Models\CourseModule::class)->id,
            'created_by' => \App\Models\User::factory()->create(['role' => 'faculty']),
            'title' => fake()->randomElement([
                'Homework 1',
                'Quiz 1',
                'Midterm Exam',
                'Final Project',
                'Lab Assignment',
                'Case Study Analysis',
                'Research Paper',
                'Group Project',
                'Weekly Reflection',
                'Coding Challenge',
            ]),
            'description' => fake()->paragraphs(2, true),
            'instructions' => fake()->paragraphs(3, true),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+3 months'),
            'max_points' => fake()->randomFloat(2, 10, 100),
            'submission_type' => fake()->randomElement(['text', 'file', 'link', 'mixed']),
            'allowed_file_types' => fake()->optional()->randomElement(['pdf,doc,docx', 'pdf,zip', 'any']),
            'max_file_size' => fake()->optional()->numberBetween(1024, 10485760), // 1KB to 10MB
            'attempts_allowed' => fake()->numberBetween(1, 5),
            'is_published' => fake()->boolean(80), // 80% chance of being published
            'published_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'allow_late_submission' => fake()->boolean(50),
            'late_penalty' => fake()->randomFloat(2, 0, 50), // 0-50% penalty
            'order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the assignment is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the assignment is not published (draft).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the assignment is for a specific course.
     */
    public function forCourse(int $courseId): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $courseId,
        ]);
    }

    /**
     * Indicate that the assignment is for a specific module.
     */
    public function forModule(int $moduleId): static
    {
        return $this->state(fn (array $attributes) => [
            'module_id' => $moduleId,
        ]);
    }

    /**
     * Indicate that the assignment has a specific due date.
     */
    public function dueIn(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->addDays($days),
        ]);
    }

    /**
     * Indicate that the assignment is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->subDays(7),
        ]);
    }

    /**
     * Indicate that the assignment allows late submissions.
     */
    public function allowLate(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_late_submission' => true,
            'late_penalty' => fake()->randomFloat(2, 5, 20),
        ]);
    }

    /**
     * Indicate that the assignment requires file submission.
     */
    public function fileSubmission(): static
    {
        return $this->state(fn (array $attributes) => [
            'submission_type' => 'file',
            'allowed_file_types' => 'pdf,doc,docx,zip',
            'max_file_size' => 5242880, // 5MB
        ]);
    }
}