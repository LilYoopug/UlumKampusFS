<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DiscussionThread>
 */
class DiscussionThreadFactory extends Factory
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
            'module_id' => fake()->optional(30)->create(\App\Models\CourseModule::class)->id,
            'created_by' => \App\Models\User::factory(),
            'title' => fake()->randomElement([
                'Question about homework',
                'Discussion on chapter 5',
                'Help needed with assignment',
                'Exam preparation tips',
                'Project collaboration',
                'Code review request',
                'Concept clarification',
                'Resources sharing',
                'Study group formation',
            ]),
            'content' => fake()->paragraphs(3, true),
            'type' => fake()->randomElement(['question', 'discussion', 'announcement', 'help']),
            'status' => fake()->randomElement(['open', 'closed', 'archived']),
            'is_pinned' => fake()->boolean(10), // 10% chance of being pinned
            'is_locked' => fake()->boolean(5), // 5% chance of being locked
            'locked_by' => null,
            'locked_at' => null,
            'closed_by' => null,
            'closed_at' => null,
            'view_count' => fake()->numberBetween(0, 500),
            'reply_count' => fake()->numberBetween(0, 50),
            'last_post_by' => null,
            'last_post_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'attachment_url' => fake()->optional(20)->url(),
            'attachment_type' => fake()->optional()->randomElement(['pdf', 'doc', 'docx', 'jpg', 'png']),
        ];
    }

    /**
     * Indicate that the thread is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    /**
     * Indicate that the thread is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }

    /**
     * Indicate that the thread is a question.
     */
    public function question(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'question',
        ]);
    }

    /**
     * Indicate that the thread is pinned.
     */
    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
        ]);
    }

    /**
     * Indicate that the thread is locked.
     */
    public function locked(): static
    {
        $user = \App\Models\User::factory()->create();

        return $this->state(fn (array $attributes) => [
            'is_locked' => true,
            'locked_by' => $user->id,
            'locked_at' => now(),
        ]);
    }

    /**
     * Indicate that the thread is for a specific course.
     */
    public function forCourse(int $courseId): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $courseId,
        ]);
    }

    /**
     * Indicate that the thread is for a specific module.
     */
    public function forModule(int $moduleId): static
    {
        return $this->state(fn (array $attributes) => [
            'module_id' => $moduleId,
        ]);
    }
}