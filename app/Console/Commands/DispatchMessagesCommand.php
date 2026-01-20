<?php

namespace App\Console\Commands;

use App\Jobs\SendMessageJob;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Console\Command;

class DispatchMessagesCommand extends Command
{
    protected $signature = 'messages:dispatch {--batch=2 : Number of messages per batch} {--interval=5 : Seconds between batches}';

    protected $description = 'Dispatch pending messages to queue';

    public function handle(MessageRepositoryInterface $messageRepository): int
    {
        $batch = (int) $this->option('batch');
        $interval = (int) $this->option('interval');
        $messages = $messageRepository->getPendingMessages($batch);
        
        if (empty($messages)) {
            $this->info('No pending messages to dispatch.');
            return self::SUCCESS;
        }

        foreach ($messages as $message) {
            SendMessageJob::dispatch($message['id']);
            $this->line("Queued message ID: {$message['id']}");
        }

        $this->info("Dispatched " . count($messages) . " messages with {$interval}s interval.");

        return self::SUCCESS;
    }
}
