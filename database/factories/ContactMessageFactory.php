<?php

namespace Database\Factories;

use App\Enums\ContactMessages\RequestStatus;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'subject' => fake()->optional()->sentence(4),
            'message' => fake()->paragraph(),
            'is_read' => false,
            'read_at' => null,
            'request_status' => RequestStatus::New,
        ];
    }

    public function requestStatus(RequestStatus $status): static
    {
        return $this->state(fn () => [
            'request_status' => $status,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn () => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn () => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public function phoneOnly(): static
    {
        return $this->state(fn () => [
            'email' => null,
            'phone' => fake()->phoneNumber(),
        ]);
    }
}
