<?php

namespace App\Services\Contracts;

interface WebhookServiceInterface
{
    public function send(string $to, string $content): array;
}
