<?php

use App\Http\Controllers\Api\B2BDatasyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth & Admin Controllers
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\AiEmailResponsesController;
use App\Http\Controllers\PromptsRelanceController;
use App\Http\Controllers\ApiResponse\ResponseController;
use App\Http\Controllers\CategorieController;

// API Controllers
use App\Http\Controllers\Api\ProspectController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\B2BManualController;
use App\Http\Controllers\Api\C2BManualController;
use App\Http\Controllers\Api\Promptmail_autoprospectionController;
use App\Http\Controllers\Api\MailsgeneresController;
use App\Http\Controllers\Api\B2bAutoproEnvoiemasseHistoController;
use App\Http\Controllers\Api\RealTimeStatusController;
use App\Http\Controllers\Api\MailsReponsesController;
use App\Http\Controllers\Api\MailsReponsesStatusController;
use App\Http\Controllers\Api\RelancePromptController;
use App\Http\Controllers\Api\RelanceMailsgeneresController;
use App\Http\Controllers\Api\B2b_response_mailController;
use App\Http\Controllers\Api\SocieteController;
// Test/Health
Route::get('/test', function () {
    return response()->json(['message' => 'API Laravel fonctionne!', 'status' => 'success']);
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// User Auth route
Route::get('/user',[UserController::class,'alluser']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::patch('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

// Contacts manuels
Route::get('/b2b_manual', [B2BManualController::class, 'index']);
Route::post('/b2b_manual', [B2BManualController::class, 'store']);
Route::patch('/b2b_manual/{id}', [B2BManualController::class, 'update']);
Route::delete('/b2b_manual/{id}', [B2BManualController::class, 'destroy']);

//contact datasync
Route::get('/b2b_datasynch', [B2BDatasyncController::class, 'index']);
Route::post('/b2b_datasynch', [B2BDatasyncController::class, 'store']);
Route::patch('/b2b_datasynch/{id}', [B2BDatasyncController::class, 'update']);
Route::delete('/b2b_datasynch/{id}', [B2BDatasyncController::class, 'destroy']);

// Prompts
Route::get('/prompt', [Promptmail_autoprospectionController::class, 'index']);
Route::post('/prompt', [Promptmail_autoprospectionController::class, 'store']);
Route::patch('/prompt/{id}', [Promptmail_autoprospectionController::class, 'update']);
Route::delete('/prompt/{id}', [Promptmail_autoprospectionController::class, 'destroy']);

// Mails générés
Route::get('/mailsgeneres', [MailsgeneresController::class, 'index']);
Route::post('/mailsgeneres', [MailsgeneresController::class, 'store']);
Route::patch('/mailsgeneres/{id}', [MailsgeneresController::class, 'update']);
Route::delete('/mailsgeneres/{id}', [MailsgeneresController::class, 'destroy']);

// Historique envoi masse
Route::get('/envoiemassehisto', [B2bAutoproEnvoiemasseHistoController::class, 'index']);
Route::post('/envoiemassehisto', [B2bAutoproEnvoiemasseHistoController::class, 'store']);
Route::patch('/envoiemassehisto/{id}', [B2bAutoproEnvoiemasseHistoController::class, 'update']);
Route::delete('/envoiemassehisto/{id}', [B2bAutoproEnvoiemasseHistoController::class, 'destroy']);

// status en temps réel
Route::get('/realtimestatus', [RealTimeStatusController::class, 'index']);
Route::get('/status/{id}', [RealTimeStatusController::class, 'status']);
Route::post('/realtimestatus', [RealTimeStatusController::class, 'store']);
Route::patch('/realtimestatus/{id}', [RealTimeStatusController::class, 'update']);
Route::delete('/realtimestatus/{id}', [RealTimeStatusController::class, 'destroy']);

// Mails de reponse
Route::get('/mailsreponses', [MailsReponsesController::class, 'index']);
Route::post('/mailsreponses', [MailsReponsesController::class, 'store']);
Route::patch('/mailsreponses/{id}', [MailsReponsesController::class, 'update']);
Route::delete('/mailsreponses/{id}', [MailsReponsesController::class, 'destroy']);

// statut de Mails de reponse
Route::get('/mailsreponsesstatus', [MailsReponsesStatusController::class, 'index']);
Route::post('/mailsreponsesstatus', [MailsReponsesStatusController::class, 'store']);
Route::patch('/mailsreponsesstatus/{id}', [MailsReponsesStatusController::class, 'update']);
Route::delete('/mailsreponsesstatus/{id}', [MailsReponsesStatusController::class, 'destroy']);

// relance prompt
Route::get('/relance-prompt', [RelancePromptController::class, 'index']);
Route::post('/relance-prompt', [RelancePromptController::class, 'store']);
Route::patch('/relance-prompt/{id}', [RelancePromptController::class, 'update']);
Route::delete('/relance-prompt/{id}', [RelancePromptController::class, 'destroy']);

// relance mails générés
Route::get('/relance-mailsgen', [RelanceMailsgeneresController::class, 'index']);
Route::post('/relance-mailsgen', [RelanceMailsgeneresController::class, 'store']);
Route::patch('/relance-mailsgen/{id}', [RelanceMailsgeneresController::class, 'update']);
Route::delete('/relance-mailsgen/{id}', [RelanceMailsgeneresController::class, 'destroy']);

// B2b response mail
Route::get('/b2b_mailsreponses', [B2b_response_mailController::class, 'index']);
Route::post('/b2b_mailsreponses', [B2b_response_mailController::class, 'store']);
Route::patch('/b2b_mailsreponses/{id}', [B2b_response_mailController::class, 'update']);
Route::delete('/b2b_mailsreponses/{id}', [B2b_response_mailController::class, 'destroy']);

// Prospects
Route::get('/prospects', [ProspectController::class, 'index']);
Route::get('/prospects/{id}', [ProspectController::class, 'show']);
Route::post('/prospects', [ProspectController::class, 'store']);
Route::put('/prospects/{id}', [ProspectController::class, 'update']);
Route::delete('/prospects/{id}', [ProspectController::class, 'destroy']);

// Users (apiResource + verify-password)
Route::apiResource('/users', UserController::class);
Route::post('/users/{id}/verify-password', [UserController::class, 'verifyPassword']);
Route::post('/users', [UserController::class,'store']);
Route::patch('/user/{id}', [UserController::class, 'updateUser']);




// Settings
Route::get('/settings', [SettingController::class, 'index']);
Route::put('/settings', [SettingController::class, 'update']);

// AI Email Responses
Route::apiResource('ai-email-responses', AiEmailResponsesController::class)->only(['index', 'store', 'update']);

// Prompts Relance
Route::apiResource('prompts-relance', PromptsRelanceController::class);

// Contacts
Route::apiResource('contacts', ContactController::class);
Route::post('/contacts/import', [ContactController::class, 'import']);
Route::get('/contacts/export', [ContactController::class, 'export']);
Route::get('/source-stats', [ContactController::class, 'sourceStats']);

// Supabase
Route::get('/test-supabase', [SyncController::class, 'testSupabase']);
Route::post('/sync-to-supabase', [SyncController::class, 'syncToSupabase']);

// Reponses
Route::apiResource('reponses', ResponseController::class)->only(['index', 'show', 'update']);

//contact societe
Route::get('/societe', [SocieteController::class, 'index']);//recuperation

