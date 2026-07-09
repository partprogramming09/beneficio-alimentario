<?php

namespace App\Modules\Student\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Student\Services\StudentService;
use Illuminate\Http\Request;
use Exception;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    /**
     * Valida si el estudiante está matriculado en la institución y apto para registrarse.
     */
    public function validar(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $data = $this->studentService->validateStudent($request->input('documento'));
            return response()->json($data);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Registra un perfil de beneficiario para el estudiante.
     */
    public function registro(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
        ]);

        try {
            $resultado = $this->studentService->registerProfile($request->all());
            return response()->json([
                'message' => 'Perfil registrado y asistencia diaria marcada con éxito. ¡Buen provecho!',
                'estudiante' => $resultado['estudiante'],
                'comprobante' => $resultado['comprobante'],
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Permite renunciar voluntariamente al cupo del comedor escolar.
     */
    public function renunciar(Request $request)
    {
        $request->validate([
            'documento' => 'required|string',
        ]);

        try {
            $resultado = $this->studentService->renounceBenefit($request->input('documento'));
            return response()->json($resultado);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
