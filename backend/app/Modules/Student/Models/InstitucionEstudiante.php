<?php

namespace App\Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;

class InstitucionEstudiante extends Model
{
    protected $table = 'institucion_estudiantes';
    protected $primaryKey = 'documento';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'documento',
        'nombre_completo',
        'grupo',
    ];
}
