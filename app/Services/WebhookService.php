<?php

namespace App\Services;

use App\Exceptions\MessageSendingException;
use App\Services\Contracts\WebhookServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WebhookService implements WebhookServiceInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function send(string $to, string $content): array
    {
        $url = config('services.webhook.url');

        if (!$url) {
            throw new MessageSendingException('Webhook URL not configured', 500);
        }

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-INS-Auth-Key' => config('services.webhook.auth_key'),
                ],
                'json' => [
                    'to' => $to,
                    'content' => $content,
                ],
                'timeout' => 10,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'status_code' => $response->getStatusCode(),
                'message_id' => $body['messageId'] ?? null,
                'message' => $body['message'] ?? 'Success',
            ];
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                throw new MessageSendingException(
                    'Failed to send message: ' . $e->getResponse()->getReasonPhrase(),
                    $e->getResponse()->getStatusCode(),
                );
            }

            throw new MessageSendingException('Webhook request failed: ' . $e->getMessage(), 500);
        }
    }
}
