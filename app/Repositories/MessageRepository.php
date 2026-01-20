<?php

namespace App\Repositories;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;

class MessageRepository implements MessageRepositoryInterface
{
    public function getPendingMessages(int $limit): array
    {
        return Message::where('status', Message::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function updateStatus(int $id, string $status, ?string $externalId = null): void
    {
        $message = Message::findOrFail($id);

        $data = ['status' => $status];

        if ($externalId) {
            $data['external_message_id'] = $externalId;
            $data['sent_at'] = now();
        }

        $message->update($data);
    }

    public function getSentMessages(int $perPage = 15): array
    {
        return Message::where('status', Message::STATUS_SENT)
            ->orderBy('sent_at', 'desc')
            ->paginate($perPage)
            ->toArray();
    }

    public function find(int $id)
    {
        return Message::find($id);
    }
}
