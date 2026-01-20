# Bulk Message Dispatcher

Bulk message sending system built with Laravel 12, featuring queue processing, Redis caching, and webhook integration.

## Features

- Bulk message sending via queue jobs
- Redis caching for message metadata
- RESTful API with pagination
- Message status tracking (pending, sent, failed)
- Webhook integration for message delivery
- Comprehensive test coverage
- Docker support for easy setup
- Swagger documentation

## Requirements

- PHP 8.2+
- Laravel 12
- MySQL 8.0+
- Redis 7+
- Composer
- Docker

## Installation

### Option 1: Docker (Recommended)

```bash
docker-compose up -d
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan queue:work
```

### Option 2: Local Setup

#### 1. Clone and Install Dependencies

```bash
composer install
```

#### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database and Redis credentials:

```env
DB_HOST=127.0.0.1
DB_DATABASE=message_queue_db
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

WEBHOOK_URL=https://webhook.site/your-unique-id
```

#### 3. Database Setup

```bash
php artisan migrate --seed
```

#### 4. Start Services

Terminal 1 - Start Laravel Server:
```bash
php artisan serve
```

Terminal 2 - Start Queue Worker:
```bash
php artisan queue:work redis --sleep=3 --tries=3
```

## Usage

### Dispatching Messages

Dispatch pending messages to the queue:

```bash
php artisan messages:dispatch
```

With custom options:
```bash
php artisan messages:dispatch --batch=2 --interval=5
```

### API Endpoints

#### Get Sent Messages

```bash
curl http://localhost:8000/api/v1/messages/sent
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "recipient": "+905551111111",
      "content": "Message content",
      "status": "sent",
      "external_message_id": "67f2f8a8-ea58-4ed0-a6f9-ff217df4d849",
      "sent_at": "2024-01-19T10:30:00Z",
      "created_at": "2024-01-19T10:00:00Z",
      "updated_at": "2024-01-19T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

## Architecture

### Directory Structure

```
app/
├── Console/Commands/           # Artisan commands
├── Exceptions/                 # Custom exceptions
├── Http/Controllers/Api/       # API controllers
├── Jobs/                       # Queue jobs
├── Models/                     # Eloquent models
├── Providers/                  # Service providers
├── Repositories/               # Data access layer
│   ├── Contracts/              # Repository interfaces
│   └── MessageRepository.php    # Implementation
└── Services/                   # Business logic
    ├── Contracts/              # Service interfaces
    └── MessageService.php       # Implementation

config/
├── app.php                     # Application config
├── database.php                # Database config
├── queue.php                   # Queue config
├── cache.php                   # Cache config
└── services.php                # Third-party services

database/
├── factories/                  # Model factories
├── migrations/                 # Database migrations
└── seeders/                    # Database seeders

tests/
├── Feature/                    # Feature tests
└── Unit/                       # Unit tests

routes/
└── api.php                     # API routes
```

### Design Patterns

**Repository Pattern**
- Abstracts database operations
- Implements `MessageRepositoryInterface`
- Enables easy testing and switching implementations

**Service Layer Pattern**
- `MessageService` handles business logic
- `WebhookService` manages external communication
- Clear separation of concerns

**Dependency Injection**
- AppServiceProvider binds contracts to implementations
- Constructor injection in controllers and jobs
- Testable and loosely coupled code

### Message Flow

1. **Dispatch Command** → Creates jobs for pending messages
2. **Queue Job** → Processes each message via `SendMessageJob`
3. **Message Service** → Validates and coordinates sending
4. **Webhook Service** → Sends message via HTTP POST to webhook
5. **Cache** → Stores message ID and timestamp in Redis
6. **Database** → Updates message status and external ID

## Configuration

### Key Settings (.env)

```env
MESSAGE_CONTENT_LIMIT=160           # Character limit for messages
BATCH_SIZE=2                        # Messages per batch
BATCH_INTERVAL=5                    # Seconds between batches
WEBHOOK_URL=...                     # Webhook endpoint
WEBHOOK_AUTH_KEY=...                # Webhook auth key
QUEUE_CONNECTION=redis              # Queue driver
CACHE_DRIVER=redis                  # Cache driver
```

### Redis Cache

Message metadata is cached in Redis.

Cache duration: 24 hours

## Testing

Run all tests:
```bash
php artisan test
```

Run with coverage:
```bash
php artisan test --coverage
```

## Webhook Integration

### Setup

1. Create a webhook.site URL: https://webhook.site
2. Update `.env`:
   ```env
   WEBHOOK_URL=https://webhook.site/your-unique-id
   WEBHOOK_AUTH_KEY=test-key
   ```
3. Configure webhook response in webhook.site:
   - Status code: 202
   - Content-Type: application/json
   - Response body:
     ```json
     {
       "message": "Accepted",
       "messageId": "67f2f8a8-ea58-4ed0-a6f9-ff217df4d849"
     }
     ```

### Request Format

```bash
curl -X POST https://webhook.site/your-id \
  -H 'Content-Type: application/json' \
  -H 'X-INS-Auth-Key: INS.mel9uMcyYG1hKKQVPoc.b03j9aZwRTOcA2Ywo' \
  -d '{"to":"+905551111111","content":"Message text"}'
```

### Response Handling

- Success: 202 Accepted with `messageId`
- Failure: Exception thrown, job retried (3 attempts with backoff)
- Failed job stored in `failed_jobs` table

## Error Handling

### Message Validation

- Content length: max 160 characters
- Invalid status transitions prevented
- Duplicate sending prevented

### Retry Strategy

- Attempts: 3
- Backoff: [10s, 30s, 60s]
- Failed jobs logged and tracked
- Graceful failure handling

## API Documentation

Swagger specification available in `http://localhost:8000/api/documentation`

## Database Schema

### messages table

| Column | Type | Properties |
|--------|------|-----------|
| id | bigint | PK |
| recipient | string(20) | Phone number |
| content | text | Message content |
| status | string | pending, sent, failed |
| external_message_id | string | Webhook response ID |
| sent_at | timestamp | When message was sent |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Record update |

Indexes:
- `status` (for pending message queries)
- `(status, created_at)` (composite for efficient filtering)
- `external_message_id` (unique, for deduplication)

### failed_jobs table

Tracks jobs that exceed retry attempts for manual intervention.

## Performance Considerations

- Batch processing: 2 messages per 5 seconds (configurable)
- Queue-based async processing prevents blocking
- Redis caching for fast message lookup
- Database indexes optimize query performance
- Connection pooling for database efficiency

## Code Quality

- **PSR-12** compliance
- **SOLID** principles
- Clean code architecture
- Comprehensive test coverage

### Queue Not Processing

```bash
# Check queue status
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

## Support

Email Address: yasinugurb@ymail.com
