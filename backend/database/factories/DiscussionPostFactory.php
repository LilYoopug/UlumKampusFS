<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DiscussionPost>
 */
class DiscussionPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thread_id' => \App\Models\DiscussionThread::factory(),
            'parent_id' => null,
            'user_id' => \App\Models\User::factory(),
            'content' => fake()->paragraphs(2, true),
            'is_edited' => fake()->boolean(15), // 15% chance of being edited
            'edited_at' => fake()->optional(15)->dateTimeBetween('-1 week', 'now'),
            'edited_by' => fake()->optional(15)->randomElement([null]),
            'is_solution' => fake()->boolean(5), // 5% chance of being marked as solution
            'marked_as_solution_by' => fake()->optional(5)->randomElement([null]),
            'marked_as_solution_at' => fake()->optional(5)->dateTimeBetween('-1 week', 'now'),
            'likes_count' => fake()->numberBetween(0, 20),
            'attachment_url' => fake()->optional(20)->url(),
            'attachment_type' => fake()->optional()->randomElement(['pdf', 'doc', 'docx', 'jpg', 'png', 'zip']),
        ];
    }

    /**
     * Indicate that the post is edited.
     */
    public function edited(): static
    {
        $user = \App\Models\User::factory()->create();

        return $this->state(fn (array $attributes) => [
            'is_edited' => true,
            'edited_at' => now(),
            'edited_by' => $user->id,
        ]);
    }

    /**
     * Indicate that the post is marked as solution.
     */
    public function solution(): static
    {
        $user = \App\Models\User::factory()->create();

        return $this->state(fn (array $attributes) => [
            'is_solution' => true,
            'marked_as_solution_by' => $user->id,
            'marked_as_solution_at' => now(),
        ]);
    }

    /**
     * Indicate that the post is a reply to another post.
     */
    public function replyTo(int $parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Indicate that the post is for a specific thread.
     */
    public function forThread(int $threadId): static
    {
        return $this->state(fn (array $attributes) => [
            'thread_id' => $threadId,
        ]);
    }

    /**
     * Indicate that the post is by a specific user.
     */
    public function byUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }
}