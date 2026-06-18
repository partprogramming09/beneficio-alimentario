<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Student\Models\Estudiante;
use App\Modules\Attendance\Models\Asistencia;
use App\Modules\Webhook\Services\WebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceRuleService
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Evalúa y actualiza el estado de suspensión de un estudiante si acumula 3 faltas consecutivas.
     * 
     * @param string $documento
     * @return bool Retorna true si el estudiante fue suspendido, false en caso contrario.
     */
    public function evaluateSuspension(string $documento): bool
    {
        $estudiante = Estudiante::find($documento);

        if (!$estudiante || $estudiante->estado !== 'Activo') {
            return false;
        }

        // Obtener los últimos 3 días distintos en los que el comedor escolar prestó servicio
        $diasServicio = Asistencia::select('fecha')
            ->groupBy('fecha')
            ->orderBy('fecha', 'desc')
            ->limit(3)
            ->pluck('fecha')
            ->toArray();

        // Si el comedor ha operado menos de 3 días en total, no se puede sancionar todavía
        if (count($diasServicio) < 3) {
            return false;
        }

        // Verificar si el estudiante asistió a alguno de estos 3 días de servicio
        $asistenciasCount = Asistencia::where('documento', $documento)
            ->whereIn('fecha', $diasServicio)
            ->count();

        // Si asistió a 0 días de los últimos 3 días de servicio, se procede con la suspensión
        if ($asistenciasCount === 0) {
            $estudiante->update(['estado' => 'Suspendido']);

            Log::warning("Estudiante {$documento} suspendido automáticamente por 3 inasistencias consecutivas.");

            // Disparar Webhook
            $this->webhookService->trigger('student.suspended', [
                'documento' => $estudiante->documento,
                'nombre_completo' => "{$estudiante->nombres} {$estudiante->apellidos}",
                'grupo' => $estudiante->grupo,
                'estado' => $estudiante->estado,
                'motivo' => 'Acumulación de 3 inasistencias consecutivas en los días de servicio del comedor.',
                'fecha_suspension' => date('Y-m-d'),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Evalúa y actualiza la suspensión para TODOS los estudiantes en estado Activo.
     * Útil al simular días escolares o cierres de jornada.
     */
    public function evaluateAllSuspensions(): array
    {
        $estudiantesActivos = Estudiante::where('estado', 'Activo')->get();
        $suspendidos = [];

        foreach ($estudiantesActivos as $estudiante) {
            if ($this->evaluateSuspension($estudiante->documento)) {
                $suspendidos[] = $estudiante->documento;
            }
        }

        return $suspendidos;
    }
}
