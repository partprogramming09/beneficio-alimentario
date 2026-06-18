# Plan Técnico: Rediseño de Layout (Sidebar Vertical + Aside Drawer)

## Análisis Arquitectónico
Este cambio requiere desacoplar el estado de la pestaña activa de `DashboardPage.vue` para que sea manejado o enlazado por el nuevo componente de navegación vertical (Sidebar) en `AdminLayout.vue`, o inyectado directamente en el flujo del panel.
Para mantener el flujo simple y directo:
1. **Layout e Integración del Sidebar (`AdminLayout.vue` y `DashboardPage.vue`)**:
   - Definiremos la barra lateral (`Sidebar`) izquierda fija en `AdminLayout.vue` o directamente en `DashboardPage.vue` para simplificar la sincronización del estado de la pestaña activa (`activeSubTab`). Si lo hacemos en `DashboardPage.vue` con un diseño de rejilla (Grid / Flexbox), mantendremos el estado reactivo de Vue de forma directa y limpia sin emitir múltiples eventos de layout.
   - Usaremos Flexbox: una barra lateral izquierda de `260px` de ancho con fondo Crimson (`var(--primary)`), y un área de contenido central flexible (`flex: 1`) con fondo blanco/gris (`var(--bg-primary)`).
2. **Implementación del Aside Drawer (`StudentAside.vue`)**:
   - Crearemos un nuevo componente `StudentAside.vue` en `frontend-coordinadora/src/components/StudentAside.vue`.
   - Este componente recibirá al estudiante seleccionado como propiedad (`student`), y un callback o evento para cerrar el panel y refrescar la lista.
   - Tendrá una clase CSS de drawer deslizante: `position: fixed; right: 0; top: 0; width: 400px; height: 100vh;` con transiciones de CSS usando `transform` y desenfoque de fondo.

## Archivos por Modificar/Crear
- `frontend-coordinadora/src/components/StudentAside.vue` (crear)
- `frontend-coordinadora/src/pages/DashboardPage.vue` (modificar)
- `frontend-coordinadora/src/layouts/AdminLayout.vue` (modificar)

## Riesgos y Mitigaciones
- **Riesgo: Layout roto en pantallas móviles**.
  - *Mitigación*: En pantallas de menos de `768px`, ocultaremos el Sidebar lateral izquierdo y utilizaremos un menú hamburguesa o lo colapsaremos en la parte superior para asegurar la adaptabilidad móvil de la interfaz. El Aside pasará a ocupar el `100%` del ancho en pantallas móviles.
