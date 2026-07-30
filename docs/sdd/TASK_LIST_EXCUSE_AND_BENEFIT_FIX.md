# 📋 TASK LIST: Solución SDD de Justificaciones y Aislamiento de Formularios

- [x] **Fase 1: Backend - JustificationService**
  - [x] Modificar `JustificationService.php` para consultar `InstitucionEstudiante` si el alumno no existe en `estudiantes`.
  - [x] Auto-crear perfil en `estudiantes` si está matriculado oficialmente en `institucion_estudiantes`.
  - [x] Ejecutar suite de pruebas Backend (`DiningHallTest.php`) con PHPUnit (10/10 pruebas pasadas, 69 aserciones).

- [x] **Fase 2: Frontend Estudiante - Componentes de Gestión**
  - [x] Refactorizar y separar formularios HTML independientes en `src/components/justificaciones/GestionTab.vue`.
  - [x] Sincronizar/Consolidar `src/components/GestionTab.vue` y `src/components/justificaciones/GestionTab.vue`.
  - [x] Verificar `type="submit"` y `type="button"` en botones y campos con Enter.

- [x] **Fase 3: Validación & Pruebas**
  - [x] Probar compilación/build de Vite en `frontend-estudiante` (Construcción exitosa en <7s).
  - [x] Verificar que enviar excusa con documento matriculado cree la justificación correctamente.
  - [x] Verificar que hacer clic o dar Enter en justificación NO dispare la renuncia ni abra el modal de retiro de bono.
