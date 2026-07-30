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
            'fecha' => '2026-06-16',
            'asistentes' => ['1001']
        ]);
        $response->assertStatus(200);

        // Simular Día 2: Asiste solo Juan (Gomez falta por segunda vez)
        $response = $this->postJson('/api/admin/simular-dia', [
            'fecha' => '2026-06-17',
            'asistentes' => ['1001']
        ]);
        $response->assertStatus(200);

        // Confirmar que Maria Gomez sigue Activa
        $this->assertEquals('Activo', Estudiante::find('1002')->estado);

        // Simular Día 3: Asiste solo Juan (Gomez falta por tercera vez consecutiva)
        $response = $this->postJson('/api/admin/simular-dia', [
            'fecha' => '2026-06-18',
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

    /**
     * Test: Endpoint /api/admin/grupos con estructura agrupada
     */
    public function test_grouped_courses_api(): void
    {
        InstitucionEstudiante::create([
            'documento' => '9901',
            'nombre_completo' => 'Prueba Grupo',
            'grupo' => '10-A'
        ]);

        $response = $this->getJson('/api/admin/grupos');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['nombre_grupo', 'total_matriculados', 'total_inscritos', 'total_sin_inscribir', 'estudiantes']
                 ]);
    }

    /**
     * Test: Activación por Excepción de estudiante sin registrar
     */
    public function test_manual_exception_activation(): void
    {
        InstitucionEstudiante::create([
            'documento' => '9902',
            'nombre_completo' => 'Carlos Excepcion',
            'grupo' => '10-A'
        ]);

        $response = $this->postJson('/api/admin/estudiantes/activar-manual', [
            'documento' => '9902'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('estudiantes', [
            'documento' => '9902',
            'estado' => 'Activo'
        ]);
    }

    /**
     * Test: Edición y eliminación de estudiante matriculado
     */
    public function test_update_and_delete_institutional_student(): void
    {
        InstitucionEstudiante::create([
            'documento' => '9903',
            'nombre_completo' => 'Estudiante Para Editar',
            'grupo' => '10-A'
        ]);

        // Editar
        $responseUpdate = $this->postJson('/api/admin/estudiantes/actualizar', [
            'documento_original' => '9903',
            'documento' => '9903',
            'nombre_completo' => 'Estudiante Editado Exitoso',
            'grupo' => '10-B'
        ]);
        $responseUpdate->assertStatus(200);

        $this->assertDatabaseHas('institucion_estudiantes', [
            'documento' => '9903',
            'nombre_completo' => 'Estudiante Editado Exitoso',
            'grupo' => '10-B'
        ]);

        // Eliminar
        $responseDelete = $this->postJson('/api/admin/estudiantes/eliminar-institucional', [
            'documento' => '9903'
        ]);
        $responseDelete->assertStatus(200);

        $this->assertDatabaseMissing('institucion_estudiantes', [
            'documento' => '9903'
        ]);
    }

    /**
     * Test SDD: Cascada Transaccional de actualización de Documento preservando asistencias
     */
    public function test_update_student_document_cascades_history(): void
    {
        Estudiante::create([
            'documento' => '1001',
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'grupo' => '10-A',
            'estado' => 'Activo'
        ]);

        Asistencia::create([
            'documento' => '1001',
            'fecha' => '2026-07-20',
            'hora' => '12:00:00'
        ]);

        Comprobante::create([
            'documento' => '1001',
            'fecha' => '2026-07-20',
            'hora' => '12:00:00',
            'codigo' => 'ALM-TEST-1001'
        ]);

        // Actualizar documento de 1001 a 2001 (ej. corrección de TI a CC)
        $response = $this->postJson('/api/admin/estudiantes/actualizar', [
            'documento_original' => '1001',
            'documento' => '2001',
            'nombre_completo' => 'Juan Carlos Pérez',
            'grupo' => '10-A'
        ]);

        $response->assertStatus(200);

        // Verificar que institucion_estudiantes y estudiantes tienen el nuevo documento
        $this->assertDatabaseHas('institucion_estudiantes', ['documento' => '2001', 'nombre_completo' => 'Juan Carlos Pérez']);
        $this->assertDatabaseHas('estudiantes', ['documento' => '2001', 'nombres' => 'Juan Carlos', 'apellidos' => 'Pérez']);

        // Verificar que la asistencia y el comprobante histórico se actualizaron al nuevo documento
        $this->assertDatabaseHas('asistencias', ['documento' => '2001']);
        $this->assertDatabaseHas('comprobantes', ['documento' => '2001']);

        // Verificar que el viejo documento ya no exista
        $this->assertDatabaseMissing('institucion_estudiantes', ['documento' => '1001']);
        $this->assertDatabaseMissing('estudiantes', ['documento' => '1001']);
    }

    /**
     * Test SDD: Desactivación por ToggleCupo marca Inactivo e impide autoregistro voluntario
     */
    public function test_toggle_cupo_sets_inactivo_and_blocks_autoregistration(): void
    {
        Estudiante::create([
            'documento' => '1001',
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'grupo' => '10-A',
            'estado' => 'Activo'
        ]);

        // 1. Coordinadora alterna cupo (Desactiva)
        $response = $this->postJson('/api/admin/estudiantes/toggle-cupo', ['documento' => '1001']);
        $response->assertStatus(200)
                 ->assertJsonPath('estado', 'Inactivo')
                 ->assertJsonPath('tiene_cupo', false);

        $this->assertDatabaseHas('estudiantes', ['documento' => '1001', 'estado' => 'Inactivo']);

        // 2. El estudiante intenta ingresar al portal a validar registro
        $responseValidar = $this->postJson('/api/estudiantes/validar', ['documento' => '1001']);
        $responseValidar->assertStatus(400)
                        ->assertJsonPath('error', 'Tu beneficio figura en estado inactivo. Consulta con la coordinadora si requieres solicitar una reactivación.');
    }

    /**
     * Test SDD: Justificación enviada por un estudiante matriculado oficialmente auto-crea perfil y registra la excusa
     */
    public function test_justification_for_unregistered_institutional_student(): void
    {
        // 1. 1003 está en institucion_estudiantes pero aún no en 'estudiantes'
        $this->assertDatabaseMissing('estudiantes', ['documento' => '1003']);

        // 2. El estudiante envía justificación
        $response = $this->postJson('/api/justificaciones', [
            'documento' => '1003',
            'fecha_inasistencia' => '2026-07-28',
            'motivo' => 'Calamidad doméstica justificada.'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('justificacion.documento', '1003')
                 ->assertJsonPath('justificacion.estado', 'Pendiente');

        // 3. Verificar que el perfil de estudiante fue auto-creado en 'estudiantes'
        $this->assertDatabaseHas('estudiantes', [
            'documento' => '1003',
            'nombres' => 'Carlos',
            'apellidos' => 'Ruiz',
            'grupo' => '10-B'
        ]);

        // 4. Verificar que la justificación existe en la BD
        $this->assertDatabaseHas('justificaciones', [
            'documento' => '1003',
            'estado' => 'Pendiente'
        ]);
    }

    /**
     * Test SDD: Actualizar estudiante permite cambiar estado a Suspendido e Inactivo
     */
    public function test_update_student_changes_status(): void
    {
        Estudiante::create([
            'documento' => '1001',
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'grupo' => '10-A',
            'estado' => 'Activo'
        ]);

        // Cambiar a Suspendido
        $response = $this->postJson('/api/admin/estudiantes/actualizar', [
            'documento_original' => '1001',
            'documento' => '1001',
            'nombre_completo' => 'Juan Pérez',
            'grupo' => '10-A',
            'estado' => 'Suspendido'
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('estudiantes', ['documento' => '1001', 'estado' => 'Suspendido']);

        // Cambiar a Inactivo
        $response = $this->postJson('/api/admin/estudiantes/actualizar', [
            'documento_original' => '1001',
            'documento' => '1001',
            'nombre_completo' => 'Juan Pérez',
            'grupo' => '10-A',
            'estado' => 'Inactivo'
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('estudiantes', ['documento' => '1001', 'estado' => 'Inactivo']);
    }

    /**
     * Test SDD: Actualizar estudiante existente auto-sincroniza entrada institucional y permite cambio de estado
     */
    public function test_update_student_creates_missing_institutional_record(): void
    {
        InstitucionEstudiante::create([
            'documento' => '8888',
            'nombre_completo' => 'Pedro Picapiedra',
            'grupo' => '10-A'
        ]);

        Estudiante::create([
            'documento' => '8888',
            'nombres' => 'Pedro',
            'apellidos' => 'Picapiedra',
            'grupo' => '10-A',
            'estado' => 'Activo'
        ]);

        $response = $this->postJson('/api/admin/estudiantes/actualizar', [
            'documento_original' => '8888',
            'documento' => '8888',
            'nombre_completo' => 'Pedro Picapiedra',
            'grupo' => '10-A',
            'estado' => 'Suspendido'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('institucion_estudiantes', ['documento' => '8888', 'nombre_completo' => 'Pedro Picapiedra']);
        $this->assertDatabaseHas('estudiantes', ['documento' => '8888', 'estado' => 'Suspendido']);
    }

    /**
     * Test SDD: Actualizar estudiante con documento nuevo no existente (ej. 1001163) auto-crea el registro y guarda el estado
     */
    public function test_update_student_with_completely_new_document_auto_creates_record(): void
    {
        $response = $this->postJson('/api/admin/estudiantes/actualizar', [
            'documento_original' => '1001163',
            'documento' => '1001163',
            'nombre_completo' => 'Estudiante Nuevo Test',
            'grupo' => '11-B',
            'estado' => 'Activo'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('institucion_estudiantes', [
            'documento' => '1001163',
            'nombre_completo' => 'Estudiante Nuevo Test',
            'grupo' => '11-B'
        ]);

        $this->assertDatabaseHas('estudiantes', [
            'documento' => '1001163',
            'estado' => 'Activo'
        ]);
    }
}
