<?php

namespace App\Modules\Webhook\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Webhook\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class WebhookController extends Controller
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Lista todas las suscripciones de webhooks.
     */
    public function listar()
    {
        try {
            $suscripciones = $this->webhookService->listSubscriptions();
            return response()->json($suscripciones);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Registra o actualiza una suscripción de webhook.
     */
    public function registrar(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'eventos' => 'required|array',
        ]);

        try {
            $webhook = $this->webhookService->registerSubscription(
                $request->input('url'),
                $request->input('eventos')
            );
            return response()->json([
                'message' => 'Suscripción de webhook registrada con éxito.',
                'webhook' => $webhook,
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Elimina una suscripción de webhook.
     */
    public function eliminar(int $id)
    {
        try {
            $this->webhookService->deleteSubscription($id);
            return response()->json(['message' => 'Suscripción de webhook eliminada con éxito.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Endpoint receptor de pruebas local (simula sistema externo receptor).
     */
    public function recibirPrueba(Request $request)
    {
        // Guardar en logs locales para depuración y testing
        Log::channel('single')->info('--- WEBHOOK DE PRUEBA RECIBIDO ---', $request->all());

        return response()->json([
            'status' => 'recibido',
            'message' => 'El webhook fue procesado con éxito por el receptor de pruebas.',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
