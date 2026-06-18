<template>
  <div class="portal-container">
    <!-- Pestañas de Navegación del Portal -->
    <div class="card tabs-card">
      <div class="tabs-nav">
        <button 
          :class="['tab-btn', { active: activeTab === 'registro' }]" 
          @click="activeTab = 'registro'"
        >
          📝 Solicitar Inscripción
        </button>

        <button 
          :class="['tab-btn', { active: activeTab === 'recuperar' }]" 
          @click="activeTab = 'recuperar'"
        >
          📄 Recuperar Ticket
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
        <RegistroTab v-if="activeTab === 'registro'" />
        <RecuperarTab v-else-if="activeTab === 'recuperar'" />
        <GestionTab v-else-if="activeTab === 'gestion'" />
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
      activeTab: 'recuperar' // Por defecto mostramos recuperar ticket
    };
  }
}
</script>

<style scoped>
.portal-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
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
