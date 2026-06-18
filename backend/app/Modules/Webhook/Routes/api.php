<?php

use App\Modules\Webhook\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/webhooks', [WebhookController::class, 'listar']);
Route::post('/webhooks', [WebhookController::class, 'registrar']);
Route::delete('/webhooks/{id}', [WebhookController::class, 'eliminar']);
Route::post('/test/webhook-receptor', [WebhookController::class, 'recibirPrueba']);
