<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\StudentManagementService;
use App\Modules\Admin\Services\AttendanceReportService;
use App\Modules\Admin\Services\AttendanceSimulationService;
use Illuminate\Http\Request;
use Exception;

class AdminController extends Controller
{
    protected $studentService;
    protected $reportService;
    protected $simulationService;

    public function __construct(
        StudentManagementService $studentService,
        AttendanceReportService $reportService,
        AttendanceSimulationService $simulationService
    ) {
        $this->studentService = $studentService;
        $this->reportService = $reportService;
        $this->simulationService = $simulationService;
    }

    /**
     * Retorna todos los estudiantes beneficiarios.
     */
    public function estudiantes()
    {
        try {
            $estudiantes = $this->studentService->getStudents();
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
            $justificaciones = $this->studentService->getJustifications();
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
            $resultado = $this->studentService->approveStudent($request->input('documento'));
            return response()->json($resultado);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Rechaza una inscripción.
     */
    public function rechazar(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $resultado = $this->studentService->rejectStudent($request->input('documento'));
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
            $resultado = $this->studentService->deleteStudent($request->input('documento'));
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
            $resultado = $this->studentService->reactivateStudent($request->input('documento'));
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
            $justificacion = $this->studentService->submitJustification($request->all());
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
            $reporte = $this->reportService->getDailyReport($request->input('fecha'));
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
            $reporte = $this->reportService->getWeeklyReport();
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
            $resultado = $this->simulationService->simulateDay(
                $request->input('fecha'),
                $request->input('asistentes')
            );
            return response()->json($resultado);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Registra un estudiante individual manualmente.
     */
    public function crearEstudianteIndividual(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'grupo' => 'required|string|max:20',
        ]);

        try {
            $resultado = $this->studentService->createSingleStudent($request->all());
            return response()->json($resultado, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Importa un arreglo masivo de estudiantes leídos desde Excel/CSV.
     */
    public function importarEstudiantesMasivo(Request $request)
    {
        $request->validate([
            'estudiantes' => 'required|array|min:1',
        ]);

        try {
            $resultado = $this->studentService->importBulkStudents($request->input('estudiantes'));
            return response()->json($resultado, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Retorna los estudiantes agrupados por Cursos y Grupos con estatus de inscripción.
     */
    public function grupos()
    {
        try {
            $grupos = $this->reportService->getGroupedCourses();
            return response()->json($grupos);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Activa directamente a un estudiante de la institución.
     */
    public function activarManual(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $resultado = $this->studentService->activateStudentManually($request->input('documento'));
            return response()->json($resultado, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Edita los datos de un estudiante matriculado.
     */
    public function actualizarEstudiante(Request $request)
    {
        $request->validate([
            'documento_original' => 'required|string',
            'documento' => 'required|string',
            'nombre_completo' => 'required|string|max:100',
            'grupo' => 'required|string|max:20',
        ]);

        try {
            $resultado = $this->studentService->updateStudent($request->all());
            return response()->json($resultado, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Elimina a un estudiante de la institución.
     */
    public function eliminarEstudianteInstitucional(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $resultado = $this->studentService->deleteInstitutionalStudent($request->input('documento'));
            return response()->json($resultado, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Activa o desactiva el cupo de beneficio de un estudiante.
     */
    public function toggleCupo(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $resultado = $this->studentService->toggleCupo($request->input('documento'));
            return response()->json($resultado, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Cambia el estado del beneficio de un estudiante.
     */
    public function cambiarEstadoBeneficio(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
            'estado' => 'required|string|in:Pendiente,Activo,Suspendido,Inactivo',
        ]);

        try {
            $resultado = $this->studentService->cambiarEstadoBeneficio(
                $request->input('documento'),
                $request->input('estado')
            );
            return response()->json($resultado, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
