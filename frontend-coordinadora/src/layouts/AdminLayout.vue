<template>
  <div :class="['app-wrapper', { 'dark-mode': isDark }]">
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
  provide() {
    return {
      isDark: () => this.isDark,
      toggleTheme: this.toggleTheme
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
</style>

