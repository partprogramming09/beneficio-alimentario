<template>
  <div class="coordinadora-container">
    <div class="card header-card admin-header">
      <h2>Panel Administrativo de la Coordinadora 👩‍💼</h2>
      <p>Administra los beneficiarios, aprueba solicitudes, consulta reportes de asistencia y gestiona reactivaciones.</p>
    </div>

    <!-- Administrative Tabs -->
    <div class="sub-tabs">
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

    <AlertBox :message="message" :isError="isError" class="mb-3" />

    <!-- Dynamic tab rendering -->
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
</template>

<script>
import { getAdminStudents } from '../services/api'
import AlertBox from '../components/AlertBox.vue'
import AprobacionesTab from './coordinadora/AprobacionesTab.vue'
import EstudiantesTab from './coordinadora/EstudiantesTab.vue'
import ReportesTab from './coordinadora/ReportesTab.vue'
import ReactivacionesTab from './coordinadora/ReactivacionesTab.vue'
import SimuladorTab from './coordinadora/SimuladorTab.vue'

export default {
  name: 'CoordinadoraView',
  components: {
    AlertBox,
    AprobacionesTab,
    EstudiantesTab,
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

<style scoped src="../assets/css/coordinadora.css">
</style>
