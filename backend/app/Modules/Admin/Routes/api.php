<?php

use App\Modules\Admin\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/estudiantes', [AdminController::class, 'estudiantes']);
Route::get('/justificaciones', [AdminController::class, 'justificaciones']);
Route::post('/estudiantes/aprobar', [AdminController::class, 'aprobar']);
Route::post('/estudiantes/rechazar', [AdminController::class, 'rechazar']);
Route::post('/estudiantes/eliminar', [AdminController::class, 'eliminar']);
Route::post('/estudiantes/reingresar', [AdminController::class, 'reingresar']);
Route::get('/asistencia/diaria', [AdminController::class, 'asistenciaDiaria']);
Route::get('/asistencia/semanal', [AdminController::class, 'asistenciaSemanal']);
Route::post('/simular-dia', [AdminController::class, 'simularDia']);
Route::post('/estudiantes/crear-individual', [AdminController::class, 'crearEstudianteIndividual']);
Route::post('/estudiantes/importar-masivo', [AdminController::class, 'importarEstudiantesMasivo']);
Route::get('/grupos', [AdminController::class, 'grupos']);
Route::post('/estudiantes/activar-manual', [AdminController::class, 'activarManual']);



