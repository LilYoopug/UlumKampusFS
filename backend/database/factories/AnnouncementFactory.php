<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => null,
            'faculty_id' => null,
            'created_by' => \App\Models\User::factory(),
            'title' => fake()->randomElement([
                'Important Notice',
                'Exam Schedule Update',
                'Holiday Schedule',
                'Library Hours Change',
                'Campus Maintenance',
                'Registration Reminder',
                'Guest Lecture Announcement',
                'Workshop Invitation',
                'Scholarship Opportunity',
                'Career Fair Information',
            ]),
            'content' => fake()->paragraphs(3, true),
            'category' => fake()->randomElement([
                'general',
                'academic',
                'event',
                'emergency',
                'policy',
                'exam',
                'holiday',
            ]),
            'target_audience' => fake()->randomElement([
                'everyone',
                'students',
                'faculty',
                'staff',
                'specific_course',
                'specific_faculty',
            ]),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'is_published' => fake()->boolean(80), // 80% chance of being published
            'published_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+3 months'),
            'allow_comments' => fake()->boolean(50),
            'view_count' => fake()->numberBetween(0, 500),
            'attachment_url' => fake()->optional(30)->url(),
            'attachment_type' => fake()->optional()->randomElement(['pdf', 'doc', 'docx', 'jpg', 'png']),
            'order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the announcement is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the announcement is not published (draft).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the announcement is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Indicate that the announcement has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Indicate that the announcement is for a specific course.
     */
    public function forCourse(int $courseId): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $courseId,
            'target_audience' => 'specific_course',
        ]);
    }

    /**
     * Indicate that the announcement is for a specific faculty.
     */
    public function forFaculty(int $facultyId): static
    {
        return $this->state(fn (array $attributes) => [
            'faculty_id' => $facultyId,
            'target_audience' => 'specific_faculty',
        ]);
    }
}