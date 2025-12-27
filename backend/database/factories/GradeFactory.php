<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory()->create(['role' => 'student']),
            'course_id' => \App\Models\Course::factory(),
            'assignment_id' => fake()->optional(70)->create(\App\Models\Assignment::class)->id,
            'grade' => fake()->randomFloat(2, 0, 100),
            'grade_letter' => fake()->optional()->randomElement(['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F']),
            'comments' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the grade is passing (60+).
     */
    public function passing(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade' => fake()->randomFloat(2, 60, 100),
            'grade_letter' => fake()->randomElement(['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'D']),
        ]);
    }

    /**
     * Indicate that the grade is failing (<60).
     */
    public function failing(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade' => fake()->randomFloat(2, 0, 59.99),
            'grade_letter' => fake()->randomElement(['D', 'F']),
        ]);
    }

    /**
     * Indicate that the grade is excellent (90+).
     */
    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade' => fake()->randomFloat(2, 90, 100),
            'grade_letter' => fake()->randomElement(['A', 'A-']),
        ]);
    }

    /**
     * Indicate that the grade is for a specific course.
     */
    public function forCourse(int $courseId): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $courseId,
        ]);
    }

    /**
     * Indicate that the grade is for a specific user.
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Indicate that the grade is for a specific assignment.
     */
    public function forAssignment(int $assignmentId): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_id' => $assignmentId,
        ]);
    }
}