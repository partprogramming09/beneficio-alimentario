<template>
  <div class="coordinadora-dashboard-layout">
    <!-- Left Sidebar (Crimson Modern Theme) -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <div class="brand-shield">🛡️</div>
        <div class="brand-text">
          <h4>I.E. Enrique Vélez Escobar</h4>
          <span class="brand-subtitle">Gestión Comedor</span>
        </div>
      </div>
      
      <nav class="sidebar-nav">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :class="['nav-item', { active: activeSubTab === tab.id }]"
          @click="activeSubTab = tab.id"
        >
          <span class="nav-icon">{{ getTabIcon(tab.id) }}</span>
          <span class="nav-label">{{ tab.label }}</span>
          <span v-if="tab.id === 'pendientes' && pendingCount > 0" class="badge-count">{{ pendingCount }}</span>
          <span v-else-if="tab.id === 'excusas' && suspendedStudents.length > 0" class="badge-count warning-badge">{{ suspendedStudents.length }}</span>
        </button>
      </nav>

      <div class="sidebar-footer">
        <div class="user-profile">
          <div class="user-avatar">CO</div>
          <div class="user-meta">
            <strong>Coordinación Escolar</strong>
            <span>Administrador Activo</span>
          </div>
        </div>
        <button v-if="toggleTheme" class="btn-theme-sidebar" @click="toggleTheme" title="Cambiar Tema (Claro/Oscuro)">
          {{ isThemeDark ? '☀️ Claro' : '🌙 Oscuro' }}
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content-area">
      <!-- Header con KPIs Dinámicos y Título Limpio -->
      <header class="content-header">
        <div class="header-title-section">
          <h2>{{ getTabTitle(activeSubTab) }}</h2>
        </div>

        <div class="kpi-group">
          <div class="kpi-card">
            <span class="kpi-val">{{ pendingCount }}</span>
            <span class="kpi-lbl">Inscritos Hoy</span>
          </div>
          <div class="kpi-card">
            <span class="kpi-val">{{ allStudents.length }}</span>
            <span class="kpi-lbl">Total Beneficiarios</span>
          </div>
          <div class="kpi-card warning-kpi">
            <span class="kpi-val">{{ suspendedStudents.length }}</span>
            <span class="kpi-lbl">Suspendidos</span>
          </div>
        </div>
      </header>

      <AlertBox :message="message" :isError="isError" class="mb-3" />

      <!-- Dynamic tab rendering -->
      <div class="card content-card">
        <keep-alive>
          <component 
            :is="activeTabComponent"
            :students="tabStudents"
            :suspended-students="suspendedStudents"
            :active-students="activeStudentsForSim"
            @refresh-students="loadStudents"
            @select-student="selectedStudent = $event"
          ></component>
        </keep-alive>
      </div>
    </main>

    <!-- Student Detail Slide-in Drawer -->
    <StudentAside 
      :student="selectedStudent" 
      @close="selectedStudent = null"
      @refresh="loadStudents"
      @update-student="updateStudentState"
      @remove-student="removeStudentState"
    />
  </div>
</template>

<script>
import { getAdminStudents } from '../services/api'
import AlertBox from '../components/AlertBox.vue'
import AprobacionesTab from '../components/AprobacionesTab.vue'
import EstudiantesTab from '../components/EstudiantesTab.vue'
import AsistenciaTab from '../components/AsistenciaTab.vue'
import ReportesTab from '../components/ReportesTab.vue'
import ReactivacionesTab from '../components/ReactivacionesTab.vue'
import SimuladorTab from '../components/SimuladorTab.vue'
import StudentAside from '../components/StudentAside.vue'

