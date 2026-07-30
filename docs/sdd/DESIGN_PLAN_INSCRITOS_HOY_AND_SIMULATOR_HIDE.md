# 🏗️ DESIGN PLAN: Filtrado de Inscritos Hoy y Remoción de Simulación

## 🛠️ Modificaciones

### 1. `frontend-coordinadora/src/views/DashboardView.vue` y `frontend-coordinadora/src/pages/DashboardPage.vue`

- Modificar `pendingStudents()`:
```javascript
pendingStudents() {
  const hoyStr = new Date().toISOString().split('T')[0];
  return this.allStudents.filter(s => 
    s.creado_en && 
    s.creado_en.startsWith(hoyStr) && 
    (s.estado === 'Activo' || s.estado === 'Pendiente')
  );
}
```

- Modificar `tabs`:
```javascript
tabs: [
  { id: 'pendientes', label: 'Inscritos Hoy' },
  { id: 'listado', label: 'Estudiantes' },
  { id: 'cursos', label: 'Cursos y Grupos' },
  { id: 'asistencia', label: 'Registrar Almuerzo' },
  { id: 'reportes', label: 'Reportes Asistencia' },
  { id: 'excusas', label: 'Reactivaciones' }
]
```

- Remover la pestaña `simulador` en `activeTabComponent`:
```javascript
activeTabComponent() {
  switch (this.activeSubTab) {
    case 'pendientes': return 'AprobacionesTab'
    case 'listado': return 'EstudiantesTab'
    case 'cursos': return 'CursosTab'
    case 'asistencia': return 'AsistenciaTab'
    case 'reportes': return 'ReportesTab'
    case 'excusas': return 'ReactivacionesTab'
    default: return 'AprobacionesTab'
  }
}
```

### 2. Pruebas y Validación
- Ejecutar `npm run build` en `frontend-coordinadora`.
