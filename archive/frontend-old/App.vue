<template>
  <div :class="['app-wrapper', { 'dark-theme': isDark }]">
    <div class="app-container">
      <!-- School Header Banner -->
      <header class="school-header">
        <div class="emblem-band">
          <div class="band-blue"></div>
          <div class="band-white"></div>
          <div class="band-red"></div>
        </div>
        <div class="header-content">
          <div class="logo-title">
            <div class="school-emblem-container">
              <div class="school-emblem">
                <span class="emblem-symbol">📖</span>
                <span class="emblem-initials">E.V.E.</span>
              </div>
            </div>
            <div>
              <h1 class="school-title">Institución Educativa Enrique Vélez Escobar</h1>
              <p class="subtitle">Sistema de Control de Beneficio Alimentario (Comedor Escolar)</p>
            </div>
          </div>
          <div class="header-actions">
            <!-- Theme Toggle -->
            <button class="btn-theme" @click="toggleTheme" title="Cambiar tema">
              {{ isDark ? '☀️ Claro' : '🌙 Oscuro' }}
            </button>
          </div>
        </div>
      </header>

      <!-- Main Navigation Tabs -->
      <nav class="main-navigation">
        <button 
          :class="['nav-btn', { active: activeView === 'inicio' }]"
          @click="activeView = 'inicio'"
        >
          Inicio
        </button>
        <button 
          :class="['nav-btn', { active: activeView === 'estudiante' }]"
          @click="activeView = 'estudiante'"
        >
          Portal del Estudiante
        </button>
        <button 
          :class="['nav-btn', { active: activeView === 'coordinadora' }]"
          @click="activeView = 'coordinadora'"
        >
          Panel de la Coordinadora
        </button>
      </nav>

      <!-- Main Portal Views -->
      <main class="main-content">
        <transition name="fade" mode="out-in">
          <HomeView v-if="activeView === 'inicio'" @change-view="activeView = $event" />
          <EstudianteView v-else-if="activeView === 'estudiante'" />
          <CoordinadoraView v-else-if="activeView === 'coordinadora'" />
        </transition>
      </main>

      <footer class="app-footer">
        <p>&copy; 2026 I.E. Enrique Vélez Escobar. Todos los derechos reservados.</p>
        <p class="small text-muted">Prototipo de Control de Comedor Escolar | Desarrollado con Vue 3, Express y SQLite</p>
      </footer>
    </div>
  </div>
</template>

<script>
import HomeView from './views/HomeView.vue'
import EstudianteView from './views/EstudianteView.vue'
import CoordinadoraView from './views/CoordinadoraView.vue'

export default {
  name: 'App',
  components: {
    HomeView,
    EstudianteView,
    CoordinadoraView
  },
  data() {
    return {
      activeView: 'inicio',
      isDark: false
    }
  },
  mounted() {
    // Check saved preference
    const savedTheme = localStorage.getItem('theme')
    this.isDark = savedTheme === 'dark'
    this.applyThemeClass()
  },
  methods: {
    toggleTheme() {
      this.isDark = !this.isDark
      localStorage.setItem('theme', this.isDark ? 'dark' : 'light')
      this.applyThemeClass()
    },
    applyThemeClass() {
      if (this.isDark) {
        document.body.classList.add('dark-theme')
      } else {
        document.body.classList.remove('dark-theme')
      }
    }
  }
}
</script>

<style>
/* Global CSS variables & Reset */
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

