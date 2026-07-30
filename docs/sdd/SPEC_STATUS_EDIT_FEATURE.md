# 📋 SPEC: Edición Manual de Estado de Beneficiario (Activo, Suspendido, Inactivo, Sin Registrar)

## 🎯 Objetivo
Permitir a la Coordinadora modificar manualmente el estado del beneficio de un estudiante (`Activo`, `Suspendido`, `Inactivo`, `Sin Registrar`) directamente desde el modal de edición de estudiantes en `frontend-coordinadora`.

---

## 📜 Reglas de Negocio

1. **Opciones de Estado:**
   - **Activo:** El estudiante tiene el beneficio activo y puede generar tickets de asistencia diarios.
   - **Suspendido:** El estudiante se encuentra suspendido por inasistencias o decisión administrativa. No puede ingresar ni sacar tickets hasta ser reactivado.
   - **Inactivo:** El beneficio se encuentra desactivado o fue renunciado voluntariamente.
   - **Sin Registrar:** Limpia el perfil voluntario del estudiante si no posee historial de asistencia, dejándolo en la lista de matriculados oficiales para autoregistro posterior.

2. **Integridad de Datos:**
   - Si se cambia el documento del estudiante al mismo tiempo que el estado, el cambio de estado debe aplicarse sobre el nuevo número de documento de forma transaccional.
   - Preservar el historial de asistencias y comprobantes históricos.

---

## 🔍 Requisitos Técnicos

### Backend (`backend/app/Modules/Admin/Controllers/AdminController.php`)
- Agregar la regla de validación `'estado' => 'nullable|string|in:Activo,Suspendido,Inactivo,Sin Registrar,Pendiente'` al método `actualizarEstudiante`.

### Frontend (`frontend-coordinadora/src/components/students/EditarEstudianteModal.vue` y `src/components/EditarEstudianteModal.vue`)
- Incluir la etiqueta y el selector `<select v-model="form.estado">` con las opciones de estado: `Activo`, `Suspendido`, `Inactivo`, `Sin Registrar`.
- Cargar por defecto el estado actual del estudiante (`student.estado` o `student.esta_inscrito ? 'Activo' : 'Sin Registrar'`).
- Enviar la variable `estado` dentro del objeto `form` en la llamada a `updateStudent(form)`.
