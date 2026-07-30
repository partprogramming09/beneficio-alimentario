<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Admin\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes Configuration
|--------------------------------------------------------------------------
| Here we map all the modular routes of our Domain-Driven modules:
| - Student: /api/estudiantes/*
| - Attendance: /api/asistencia, /api/comprobante/*
| - Admin: /api/admin/*
| - Webhook: /api/webhooks, /api/test/*
*/

// Módulo de Estudiantes
Route::prefix('estudiantes')->group(base_path('app/Modules/Student/Routes/api.php'));

// Módulo de Asistencia
Route::group([], base_path('app/Modules/Attendance/Routes/api.php'));

// Módulo Administrativo
Route::prefix('admin')->group(base_path('app/Modules/Admin/Routes/api.php'));

// Módulo de Webhooks y Receptor
Route::group([], base_path('app/Modules/Webhook/Routes/api.php'));

// Justificación de Inasistencias (acceso de estudiantes, multipart para archivos)
Route::post('/justificaciones', [AdminController::class, 'justificar']);
