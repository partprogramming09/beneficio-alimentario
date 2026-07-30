<template>
  <div class="portal-container">
    <!-- Header de Sesión Activa del Estudiante -->
    <div v-if="studentDoc" class="student-session-card">
      <div class="session-info">
        <div class="session-avatar">👤</div>
        <div class="session-meta">
          <span class="session-title">Estudiante Identificado</span>
          <strong>{{ studentName }}</strong>
          <span class="session-doc">Doc: {{ studentDoc }}</span>
        </div>
      </div>
      <button class="btn-logout" @click="logout">
        <span>✕ Cambiar Estudiante</span>
      </button>
    </div>

    <!-- Pestañas de Navegación del Portal (Pill Tabs) -->
    <div class="tabs-pill-wrapper">
      <nav class="pill-nav">
        <button 
          v-if="!studentDoc"
          :class="['pill-btn', { active: activeTab === 'registro' }]" 
          @click="activeTab = 'registro'"
        >
          <span>📝</span> Verificar Registro
        </button>

        <button 
          :class="['pill-btn', { active: activeTab === 'recuperar' }]" 
          @click="activeTab = 'recuperar'"
        >
          <span>🎫</span> Mi Ticket Diario
        </button>

        <button 
          :class="['pill-btn', { active: activeTab === 'gestion' }]" 
          @click="activeTab = 'gestion'"
        >
          <span>⚙️</span> Excusa / Renuncia
        </button>
      </nav>
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
  animation: fadeIn var(--transition-normal);
}

.student-session-card {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-left: 5px solid var(--success);
  border-radius: var(--border-radius-md);
  padding: 16px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: var(--shadow-sm);
  flex-wrap: wrap;
  gap: 15px;
}

.session-info {
  display: flex;
  align-items: center;
  gap: 14px;
}

.session-avatar {
  width: 44px;
  height: 44px;
  background: var(--success-light);
  color: var(--success);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.session-meta {
  display: flex;
  flex-direction: column;
}

.session-title {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: var(--text-muted);
  font-weight: 700;
  letter-spacing: 0.5px;
}

.session-meta strong {
  font-size: 1.05rem;
  color: var(--text-primary);
  line-height: 1.2;
}

.session-doc {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.btn-logout {
  padding: 8px 16px;
  font-size: 0.85rem;
  font-weight: 600;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
  background-color: var(--bg-tertiary);
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.btn-logout:hover {
  background-color: var(--danger-light);
  color: var(--danger);
  border-color: var(--danger-light);
}

/* Pill Navigation */
.tabs-pill-wrapper {
  display: flex;
  justify-content: center;
}

.pill-nav {
  display: flex;
  background: var(--bg-tertiary);
  padding: 5px;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
  gap: 4px;
  width: 100%;
  max-width: 600px;
  flex-wrap: wrap;
}

.pill-btn {
  flex: 1;
  min-width: 130px;
  padding: 10px 14px;
  font-size: 0.9rem;
  font-weight: 600;
  text-align: center;
  border-radius: var(--border-radius-pill);
  background: transparent;
  color: var(--text-secondary);
  transition: all var(--transition-fast);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}


.pill-btn:hover {
  color: var(--text-primary);
}

.pill-btn.active {
  background-color: var(--bg-secondary);
  color: var(--primary);
  box-shadow: var(--shadow-sm);
}

.content-card {
  border-radius: var(--border-radius-md);
  min-height: 320px;
  box-shadow: var(--shadow-sm);
  background-color: var(--bg-secondary);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity var(--transition-fast) ease, transform var(--transition-fast) ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(4px);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

