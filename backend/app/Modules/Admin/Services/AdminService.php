<?php

namespace App\Modules\Admin\Services;

use App\Modules\Student\Models\Estudiante;
use App\Modules\Student\Models\InstitucionEstudiante;
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
        // Validar si la fecha es un día escolar hábil en Colombia
        if (!\App\Services\ColombianCalendarService::isSchoolDay($fecha)) {
            throw new Exception("La fecha {$fecha} cae en un fin de semana o día festivo en Colombia. El comedor escolar no opera en días no hábiles.");
        }

        $horaSimulada = '12:00:00';
        $suspendidosPre = Estudiante::where('estado', 'Suspendido')->pluck('documento')->toArray();

        DB::transaction(function () use ($fecha, $asistentes, $horaSimulada) {
            foreach ($asistentes as $doc) {
                $estudiante = Estudiante::find($doc);

                if ($estudiante && $estudiante->estado !== 'Inactivo') {
                    $existe = Asistencia::where('documento', $doc)->where('fecha', $fecha)->exists();
                    if (!$existe) {
                        Asistencia::create([
                            'documento' => $doc,
                            'fecha' => $fecha,
                            'hora' => $horaSimulada,
                        ]);

                        Comprobante::create([
                            'documento' => $doc,
                            'fecha' => $fecha,
                            'hora' => $horaSimulada,
                            'codigo' => 'ALM-SIM-' . strtoupper(Str::random(4)) . '-' . $doc,
                        ]);
                    }
                }
            }

            $this->ruleService->evaluateAllSuspensions();
        });

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
     * Registra un estudiante individual en la lista institucional.
     * El estudiante activará su perfil al registrarse voluntariamente en el portal.
     */
    public function createSingleStudent(array $data): array
    {
        $documento = trim($data['documento']);
        $nombres = trim($data['nombres']);
        $apellidos = trim($data['apellidos']);
        $grupo = trim($data['grupo']);
        $nombreCompleto = "{$nombres} {$apellidos}";

        $institucion = InstitucionEstudiante::updateOrCreate(
            ['documento' => $documento],
            [
                'nombre_completo' => $nombreCompleto,
                'grupo' => $grupo,
            ]
        );

        return [
            'message' => "Estudiante {$nombreCompleto} (Doc: {$documento}) registrado en la lista oficial de la institución. El estudiante cambiará su estado al realizar su inscripción en el portal.",
            'institucion' => $institucion,
        ];
    }

    /**
     * Carga masiva de la lista de matriculados institucionales desde Excel/CSV.
     * Los estudiantes cambiarán a estado 'Activo' cuando realicen su registro personal.
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
            }
        });

        return [
            'message' => "Importación masiva completada con éxito. Registros institucionales guardados: " . ($insertados + $actualizados) . " ({$insertados} nuevos, {$actualizados} actualizados). Los estudiantes podrán ingresar su documento en el portal para inscribirse.",
            'total' => $insertados + $actualizados,
            'insertados' => $insertados,
            'actualizados' => $actualizados,
        ];
    }

    /**
     * Permite a la coordinadora activar directamente a un estudiante de forma manual.
     */
    public function activateStudentManually(string $documento): array
    {
        $institucion = InstitucionEstudiante::find($documento);
        if (!$institucion) {
            throw new Exception("El estudiante no se encuentra en la lista institucional.");
        }

        $parts = explode(' ', trim($institucion->nombre_completo), 2);
        $nombres = $parts[0] ?? $institucion->nombre_completo;
        $apellidos = $parts[1] ?? '';

        $estudiante = Estudiante::updateOrCreate(
            ['documento' => $documento],
            [
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'grupo' => $institucion->grupo,
                'estado' => 'Activo',
            ]
        );

        return [
            'message' => "Estudiante {$institucion->nombre_completo} activado directamente por la coordinadora.",
            'estudiante' => $estudiante,
        ];
    }

    /**
     * Edita los datos de un estudiante matriculado (documento, nombre completo, grupo).
     */
    public function updateStudent(array $data): array
    {
        $docOriginal = trim($data['documento_original']);
        $docNuevo = trim($data['documento']);
        $nombreCompleto = trim($data['nombre_completo']);
        $grupo = trim($data['grupo']);

        $institucion = InstitucionEstudiante::find($docOriginal);
        if (!$institucion) {
            throw new Exception("Estudiante no encontrado en la lista de la institución.");
        }

        // Si cambió el número de documento
        if ($docOriginal !== $docNuevo) {
            InstitucionEstudiante::where('documento', $docOriginal)->delete();
            InstitucionEstudiante::create([
                'documento' => $docNuevo,
                'nombre_completo' => $nombreCompleto,
                'grupo' => $grupo,
            ]);

            $est = Estudiante::find($docOriginal);
            if ($est) {
                $estado = $est->estado;
                $est->delete();
                $parts = explode(' ', $nombreCompleto, 2);
                Estudiante::create([
                    'documento' => $docNuevo,
                    'nombres' => $parts[0] ?? $nombreCompleto,
                    'apellidos' => $parts[1] ?? '',
                    'grupo' => $grupo,
                    'estado' => $estado,
                ]);
            }
        } else {
            $institucion->update([
                'nombre_completo' => $nombreCompleto,
                'grupo' => $grupo,
            ]);

            $est = Estudiante::find($docOriginal);
            if ($est) {
                $parts = explode(' ', $nombreCompleto, 2);
                $est->update([
                    'nombres' => $parts[0] ?? $nombreCompleto,
                    'apellidos' => $parts[1] ?? '',
                    'grupo' => $grupo,
                ]);
            }
        }

        return [
            'message' => "Datos del estudiante {$nombreCompleto} (Doc: {$docNuevo}) actualizados correctamente.",
        ];
    }

    /**
     * Elimina a un estudiante de la lista institucional y de la lista de beneficiarios.
     */
    public function deleteInstitutionalStudent(string $documento): array
    {
        InstitucionEstudiante::where('documento', $documento)->delete();
        Estudiante::where('documento', $documento)->delete();

        return [
            'message' => "Estudiante con documento {$documento} eliminado correctamente de la institución.",
        ];
    }



    /**
     * Retorna la estructura organizada de Cursos y Grupos con detalle de inscritos / no inscritos.
     */
    public function getGroupedCourses(): array
    {
        $institucion = DB::table('institucion_estudiantes')->get();
        $beneficiarios = DB::table('estudiantes')->get()->keyBy('documento');

        $grupos = [];

        foreach ($institucion as $est) {
            $grp = $est->grupo ?: 'Sin Grupo';
            if (!isset($grupos[$grp])) {
                $grupos[$grp] = [
                    'nombre_grupo' => $grp,
                    'total_matriculados' => 0,
                    'total_inscritos' => 0,
                    'total_sin_inscribir' => 0,
                    'estudiantes' => [],
                ];
            }

            $beneficiario = $beneficiarios->get($est->documento);
            $estaInscrito = (bool) $beneficiario;
            $estado = $beneficiario ? $beneficiario->estado : 'Sin Registrar';

            $grupos[$grp]['total_matriculados']++;
            if ($estaInscrito && $estado === 'Activo') {
                $grupos[$grp]['total_inscritos']++;
            } else {
                $grupos[$grp]['total_sin_inscribir']++;
            }

            $grupos[$grp]['estudiantes'][] = [
                'documento' => $est->documento,
                'nombre_completo' => $est->nombre_completo,
                'grupo' => $grp,
                'esta_inscrito' => $estaInscrito,
                'estado' => $estado,
            ];
        }

        ksort($grupos);

        return array_values($grupos);
    }
}


