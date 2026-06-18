<?php

namespace App\Modules\Webhook\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;
    protected $event;
    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(string $url, string $event, array $payload)
    {
        $this->url = $url;
        $this->event = $event;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = Http::timeout(5)->post($this->url, [
                'event' => $this->event,
                'data' => $this->payload,
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($response->failed()) {
                Log::warning("Webhook failed to deliver to {$this->url}. Status: {$response->status()}");
            } else {
                Log::info("Webhook delivered successfully to {$this->url} for event {$this->event}");
            }
        } catch (Exception $e) {
            Log::error("Error delivering webhook to {$this->url} for event {$this->event}: " . $e->getMessage());
            // Opcional: relanzar para que Laravel reintente según las políticas de cola
            throw $e;
        }
    }
}
