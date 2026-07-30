# 📋 TASK LIST: Lista de Tareas Atómicas de Ejecución (Ajustes & Mejoras)

> **Proyecto:** Sistema de Beneficio Alimentario - I.E. Enrique Vélez Escobar  
> **Fecha:** 2026-07-30  
> **Estado:** 100% Completado (Pruebas & Builds OK)

---

## 🚀 Fase 1: Ajustes en Backend (Renuncia, Justificación & Selector de Estado)

- [x] **Tarea 1.1**: Modificar `StudentService::renounceBenefit()` para permitir renunciar a cualquier estudiante matriculado en `institucion_estudiantes` aunque no tuviera fila previa en `estudiantes`.
- [x] **Tarea 1.2**: Modificar `StudentManagementService::submitJustification()` para permitir enviar justificante a cualquier alumno matriculado en `institucion_estudiantes`.
- [x] **Tarea 1.3**: Actualizar `StudentManagementService::updateStudent()` para permitir actualizar el `estado` del estudiante directamente durante la edición.

---

## 🎨 Fase 2: Favicon e Identidad de Pestaña en HTML

- [x] **Tarea 2.1**: Actualizar `frontend-coordinadora/index.html` con favicon `/escudo.png` y título formal.
- [x] **Tarea 2.2**: Actualizar `frontend-estudiante/index.html` con favicon `/escudo.png` y título formal.

---

## 🖥️ Fase 3: Nomenclatura de Pestañas y Selector de Estado en Frontend

- [x] **Tarea 3.1**: Actualizar nombres de pestañas en `DashboardPage.vue`, `ReactivacionesTab.vue` y `SimuladorTab.vue` para dar máxima claridad conceptual a la Coordinadora.
- [x] **Tarea 3.2**: Incorporar el selector `<select v-model="form.estado">` en el modal de edición de estudiante en `EstudiantesTab.vue` y `CursosTab.vue`.

---

## 🧪 Fase 4: Pruebas y Verificación de Builds

- [x] **Tarea 4.1**: Correr la suite de pruebas unitarias/integración de PHPUnit (9/9 Pasados).
- [x] **Tarea 4.2**: Compilar y verificar con `npm run build` ambos frontends.
- [x] **Tarea 4.3**: Commit & Push a la rama `master`.
