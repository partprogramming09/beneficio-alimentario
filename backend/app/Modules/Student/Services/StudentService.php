<?php

namespace App\Modules\Student\Services;

use App\Modules\Student\Models\InstitucionEstudiante;
use App\Modules\Student\Models\Estudiante;
use App\Modules\Webhook\Services\WebhookService;
use Exception;

class StudentService
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Valida si un estudiante está matriculado y no está registrado en el beneficio.
     */
    public function validateStudent(string $documento): array
    {
        $institucion = InstitucionEstudiante::find($documento);

        if (!$institucion) {
            throw new Exception("El documento ingresado no se encuentra matriculado en la institución.");
        }

        $existente = Estudiante::find($documento);

        if ($existente) {
            throw new Exception("Este estudiante ya cuenta con un perfil registrado en el sistema. Estado: {$existente->estado}.");
        }

        return [
            'documento' => $institucion->documento,
            'nombre_completo' => $institucion->nombre_completo,
            'grupo' => $institucion->grupo,
        ];
    }

    /**
     * Crea un perfil de beneficiario para el estudiante.
     */
    public function registerProfile(array $data): Estudiante
    {
        $documento = $data['documento'];
        
        // Validar matrícula y existencia previa
        $valido = $this->validateStudent($documento);

        $estudiante = Estudiante::create([
            'documento' => $documento,
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'grupo' => $valido['grupo'],
            'estado' => 'Pendiente', // Inicia en espera de aprobación de la coordinadora
        ]);

        // Disparar Webhook
        $this->webhookService->trigger('student.registered', [
            'documento' => $estudiante->documento,
            'nombres' => $estudiante->nombres,
            'apellidos' => $estudiante->apellidos,
            'grupo' => $estudiante->grupo,
            'estado' => $estudiante->estado,
            'creado_en' => date('Y-m-d H:i:s'),
        ]);

        return $estudiante;
    }

    /**
     * Procesa la renuncia voluntaria al beneficio alimentario.
     */
    public function renounceBenefit(string $documento): array
    {
        $estudiante = Estudiante::find($documento);

        if (!$estudiante) {
            throw new Exception("Estudiante no encontrado.");
        }

        if ($estudiante->estado === 'Inactivo') {
            throw new Exception("El estudiante ya se encuentra en estado inactivo.");
        }

        $estudiante->update(['estado' => 'Inactivo']);

        // Disparar Webhook
        $this->webhookService->trigger('student.renounced', [
            'documento' => $estudiante->documento,
            'nombre_completo' => "{$estudiante->nombres} {$estudiante->apellidos}",
            'grupo' => $estudiante->grupo,
            'estado' => $estudiante->estado,
        ]);

        return [
            'message' => 'Has renunciado voluntariamente al beneficio alimentario. El cupo ha sido liberado.',
            'estudiante' => $estudiante,
        ];
    }
}
