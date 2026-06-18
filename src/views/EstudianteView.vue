<template>
  <div class="estudiante-container">
    <div class="card header-card">
      <h2>Portal del Estudiante 🎓</h2>
      <p>Registra tu perfil, marca tu asistencia diaria y obtén tu comprobante para el comedor escolar.</p>
    </div>

    <!-- Tabs inside Student Portal -->
    <div class="sub-tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        :class="['sub-tab-btn', { active: activeSubTab === tab.id }]"
        @click="activeSubTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Dynamic tab rendering -->
    <keep-alive>
      <component :is="activeTabComponent"></component>
    </keep-alive>
  </div>
</template>

<script>
import RegistroTab from './estudiante/RegistroTab.vue'
import AsistenciaTab from './estudiante/AsistenciaTab.vue'
import RecuperarTab from './estudiante/RecuperarTab.vue'
import GestionTab from './estudiante/GestionTab.vue'

export default {
  name: 'EstudianteView',
  components: {
    RegistroTab,
    AsistenciaTab,
    RecuperarTab,
    GestionTab
  },
  data() {
    return {
      activeSubTab: 'registro',
      tabs: [
        { id: 'registro', label: '1. Crear Perfil' },
        { id: 'asistencia', label: '2. Marcar Asistencia' },
        { id: 'recuperar', label: '3. Recuperar Ticket' },
        { id: 'gestion', label: '4. Gestión de Faltas' }
      ]
    }
  },
  computed: {
    activeTabComponent() {
      switch (this.activeSubTab) {
        case 'registro': return 'RegistroTab'
        case 'asistencia': return 'AsistenciaTab'
        case 'recuperar': return 'RecuperarTab'
        case 'gestion': return 'GestionTab'
        default: return 'RegistroTab'
      }
    }
  }
}
</script>

<style scoped src="../assets/css/estudiante.css">
</style>
