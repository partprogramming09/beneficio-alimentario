<template>
  <div class="coordinadora-container">
    <div class="card header-card admin-header">
      <h2>Panel Administrativo de la Coordinadora 👩‍💼</h2>
      <p class="description">Administra los beneficiarios, aprueba solicitudes, consulta reportes de asistencia y gestiona reactivaciones.</p>
    </div>

    <!-- Administrative Tabs -->
    <div class="sub-tabs card">
      <div class="tabs-nav">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :class="['sub-tab-btn', { active: activeSubTab === tab.id }]"
          @click="activeSubTab = tab.id"
        >
          {{ tab.label }}
          <span v-if="tab.id === 'pendientes' && pendingCount > 0" class="badge-count">{{ pendingCount }}</span>
        </button>
      </div>
    </div>

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
        ></component>
      </keep-alive>
    </div>
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

export default {
  name: 'DashboardPage',
  components: {
    AlertBox,
    AprobacionesTab,
    EstudiantesTab,
    AsistenciaTab,
    ReportesTab,
    ReactivacionesTab,
    SimuladorTab
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
      } catch (err) {
        console.error(err)
        this.message = err.message
        this.isError = true
      }
    }
  }
}
</script>

<style scoped>
.coordinadora-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.admin-header {
  border-left: 6px solid var(--accent);
}

.sub-tabs {
  padding: 10px;
}

.tabs-nav {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.sub-tab-btn {
  flex: 1;
  min-width: 130px;
  padding: 12px;
  border-radius: var(--border-radius-sm);
  background-color: var(--bg-tertiary);
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all var(--transition-fast);
}

.sub-tab-btn:hover {
  background-color: var(--border-color);
  color: var(--text-primary);
}

.sub-tab-btn.active {
  background-color: var(--primary);
  color: white;
  box-shadow: var(--shadow-sm);
}

.badge-count {
  background-color: var(--danger);
  color: white;
  border-radius: 50%;
  padding: 2px 7px;
  font-size: 0.75rem;
  font-weight: bold;
}

.content-card {
  min-height: 300px;
}

.description {
  margin-bottom: 0px !important;
}

@media (max-width: 768px) {
  .tabs-nav {
    flex-direction: column;
  }
  .sub-tab-btn {
    width: 100%;
  }
}
</style>
