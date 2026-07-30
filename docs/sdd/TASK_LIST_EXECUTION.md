# 📋 TASK LIST: Lista de Tareas Atómicas de Ejecución (SDD)

> **Proyecto:** Sistema de Beneficio Alimentario - I.E. Enrique Vélez Escobar  
> **Fecha:** 2026-07-30  
> **Estado:** 100% Completado (9/9 Pruebas Unitarias & Integración Verificadas)

---

## 🚀 Fase 1: Modularización de Servicios Administrativos

- [x] **Tarea 1.1**: Crear el servicio `App\Modules\Admin\Services\StudentManagementService.php` y mover la lógica de gestión de estudiantes, edición y actualización de matrículas.
- [x] **Tarea 1.2**: Crear el servicio `App\Modules\Admin\Services\AttendanceReportService.php` y mover la lógica de reportes diarios, semanales y agrupación de cursos.
- [x] **Tarea 1.3**: Crear el servicio `App\Modules\Admin\Services\AttendanceSimulationService.php` y mover la simulación de días escolares y evaluación de suspensión.
- [x] **Tarea 1.4**: Refactorizar `AdminController.php` e inyectar los 3 nuevos servicios especializados.

---

## 🔒 Fase 2: Solución de Conflictos e Integridad de Datos

- [x] **Tarea 2.1**: Implementar la actualización en cascada transaccional (`DB::transaction`) en `updateStudent` para sincronizar `institucion_estudiantes`, `estudiantes`, `asistencias`, `comprobantes` y `justificaciones`.
- [x] **Tarea 2.2**: Corregir la lógica de `toggleCupo` y `renounceBenefit` para utilizar el estado `'Inactivo'` en lugar de eliminar el registro de la base de datos, preservando el historial.
- [x] **Tarea 2.3**: Ajustar `StudentService::validateStudent()` para impedir que un estudiante en estado `'Inactivo'` se autoregistre sin ser reactivado formalmente por la coordinadora.
- [x] **Tarea 2.4**: Implementar el formateador/normalizador de nombres para prevenir la fragmentación de nombres compuestos.

---

## 🧪 Fase 3: Pruebas y Validación

- [x] **Tarea 3.1**: Validar mediante sintaxis y pruebas de integración que la actualización de un documento mantenga intactas todas las asistencias asociadas (`test_update_student_document_cascades_history`).
- [x] **Tarea 3.2**: Probar el flujo completo de estado (`test_toggle_cupo_sets_inactivo_and_blocks_autoregistration`).
- [x] **Tarea 3.3**: Verificar la estabilidad de la interfaz en `frontend-coordinadora` y `frontend-estudiante`.
