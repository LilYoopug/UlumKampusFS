<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseEnrollment>
 */
class CourseEnrollmentFactory extends Factory
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
            'student_id' => \App\Models\User::factory()->create(['role' => 'student']),
            'status' => fake()->randomElement(['pending', 'enrolled', 'dropped', 'completed']),
            'enrolled_at' => fake()->optional()->dateTimeBetween('-6 months', 'now'),
            'completed_at' => fake()->optional()->dateTimeBetween('-3 months', 'now'),
            'final_grade' => fake()->optional(60)->randomFloat(2, 0, 100),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the enrollment is active (enrolled).
     */
    public function enrolled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'enrolled',
            'enrolled_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the enrollment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'enrolled_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the enrollment is dropped.
     */
    public function dropped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dropped',
            'completed_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the enrollment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'enrolled_at' => fake()->dateTimeBetween('-1 year', '-6 months'),
            'completed_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'final_grade' => fake()->randomFloat(2, 50, 100),
        ]);
    }

    /**
     * Indicate that the enrollment is for a specific course.
     */
    public function forCourse(int $courseId): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $courseId,
        ]);
    }

    /**
     * Indicate that the enrollment is for a specific student.
     */
    public function forStudent(int $studentId): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $studentId,
        ]);
    }
}