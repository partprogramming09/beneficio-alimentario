<?php

namespace Tests\Feature;

use App\Modules\Student\Models\InstitucionEstudiante;
use App\Modules\Student\Models\Estudiante;
use App\Modules\Attendance\Models\Asistencia;
use App\Modules\Attendance\Models\Comprobante;
use App\Modules\Admin\Models\Justificacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiningHallTest extends TestCase
{
    use RefreshDatabase; // Reinicia la base de datos para cada test de forma limpia

    protected function setUp(): void
    {
        parent::setUp();

        // Precargar la base de datos de la institución (Seeders)
        InstitucionEstudiante::create(['documento' => '1001', 'nombre_completo' => 'Juan Pérez', 'grupo' => '10-A']);
        InstitucionEstudiante::create(['documento' => '1002', 'nombre_completo' => 'Maria Gomez', 'grupo' => '10-A']);
        InstitucionEstudiante::create(['documento' => '1003', 'nombre_completo' => 'Carlos Ruiz', 'grupo' => '10-B']);
    }

    /**
     * Test: Flujo completo de Registro Directo y Auto-Asistencia
     */
    public function test_student_registration_and_approval_flow(): void
    {
        // 1. Validar matrícula
        $response = $this->postJson('/api/estudiantes/validar', ['documento' => '1001']);
        $response->assertStatus(200)
                 ->assertJsonPath('nombre_completo', 'Juan Pérez');

        // 2. Intentar validar documento no matriculado
        $response = $this->postJson('/api/estudiantes/validar', ['documento' => '9999']);
        $response->assertStatus(400)
                 ->assertJsonStructure(['error']);

        // 3. Registrar el perfil (debe auto-activar y auto-marcar asistencia hoy)
        $response = $this->postJson('/api/estudiantes/registro', [
            'documento' => '1001',
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
        ]);
        $response->assertStatus(201)
                 ->assertJsonPath('estudiante.estado', 'Activo')
                 ->assertJsonStructure(['message', 'estudiante', 'comprobante']);

        // Comprobar persistencia en DB de estudiante Activo, asistencia y comprobante
        $this->assertDatabaseHas('estudiantes', [
            'documento' => '1001',
            'estado' => 'Activo'
        ]);

        $this->assertDatabaseHas('asistencias', [
            'documento' => '1001',
            'fecha' => date('Y-m-d')
        ]);

        $this->assertDatabaseHas('comprobantes', [
            'documento' => '1001',
            'fecha' => date('Y-m-d')
        ]);
    }

    /**
     * Test: Marcación de asistencia diaria y no-duplicidad
     */
    public function test_daily_attendance_marking_and_uniqueness(): void
    {
        // Registrar y aprobar estudiante
        $estudiante = Estudiante::create([
            'documento' => '1001',
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'grupo' => '10-A',
            'estado' => 'Activo'
        ]);

        // 1. Registrar asistencia exitosa
        $response = $this->postJson('/api/asistencia', ['documento' => '1001']);
        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'comprobante'])
                 ->assertJsonPath('comprobante.nombre', 'Juan Pérez');

        // Comprobar en DB
        $this->assertDatabaseHas('asistencias', ['documento' => '1001']);
        $this->assertDatabaseHas('comprobantes', ['documento' => '1001']);

        // 2. Intentar registrar asistencia por segunda vez el mismo día
        $response = $this->postJson('/api/asistencia', ['documento' => '1001']);
        $response->assertStatus(400)
                 ->assertJsonStructure(['error']);
    }

    /**
     * Test: Regla de suspensión por 3 inasistencias consecutivas
     */
    public function test_suspension_by_three_consecutive_absences(): void
    {
        // Registrar estudiantes Activos
        $estudiante1 = Estudiante::create(['documento' => '1001', 'nombres' => 'Juan', 'apellidos' => 'Pérez', 'grupo' => '10-A', 'estado' => 'Activo']);
        $estudiante2 = Estudiante::create(['documento' => '1002', 'nombres' => 'Maria', 'apellidos' => 'Gomez', 'grupo' => '10-A', 'estado' => 'Activo']);

        // Simular Día 1: Asiste solo Juan (Gomez falta)
        $response = $this->postJson('/api/admin/simular-dia', [
            'fecha' => '2026-06-15',
            'asistentes' => ['1001']
        ]);
        $response->assertStatus(200);

        // Simular Día 2: Asiste solo Juan (Gomez falta por segunda vez)
        $response = $this->postJson('/api/admin/simular-dia', [
            'fecha' => '2026-06-16',
            'asistentes' => ['1001']
        ]);
        $response->assertStatus(200);

        // Confirmar que Maria Gomez sigue Activa
        $this->assertEquals('Activo', Estudiante::find('1002')->estado);

        // Simular Día 3: Asiste solo Juan (Gomez falta por tercera vez consecutiva)
        $response = $this->postJson('/api/admin/simular-dia', [
            'fecha' => '2026-06-17',
            'asistentes' => ['1001']
        ]);
        $response->assertStatus(200);

        // Confirmar que Maria Gomez fue suspendida automáticamente
        $this->assertEquals('Suspendido', Estudiante::find('1002')->estado);

        // Intentar registrar asistencia de Gomez estando suspendida
        $response = $this->postJson('/api/asistencia', ['documento' => '1002']);
        $response->assertStatus(400)
                 ->assertJsonStructure(['error']);
    }

    /**
     * Test: Justificación de faltas y reactivación
     */
    public function test_justification_and_reactivation_flow(): void
    {
        // Estudiante suspendido
        $estudiante = Estudiante::create([
            'documento' => '1002',
            'nombres' => 'Maria',
            'apellidos' => 'Gomez',
            'grupo' => '10-A',
            'estado' => 'Suspendido'
        ]);

        // 1. Enviar justificación
        $response = $this->postJson('/api/justificaciones', [
            'documento' => '1002',
            'fecha_inasistencia' => '2026-06-17',
            'motivo' => 'Cita médica odontológica programada.'
        ]);
        $response->assertStatus(201);

        $this->assertDatabaseHas('justificaciones', [
            'documento' => '1002',
            'estado' => 'Pendiente'
        ]);

        // 2. Aprobar justificación y reactivar estudiante (vía admin reingresar)
        $response = $this->postJson('/api/admin/estudiantes/reingresar', ['documento' => '1002']);
        $response->assertStatus(200);

        // Comprobar que el estudiante volvió a estado Activo
        $this->assertEquals('Activo', Estudiante::find('1002')->estado);

        // Comprobar que su justificación fue aprobada
        $this->assertDatabaseHas('justificaciones', [
            'documento' => '1002',
            'estado' => 'Aprobado'
        ]);
    }
}
