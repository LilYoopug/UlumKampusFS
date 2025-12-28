<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['info', 'warning', 'error', 'success']),
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'is_read' => fake()->boolean(30),
            'read_at' => fake()->optional(30, null)->dateTime(),
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'action_url' => fake()->optional(50)->url(),
            'related_entity_type' => fake()->optional(30)->randomElement(['course', 'assignment', 'announcement', 'grade']),
            'related_entity_id' => fake()->optional(30)->randomNumber(),
            'expires_at' => fake()->optional(40)->dateTimeBetween('+1 day', '+30 days'),
            'is_sent' => fake()->boolean(70),
            'sent_at' => fake()->optional(70, null)->dateTimeBetween('-1 day', 'now'),
        ];
    }

    /**
     * Mark the notification as read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    /**
     * Mark the notification as unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Set the notification priority to urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Mark the notification as sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_sent' => true,
            'sent_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    /**
     * Set the notification as expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * Assign notification to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Set the notification type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}