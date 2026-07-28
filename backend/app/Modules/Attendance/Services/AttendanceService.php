<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Student\Models\Estudiante;
use App\Modules\Attendance\Models\Asistencia;
use App\Modules\Attendance\Models\Comprobante;
use App\Modules\Webhook\Services\WebhookService;
use Exception;
use Illuminate\Support\Str;

class AttendanceService
{
    protected $webhookService;
    protected $ruleService;

    public function __construct(WebhookService $webhookService, AttendanceRuleService $ruleService)
    {
        $this->webhookService = $webhookService;
        $this->ruleService = $ruleService;
    }

    /**
     * Registra la asistencia diaria para un estudiante y genera su comprobante.
     */
    public function markAttendance(string $documento): array
    {
        $estudiante = Estudiante::find($documento);

        if (!$estudiante) {
            throw new Exception("El estudiante no se encuentra registrado en el sistema de beneficios.");
        }

        // Validar estado del beneficiario
        if ($estudiante->estado === 'Pendiente') {
            throw new Exception("Tu registro está pendiente de aprobación por la coordinadora.");
        }

        if ($estudiante->estado === 'Suspendido') {
            throw new Exception("Tu beneficio está suspendido por inasistencias. Debes enviar una justificación a la coordinadora.");
        }

        if ($estudiante->estado === 'Inactivo') {
            throw new Exception("Tu beneficio se encuentra inactivo.");
        }

        $hoy = date('Y-m-d');
        $hora = date('H:i:s');

        // Validar si es día escolar hábil en Colombia (excluye fines de semana y festivos Ley Emiliani)
        if (!\App\Services\ColombianCalendarService::isSchoolDay($hoy)) {
            throw new Exception("Hoy es un día no hábil en Colombia (fin de semana o día festivo). El comedor escolar no presta servicio.");
        }

        // Validar si ya registró asistencia hoy

        $asistenciaExistente = Asistencia::where('documento', $documento)
            ->where('fecha', $hoy)
            ->first();

        if ($asistenciaExistente) {
            throw new Exception("Ya has registrado tu asistencia el día de hoy.");
        }

        // Registrar Asistencia
        $asistencia = Asistencia::create([
            'documento' => $documento,
            'fecha' => $hoy,
            'hora' => $hora,
        ]);

        // Generar Código Único del Comprobante
        $codigoTicket = 'ALM-' . strtoupper(Str::random(6)) . '-' . $documento;

        // Crear Comprobante
        $comprobante = Comprobante::create([
            'documento' => $documento,
            'fecha' => $hoy,
            'hora' => $hora,
            'codigo' => $codigoTicket,
        ]);

        $comprobanteData = [
            'nombre' => "{$estudiante->nombres} {$estudiante->apellidos}",
            'grupo' => $estudiante->grupo,
            'fecha' => $hoy,
            'hora' => $hora,
            'codigo' => $codigoTicket,
        ];

        // Disparar Webhook
        $this->webhookService->trigger('attendance.marked', [
            'documento' => $documento,
            'fecha' => $hoy,
            'hora' => $hora,
            'comprobante' => $comprobanteData,
        ]);

        return [
            'message' => 'Asistencia registrada con éxito. ¡Buen provecho!',
            'comprobante' => $comprobanteData,
        ];
    }

    /**
     * Recupera el comprobante de asistencia del estudiante para el día de hoy.
     */
    public function getTodayReceipt(string $documento): array
    {
        $estudiante = Estudiante::find($documento);

        if (!$estudiante) {
            throw new Exception("El estudiante no se encuentra registrado en el sistema.");
        }

        $hoy = date('Y-m-d');

        $comprobante = Comprobante::where('documento', $documento)
            ->where('fecha', $hoy)
            ->first();

        if (!$comprobante) {
            throw new Exception("No se encontró ningún comprobante emitido para el día de hoy.");
        }

        return [
            'nombre' => "{$estudiante->nombres} {$estudiante->apellidos}",
            'grupo' => $estudiante->grupo,
            'fecha' => $comprobante->fecha,
            'hora' => $comprobante->hora,
            'codigo' => $comprobante->codigo,
        ];
    }
}
