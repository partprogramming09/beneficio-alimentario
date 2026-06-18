# Documento de Negocio: Control Interno y Panel Lateral (Aside) de Estudiantes

Este documento describe el flujo operativo y el diseño visual para el panel lateral de detalles de los estudiantes (Aside), permitiendo a la Coordinadora gestionar de manera formal y eficiente a los alumnos del beneficio alimentario.

---

## 1. Contexto del Control Interno

En el modelo de negocio actual, el control de los alumnos beneficiados se centraliza en el panel administrativo de la Coordinadora:
1. **Existencia Previa del Estudiante**: Los estudiantes ya están matriculados y precargados en la base de datos oficial del colegio.
2. **Inscripción y Control Interno**: La Coordinadora administra las solicitudes y registros. Una vez asignado el beneficio, el sistema realiza de forma transparente el control interno de sus estados (Activo, Suspendido, Inactivo, Pendiente).
3. **El Problema**: El listado de estudiantes contiene demasiados datos (documento, grupo, estado, inasistencias, etc.) y realizar acciones directas sobre una tabla saturada daña la experiencia de usuario (UX) en pantallas de tabletas o dispositivos móviles de uso rápido.

---

## 2. Solución Propuesta: Panel Lateral de Detalles (Aside)

En lugar de recargar la tabla o redirigir a otra página, al hacer clic sobre la fila de un estudiante se desplegará un panel lateral derecho (Aside Drawer) animado con transiciones suaves.

### 2.1. Estructura y Contenido del Aside:
```
┌──────────────────────────────────────────┐
│ Estudiante: Carlos Mario Pérez Gómez  ✖ │ <-- Nombre y Botón Cerrar
├──────────────────────────────────────────┤
│ 🛡️ Información General                  │
│ • Documento: 1023456789                  │
│ • Grado/Grupo: 10-A                      │
│ • Estado: [ ACTIVO ] (Badge dinámico)    │
├──────────────────────────────────────────┤
│ 📊 Estadísticas de Almuerzo              │
│ • Almuerzos Consumidos: 12               │
│ • Inasistencias Consecutivas: 2 / 3      │
│   (Barra de progreso de riesgo de susp.) │
├──────────────────────────────────────────┤
│ ⚡ Acciones Administrativas             │
│   [ APROBAR ]   [ SUSPENDER ]            │
│   [ REACTIVAR ] [ DAR DE BAJA ]          │
└──────────────────────────────────────────┘
```

### 2.2. Flujo Operativo del Aside:
1. **Selección**: La Coordinadora hace clic en cualquier fila de la lista de estudiantes.
2. **Despliegue**: El panel Aside emerge desde la derecha de la pantalla (`translate-x(0)`) con un fondo translúcido (efecto glassmorphic) y desenfoque de fondo.
3. **Monitoreo**: El panel muestra información detallada del estudiante, incluyendo la barra de riesgo de suspensión (que avisa visualmente si el alumno tiene 1 o 2 faltas consecutivas y está cerca de ser suspendido automáticamente en la tercera falta).
4. **Acciones en Caliente**: Desde este panel, la Coordinadora puede realizar acciones rápidas sin perder el contexto de la lista. Tras procesarse la acción (ej. aprobar o suspender), el panel se actualiza y la tabla de fondo se refresca automáticamente.

---

## 3. Criterios de Diseño Visual (Aesthetics)
- **Fondo Glassmorphism**: `rgba(255, 255, 255, 0.85)` con `backdrop-filter: blur(12px)`.
- **Efectos de Transición**: El Aside debe tener una animación fluida (`transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)`).
- **Indicadores de Riesgo**: Las inasistencias consecutivas se representarán con una barra de peligro que se torna roja a medida que se acerca a 3.

---

## 4. Beneficios del Flujo
- **Reducción de Carga Cognitiva**: La tabla principal queda limpia, mostrando solo datos esenciales (Documento, Nombre, Grupo, Estado). Los detalles avanzados y las acciones se delegan al Aside.
- **Optimización Móvil**: Facilita la operación táctil en la fila del comedor mediante tabletas.
- **Acceso Rápido**: Permite a la coordinadora buscar un estudiante, ver su historial completo de inasistencias y reactivarlo en segundos.
