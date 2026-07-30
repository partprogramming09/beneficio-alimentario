<?php

namespace App\Modules\Admin\Services;

use App\Modules\Student\Models\Estudiante;
use App\Modules\Attendance\Models\Asistencia;
use App\Modules\Attendance\Models\Comprobante;
use App\Modules\Attendance\Services\AttendanceRuleService;
use App\Services\ColombianCalendarService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceSimulationService
{
    protected $ruleService;

    public function __construct(AttendanceRuleService $ruleService)
    {
        $this->ruleService = $ruleService;
    }

    /**
     * Simula la marcación de asistencia de un día escolar completo y ejecuta reglas de suspensión.
     */
    public function simulateDay(string $fecha, array $asistentes): array
    {
        if (!ColombianCalendarService::isSchoolDay($fecha)) {
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
}
