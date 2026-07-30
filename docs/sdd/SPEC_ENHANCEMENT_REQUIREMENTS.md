# 📄 SPEC: Ajustes de Negocio, Favicon, Nomenclatura y Selector de Estado

> **Proyecto:** Sistema de Beneficio Alimentario - I.E. Enrique Vélez Escobar  
> **Fecha:** 2026-07-30  
> **Estado:** Aprobado para Diseño y Ejecución  

---

## 🎯 1. Requerimientos de Negocio y Funcionales

### R1. Renuncia y Justificación de Estudiantes Matriculados Sin Registro previo
- **Renuncia Voluntaria**: Un estudiante matriculado en `institucion_estudiantes` que aún no ha realizado su registro voluntario personal en `estudiantes` debe poder renunciar al beneficio. El sistema creará/marcará su perfil directamente en estado `Inactivo`.
- **Justificación de Inasistencia**: Al enviar una justificación de falta, el sistema verificará la existencia del alumno en `institucion_estudiantes`. Si no está en `estudiantes`, registrará su excusa vinculada a su documento institucional sin requerir el autoregistro previo.

### R2. Favicon e Identidad de Pestaña del Navegador
- Configurar en `frontend-coordinadora/index.html` (y en `frontend-estudiante/index.html`) el escudo institucional `/escudo.png` como favicon oficial de la pestaña del navegador con el título formal del colegio.

### R3. Nomenclatura y Claridad Conceptual (Simulador y Reactivaciones)
- **Excusas y Reactivaciones**: Reorganizar la pestaña a *"Gestión de Excusas y Reactivación de Cupos"*, explicando claramente que es la bandeja donde la Coordinadora evalúa las incapacidades/justificantes enviados por los alumnos para devolverles el estado `Activo`.
- **Simulador de Asistencias**: Renombrar a *"Simulador de Asistencias & Control de Suspensiones"*, explicando que permite probar cómo impactan las inasistencias en días hábiles escolares (lunes a viernes no festivos en Colombia) e identificar suspensiones automáticas por 3 faltas consecutivas.

### R4. Selector de Estado en el Modal de Edición de Estudiante
- En las vistas de **Estudiantes** y **Cursos/Grupos**, el modal de edición de un estudiante incorporará un control `<select v-model="form.estado">` con los estados válidos (`Activo`, `Suspendido`, `Inactivo`, `Sin Registrar`), permitiendo a la coordinadora corregir datos y estado en una sola acción.
