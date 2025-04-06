<?php

use Illuminate\Support\Facades\Route;
use RefactorLab\AICodeReviewer\Http\Controllers\GitHubWebhookController;

Route::post('/github/webhook', [GitHubWebhookController::class, 'handle'])
    ->middleware(['web', 'throttle:60,1'])
    ->name('aicode.github.webhook'); 