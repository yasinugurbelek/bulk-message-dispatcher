<?php

namespace App\Repositories\Contracts;

interface MessageRepositoryInterface
{
    public function getPendingMessages(int $limit): array;

    public function updateStatus(int $id, string $status, ?string $externalId = null): void;

    public function getSentMessages(int $perPage = 15): array;

    public function find(int $id);
}
