<?php

namespace App\Modules\Admin\Services;

use App\Modules\Student\Models\Estudiante;
use App\Modules\Student\Models\InstitucionEstudiante;
use App\Modules\Admin\Models\Justificacion;
use App\Modules\Webhook\Services\WebhookService;
use Exception;
use Illuminate\Support\Facades\DB;

class StudentManagementService
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Helper para separar nombre completo en nombres y apellidos sin corromper nombres compuestos.
     */
    public function formatNames(string $nombreCompleto): array
    {
        $partes = array_values(array_filter(explode(' ', trim($nombreCompleto))));
        $count = count($partes);

        if ($count === 0) {
            return ['nombres' => '', 'apellidos' => ''];
        } elseif ($count === 1) {
            return ['nombres' => $partes[0], 'apellidos' => ''];
        } elseif ($count === 2) {
            return ['nombres' => $partes[0], 'apellidos' => $partes[1]];
        } elseif ($count === 3) {
            return ['nombres' => "{$partes[0]} {$partes[1]}", 'apellidos' => $partes[2]];
        } else {
            $nombres = implode(' ', array_slice($partes, 0, $count - 2));
            $apellidos = implode(' ', array_slice($partes, $count - 2));
            return ['nombres' => $nombres, 'apellidos' => $apellidos];
        }
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
                    'estado' => $item->estado_estudiante,
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
     * Rechaza la solicitud de inscripción de un estudiante.
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

        $payload = [
            'documento' => $estudiante->documento,
            'nombre_completo' => "{$estudiante->nombres} {$estudiante->apellidos}",
            'grupo' => $estudiante->grupo,
            'estado' => 'Rechazado',
        ];

        $hasHistory = DB::table('asistencias')->where('documento', $documento)->exists();
        if ($hasHistory) {
            $estudiante->update(['estado' => 'Inactivo']);
        } else {
            $estudiante->delete();
        }

        $this->webhookService->trigger('student.rejected', $payload);

        return [
            'message' => 'Solicitud de beneficio rechazada.',
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

        $hasHistory = DB::table('asistencias')->where('documento', $documento)->exists();
        if ($hasHistory) {
            $estudiante->update(['estado' => 'Inactivo']);
            return [
                'message' => 'Estudiante desactivado preservando su historial de asistencias en la institución.',
            ];
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

        if ($estudiante->estado !== 'Suspendido' && $estudiante->estado !== 'Inactivo') {
            throw new Exception("El estudiante no se encuentra en estado suspendido o inactivo.");
        }

        $estudiante->update(['estado' => 'Activo']);

        Justificacion::where('documento', $documento)
            ->where('estado', 'Pendiente')
            ->update(['estado' => 'Aprobado']);

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

        return Justificacion::create([
            'documento' => $documento,
            'fecha_inasistencia' => $data['fecha_inasistencia'],
            'motivo' => $data['motivo'],
            'estado' => 'Pendiente',
        ]);
    }

    /**
     * Registra un estudiante individual en la lista institucional.
     */
    public function createSingleStudent(array $data): array
    {
        $documento = trim($data['documento']);
        $nombres = trim($data['nombres']);
        $apellidos = trim($data['apellidos']);
        $grupo = trim($data['grupo']);
        $nombreCompleto = trim("{$nombres} {$apellidos}");

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
                    $parsed = $this->formatNames($item['nombre_completo']);
                    $nombres = $parsed['nombres'];
                    $apellidos = $parsed['apellidos'];
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
            'message' => "Importación masiva completada con éxito. Registros institucionales guardados: " . ($insertados + $actualizados) . " ({$insertados} nuevos, {$actualizados} actualizados).",
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

        $parsed = $this->formatNames($institucion->nombre_completo);

        $estudiante = Estudiante::updateOrCreate(
            ['documento' => $documento],
            [
                'nombres' => $parsed['nombres'],
                'apellidos' => $parsed['apellidos'],
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
     * Asigna o remueve el cupo de beneficio alternando estados (Inactivo vs Activo/Pendiente) preservando el historial.
     */
    public function toggleCupo(string $documento): array
    {
        $institucion = InstitucionEstudiante::find($documento);
        if (!$institucion) {
            throw new Exception("El estudiante no se encuentra en la lista institucional.");
        }

        $existente = Estudiante::find($documento);

        if ($existente) {
            if ($existente->estado === 'Inactivo') {
                $existente->update(['estado' => 'Activo']);
                return [
                    'message' => "Cupo de beneficio reactivado para {$institucion->nombre_completo}.",
                    'tiene_cupo' => true,
                    'estado' => 'Activo',
                ];
            } else {
                $existente->update(['estado' => 'Inactivo']);
                return [
                    'message' => "Cupo de beneficio desactivado para {$institucion->nombre_completo}.",
                    'tiene_cupo' => false,
                    'estado' => 'Inactivo',
                ];
            }
        } else {
            $parsed = $this->formatNames($institucion->nombre_completo);

            $estudiante = Estudiante::create([
                'documento' => $documento,
                'nombres' => $parsed['nombres'],
                'apellidos' => $parsed['apellidos'],
                'grupo' => $institucion->grupo,
                'estado' => 'Activo',
            ]);

            return [
                'message' => "Cupo de beneficio asignado y activado a {$institucion->nombre_completo}.",
                'tiene_cupo' => true,
                'estado' => $estudiante->estado,
            ];
        }
    }

    /**
     * Cambia el estado del beneficio de un estudiante.
     */
    public function cambiarEstadoBeneficio(string $documento, string $nuevoEstado): array
    {
        $estudiante = Estudiante::find($documento);
        if (!$estudiante) {
            throw new Exception("El estudiante no tiene perfil de beneficiario registrado.");
        }

        $estadosValidos = ['Pendiente', 'Activo', 'Suspendido', 'Inactivo'];
        if (!in_array($nuevoEstado, $estadosValidos)) {
            throw new Exception("Estado no válido. Use: Pendiente, Activo, Suspendido o Inactivo.");
        }

        $estudiante->estado = $nuevoEstado;
        $estudiante->save();

        return [
            'message' => "Estado cambiado a '{$nuevoEstado}' para {$estudiante->nombres} {$estudiante->apellidos}.",
            'estado' => $nuevoEstado,
        ];
    }

    /**
     * Edita los datos de un estudiante matriculado con actualización transaccional en cascada.
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

        $parsedNames = $this->formatNames($nombreCompleto);

        DB::transaction(function () use ($docOriginal, $docNuevo, $nombreCompleto, $grupo, $parsedNames, $institucion) {
            if ($docOriginal !== $docNuevo) {
                $driver = DB::getDriverName();
                if ($driver === 'sqlite') {
                    DB::statement('PRAGMA foreign_keys = OFF;');
                } elseif ($driver === 'mysql') {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                }

                try {
                    DB::table('institucion_estudiantes')->where('documento', $docOriginal)->update([
                        'documento' => $docNuevo,
                        'nombre_completo' => $nombreCompleto,
                        'grupo' => $grupo,
                    ]);

                    DB::table('estudiantes')->where('documento', $docOriginal)->update([
                        'documento' => $docNuevo,
                        'nombres' => $parsedNames['nombres'],
                        'apellidos' => $parsedNames['apellidos'],
                        'grupo' => $grupo,
                    ]);

                    DB::table('asistencias')->where('documento', $docOriginal)->update(['documento' => $docNuevo]);
                    DB::table('comprobantes')->where('documento', $docOriginal)->update(['documento' => $docNuevo]);
                    DB::table('justificaciones')->where('documento', $docOriginal)->update(['documento' => $docNuevo]);
                } finally {
                    if ($driver === 'sqlite') {
                        DB::statement('PRAGMA foreign_keys = ON;');
                    } elseif ($driver === 'mysql') {
                        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    }
                }
            } else {
                $institucion->update([
                    'nombre_completo' => $nombreCompleto,
                    'grupo' => $grupo,
                ]);

                $est = Estudiante::find($docOriginal);
                if ($est) {
                    $est->update([
                        'nombres' => $parsedNames['nombres'],
                        'apellidos' => $parsedNames['apellidos'],
                        'grupo' => $grupo,
                    ]);
                }
            }
        });

        return [
            'message' => "Datos del estudiante {$nombreCompleto} (Doc: {$docNuevo}) actualizados correctamente sin perder historial.",
        ];
    }

    /**
     * Elimina a un estudiante de la lista institucional y de la lista de beneficiarios con borrado transaccional.
     */
    public function deleteInstitutionalStudent(string $documento): array
    {
        DB::transaction(function () use ($documento) {
            Estudiante::where('documento', $documento)->delete();
            InstitucionEstudiante::where('documento', $documento)->delete();
        });

        return [
            'message' => "Estudiante con documento {$documento} eliminado correctamente de la institución.",
        ];
    }
}
