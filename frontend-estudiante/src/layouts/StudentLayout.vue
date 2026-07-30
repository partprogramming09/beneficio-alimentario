<template>
  <div :class="['app-wrapper', { 'dark-mode': isDark }]">
    <div class="app-container">
      <!-- School Header Banner -->
      <header class="school-header">
        <div class="emblem-band">
          <div class="band-blue"></div>
          <div class="band-white"></div>
          <div class="band-red"></div>
        </div>
        <div class="header-content">
          <div class="logo-title" @click="$emit('go-home')">
            <div class="school-emblem-wrapper">
              <img src="/escudo.png" alt="Escudo I.E. Enrique Vélez Escobar" class="header-shield-img" />
            </div>
            <div>
              <h1 class="school-title">I.E. Enrique Vélez Escobar</h1>
              <p class="subtitle">Portal del Estudiante — Beneficio Alimentario</p>
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

      <!-- Main Slot Content -->
      <main class="main-content">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="app-footer">
        <p>&copy; 2026 I.E. Enrique Vélez Escobar. Todos los derechos reservados.</p>
        <p class="small text-muted">Portal de Alimentación Escolar | Conectado a Laravel 13 API</p>
      </footer>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StudentLayout',
  data() {
    return {
      isDark: false
    }
  },
  mounted() {
    const savedTheme = localStorage.getItem('theme');
    this.isDark = savedTheme === 'dark';
    this.applyThemeClass();
  },
  methods: {
    toggleTheme() {
      this.isDark = !this.isDark;
      localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
      this.applyThemeClass();
    },
    applyThemeClass() {
      const root = document.documentElement;
      if (this.isDark) {
        root.classList.add('dark-mode');
        root.classList.remove('light-mode');
      } else {
        root.classList.add('light-mode');
        root.classList.remove('dark-mode');
      }
    }
  }
}
</script>

<style scoped>
.app-wrapper {
  min-height: 100vh;
  background-color: var(--bg-primary);
  color: var(--text-primary);
  padding: 20px 10px;
  transition: background-color var(--transition-normal), color var(--transition-normal);
}

.app-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.school-header {
  background-color: var(--bg-secondary);
  border-radius: var(--border-radius-md);
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
  background-color: #0f4c81;
}

.band-white {
  flex: 1;
  background-color: #ffffff;
}

.band-red {
  flex: 1;
  background-color: #d32f2f;
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
  cursor: pointer;
}

.school-emblem-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  padding: 5px;
  box-shadow: var(--shadow-sm);
  transition: transform var(--transition-fast), border-color var(--transition-fast);
}

.school-emblem-wrapper:hover {
  transform: scale(1.06);
  border-color: var(--primary);
}

.header-shield-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
}

.school-title {
  font-size: 1.5rem !important;
  font-weight: 800;
  background: var(--gradient-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  line-height: 1.2;
}

.subtitle {
  font-size: 0.88rem;
  color: var(--text-secondary);
}

.btn-theme {
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  padding: 8px 16px;
  border-radius: var(--border-radius-sm);
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color var(--transition-fast);
}

.btn-theme:hover {
  background-color: var(--border-color);
}

.app-footer {
  text-align: center;
  padding: 20px;
  color: var(--text-secondary);
  font-size: 0.88rem;
}

.app-footer p {
  margin: 4px 0;
}

.small {
  font-size: 0.8rem;
}

@media (max-width: 768px) {
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

  .school-title {
    font-size: 1.25rem !important;
  }
}
</style>
