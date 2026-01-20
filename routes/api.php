<?php

use App\Http\Controllers\Api\V1\MessageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'throttle:10,1'])->prefix('v1')->group(function () {
    Route::get('messages/sent', [MessageController::class, 'sent'])->name('messages.sent');
});
