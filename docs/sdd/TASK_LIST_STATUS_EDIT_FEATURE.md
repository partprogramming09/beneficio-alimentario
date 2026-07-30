# 📋 TASK LIST: Edición de Estados en Modal de Estudiantes

- [x] **Fase 1: Backend**
  - [x] Agregar validación de `estado` en `AdminController::actualizarEstudiante`.
  - [x] Robustecer búsqueda en `StudentManagementService::updateStudent` para manejar registros institucionales y de beneficiarios sin errores de inconsistencia.
  - [x] Añadir test de integración en `DiningHallTest.php` para cambio manual a `Suspendido` e `Inactivo` desde actualización.

- [x] **Fase 2: Frontend Coordinadora**
  - [x] Sincronizar y actualizar `src/components/students/EditarEstudianteModal.vue` y `src/components/EditarEstudianteModal.vue` garantizando casteo explícito a string de `documento_original` y `documento`.
  - [x] Verificar transmisión de `form.estado` en `updateStudent(this.form)`.

- [x] **Fase 3: Validación**
  - [x] Ejecutar PHPUnit suite en Backend.
  - [x] Ejecutar build de Vite en `frontend-coordinadora` (100% Exitoso en 7.21s).
