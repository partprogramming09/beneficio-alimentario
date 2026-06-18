# Spec: Rediseño de Layout (Sidebar Vertical + Aside Drawer)

## Contexto y Motivación
Para lograr que la aplicación administrativa de la Coordinadora refleje la propuesta premium e institucional (colores rojo, verde y blanco) y se parezca al mockup aprobado:
- **Antes**: La navegación se realiza mediante pestañas horizontales y no hay panel de detalles rápido.
- **Ahora**: Se estructurará el panel con un Sidebar de navegación izquierdo elegante (rojo) y un Main Content central (blanco/gris). Al hacer clic en un estudiante, se desplegará el panel lateral derecho (Aside Drawer) con detalles y acciones del alumno.

## Actores y Flujos
- **Coordinadora**:
  1. Navega por las secciones (Aprobaciones, Estudiantes, Registrar Almuerzo, Reportes, etc.) usando el Sidebar vertical a la izquierda.
  2. Selecciona un estudiante de la lista.
  3. Visualiza los detalles completos en el Aside Drawer que emerge por el lateral derecho, y ejecuta acciones rápidas (Aprobar, Suspender, etc.) directamente desde allí.

## Metas (Goals)
1. Reemplazar las pestañas horizontales de `DashboardPage.vue` por un Sidebar vertical en `AdminLayout.vue`.
2. Implementar un panel de detalles deslizable (`Aside` o `StudentAside.vue`) en `DashboardPage.vue` para concentrar la información detallada del estudiante y sus acciones administrativas.
3. Asegurar que las interacciones y transiciones sean fluidas y utilicen los colores rojo, verde y blanco del nuevo tema en HSL.

## No-Metas (Non-Goals)
1. No se modificará el portal del estudiante, el cual se mantiene con su estructura superior y su Dashboard actual.
2. No se modificará la base de datos ni los endpoints existentes del backend.

## Criterios de Aceptación (AC)
- [x] **AC-1**: El panel de la coordinadora tiene un Sidebar vertical fijo a la izquierda en color rojo institucional, con el nombre y escudo de la institución.
- [x] **AC-2**: Al hacer clic en las opciones del Sidebar, cambian los contenidos de la pantalla principal correctamente sin romper el estado del dashboard.
- [x] **AC-3**: Al hacer clic sobre una fila de un estudiante en los listados (Aprobaciones, Estudiantes o Reactivaciones), emerge un panel lateral Aside derecho.
- [x] **AC-4**: El Aside muestra el nombre, documento, grupo, estado, cantidad de inasistencias consecutivas y los botones para ejecutar acciones rápidas que refrescan el listado al completarse.
