<template>
  <div :class="['app-wrapper', { 'dark-mode': isDark }]">
    <!-- Floating Theme Toggle -->
    <div class="floating-controls">
      <button class="btn-theme" @click="toggleTheme" title="Cambiar tema">
        {{ isDark ? '☀️ Modo Claro' : '🌙 Modo Oscuro' }}
      </button>
    </div>

    <!-- Main Content -->
    <slot />
  </div>
</template>

<script>
export default {
  name: 'AdminLayout',
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
  transition: background-color var(--transition-normal), color var(--transition-normal);
}

.floating-controls {
  position: fixed;
  top: 15px;
  right: 15px;
  z-index: 10;
}

.btn-theme {
  background-color: var(--bg-secondary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  padding: 8px 14px;
  border-radius: var(--border-radius-sm);
  font-weight: 600;
  cursor: pointer;
  font-size: 0.85rem;
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-fast);
}

.btn-theme:hover {
  background-color: var(--bg-tertiary);
  transform: translateY(-1px);
}
</style>
