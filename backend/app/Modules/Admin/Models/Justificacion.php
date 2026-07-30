<?php

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Student\Models\Estudiante;

class Justificacion extends Model
{
    protected $table = 'justificaciones';
    public $timestamps = false; // Creado en db vía creado_en

    protected $fillable = [
        'documento',
        'fecha_inasistencia',
        'motivo',
        'estado',
        'archivo_adjunto',
    ];

    public function getArchivoUrl(): ?string
    {
        if (!$this->archivo_adjunto) {
            return null;
        }

        $path = storage_path("app/private/excusas/{$this->archivo_adjunto}");

        return file_exists($path) ? asset("storage/excusas/{$this->archivo_adjunto}") : null;
    }

    /**
     * Relación con el estudiante beneficiario
     */
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'documento', 'documento');
    }
}
