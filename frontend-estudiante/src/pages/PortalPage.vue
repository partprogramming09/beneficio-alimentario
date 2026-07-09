<template>
  <div class="portal-container">
    <!-- Header de Sesión Activa del Estudiante -->
    <div v-if="studentDoc" class="card header-card student-session-bar">
      <div class="session-info">
        <span class="session-avatar">👤</span>
        <div class="session-meta">
          <span class="session-title">Estudiante Identificado</span>
          <strong>{{ studentName }}</strong>
          <span class="session-doc">Documento: {{ studentDoc }}</span>
        </div>
      </div>
      <button class="btn btn-secondary btn-sm" @click="logout">
        ✕ Salir / Cambiar Estudiante
      </button>
    </div>

    <!-- Pestañas de Navegación del Portal -->
    <div class="card tabs-card">
      <div class="tabs-nav">
        <!-- Ocultamos la pestaña de registro si ya está identificado -->
        <button 
          v-if="!studentDoc"
          :class="['tab-btn', { active: activeTab === 'registro' }]" 
          @click="activeTab = 'registro'"
        >
          📝 Solicitar Inscripción
        </button>

        <button 
          :class="['tab-btn', { active: activeTab === 'recuperar' }]" 
          @click="activeTab = 'recuperar'"
        >
          📄 Mi Ticket Diario
        </button>
        <button 
          :class="['tab-btn', { active: activeTab === 'gestion' }]" 
          @click="activeTab = 'gestion'"
        >
          ⚙️ Excusa / Renuncia
        </button>
      </div>
    </div>

    <!-- Contenido Dinámico de Pestañas -->
    <div class="card content-card">
      <transition name="fade" mode="out-in">
        <RegistroTab 
          v-if="activeTab === 'registro' && !studentDoc" 
          @session-started="onSessionStarted"
        />
        <RecuperarTab 
          v-else-if="activeTab === 'recuperar'" 
          :student-doc="studentDoc"
          @session-started="onSessionStarted"
        />
        <GestionTab 
          v-else-if="activeTab === 'gestion'" 
          :student-doc="studentDoc"
        />
      </transition>
    </div>
  </div>
</template>

<script>
import RegistroTab from '../components/RegistroTab.vue';
import RecuperarTab from '../components/RecuperarTab.vue';
import GestionTab from '../components/GestionTab.vue';

export default {
  name: 'PortalPage',
  components: {
    RegistroTab,
    RecuperarTab,
    GestionTab
  },
  data() {
    return {
      activeTab: 'recuperar',
      studentDoc: localStorage.getItem('studentDoc') || null,
      studentName: localStorage.getItem('studentName') || null
    };
  },
  methods: {
    onSessionStarted(session) {
      this.studentDoc = session.documento;
      this.studentName = session.nombre;
      localStorage.setItem('studentDoc', session.documento);
      localStorage.setItem('studentName', session.nombre);
      this.activeTab = 'recuperar';
    },
    logout() {
      this.studentDoc = null;
      this.studentName = null;
      localStorage.removeItem('studentDoc');
      localStorage.removeItem('studentName');
      this.activeTab = 'recuperar';
    }
  }
}
</script>

<style scoped>
.portal-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.student-session-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 25px !important;
  border-left: 6px solid var(--accent) !important;
  flex-wrap: wrap;
  gap: 15px;
}

.session-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.session-avatar {
  font-size: 2rem;
}

.session-meta {
  display: flex;
  flex-direction: column;
}

.session-title {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: var(--text-secondary);
  font-weight: 700;
  letter-spacing: 0.5px;
}

.session-meta strong {
  font-size: 1.1rem;
  color: var(--text-primary);
}

.session-doc {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.tabs-card {
  padding: 10px;
  border-radius: var(--border-radius-md);
}

.tabs-nav {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.tab-btn {
  flex: 1;
  min-width: 150px;
  padding: 12px 20px;
  font-size: 0.95rem;
  font-weight: 600;
  text-align: center;
  border-radius: var(--border-radius-sm);
  background-color: var(--bg-tertiary);
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.tab-btn:hover {
  background-color: var(--border-color);
  color: var(--text-primary);
}

.tab-btn.active {
  background-color: var(--primary);
  color: white;
  box-shadow: var(--shadow-sm);
}

.content-card {
  border-radius: var(--border-radius-md);
  min-height: 300px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity var(--transition-fast) ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
