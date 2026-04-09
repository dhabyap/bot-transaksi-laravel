<?php

use App\Http\Controllers\StatusController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/status', [StatusController::class, 'index']);
Route::get('/webhook/telegram', [TelegramWebhookController::class, 'verify']);
Route::post('/webhook/telegram', [TelegramWebhookController::class, 'handle']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
