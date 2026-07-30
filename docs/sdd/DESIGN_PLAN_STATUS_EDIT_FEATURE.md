# 🏗️ DESIGN PLAN: Edición de Estados en Modal de Estudiantes

## 🛠️ Modificaciones

### 1. `backend/app/Modules/Admin/Controllers/AdminController.php`
- En `actualizarEstudiante()`, extender `$request->validate()`:
```php
'estado' => 'nullable|string|in:Activo,Suspendido,Inactivo,Sin Registrar,Pendiente',
```

### 2. `frontend-coordinadora/src/components/students/EditarEstudianteModal.vue`
- Sincronizar el componente con `src/components/EditarEstudianteModal.vue`.
- Agregar el campo de formulario `Estado del Beneficio Comedor` con opciones visuales identificadas con emojis y descripciones claras:
  - `Activo`: ✅ Activo (Inscrito / Beneficio Activo)
  - `Suspendido`: ⛔ Suspendido (3 Inasistencias)
  - `Inactivo`: 🚫 Inactivo (Desactivado / Renuncia)
  - `Sin Registrar`: ⚪ Sin Registrar (Sin Cupo Asignado)

### 3. Pruebas Backend & Frontend
- Agregar prueba PHPUnit en `DiningHallTest.php` probando el cambio de estado a `Suspendido` e `Inactivo` mediante el endpoint `actualizarEstudiante`.
- Probar compilación con `npx vite build` en `frontend-coordinadora`.
