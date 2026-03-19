<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiEmailResponsesController;
use App\Http\Controllers\PromptsRelanceController;
use App\Http\Controllers\WebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/favicon.ico', function () {
    return file_exists(public_path('favicon.ico'))
        ? response()->file(public_path('favicon.ico'))
        : response('', 204);
});

Route::prefix('api')->middleware('api')->group(function () {
    Route::get('/ai-email-responses', [AiEmailResponsesController::class, 'index']);
    Route::post('/ai-email-responses', [AiEmailResponsesController::class, 'store']);
    Route::patch('/ai-email-responses/{id}', [AiEmailResponsesController::class, 'update']);

    Route::get('/prompts-relance', [PromptsRelanceController::class, 'index']);
    Route::post('/prompts-relance', [PromptsRelanceController::class, 'store']);
    Route::patch('/prompts-relance/{id}', [PromptsRelanceController::class, 'update']);
    Route::delete('/prompts-relance/{id}', [PromptsRelanceController::class, 'destroy']);

    // Proxy serveur pour préremplir via le webhook externe (évite CORS)
    Route::post('/prefill-relance', [WebhookController::class, 'prefillRelance']);
});
