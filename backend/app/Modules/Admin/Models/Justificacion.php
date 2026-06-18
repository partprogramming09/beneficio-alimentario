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
    ];

    /**
     * Relación con el estudiante beneficiario
     */
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'documento', 'documento');
    }
}
