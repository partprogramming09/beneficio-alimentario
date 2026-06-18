# Tareas de Trabajo: Rediseño de Layout (Sidebar Vertical + Aside Drawer)

## Checklist de Implementación

- [x] **[T-701] Crear el Componente de Detalles Lateral (StudentAside.vue)**
  - **Criterio de Done**: Crear `frontend-coordinadora/src/components/StudentAside.vue` con la maquetación del panel lateral (Aside), barra de inasistencias y botones para ejecutar las acciones (`aprobar`, `rechazar`, `reingresar/reactivar`, `eliminar`).
  - **Commit Sugerido**: `[T-701] feat: create StudentAside drawer component`

- [x] **[T-702] Implementar Sidebar y Ajustar DashboardPage**
  - **Criterio de Done**: Modificar `frontend-coordinadora/src/pages/DashboardPage.vue` para incluir la estructura de dos columnas (Sidebar lateral rojo a la izquierda y listados a la derecha), enlazar la navegación vertical e integrar el componente `StudentAside`.
  - **Commit Sugerido**: `[T-702] feat: integrate Sidebar navigation and Aside drawer in DashboardPage`

- [x] **[T-703] Limpiar el Layout Base de Coordinadora**
  - **Criterio de Done**: Modificar `frontend-coordinadora/src/layouts/AdminLayout.vue` para remover la cabecera repetida y dejar que sea el Sidebar del Dashboard quien maneje la estructura completa de pantalla completa.
  - **Commit Sugerido**: `[T-703] refactor: clean up AdminLayout header structures`