:root {
  /* Colors for Light Theme */
  --bg-color: #f4f6f9;
  --text-color: #1e293b;
  --text-muted: #475569; /* Slate 600 - High contrast */
  
  --card-bg: #ffffff;
  --card-bg-secondary: #f8fafc;
  --border-color: #e2e8f0;
  
  /* School Emblem Colors */
  --emblem-blue: #0f4c81;
  --emblem-red: #d32f2f;
  
  --primary-color: var(--emblem-blue);
  --accent-color: var(--emblem-red);
  --danger-color: #d32f2f;
  --success-color: #2e7d32;
  --warning-color: #f57c00;
  
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.dark-theme {
  /* Colors for Dark Theme */
  --bg-color: #0f172a;
  --text-color: #f8fafc;
  --text-muted: #cbd5e1; /* Slate 300 - High contrast */
  
  --card-bg: #1e293b;
  --card-bg-secondary: #0f172a;
  --border-color: #334155;
  
  --primary-color: #3b82f6;
  --accent-color: #ef4444;
  --danger-color: #ef4444;
  --success-color: #10b981;
  --warning-color: #f59e0b;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  background-color: var(--bg-color);
  color: var(--text-color);
  line-height: 1.5;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* App Layout Styles */
.app-wrapper {
  min-height: 100vh;
  background-color: var(--bg-color);
  color: var(--text-color);
  padding: 20px 10px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.app-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 30px;
}

/* Header & School Emblem Band */
.school-header {
  background-color: var(--card-bg);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.emblem-band {
  display: flex;
  height: 8px;
  width: 100%;
}

.band-blue {
  flex: 1;
  background-color: #0f4c81; /* Blue */
}

.band-white {
  flex: 1;
  background-color: #ffffff; /* White */
}

.band-red {
  flex: 1;
  background-color: #d32f2f; /* Red */
}

.header-content {
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.logo-title {
  display: flex;
  align-items: center;
  gap: 20px;
}

.school-emblem-container {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 5px;
  background: linear-gradient(135deg, rgba(15, 76, 129, 0.15), rgba(211, 47, 47, 0.15));
  border-radius: 50%;
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-sm);
  transition: border-color 0.3s ease;
}

.school-emblem {
  position: relative;
  width: 68px;
  height: 68px;
  background: linear-gradient(135deg, #0f4c81, #d32f2f);
  border: 3px solid #ffffff;
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18);
  color: #ffffff;
  transition: transform 0.3s ease;
}

.school-emblem:hover {
  transform: rotate(5deg) scale(1.05);
}

.emblem-symbol {
  font-size: 1.4rem;
  line-height: 1;
  margin-top: 3px;
}

.emblem-initials {
  font-size: 0.65rem;
  font-weight: 800;
  letter-spacing: 1px;
  margin-top: 2px;
}

.school-title {
  font-size: 1.6rem !important;
  font-weight: 800;
  background: linear-gradient(to right, var(--primary-color), var(--accent-color));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  line-height: 1.2;
}

.logo-title h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-color);
  line-height: 1.2;
}

.subtitle {
  font-size: 0.9rem;
  color: var(--text-muted);
}

/* Main Navigation */
.main-navigation {
  display: flex;
  background-color: var(--card-bg);
  border-radius: 12px;
  padding: 8px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
  gap: 10px;
}

.nav-btn {
  flex: 1;
  padding: 12px;
  font-size: 1rem;
  font-weight: 600;
  background: transparent;
  color: var(--text-muted);
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: inherit;
}

.nav-btn.active {
  background-color: var(--primary-color);
  color: white;
  box-shadow: var(--shadow-md);
}

/* General Card UI Elements */
.card {
  background-color: var(--card-bg);
  border-radius: 14px;
  padding: clamp(20px, 3.5vw, 35px);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  transition: transform 0.2s, box-shadow 0.2s, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
}

.header-card {
  border-left: 6px solid var(--primary-color);
}

.admin-header {
  border-left-color: var(--accent-color);
}

.card h2 {
  font-size: 1.4rem;
  margin-bottom: 8px;
  color: var(--text-color);
}

.card h3 {
  font-size: 1.2rem;
  margin-bottom: 15px;
  border-bottom: 2px solid var(--border-color);
  padding-bottom: 8px;
  color: var(--primary-color);
}

.card h4 {
  font-size: 1.1rem;
  margin-bottom: 10px;
  color: var(--text-color);
}

/* Form controls */
.form-group {
  margin-bottom: 15px;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.form-group label {
  font-weight: 600;
  font-size: 0.95rem;
}

.form-group input, 
.form-group textarea,
.form-group select {
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background-color: var(--card-bg-secondary);
  color: var(--text-color);
  font-family: inherit;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
  border-color: var(--primary-color);
}

/* High contrast placeholders */
input::placeholder,
textarea::placeholder {
  color: #64748b;
}

.dark-theme input::placeholder,
.dark-theme textarea::placeholder {
  color: #cbd5e1;
}

.inline-form {
  flex-direction: row;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.inline-form input {
  flex: 1;
  min-width: 150px;
}

/* Button UI */
.btn {
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  font-family: inherit;
  font-size: 0.95rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-secondary {
  background-color: var(--card-bg-secondary);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover:not(:disabled) {
  background-color: var(--border-color);
}

.btn-success {
  background-color: var(--success-color);
  color: white;
}

.btn-danger {
  background-color: var(--danger-color);
  color: white;
}

.btn-block {
  width: 100%;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 0.85rem;
}

.btn-xs {
  padding: 4px 8px;
  font-size: 0.75rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Theme toggle button specific */
.btn-theme {
  background-color: var(--card-bg-secondary);
  color: var(--text-color);
  border: 1px solid var(--border-color);
  padding: 8px 14px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9rem;
  font-family: inherit;
}

/* Alerts and badges */
.alert {
  padding: 12px 15px;
  border-radius: 8px;
  font-size: 0.95rem;
  line-height: 1.4;
  border-width: 1px;
  border-style: solid;
}

.alert-success {
  background-color: rgba(46, 204, 113, 0.15);
  color: #27ae60;
  border-color: rgba(46, 204, 113, 0.3);
}

.alert-danger {
  background-color: rgba(231, 76, 60, 0.15);
  color: #c0392b;
  border-color: rgba(231, 76, 60, 0.3);
}

.alert-warning {
  background-color: rgba(243, 156, 18, 0.15);
  color: #d35400;
  border-color: rgba(243, 156, 18, 0.3);
}

.badge-group {
  background-color: var(--card-bg-secondary);
  border: 1px solid var(--border-color);
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: bold;
  color: var(--text-color);
}

.badge-status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: bold;
  text-transform: uppercase;
}

.badge-pendiente {
  background-color: rgba(243, 156, 18, 0.15);
  color: #d84315;
}

.dark-theme .badge-pendiente {
  background-color: rgba(245, 158, 11, 0.25);
  color: #fbbf24;
}

.badge-activo {
  background-color: rgba(46, 204, 113, 0.15);
  color: #2e7d32;
}

.dark-theme .badge-activo {
  background-color: rgba(16, 185, 129, 0.25);
  color: #34d399;
}

.badge-suspendido {
  background-color: rgba(231, 76, 60, 0.15);
  color: #c62828;
}

.dark-theme .badge-suspendido {
  background-color: rgba(239, 68, 68, 0.25);
  color: #f87171;
}

.badge-inactivo {
  background-color: rgba(127, 140, 141, 0.15);
  color: #475569;
}

.dark-theme .badge-inactivo {
  background-color: rgba(148, 163, 184, 0.25);
  color: #cbd5e1;
}

/* Global utility text color classes for list state details */
.text-suspendido {
  color: var(--danger-color);
  font-weight: bold;
}

.text-activo {
  color: #2e7d32;
  font-weight: bold;
}

.dark-theme .text-activo {
  color: #34d399;
}

.text-pendiente {
  color: #d84315;
  font-weight: bold;
}

.dark-theme .text-pendiente {
  color: #fbbf24;
}

.text-inactivo {
  color: #475569;
  font-weight: bold;
}

.dark-theme .text-inactivo {
  color: #cbd5e1;
}

/* Grid Layouts */
.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
}

@media (max-width: 768px) {
  .grid-2 {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .header-content {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 15px;
  }

  .logo-title {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
  }

  .main-navigation {
    flex-direction: column;
    gap: 6px;
    padding: 6px;
  }

  .nav-btn {
    padding: 10px;
    font-size: 0.95rem;
    width: 100%;
  }

  .school-title {
    font-size: 1.35rem !important;
  }
}


/* Tables style */
.table-container {
  overflow-x: auto;
  border: 1px solid var(--border-color);
  border-radius: 8px;
}

table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

th, td {
  padding: 12px 15px;
  border-bottom: 1px solid var(--border-color);
  font-size: 0.95rem;
  color: var(--text-color);
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

th {
  background-color: var(--card-bg-secondary);
  font-weight: 600;
  color: var(--text-muted);
  transition: background-color 0.3s ease, color 0.3s ease;
}

tr:last-child td {
  border-bottom: none;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Footer styling */
.app-footer {
  text-align: center;
  padding: 20px;
  color: var(--text-muted);
  font-size: 0.9rem;
}

.app-footer p {
  margin: 4px 0;
}

.small {
  font-size: 0.8rem;
}

/* Helpers */
.mt-3 { margin-top: 1rem; }
.mt-4 { margin-top: 1.5rem; }
.mb-3 { margin-bottom: 1rem; }
.mb-4 { margin-bottom: 1.5rem; }
.ml-2 { margin-left: 0.5rem; }
.text-muted { color: var(--text-muted); }
.small { font-size: 0.8rem; }
</style>
