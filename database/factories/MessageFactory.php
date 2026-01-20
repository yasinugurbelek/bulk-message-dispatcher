<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'recipient' => $this->faker->e164PhoneNumber(),
            'content' => $this->faker->text(160),
            'status' => 'pending',
            'external_message_id' => null,
            'sent_at' => null,
        ];
    }

    public function sent(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'sent',
                'external_message_id' => $this->faker->uuid(),
                'sent_at' => now(),
            ];
        });
    }
}
