<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetSentMessagesRequest;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class MessageController extends Controller
{
    public function __construct(private MessageService $messageService) {}

    #[OA\Get(
        path: '/api/v1/messages/sent',
        summary: 'Get list of sent messages',
        description: 'Retrieve a paginated list of all successfully sent messages',
        operationId: 'getSentMessages',
        tags: ['Messages']
    )]
    #[OA\QueryParameter(
        name: 'page',
        description: 'Page number for pagination',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Successfully retrieved sent messages',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'recipient', type: 'string', example: '+905551111111'),
                            new OA\Property(property: 'content', type: 'string', example: 'Test message content'),
                            new OA\Property(property: 'status', type: 'string', enum: ['pending', 'sent', 'failed'], example: 'sent'),
                            new OA\Property(property: 'external_message_id', type: 'string', format: 'uuid', example: '67f2f8a8-ea58-4ed0-a6f9-ff217df4d849'),
                            new OA\Property(property: 'sent_at', type: 'string', format: 'date-time', example: '2024-01-19T10:30:00Z'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                        ],
                        type: 'object'
                    )
                ),
                new OA\Property(
                    property: 'pagination',
                    properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'per_page', type: 'integer', example: 15),
                        new OA\Property(property: 'total', type: 'integer', example: 100),
                        new OA\Property(property: 'last_page', type: 'integer', example: 7),
                    ],
                    type: 'object'
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Too Many Requests - Rate limit exceeded',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'error', type: 'string', example: 'Validation error'),
                new OA\Property(property: 'message', type: 'string', example: 'The page field must be an integer.')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 429,
        description: 'Too Many Requests - Rate limit exceeded',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'error', type: 'string', example: 'Too Many Requests'),
                new OA\Property(property: 'message', type: 'string', example: 'Rate limit exceeded. Please try again later.'),
                new OA\Property(property: 'retry_after', type: 'integer', example: 60, nullable: true)
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal Server Error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'error', type: 'string', example: 'Internal Server Error'),
                new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred')
            ],
            type: 'object'
        )
    )]
    public function sent(GetSentMessagesRequest $request): JsonResponse
    {
        try {
            $messages = $this->messageService->getSentMessages();

            return response()->json([
                'success' => true,
                'data' => data_get($messages, 'data') ?? [],
                'pagination' => $this->extractPagination($messages),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function extractPagination(array $data): array
    {
        return [
            'current_page' => data_get($data, 'current_page') ?? 1,
            'per_page' => data_get($data, 'per_page') ?? 15,
            'total' => data_get($data, 'total') ?? 0,
            'last_page' => data_get($data, 'last_page') ?? 1,
        ];
    }
}
