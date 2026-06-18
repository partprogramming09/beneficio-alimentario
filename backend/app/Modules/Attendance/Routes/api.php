<?php

use App\Modules\Attendance\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::post('/asistencia', [AttendanceController::class, 'registrarAsistencia']);
Route::get('/comprobante/{documento}', [AttendanceController::class, 'obtenerComprobante']);
