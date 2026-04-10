<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Inbox Module
|--------------------------------------------------------------------------
*/

// ─── Public ──────────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ─── Protected (JWT Bearer token) ───────────────────────────────────────────
Route::middleware(['auth.api'])->group(function () {

    // Auth
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Threads
    Route::get('/threads', [ThreadController::class, 'index']);
    Route::post('/threads', [ThreadController::class, 'store']);
    Route::get('/threads/{thread}', [ThreadController::class, 'show']);

    // Messages (replies)
    Route::post('/threads/{thread}/messages', [MessageController::class, 'store']);

    // Notifications (bonus)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
});
