<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\AdminService;
use Illuminate\Http\Request;
use Exception;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Retorna todos los estudiantes beneficiarios.
     */
    public function estudiantes()
    {
        try {
            $estudiantes = $this->adminService->getStudents();
            return response()->json($estudiantes);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Retorna todas las excusas cargadas.
     */
    public function justificaciones()
    {
        try {
            $justificaciones = $this->adminService->getJustifications();
            return response()->json($justificaciones);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Aprueba una inscripción de estudiante.
     */
    public function aprobar(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $resultado = $this->adminService->approveStudent($request->input('documento'));
            return response()->json($resultado);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Rechaza una inscripción (elimina la solicitud pendiente).
     */
    public function rechazar(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $resultado = $this->adminService->rejectStudent($request->input('documento'));
            return response()->json($resultado);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Elimina permanentemente a un estudiante.
     */
    public function eliminar(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $resultado = $this->adminService->deleteStudent($request->input('documento'));
            return response()->json($resultado);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Reactiva a un estudiante suspendido.
     */
    public function reingresar(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $resultado = $this->adminService->reactivateStudent($request->input('documento'));
            return response()->json($resultado);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Registra una justificación de inasistencia (acceso estudiante).
     */
    public function justificar(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
            'fecha_inasistencia' => 'required|date',
            'motivo' => 'required|string',
        ]);

        try {
            $justificacion = $this->adminService->submitJustification($request->all());
            return response()->json([
                'message' => 'Justificación enviada con éxito. La coordinadora revisará tu caso.',
                'justificacion' => $justificacion,
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Retorna asistencia diaria para una fecha específica.
     */
    public function asistenciaDiaria(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
        ]);

        try {
            $reporte = $this->adminService->getDailyReport($request->input('fecha'));
            return response()->json($reporte);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Retorna el reporte acumulado de los últimos 7 días de servicio.
     */
    public function asistenciaSemanal()
    {
        try {
            $reporte = $this->adminService->getWeeklyReport();
            return response()->json($reporte);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Ejecuta el simulador de días escolares.
     */
    public function simularDia(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'asistentes' => 'present|array',
        ]);

        try {
            $resultado = $this->adminService->simulateDay(
                $request->input('fecha'),
                $request->input('asistentes')
            );
            return response()->json($resultado);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
