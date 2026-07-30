# 📄 SPEC: Resolución de Conflictos de Registro e Integridad entre Coordinadora y Portal de Estudiantes

> **Proyecto:** Sistema de Beneficio Alimentario - I.E. Enrique Vélez Escobar  
> **Fecha:** 2026-07-30  
> **Estado:** Aprobado para Diseño  

---

## 🎯 1. Objetivo General
Eliminar los conflictos de desincronización de datos, perdidas de historial y estados inconsistentes que ocurren entre el **Portal de Estudiantes** (autoregistro voluntario y ticket diario) y el **Panel de la Coordinadora** (control de matrícula, edición de datos y simulación), garantizando una arquitectura modular y 100% transaccional.

---

## 🔍 2. Diagnóstico de Conflictos Actuales

| # | Conflicto Identificado | Impacto en el Sistema | Causa Raíz Técnica |
|---|---|---|---|
| **C1** | **Edición de Documento Corrompe Historial** | Al modificar una cédula/TI en el panel de la coordinadora, las asistencias y comprobantes pasados se eliminaban o quedaban huérfanos. | `updateStudent` hacía borrado y recreación sin transacciones en cascada sobre `asistencias`, `comprobantes` y `justificaciones`. |
| **C2** | **Re-registro No Autorisado** | Si la coordinadora desactivaba o quitaba el cupo a un estudiante (`toggleCupo`), este podía volver a registrarse libremente desde el portal. | `toggleCupo` borraba la fila en `estudiantes` en lugar de marcar estado `'Inactivo'`, haciendo que `validateStudent` lo tratara como un estudiante nuevo "sin registrar". |
| **C3** | **Fragmentación de Nombres Compuestos** | Nombres compuestos (ej. "Juan Carlos Perez Rodriguez") se guardaban mal o se dividían incorrectamente al sincronizar entre `institucion_estudiantes` y `estudiantes`. | Uso de `explode(' ', $nombre, 2)` ambiguo y falta de normalización unificada. |
| **C4** | **Monolito de Servicio en Backend** | `AdminService.php` acumulaba 646+ líneas gestionando simultáneamente matrículas, beneficiarios, simulación, reportería y grupos. | Falta de separación de responsabilidades en sub-servicios del módulo de Administración. |

---

## 📋 3. Reglas de Negocio Estrictas (SSOT - Single Source of Truth)

1. **Jerarquía de Tablas (SSOT)**:
   - `institucion_estudiantes` es la **Fuente Única de Verdad Institucional** (Matrícula oficial cargada por la Coordinadora).
   - `estudiantes` representa la **Inscripción y Estado del Beneficio Alimentario**.
2. **Ciclo de Vida de Estados del Beneficio**:
   - `Sin Registrar`: Figura en `institucion_estudiantes`, pero no en `estudiantes`.
   - `Activo`: Registrado voluntariamente o activado por la coordinadora. Canjea ticket diario.
   - `Suspendido`: Bloqueado automáticamente tras acumulado de 3 inasistencias consecutivas.
   - `Inactivo`: Desactivado por renuncia voluntaria o por decisión de la coordinadora. **NO puede autoregistrarse en el portal sin intervención de la coordinadora**.
3. **Integridad Transaccional**:
   - Toda actualización del número de documento debe propagarse de forma atómica a `institucion_estudiantes`, `estudiantes`, `asistencias`, `comprobantes` y `justificaciones`.
4. **Preservación del Historial**:
   - Jamás se deben eliminar físicamente registros de asistencia o comprobantes al cambiar de estado o actualizar un estudiante.

---

## 👥 4. Historias de Usuario

- **HU-01 (Coordinadora - Edición de Documento)**: Como coordinadora, quiero corregir o actualizar el número de documento de un estudiante matriculado sin que se borre ni se pierda su historial de asistencias pasadas.
- **HU-02 (Estudiante - Intento de Re-registro Inactivo)**: Como estudiante que he sido desactivado por la coordinadora o que he renunciado, al intentar registrarme nuevamente en el portal, el sistema debe informarme que debo solicitar mi reactivación con la coordinadora.
- **HU-03 (Coordinadora - Control Claro de Cupos)**: Como coordinadora, quiero que la opción de "Quitar/Asignar Cupo" cambie de forma limpia el estado del estudiante a `Inactivo` / `Activo` sin borrar su registro histórico.
