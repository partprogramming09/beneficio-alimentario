<?php

namespace App\Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Attendance\Models\Asistencia;
use App\Modules\Admin\Models\Justificacion;

class Estudiante extends Model
{
    protected $table = 'estudiantes';
    protected $primaryKey = 'documento';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Manejado manualmente o vía db para creado_en

    protected $fillable = [
        'documento',
        'nombres',
        'apellidos',
        'grupo',
        'estado',
    ];

    /**
     * Relación con la precarga de estudiantes de la institución
     */
    public function institucion()
    {
        return $this->belongsTo(InstitucionEstudiante::class, 'documento', 'documento');
    }

    /**
     * Relación con sus registros de asistencia
     */
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'documento', 'documento');
    }

    /**
     * Relación con sus justificaciones de inasistencia
     */
    public function justificaciones()
    {
        return $this->hasMany(Justificacion::class, 'documento', 'documento');
    }
}
