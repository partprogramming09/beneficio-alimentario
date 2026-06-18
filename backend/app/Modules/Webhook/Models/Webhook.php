<?php

namespace App\Modules\Webhook\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $table = 'webhooks';
    public $timestamps = false; // Manejado por creado_en

    protected $fillable = [
        'url',
        'eventos',
    ];

    /**
     * Cast de atributos para manejar JSON automáticamente como array
     */
    protected $casts = [
        'eventos' => 'array',
    ];
}
