<?php

namespace Tests\Feature;

use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_sent_messages(): void
    {
        Message::factory()->count(5)->sent()->create();
        Message::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/messages/sent');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJson(['success' => true]);
    }

    public function test_returns_only_sent_messages(): void
    {
        Message::factory()->count(5)->sent()->create();
        Message::factory()->count(10)->create(['status' => 'pending']);

        $response = $this->getJson('/api/v1/messages/sent');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(5, $data);
    }

    public function test_pagination_works_correctly(): void
    {
        Message::factory()->count(20)->sent()->create();

        $response = $this->getJson('/api/v1/messages/sent?page=1');

        $response->assertStatus(200);
        $pagination = $response->json('pagination');
        
        $this->assertIsArray($pagination);
        $this->assertArrayHasKey('current_page', $pagination);
        $this->assertArrayHasKey('per_page', $pagination);
        $this->assertArrayHasKey('total', $pagination);
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertGreaterThan(0, $pagination['per_page']);
    }

    public function test_api_returns_correct_data_structure(): void
    {
        $message = Message::factory()->sent()->create([
            'recipient' => '+905551111111',
            'content' => 'Test message',
            'status' => 'sent',
        ]);

        $response = $this->getJson('/api/v1/messages/sent');

        $response->assertStatus(200);
        $data = $response->json('data.0');
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('recipient', $data);
        $this->assertArrayHasKey('content', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('external_message_id', $data);
        $this->assertArrayHasKey('sent_at', $data);
    }
}
