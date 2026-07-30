<?php

namespace App\Modules\Admin\Services;

use App\Modules\Attendance\Models\Asistencia;
use Illuminate\Support\Facades\DB;

class AttendanceReportService
{
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
            $estaInscrito = $beneficiario && $beneficiario->estado === 'Activo';
            $estado = $beneficiario ? $beneficiario->estado : 'Sin Registrar';

            $grupos[$grp]['total_matriculados']++;
            if ($estaInscrito) {
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
