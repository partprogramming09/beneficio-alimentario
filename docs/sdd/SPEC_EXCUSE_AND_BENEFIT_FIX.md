# 📋 SPEC: Justificación por Documento Institucional e Aislamiento de Acciones en Portal

## 🎯 Objetivo
Resolver dos fallos reportados en el módulo de estudiante del beneficio alimentario:
1. **Permitir justificación con documento en BD:** Garantizar que cualquier estudiante registrado en la institución (`institucion_estudiantes` o `estudiantes`) pueda enviar su excusa/justificación sin recibir el error de "no se encuentra registrado".
2. **Aislamiento de formularios y botones:** Prevenir la activación accidental o simultánea del botón/modal de "Renuncia al Beneficio" al enviar una justificación de inasistencia.

---

## 📜 Reglas de Negocio

### 1. Justificación de Inasistencias
- Si el documento existe en `estudiantes`, procesar la excusa normalmente.
- Si el documento NO existe en `estudiantes` PERO SÍ existe en `institucion_estudiantes` (estudiante matriculado oficialmente):
  - Crear automáticamente el perfil del estudiante en la tabla `estudiantes` con sus nombres, apellidos y grupo correspondientes a la matrícula oficial.
  - Registrar la justificación exitosamente.
- Si el documento NO existe ni en `estudiantes` ni en `institucion_estudiantes`:
  - Retornar un error claro indicando que el documento no se encuentra matriculado en la institución.

### 2. Aislamiento de Formularios en Frontend Estudiante
- Las tarjetas "Justificar Inasistencia" y "Renuncia Voluntaria" deben operar en formularios HTML totalmente independientes (`<form @submit.prevent="...">`).
- Cada botón debe definir su tipo explícito (`type="submit"` dentro del formulario propio, `type="button"` para modales o acciones secundarias).
- La tecla `Enter` en los campos de texto de justificación solo debe disparar el envío de la excusa.
- Consolidar la estructura del componente `GestionTab.vue` en `frontend-estudiante` para evitar discrepancias por duplicidad de archivos.

---

## 🔍 Requisitos Técnicos

### Backend (`backend/app/Modules/Admin/Services/JustificationService.php`)
- Importar `InstitucionEstudiante` model.
- Verificar existencia en `InstitucionEstudiante` si `Estudiante::find($documento)` es nulo.
- Auto-crear `Estudiante` en estado `Activo` (o preserving status) si figura en `InstitucionEstudiante`.

### Frontend (`frontend-estudiante/src/components/`)
- Limpiar/Unificar `GestionTab.vue`.
- Asegurar firmas de componentes y bindings independientes.
- Agregar modal de confirmación `ConfirmModal` seguro en `GestionTab.vue`.
