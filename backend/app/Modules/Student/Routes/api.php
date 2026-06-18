<?php

use App\Modules\Student\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::post('/validar', [StudentController::class, 'validar']);
Route::post('/registro', [StudentController::class, 'registro']);
Route::post('/renunciar', [StudentController::class, 'renunciar']);
