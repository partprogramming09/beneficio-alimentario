<?php

namespace App\Modules\Admin\Services;

use App\Modules\Student\Models\Estudiante;
use App\Modules\Attendance\Models\Asistencia;
use App\Modules\Attendance\Models\Comprobante;
use App\Modules\Admin\Models\Justificacion;
use App\Modules\Attendance\Services\AttendanceRuleService;
use App\Modules\Webhook\Services\WebhookService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminService
{
    protected $webhookService;
    protected $ruleService;

    public function __construct(WebhookService $webhookService, AttendanceRuleService $ruleService)
    {
        $this->webhookService = $webhookService;
        $this->ruleService = $ruleService;
    }

    /**
     * Lista todos los beneficiarios en el sistema.
     */
    public function getStudents()
    {
        return Estudiante::orderBy('creado_en', 'desc')->get();
    }

    /**
     * Lista todas las justificaciones con datos del estudiante.
     */
    public function getJustifications()
    {
        return DB::table('justificaciones')
            ->join('estudiantes', 'justificaciones.documento', '=', 'estudiantes.documento')
            ->select(
                'justificaciones.*',
                'estudiantes.nombres',
                'estudiantes.apellidos',
                'estudiantes.grupo',
                'estudiantes.estado as estado_estudiante'
            )
            ->orderBy('justificaciones.creado_en', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'documento' => $item->documento,
                    'nombres' => $item->nombres,
                    'apellidos' => $item->apellidos,
                    'grupo' => $item->grupo,
                    'fecha_inasistencia' => $item->fecha_inasistencia,
                    'motivo' => $item->motivo,
                    'estado' => $item->estado_estudiante, // Retornar estado actual del alumno para UI
                ];
            });
    }

    /**
     * Aprueba la solicitud de inscripción de un estudiante.
     */
    public function approveStudent(string $documento): array
    {
        $estudiante = Estudiante::find($documento);

        if (!$estudiante) {
            throw new Exception("Estudiante no encontrado.");
        }

        if ($estudiante->estado !== 'Pendiente') {
            throw new Exception("El estudiante no se encuentra en estado pendiente.");
        }

        $estudiante->update(['estado' => 'Activo']);

        // Disparar Webhook
        $this->webhookService->trigger('student.approved', [
            'documento' => $estudiante->documento,
            'nombre_completo' => "{$estudiante->nombres} {$estudiante->apellidos}",
            'grupo' => $estudiante->grupo,
            'estado' => $estudiante->estado,
        ]);

        return [
            'message' => "Estudiante {$estudiante->nombres} aprobado con éxito en el programa.",
            'estudiante' => $estudiante,
        ];
    }

    /**
     * Rechaza la solicitud de inscripción de un estudiante (elimina el perfil pendiente).
     */
    public function rejectStudent(string $documento): array
    {
        $estudiante = Estudiante::find($documento);

        if (!$estudiante) {
            throw new Exception("Estudiante no encontrado.");
        }

        if ($estudiante->estado !== 'Pendiente') {
            throw new Exception("Solo se pueden rechazar solicitudes en estado pendiente.");
        }

        // Datos para el webhook antes de eliminar
        $payload = [
            'documento' => $estudiante->documento,
            'nombre_completo' => "{$estudiante->nombres} {$estudiante->apellidos}",
            'grupo' => $estudiante->grupo,
            'estado' => 'Rechazado',
        ];

        $estudiante->delete();

        // Disparar Webhook
        $this->webhookService->trigger('student.rejected', $payload);

        return [
            'message' => 'Solicitud de beneficio rechazada y eliminada del sistema.',
        ];
    }

    /**
     * Elimina permanentemente a un estudiante del beneficio.
     */
    public function deleteStudent(string $documento): array
    {
        $estudiante = Estudiante::find($documento);

        if (!$estudiante) {
            throw new Exception("Estudiante no encontrado.");
        }

        $estudiante->delete();

        return [
            'message' => 'Estudiante eliminado permanentemente de la base de datos.',
        ];
    }

    /**
     * Reactiva a un estudiante suspendido aprobando sus excusas.
     */
    public function reactivateStudent(string $documento): array
    {
        $estudiante = Estudiante::find($documento);

        if (!$estudiante) {
            throw new Exception("Estudiante no encontrado.");
        }

        if ($estudiante->estado !== 'Suspendido') {
            throw new Exception("El estudiante no se encuentra en estado suspendido.");
        }

        // Cambiar estado a Activo
        $estudiante->update(['estado' => 'Activo']);

        // Aprobar todas las justificaciones pendientes de este estudiante
        Justificacion::where('documento', $documento)
            ->where('estado', 'Pendiente')
            ->update(['estado' => 'Aprobado']);

        // Disparar Webhook
        $this->webhookService->trigger('student.approved', [
            'documento' => $estudiante->documento,
            'nombre_completo' => "{$estudiante->nombres} {$estudiante->apellidos}",
            'grupo' => $estudiante->grupo,
            'estado' => $estudiante->estado,
            'reactivado' => true,
        ]);

        return [
            'message' => "Estudiante {$estudiante->nombres} reactivado con éxito en el sistema.",
            'estudiante' => $estudiante,
        ];
    }

    /**
     * Registra una justificación de inasistencia para un estudiante.
     */
    public function submitJustification(array $data): Justificacion
    {
        $documento = $data['documento'];
        $estudiante = Estudiante::find($documento);

        if (!$estudiante) {
            throw new Exception("El estudiante no se encuentra registrado en el sistema.");
        }

        // Crear Justificación
        return Justificacion::create([
            'documento' => $documento,
            'fecha_inasistencia' => $data['fecha_inasistencia'],
            'motivo' => $data['motivo'],
            'estado' => 'Pendiente',
        ]);
    }

    /**
     * Consulta el reporte de asistencia diaria.
     */
    public function getDailyReport(string $fecha): array
    {
        return DB::table('asistencias')
            ->join('estudiantes', 'asistencias.documento', '=', 'estudiantes.documento')
            ->where('asistencias.fecha', $fecha)
            ->select(
                'asistencias.id',
                'asistencias.documento',
                'asistencias.hora',
                'estudiantes.nombres',
                'estudiantes.apellidos',
                'estudiantes.grupo'
            )
            ->orderBy('asistencias.hora', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'documento' => $row->documento,
                    'nombres' => $row->nombres,
                    'apellidos' => $row->apellidos,
                    'grupo' => $row->grupo,
                    'hora' => $row->hora,
                ];
            })
            ->toArray();
    }

    /**
     * Genera un reporte acumulado de los almuerzos de los últimos 7 días de servicio.
     */
    public function getWeeklyReport(): array
    {
        // Obtener los últimos 7 días con asistencias distintas
        $diasServicio = Asistencia::select('fecha')
            ->groupBy('fecha')
            ->orderBy('fecha', 'desc')
            ->limit(7)
            ->pluck('fecha')
            ->toArray();

        if (empty($diasServicio)) {
            return [
                'dateList' => [],
                'report' => [],
            ];
        }

        // Obtener el conteo por estudiante
        $reporte = DB::table('asistencias')
            ->join('estudiantes', 'asistencias.documento', '=', 'estudiantes.documento')
            ->whereIn('asistencias.fecha', $diasServicio)
            ->select(
                'asistencias.documento',
                'estudiantes.nombres',
                'estudiantes.apellidos',
                'estudiantes.grupo',
                DB::raw('COUNT(asistencias.id) as total_asistencias')
            )
            ->groupBy('asistencias.documento', 'estudiantes.nombres', 'estudiantes.apellidos', 'estudiantes.grupo')
            ->orderBy('total_asistencias', 'desc')
            ->get()
            ->map(function ($row) {
                return [
                    'documento' => $row->documento,
                    'nombres' => $row->nombres,
                    'apellidos' => $row->apellidos,
                    'grupo' => $row->grupo,
                    'total_asistencias' => (int) $row->total_asistencias,
                ];
            })
            ->toArray();

        return [
            'dateList' => array_reverse($diasServicio),
            'report' => $reporte,
        ];
    }

    /**
     * Simula la marcación de asistencia de un día escolar completo y ejecuta reglas de suspensión.
     */
    public function simulateDay(string $fecha, array $asistentes): array
    {
        $horaSimulada = '12:00:00';
        $suspendidosPre = Estudiante::where('estado', 'Suspendido')->pluck('documento')->toArray();

        DB::transaction(function () use ($fecha, $asistentes, $horaSimulada) {
            foreach ($asistentes as $doc) {
                $estudiante = Estudiante::find($doc);

                // Solo asisten si están registrados y no inactivos
                if ($estudiante && $estudiante->estado !== 'Inactivo') {
                    // Evitar duplicar asistencia si ya existiera para esta fecha
                    $existe = Asistencia::where('documento', $doc)->where('fecha', $fecha)->exists();
                    if (!$existe) {
                        // Crear asistencia
                        Asistencia::create([
                            'documento' => $doc,
                            'fecha' => $fecha,
                            'hora' => $horaSimulada,
                        ]);

                        // Crear comprobante
                        Comprobante::create([
                            'documento' => $doc,
                            'fecha' => $fecha,
                            'hora' => $horaSimulada,
                            'codigo' => 'ALM-SIM-' . strtoupper(Str::random(4)) . '-' . $doc,
                        ]);
                    }
                }
            }

            // Evaluar y sancionar suspensiones automáticamente para todos los estudiantes
            $this->ruleService->evaluateAllSuspensions();
        });

        // Obtener los que fueron suspendidos en esta simulación (que no estaban suspendidos previamente)
        $suspendidosPost = Estudiante::where('estado', 'Suspendido')->pluck('documento')->toArray();
        $nuevosSuspendidosDocs = array_diff($suspendidosPost, $suspendidosPre);

        $nuevosSuspendidos = Estudiante::whereIn('documento', $nuevosSuspendidosDocs)->get()->map(function ($s) {
            return "{$s->nombres} {$s->apellidos} (Doc: {$s->documento})";
        })->toArray();

        $mensaje = "Simulación ejecutada con éxito para la fecha {$fecha}. Se registraron " . count($asistentes) . " asistencias.";
        if (count($nuevosSuspendidos) > 0) {
            $mensaje .= " Estudiantes suspendidos automáticamente por acumular 3 inasistencias: " . implode(', ', $nuevosSuspendidos) . ".";
        } else {
            $mensaje .= " No hubo nuevas suspensiones.";
        }

        return [
            'message' => $mensaje,
            'suspendidos' => $nuevosSuspendidosDocs,
        ];
    }

    /**
     * Registra un estudiante individual en la institución y activa su perfil de beneficiario.
     */
    public function createSingleStudent(array $data): array
    {
        $documento = trim($data['documento']);
        $nombres = trim($data['nombres']);
        $apellidos = trim($data['apellidos']);
        $grupo = trim($data['grupo']);
        $nombreCompleto = "{$nombres} {$apellidos}";

        // 1. Guardar en la tabla institucional de matriculados
        InstitucionEstudiante::updateOrCreate(
            ['documento' => $documento],
            [
                'nombre_completo' => $nombreCompleto,
                'grupo' => $grupo,
            ]
        );

        // 2. Registrar o actualizar en la lista activa de beneficiarios
        $estudiante = Estudiante::updateOrCreate(
            ['documento' => $documento],
            [
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'grupo' => $grupo,
                'estado' => 'Activo',
            ]
        );

        return [
            'message' => "Estudiante {$nombreCompleto} (Doc: {$documento}) registrado y activado con éxito.",
            'estudiante' => $estudiante,
        ];
    }

    /**
     * Carga masiva de estudiantes desde Excel/CSV.
     */
    public function importBulkStudents(array $studentsData): array
    {
        $insertados = 0;
        $actualizados = 0;

        DB::transaction(function () use ($studentsData, &$insertados, &$actualizados) {
            foreach ($studentsData as $item) {
                if (empty($item['documento'])) continue;

                $documento = trim((string)$item['documento']);
                $nombres = trim($item['nombres'] ?? '');
                $apellidos = trim($item['apellidos'] ?? '');
                
                if (empty($nombres) && !empty($item['nombre_completo'])) {
                    $parts = explode(' ', trim($item['nombre_completo']), 2);
                    $nombres = $parts[0] ?? '';
                    $apellidos = $parts[1] ?? '';
                }

                $nombreCompleto = trim("{$nombres} {$apellidos}");
                if (empty($nombreCompleto)) {
                    $nombreCompleto = trim($item['nombre_completo'] ?? $documento);
                }

                $grupo = trim($item['grupo'] ?? 'Sin Grupo');

                $institucion = InstitucionEstudiante::find($documento);
                if ($institucion) {
                    $actualizados++;
                } else {
                    $insertados++;
                }

                InstitucionEstudiante::updateOrCreate(
                    ['documento' => $documento],
                    [
                        'nombre_completo' => $nombreCompleto,
                        'grupo' => $grupo,
                    ]
                );

                Estudiante::updateOrCreate(
                    ['documento' => $documento],
                    [
                        'nombres' => $nombres ?: $nombreCompleto,
                        'apellidos' => $apellidos ?: '',
                        'grupo' => $grupo,
                        'estado' => 'Activo',
                    ]
                );
            }
        });

        return [
            'message' => "Importación masiva completada. Procesados: " . ($insertados + $actualizados) . " ({$insertados} nuevos, {$actualizados} actualizados).",
            'total' => $insertados + $actualizados,
            'insertados' => $insertados,
            'actualizados' => $actualizados,
        ];
    }
}

