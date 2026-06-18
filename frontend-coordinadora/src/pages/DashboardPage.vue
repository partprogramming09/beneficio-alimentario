<template>
  <div class="coordinadora-dashboard-layout">
    <!-- Left Sidebar (Crimson Red Theme) -->
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
        </button>
      </nav>

      <div class="sidebar-footer">
        <div class="user-profile">
          <div class="user-avatar">CO</div>
          <div class="user-meta">
            <strong>Coordinadora</strong>
            <span>Administrador</span>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content-area">
      <header class="content-header">
        <div class="header-title-section">
          <h2>{{ getTabTitle(activeSubTab) }}</h2>
          <p class="description-text">{{ getTabDescription(activeSubTab) }}</p>
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
        { id: 'pendientes', label: 'Aprobaciones' },
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
      return this.allStudents.filter(s => s.estado === 'Pendiente')
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
        
        // Mantener actualizado el estudiante seleccionado si está abierto
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
        case 'excusas': return '⚠️'
        case 'simulador': return '⚙️'
        default: return '📄'
      }
    },
    getTabTitle(id) {
      switch (id) {
        case 'pendientes': return 'Solicitudes de Inscripción'
        case 'listado': return 'Listado de Beneficiarios'
        case 'asistencia': return 'Registrar Asistencia en Fila'
        case 'reportes': return 'Reportes e Historial'
        case 'excusas': return 'Reactivaciones y Justificaciones'
        case 'simulador': return 'Simulador de Reglas'
        default: return 'Panel Administrativo'
      }
    },
    getTabDescription(id) {
      switch (id) {
        case 'pendientes': return 'Aprueba o rechaza nuevas solicitudes de ingreso de estudiantes.'
        case 'listado': return 'Monitorea el estado y el historial de inasistencias de los alumnos registrados.'
        case 'asistencia': return 'Registra la asistencia en tiempo real de los estudiantes en la fila del almuerzo.'
        case 'reportes': return 'Genera reportes diarios y semanales de asistencia del comedor escolar.'
        case 'excusas': return 'Revisa los justificativos de alumnos suspendidos y gestiona sus reactivaciones.'
        case 'simulador': return 'Simula la asistencia del día escolar para evaluar las reglas de suspensión automática.'
        default: return 'Gestiona el beneficio alimentario de la institución.'
      }
    }
  }
}
</script>

<style scoped>
/* 2-Column Dashboard Layout */
.coordinadora-dashboard-layout {
  display: flex;
  min-height: 100vh;
  margin: -20px -10px; /* Offset parent layout padding */
  background-color: var(--bg-primary);
}

/* Sidebar Styling (Crimson Red Theme) */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, hsl(var(--primary-hue), 75%, 38%) 0%, hsl(var(--primary-hue), 75%, 28%) 100%);
  color: white;
  display: flex;
  flex-direction: column;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  flex-shrink: 0;
  box-shadow: var(--shadow-lg);
}

.sidebar-brand {
  padding: 30px 24px;
  display: flex;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand-shield {
  font-size: 2.2rem;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.brand-text h4 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.2;
  color: white;
}

.brand-subtitle {
  font-size: 0.8rem;
  color: var(--success-light);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.sidebar-nav {
  padding: 24px 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: var(--border-radius-sm);
  color: rgba(255, 255, 255, 0.82);
  font-weight: 600;
  font-size: 0.95rem;
  text-align: left;
  background: none;
  border: none;
  cursor: pointer;
  width: 100%;
  transition: all var(--transition-fast) ease;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.08);
  color: white;
}

.nav-item.active {
  background-color: var(--bg-secondary);
  color: var(--primary);
  box-shadow: var(--shadow-sm);
}

.nav-icon {
  font-size: 1.2rem;
}

.nav-label {
  flex: 1;
}

.badge-count {
  background-color: var(--bg-secondary);
  color: var(--primary);
  border-radius: 50%;
  padding: 2px 7px;
  font-size: 0.75rem;
  font-weight: 800;
  border: 1.5px solid var(--primary);
}

.nav-item.active .badge-count {
  background-color: var(--primary);
  color: white;
  border-color: var(--primary);
}

.sidebar-footer {
  padding: 20px 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background-color: rgba(0, 0, 0, 0.1);
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.95rem;
}

.user-meta strong {
  display: block;
  font-size: 0.9rem;
  color: white;
}

.user-meta span {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
}

/* Main Content Styling */
.main-content-area {
  flex: 1;
  padding: 40px 30px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.content-header {
  border-bottom: 2px solid var(--border-color);
  padding-bottom: 20px;
}

.header-title-section h2 {
  margin: 0 0 6px 0;
  font-size: 1.6rem !important;
  font-weight: 800;
  color: var(--primary);
}

.description-text {
  margin: 0;
  color: var(--text-secondary);
  font-size: 0.98rem;
}

.content-card {
  box-shadow: var(--shadow-sm);
  background-color: var(--bg-secondary);
}

@media (max-width: 992px) {
  .coordinadora-dashboard-layout {
    flex-direction: column;
    margin: 0;
  }
  .sidebar {
    width: 100%;
    height: auto;
  }
  .sidebar-brand {
    padding: 20px;
  }
  .sidebar-nav {
    flex-direction: row;
    flex-wrap: wrap;
    padding: 10px;
  }
  .nav-item {
    flex: 1;
    min-width: 140px;
    padding: 10px;
    justify-content: center;
  }
  .sidebar-footer {
    display: none;
  }
  .main-content-area {
    padding: 20px 15px;
  }
}
</style>
