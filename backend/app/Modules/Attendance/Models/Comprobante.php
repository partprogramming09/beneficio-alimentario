<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Student\Models\Estudiante;

class Comprobante extends Model
{
    protected $table = 'comprobantes';
    public $timestamps = false; // Manejado vía fecha/hora

    protected $fillable = [
        'documento',
        'fecha',
        'hora',
        'codigo',
    ];

    /**
     * Relación con el estudiante beneficiario
     */
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'documento', 'documento');
    }
}
