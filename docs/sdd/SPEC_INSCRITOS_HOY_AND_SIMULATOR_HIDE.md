# 📋 SPEC: Filtrado Estricto de "Inscritos Hoy" y Ocultamiento de la Sección de Simulación

## 🎯 Objetivo
1. **Filtrado de "Inscritos Hoy":** Excluir cualquier estudiante que se encuentre en estado `Inactivo`, `Suspendido` o `Sin Registrar`. Únicamente deben contabilizarse y visualizarse en "Inscritos Hoy" los estudiantes registrados el día actual cuyo estado sea `Activo` o `Pendiente`.
2. **Ocultar Sección Simulación:** Remover la pestaña y sección del "Simulador de Reglas" de la interfaz de la Coordinadora.

---

## 📜 Reglas de Negocio

### 1. KPI y Pestaña "Inscritos Hoy"
- Condición de inclusión:
  - `creado_en` corresponde a la fecha de hoy (`YYYY-MM-DD`).
  - `estado` es estrictamente `Activo` o `Pendiente`.
- Si un estudiante registrado hoy pasa a estado `Inactivo` o `Suspendido`, desaparece inmediatamente de la lista y del contador del KPI "Inscritos Hoy".

### 2. Navegación del Panel de Coordinación
- Eliminar la opción "Simulador Reglas" / "Simulador & Control Suspensiones" del menú lateral (`tabs`).
- Deshabilitar el componente dinámico `SimuladorTab` de la navegación.

---

## 🔍 Requisitos Técnicos

### Frontend (`frontend-coordinadora/src/views/DashboardView.vue` y `frontend-coordinadora/src/pages/DashboardPage.vue`)
- Modificar la propiedad computada `pendingStudents()` con la condición de estado estricta.
- Remover el objeto `simulador` del arreglo `tabs`.
- Ajustar `activeTabComponent`, `getTabIcon` y `getTabTitle`.
