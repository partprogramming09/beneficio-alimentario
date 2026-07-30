<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Requests\JustificarRequest;
use App\Modules\Admin\Services\StudentManagementService;
use App\Modules\Admin\Services\JustificationService;
use App\Modules\Admin\Services\AttendanceReportService;
use App\Modules\Admin\Services\AttendanceSimulationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class AdminController extends Controller
{
    protected $studentService;
    protected $justificationService;
    protected $reportService;
    protected $simulationService;

    public function __construct(
        StudentManagementService $studentService,
        JustificationService $justificationService,
        AttendanceReportService $reportService,
        AttendanceSimulationService $simulationService
    ) {
        $this->studentService = $studentService;
        $this->justificationService = $justificationService;
        $this->reportService = $reportService;
        $this->simulationService = $simulationService;
    }

    public function estudiantes()
    {
        try {
            return response()->json($this->studentService->getStudents());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function justificaciones()
    {
        try {
            return response()->json($this->justificationService->getJustifications());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function justificar(JustificarRequest $request)
    {
        try {
            $archivo = $request->hasFile('archivo') ? $request->file('archivo') : null;
            $justificacion = $this->justificationService->submitJustification(
                $request->validated(),
                $archivo
            );
            return response()->json([
                'message' => 'Justificación enviada con éxito. La coordinadora revisará tu caso.',
                'justificacion' => $justificacion,
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function descargarAdjunto(int $id)
    {
        try {
            $file = $this->justificationService->downloadAttachment($id);

            if (!$file) {
                return response()->json(['error' => 'Archivo no encontrado.'], 404);
            }

            return response()->download($file['path'], $file['name']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function aprobarJustificacion(int $id)
    {
        try {
            return response()->json($this->justificationService->approveJustification($id));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function rechazarJustificacion(int $id)
    {
        try {
            return response()->json($this->justificationService->rejectJustification($id));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function aprobar(Request $request)
    {
        $request->validate(['documento' => 'required|string']);
        try {
            return response()->json($this->studentService->approveStudent($request->input('documento')));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function rechazar(Request $request)
    {
        $request->validate(['documento' => 'required|string']);
        try {
            return response()->json($this->studentService->rejectStudent($request->input('documento')));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function eliminar(Request $request)
    {
        $request->validate(['documento' => 'required|string']);
        try {
            return response()->json($this->studentService->deleteStudent($request->input('documento')));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function reingresar(Request $request)
    {
        $request->validate(['documento' => 'required|string']);
        try {
            return response()->json($this->studentService->reactivateStudent($request->input('documento')));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function asistenciaDiaria(Request $request)
    {
        $request->validate(['fecha' => 'required|date']);
        try {
            return response()->json($this->reportService->getDailyReport($request->input('fecha')));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function asistenciaSemanal()
    {
        try {
            return response()->json($this->reportService->getWeeklyReport());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function simularDia(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'asistentes' => 'present|array',
        ]);
        try {
            return response()->json($this->simulationService->simulateDay(
                $request->input('fecha'),
                $request->input('asistentes')
            ));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function crearEstudianteIndividual(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'grupo' => 'required|string|max:20',
        ]);
        try {
            return response()->json($this->studentService->createSingleStudent($request->all()), 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function importarEstudiantesMasivo(Request $request)
    {
        $request->validate(['estudiantes' => 'required|array|min:1']);
        try {
            return response()->json($this->studentService->importBulkStudents($request->input('estudiantes')));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function grupos()
    {
        try {
            return response()->json($this->reportService->getGroupedCourses());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function activarManual(Request $request)
    {
        $request->validate(['documento' => 'required|string']);
        try {
            return response()->json($this->studentService->activateStudentManually($request->input('documento')));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function actualizarEstudiante(Request $request)
    {
        $request->validate([
            'documento_original' => 'required|string',
            'documento' => 'required|string',
            'nombre_completo' => 'required|string|max:100',
            'grupo' => 'required|string|max:20',
            'estado' => 'nullable|string|in:Activo,Suspendido,Inactivo,Sin Registrar,Pendiente',
        ]);
        try {
            return response()->json($this->studentService->updateStudent($request->all()));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function eliminarEstudianteInstitucional(Request $request)
    {
        $request->validate(['documento' => 'required|string']);
        try {
            return response()->json($this->studentService->deleteInstitutionalStudent($request->input('documento')));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function toggleCupo(Request $request)
    {
        $request->validate(['documento' => 'required|string']);
        try {
            return response()->json($this->studentService->toggleCupo($request->input('documento')));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function cambiarEstadoBeneficio(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
            'estado' => 'required|string|in:Pendiente,Activo,Suspendido,Inactivo',
        ]);
        try {
            return response()->json($this->studentService->cambiarEstadoBeneficio(
                $request->input('documento'),
                $request->input('estado')
            ));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function limpiarBaseDatos()
    {
        try {
            return response()->json($this->studentService->clearAllStudents());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
