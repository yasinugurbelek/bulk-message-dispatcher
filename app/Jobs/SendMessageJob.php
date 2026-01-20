<?php

namespace App\Jobs;

use App\Exceptions\MessageSendingException;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(private int $messageId) {}

    public function handle(MessageService $messageService): void
    {
        try {
            $messageService->send($this->messageId);
        } catch (MessageSendingException $e) {
            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                $this->release($this->backoff[$this->attempts() - 1] ?? 60);
            }
        }
    }

    public function failed(Throwable $e): void
    {
        logger()->error("Message sending failed for ID: {$this->messageId}", [
            'error' => $e->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}