export default {
  name: 'DashboardPage',
  inject: {
    toggleTheme: { default: null },
    isDark: { default: () => false }
  },
  components: {
    AlertBox,
    AprobacionesTab,
    EstudiantesTab,
    AsistenciaTab,
    ReportesTab,
    ReactivacionesTab,
    SimuladorTab,
    StudentAside
  },
  data() {
    return {
      activeSubTab: 'pendientes',
      tabs: [
        { id: 'pendientes', label: 'Inscritos Hoy' },
        { id: 'listado', label: 'Estudiantes' },
        { id: 'asistencia', label: 'Registrar Almuerzo' },
        { id: 'reportes', label: 'Reportes Asistencia' },
        { id: 'excusas', label: 'Reactivaciones' },
        { id: 'simulador', label: 'Simulador Reglas' }
      ],
      allStudents: [],
      selectedStudent: null,
      message: '',
      isError: false
    }
  },
  computed: {
    isThemeDark() {
      return typeof this.isDark === 'function' ? this.isDark() : false;
    },
    activeTabComponent() {
      switch (this.activeSubTab) {
        case 'pendientes': return 'AprobacionesTab'
        case 'listado': return 'EstudiantesTab'
        case 'asistencia': return 'AsistenciaTab'
        case 'reportes': return 'ReportesTab'
        case 'excusas': return 'ReactivacionesTab'
        case 'simulador': return 'SimuladorTab'
        default: return 'AprobacionesTab'
      }
    },
    pendingStudents() {
      const hoyStr = new Date().toISOString().split('T')[0];
      return this.allStudents.filter(s => s.creado_en && s.creado_en.startsWith(hoyStr))
    },
    pendingCount() {
      return this.pendingStudents.length
    },
    suspendedStudents() {
      return this.allStudents.filter(s => s.estado === 'Suspendido')
    },
    activeStudentsForSim() {
      return this.allStudents.filter(s => s.estado === 'Activo' || s.estado === 'Suspendido')
    },
    tabStudents() {
      if (this.activeSubTab === 'pendientes') {
        return this.pendingStudents
      }
      return this.allStudents
    }
  },
  watch: {
    activeSubTab(newTab) {
      this.clearMessages()
      this.selectedStudent = null
      if (newTab === 'pendientes' || newTab === 'listado' || newTab === 'simulador' || newTab === 'excusas') {
        this.loadStudents()
      }
    }
  },
  mounted() {
    this.loadStudents()
  },
  methods: {
    clearMessages() {
      this.message = ''
      this.isError = false
    },
    async loadStudents() {
      try {
        const data = await getAdminStudents()
        this.allStudents = data
        
        if (this.selectedStudent) {
          const updated = this.allStudents.find(s => s.documento === this.selectedStudent.documento)
          if (updated) {
            this.selectedStudent = updated
          }
        }
      } catch (err) {
        console.error(err)
        this.message = err.message
        this.isError = true
      }
    },
    getTabIcon(id) {
      switch (id) {
        case 'pendientes': return '📝'
        case 'listado': return '👥'
        case 'asistencia': return '🍽️'
        case 'reportes': return '📊'
        case 'excusas': return '🛡️'
        case 'simulador': return '⚡'
        default: return '📄'
      }
    },
    getTabTitle(id) {
      switch (id) {
        case 'pendientes': return 'Inscritos Hoy'
        case 'listado': return 'Beneficiarios Registrados'
        case 'asistencia': return 'Registro de Asistencia'
        case 'reportes': return 'Reportes e Historial'
        case 'excusas': return 'Reactivaciones y Justificaciones'
        case 'simulador': return 'Simulador de Reglas'
        default: return 'Panel de Gestión'
      }
    },
    updateStudentState(updatedStudent) {
      const index = this.allStudents.findIndex(s => s.documento === updatedStudent.documento)
      if (index !== -1) {
        this.allStudents.splice(index, 1, updatedStudent)
      }
      if (this.selectedStudent && this.selectedStudent.documento === updatedStudent.documento) {
        this.selectedStudent = updatedStudent
      }
    },
    removeStudentState(doc) {
      this.allStudents = this.allStudents.filter(s => s.documento !== doc)
      if (this.selectedStudent && this.selectedStudent.documento === doc) {
        this.selectedStudent = null
      }
    }
  }
}
</script>

