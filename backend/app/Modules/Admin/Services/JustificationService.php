<?php

namespace App\Modules\Admin\Services;

use App\Modules\Admin\Models\Justificacion;
use App\Modules\Student\Models\Estudiante;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JustificationService
{
    public function submitJustification(array $data, ?UploadedFile $archivo = null): Justificacion
    {
        $documento = $data['documento'];
        $estudiante = Estudiante::find($documento);

        if (!$estudiante) {
            throw new Exception("El estudiante no se encuentra registrado en el sistema.");
        }

        $archivoPath = null;
        if ($archivo) {
            $allowed = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!in_array($archivo->getMimeType(), $allowed)) {
                throw new Exception("El archivo debe ser un PDF o Word (.doc, .docx).");
            }
            if ($archivo->getSize() > 5 * 1024 * 1024) {
                throw new Exception("El archivo no debe superar los 5 MB.");
            }
            $archivoPath = $archivo->store('excusas', 'excusas');
        }

        return Justificacion::create([
            'documento' => $documento,
            'fecha_inasistencia' => $data['fecha_inasistencia'],
            'motivo' => $data['motivo'],
            'archivo_adjunto' => $archivoPath,
            'estado' => 'Pendiente',
        ]);
    }

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
                    'archivo_adjunto' => $item->archivo_adjunto,
                    'estado' => $item->estado,
                    'estado_estudiante' => $item->estado_estudiante,
                ];
            });
    }

    public function getJustificationById(int $id): ?array
    {
        $item = DB::table('justificaciones')
            ->join('estudiantes', 'justificaciones.documento', '=', 'estudiantes.documento')
            ->where('justificaciones.id', $id)
            ->select(
                'justificaciones.*',
                'estudiantes.nombres',
                'estudiantes.apellidos',
                'estudiantes.grupo'
            )
            ->first();

        if (!$item) {
            return null;
        }

        return [
            'id' => $item->id,
            'documento' => $item->documento,
            'nombres' => $item->nombres,
            'apellidos' => $item->apellidos,
            'grupo' => $item->grupo,
            'fecha_inasistencia' => $item->fecha_inasistencia,
            'motivo' => $item->motivo,
            'archivo_adjunto' => $item->archivo_adjunto,
            'estado' => $item->estado,
        ];
    }

    public function downloadAttachment(int $id): ?array
    {
        $justificacion = Justificacion::find($id);

        if (!$justificacion || !$justificacion->archivo_adjunto) {
            return null;
        }

        $path = "excusas/{$justificacion->archivo_adjunto}";

        if (!Storage::disk('excusas')->exists($justificacion->archivo_adjunto)) {
            return null;
        }

        return [
            'path' => Storage::disk('excusas')->path($justificacion->archivo_adjunto),
            'name' => basename($justificacion->archivo_adjunto),
        ];
    }

    public function approveJustification(int $id): array
    {
        $justificacion = Justificacion::find($id);

        if (!$justificacion) {
            throw new Exception("Justificación no encontrada.");
        }

        if ($justificacion->estado === 'Aprobado') {
            throw new Exception("La justificación ya fue aprobada.");
        }

        $justificacion->update(['estado' => 'Aprobado']);

        return [
            'message' => 'Justificación aprobada exitosamente.',
            'justificacion' => $justificacion,
        ];
    }

    public function rejectJustification(int $id): array
    {
        $justificacion = Justificacion::find($id);

        if (!$justificacion) {
            throw new Exception("Justificación no encontrada.");
        }

        if ($justificacion->estado === 'Rechazado') {
            throw new Exception("La justificación ya fue rechazada.");
        }

        $justificacion->update(['estado' => 'Rechazado']);

        return [
            'message' => 'Justificación rechazada.',
            'justificacion' => $justificacion,
        ];
    }
}
