<?php

namespace App\Providers;

use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\MessageRepository;
use App\Services\Contracts\WebhookServiceInterface;
use App\Services\WebhookService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(WebhookServiceInterface::class, WebhookService::class);
    }

    public function boot(): void
    {
    }
}
