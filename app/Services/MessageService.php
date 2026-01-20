<?php

namespace App\Services;

use App\Exceptions\MessageSendingException;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\Contracts\WebhookServiceInterface;
use Illuminate\Support\Facades\Cache;

class MessageService
{
    protected const DEFAULT_CONTENT_LIMIT = 160;

    public function __construct(
        private MessageRepositoryInterface $messageRepository,
        private WebhookServiceInterface $webhookService,
    ) {}

    public function validateContent(string $content): void
    {
        $limit = $this->getContentLimit();

        if (strlen($content) > $limit) {
            throw new MessageSendingException(
                "Message content exceeds {$limit} character limit",
                422,
            );
        }
    }

    protected function getContentLimit(): int
    {
        return (int) config('app.message_content_limit', self::DEFAULT_CONTENT_LIMIT);
    }

    public function send(int $messageId): bool
    {
        $message = $this->messageRepository->find($messageId);

        if (!$message) {
            throw new MessageSendingException('Message not found', 404);
        }

        if (!$message->isPending()) {
            throw new MessageSendingException('Message already processed', 409);
        }

        $this->validateContent($message->content);

        try {
            $response = $this->webhookService->send($message->recipient, $message->content);

            if ($response['status_code'] === 202 && $response['message_id']) {
                $this->messageRepository->updateStatus(
                    $messageId,
                    'sent',
                    $response['message_id'],
                );

                $this->cacheMessageData($response['message_id'], $message->sent_at);
                return true;
            }
            
            throw new MessageSendingException('Invalid webhook response', 500);
            
        } catch (MessageSendingException $e) {
            $this->messageRepository->updateStatus($messageId, 'failed');
            throw $e;
        }
    }

    private function cacheMessageData(string $messageId, string $sentAt): void
    {
        $cacheKey = "message:{$messageId}";

        Cache::put($cacheKey, [
            'message_id' => $messageId,
            'sent_at' => $sentAt,
        ], now()->addHours(24));
    }

    public function getSentMessages(int $page = 1): array
    {
        return $this->messageRepository->getSentMessages((int) env('PAGINATION_PER_PAGE', 15));
    }
}
