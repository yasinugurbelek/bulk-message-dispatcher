<?php

namespace Tests\Unit;

use App\Exceptions\MessageSendingException;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\Contracts\WebhookServiceInterface;
use App\Services\MessageService;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    private MessageService $service;
    private MessageService $serviceMock;
    private MessageRepositoryInterface $repository;
    private WebhookServiceInterface $webhook;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(MessageRepositoryInterface::class);
        $this->webhook = $this->createMock(WebhookServiceInterface::class);
        $this->service = new MessageService($this->repository, $this->webhook);
    }

    public function test_validates_content_character_limit(): void
    {
        $longContent = str_repeat('a', 161);

        $this->expectException(MessageSendingException::class);
        $this->expectExceptionMessage('exceeds');
        
        $this->service->validateContent($longContent);
    }

    public function test_accepts_valid_content(): void
    {
        $validContent = str_repeat('a', 160);
        
        try {
            $this->service->validateContent($validContent);
            $this->assertTrue(true);
        } catch (MessageSendingException $e) {
            $this->fail('Valid content should not throw exception');
        }
    }

    public function test_validates_content_exact_limit(): void
    {
        $exactContent = str_repeat('a', 160);
        
        try {
            $this->service->validateContent($exactContent);
            $this->assertTrue(true);
        } catch (MessageSendingException $e) {
            $this->fail('Content at exact limit should not throw exception');
        }
    }

    public function test_service_uses_configured_limit(): void
    {
        config(['app.message_content_limit' => 100]);
        
        $service = new MessageService($this->repository, $this->webhook);
        
        $contentAt100 = str_repeat('a', 100);
        try {
            $service->validateContent($contentAt100);
            $this->assertTrue(true);
        } catch (MessageSendingException $e) {
            $this->fail('Content at configured limit should not throw exception');
        }
        
        $contentAt101 = str_repeat('a', 101);
        $this->expectException(MessageSendingException::class);
        $service->validateContent($contentAt101);
    }
}