<style scoped>
.coordinadora-dashboard-layout {
  display: flex;
  min-height: 100vh;
  margin: -20px -10px;
  background-color: var(--bg-primary);
  animation: fadeIn var(--transition-normal);
}

/* Sidebar Carmesí Moderno */
.sidebar {
  width: 260px;
  background: var(--gradient-primary);
  color: white;
  display: flex;
  flex-direction: column;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  flex-shrink: 0;
  box-shadow: var(--shadow-lg);
}

.sidebar-brand {
  padding: 24px 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}

.brand-shield {
  font-size: 1.8rem;
  filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3));
}

.brand-text h4 {
  margin: 0;
  font-size: 0.98rem;
  font-weight: 800;
  line-height: 1.2;
  color: white;
}

.brand-subtitle {
  font-size: 0.75rem;
  color: hsl(var(--secondary-hue), 80%, 75%);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.sidebar-nav {
  padding: 20px 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 14px;
  border-radius: var(--border-radius-sm);
  color: rgba(255, 255, 255, 0.85);
  font-weight: 600;
  font-size: 0.92rem;
  text-align: left;
  background: none;
  border: none;
  cursor: pointer;
  width: 100%;
  transition: all var(--transition-fast) ease;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.12);
  color: white;
  transform: translateX(3px);
}

.nav-item.active {
  background-color: var(--bg-secondary);
  color: var(--primary);
  box-shadow: var(--shadow-sm);
}

.nav-icon {
  font-size: 1.1rem;
}

.nav-label {
  flex: 1;
}

.badge-count {
  background-color: var(--bg-secondary);
  color: var(--primary);
  border-radius: var(--border-radius-pill);
  padding: 2px 8px;
  font-size: 0.75rem;
  font-weight: 800;
}

.warning-badge {
  background-color: var(--warning-light);
  color: var(--warning);
}

.nav-item.active .badge-count {
  background-color: var(--primary);
  color: white;
}

.sidebar-footer {
  padding: 16px 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background-color: rgba(0, 0, 0, 0.12);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
}

.user-meta strong {
  display: block;
  font-size: 0.85rem;
  color: white;
}

.user-meta span {
  font-size: 0.72rem;
  color: rgba(255, 255, 255, 0.65);
}

.btn-theme-sidebar {
  background-color: rgba(255, 255, 255, 0.15);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.25);
  padding: 6px 12px;
  border-radius: var(--border-radius-pill);
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  transition: all var(--transition-fast);
  white-space: nowrap;
}

.btn-theme-sidebar:hover {
  background-color: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
}

/* Main Content & KPI Header */
.main-content-area {
  flex: 1;
  padding: 30px 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.content-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 16px;
  flex-wrap: wrap;
  gap: 15px;
}

.header-title-section h2 {
  margin: 0;
  font-size: 1.5rem !important;
  font-weight: 800;
  color: var(--text-primary);
  letter-spacing: -0.3px;
}

.kpi-group {
  display: flex;
  gap: 12px;
}

.kpi-card {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-sm);
  padding: 8px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: var(--shadow-sm);
  min-width: 100px;
}

.kpi-val {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--primary);
  line-height: 1;
}

.kpi-lbl {
  font-size: 0.72rem;
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
  margin-top: 4px;
}

.warning-kpi .kpi-val {
  color: var(--warning);
}

.content-card {
  box-shadow: var(--shadow-sm);
  background-color: var(--bg-secondary);
  border-radius: var(--border-radius-md);
  padding: 24px;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 992px) {
  .coordinadora-dashboard-layout {
    flex-direction: column;
    margin: 0;
  }
  .sidebar {
    width: 100%;
  }
  .sidebar-nav {
    flex-direction: row;
    flex-wrap: wrap;
    padding: 10px;
  }
  .nav-item {
    flex: 1;
    min-width: 130px;
    padding: 8px;
    justify-content: center;
  }
  .sidebar-footer {
    display: flex;
  }
}
</style>


