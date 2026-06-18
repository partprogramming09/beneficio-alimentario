<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Services\AttendanceService;
use Illuminate\Http\Request;
use Exception;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Registra la asistencia diaria de un estudiante.
     */
    public function registrarAsistencia(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $resultado = $this->attendanceService->markAttendance($request->input('documento'));
            return response()->json($resultado);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Recupera el ticket de asistencia emitido hoy para el documento.
     */
    public function obtenerComprobante(string $documento)
    {
        try {
            $comprobante = $this->attendanceService->getTodayReceipt($documento);
            return response()->json($comprobante);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
