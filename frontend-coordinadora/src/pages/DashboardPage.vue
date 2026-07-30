<template>
  <div class="coordinadora-dashboard-layout">
    <!-- Backdrop táctil para cerrar la sidebar en móvil -->
    <div 
      v-if="isSidebarOpenMobile" 
      class="sidebar-backdrop" 
      @click="isSidebarOpenMobile = false"
    ></div>

    <!-- Left Sidebar (Crimson Modern Theme - Colapsable en Móvil) -->
    <aside :class="['sidebar', { 'sidebar-open-mobile': isSidebarOpenMobile }]">
      <div class="sidebar-brand">
        <div class="brand-shield-wrapper">
          <img :src="escudoImg" alt="Escudo I.E. Enrique Vélez Escobar" class="brand-shield-img" />
        </div>
        <div class="brand-text">
          <h4>I.E. Enrique Vélez Escobar</h4>
          <span class="brand-subtitle">Gestión Comedor</span>
        </div>
        <!-- Botón cerrar menú en móvil -->
        <button class="btn-sidebar-close" @click="isSidebarOpenMobile = false" title="Cerrar menú">&times;</button>
      </div>
      
      <nav class="sidebar-nav">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :class="['nav-item', { active: activeSubTab === tab.id }]"
          @click="selectTabMobile(tab.id)"
        >
          <span class="nav-icon">
            <svg v-if="tab.id === 'pendientes'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="8.5" cy="7" r="4"></circle>
              <polyline points="17 11 19 13 23 9"></polyline>
            </svg>
            <svg v-else-if="tab.id === 'listado'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <svg v-else-if="tab.id === 'cursos'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="14" y="14" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <svg v-else-if="tab.id === 'asistencia'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <svg v-else-if="tab.id === 'reportes'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="20" x2="18" y2="10"></line>
              <line x1="12" y1="20" x2="12" y2="4"></line>
              <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            <svg v-else-if="tab.id === 'excusas'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <svg v-else-if="tab.id === 'simulador'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
          </span>
          <span class="nav-label">{{ tab.label }}</span>
          <span v-if="tab.id === 'pendientes' && pendingCount > 0" class="badge-count">{{ pendingCount }}</span>
          <span v-else-if="tab.id === 'excusas' && suspendedStudents.length > 0" class="badge-count warning-badge">{{ suspendedStudents.length }}</span>
        </button>
      </nav>

      <div class="sidebar-footer">
        <div class="user-profile">
          <div class="user-avatar">CO</div>
          <div class="user-meta">
            <strong>Coordinación</strong>
            <span>Administrador</span>
          </div>
        </div>
        <button v-if="toggleTheme" class="btn-theme-sidebar" @click="toggleTheme" title="Cambiar Tema (Claro/Oscuro)">
          {{ isThemeDark ? '☀️ Claro' : '🌙 Oscuro' }}
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content-area">
      <!-- Header con KPIs Dinámicos y Menú Hamburguesa -->
      <header class="content-header">
        <div class="header-title-section">
          <!-- Botón Hamburguesa Móvil -->
          <button class="btn-menu-mobile" @click="isSidebarOpenMobile = !isSidebarOpenMobile" title="Abrir Menú">
            <span class="hamburger-icon">☰</span>
            <span class="menu-text">Menú</span>
          </button>
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
import CursosTab from '../components/CursosTab.vue'
import StudentAside from '../components/StudentAside.vue'
import escudoImg from '../../../frontend-core/src/assets/escudo.png'

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
    CursosTab,
    StudentAside
  },
  data() {
    return {
      escudoImg,
      activeSubTab: 'pendientes',
      isSidebarOpenMobile: false,

      tabs: [
        { id: 'pendientes', label: 'Inscritos Hoy' },
        { id: 'listado', label: 'Estudiantes' },
        { id: 'cursos', label: 'Cursos y Grupos' },
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
        case 'cursos': return 'CursosTab'
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
    selectTabMobile(tabId) {
      this.activeSubTab = tabId;
      this.isSidebarOpenMobile = false;
    },
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
        case 'cursos': return '🏫'
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
  position: relative;
  animation: fadeIn var(--transition-normal);
}

.sidebar-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
  z-index: 90;
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
  z-index: 95;
  transition: transform var(--transition-normal) ease;
}

.sidebar-brand {
  padding: 24px 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  position: relative;
}

.brand-shield-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 4px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  flex-shrink: 0;
}

.brand-shield-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
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

.btn-sidebar-close {
  display: none;
  position: absolute;
  top: 18px;
  right: 18px;
  background: none;
  border: none;
  color: white;
  font-size: 1.8rem;
  line-height: 1;
  cursor: pointer;
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
  padding: 24px 20px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
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

.header-title-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-title-section h2 {
  margin: 0;
  font-size: clamp(1.2rem, 3vw, 1.5rem) !important;
  font-weight: 800;
  color: var(--text-primary);
  letter-spacing: -0.3px;
}

/* Botón Menú Hamburguesa Móvil */
.btn-menu-mobile {
  display: none;
  align-items: center;
  gap: 6px;
  background: var(--gradient-primary);
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: var(--border-radius-pill);
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  box-shadow: var(--shadow-sm);
}

.hamburger-icon {
  font-size: 1.1rem;
}

.kpi-group {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
  gap: 10px;
  width: 100%;
  max-width: 420px;
}

.kpi-card {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-sm);
  padding: 8px 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: var(--shadow-sm);
}

.kpi-val {
  font-size: 1.2rem;
  font-weight: 800;
  color: var(--primary);
  line-height: 1;
}

.kpi-lbl {
  font-size: 0.68rem;
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
  margin-top: 4px;
  text-align: center;
}

.warning-kpi .kpi-val {
  color: var(--warning);
}

.content-card {
  box-shadow: var(--shadow-sm);
  background-color: var(--bg-secondary);
  border-radius: var(--border-radius-md);
  padding: clamp(16px, 3vw, 24px);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Breakpoint Móvil / Tablet < 992px */
@media (max-width: 992px) {
  .btn-menu-mobile {
    display: inline-flex;
  }
  .btn-sidebar-close {
    display: block;
  }
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 280px;
    transform: translateX(-100%);
  }
  .sidebar.sidebar-open-mobile {
    transform: translateX(0);
  }
  .main-content-area {
    padding: 16px 12px;
  }
  .content-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .kpi-group {
    max-width: 100%;
  }
}
</style>



