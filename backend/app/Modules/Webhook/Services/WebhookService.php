<?php

namespace App\Modules\Webhook\Services;

use App\Modules\Webhook\Models\Webhook;
use App\Modules\Webhook\Jobs\SendWebhookJob;
use Illuminate\Support\Facades\Log;
use Exception;

class WebhookService
{
    /**
     * Dispara un evento enviándolo a todas las URLs suscritas.
     */
    public function trigger(string $event, array $payload): void
    {
        try {
            $subscriptions = Webhook::all();

            foreach ($subscriptions as $sub) {
                // Verificar si el webhook está suscrito a este evento
                if (is_array($sub->eventos) && in_array($event, $sub->eventos)) {
                    Log::info("Encolando webhook para {$sub->url} | Evento: {$event}");
                    
                    // Despachar el Job a la cola para ejecución asíncrona
                    SendWebhookJob::dispatch($sub->url, $event, $payload);
                }
            }
        } catch (Exception $e) {
            Log::error("Falla al disparar el webhook para el evento {$event}: " . $e->getMessage());
        }
    }

    /**
     * Registra una nueva suscripción a webhooks.
     */
    public function registerSubscription(string $url, array $eventos): Webhook
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("La URL provista no es válida.");
        }

        if (empty($eventos)) {
            throw new Exception("Debes especificar al menos un evento para suscribirte.");
        }

        // Validar eventos admitidos
        $eventosValidos = [
            'student.registered', 
            'student.approved', 
            'student.rejected', 
            'student.suspended', 
            'student.renounced',
            'attendance.marked'
        ];

        foreach ($eventos as $evt) {
            if (!in_array($evt, $eventosValidos)) {
                throw new Exception("Evento no válido: '{$evt}'.");
            }
        }

        // Verificar si la URL ya existe para actualizarla o crearla
        $webhook = Webhook::where('url', $url)->first();

        if ($webhook) {
            $webhook->update(['eventos' => $eventos]);
        } else {
            $webhook = Webhook::create([
                'url' => $url,
                'eventos' => $eventos,
            ]);
        }

        return $webhook;
    }

    /**
     * Lista todas las suscripciones de webhooks.
     */
    public function listSubscriptions()
    {
        return Webhook::all();
    }

    /**
     * Elimina una suscripción de webhook.
     */
    public function deleteSubscription(int $id): void
    {
        $webhook = Webhook::find($id);

        if (!$webhook) {
            throw new Exception("Suscripción de webhook no encontrada.");
        }

        $webhook->delete();
    }
}
