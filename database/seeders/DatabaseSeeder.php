<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Message::factory()->count(20)->create([
            'status' => 'pending',
        ]);

        Message::factory()->count(5)->create([
            'status' => 'sent',
            'external_message_id' => fn() => fake()->uuid(),
            'sent_at' => now(),
        ]);
    }
}
