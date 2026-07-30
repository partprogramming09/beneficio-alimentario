<template>
  <StudentLayout @go-home="currentPage = 'landing'">
    <transition name="fade" mode="out-in">
      <LandingPage v-if="currentPage === 'landing'" @enter-portal="currentPage = 'portal'" />
      <PortalPage v-else-if="currentPage === 'portal'" />
    </transition>
  </StudentLayout>
</template>

<script>
import StudentLayout from './layouts/StudentLayout.vue';
import LandingPage from './views/LandingView.vue';
import PortalPage from './views/PortalView.vue';

export default {
  name: 'App',
  components: {
    StudentLayout,
    LandingPage,
    PortalPage
  },
  data() {
    return {
      currentPage: 'landing'
    };
  }
}
</script>

<style>
/* Estilos globales y de utilidad para el portal */

.card {
  background-color: var(--bg-secondary);
  border-radius: var(--border-radius-md);
  padding: clamp(20px, 3.5vw, 35px);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  transition: transform var(--transition-fast), box-shadow var(--transition-fast), background-color var(--transition-normal);
}

.header-card {
  border-left: 6px solid var(--primary);
}

.card h2 {
  font-size: 1.45rem;
  margin-bottom: 8px;
  color: var(--text-primary);
}

.card h3 {
  font-size: 1.25rem;
  margin-bottom: 15px;
  border-bottom: 2px solid var(--border-color);
  padding-bottom: 8px;
  color: var(--primary);
}

.card h4 {
  font-size: 1.1rem;
  margin-bottom: 10px;
  color: var(--text-primary);
}

/* Form controls */
.form-group {
  margin-bottom: 15px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text-primary);
}

.form-group input, 
.form-group textarea,
.form-group select {
  padding: 10px 14px;
  border-radius: var(--border-radius-sm);
  border: 1px solid var(--border-color);
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
  font-family: inherit;
  font-size: 0.95rem;
  outline: none;
  transition: border-color var(--transition-fast), background-color var(--transition-fast);
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  border-color: var(--primary);
  background-color: var(--bg-secondary);
}

.form-group input[readonly] {
  background-color: var(--border-color);
  cursor: not-allowed;
  opacity: 0.8;
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
  padding: 11px 20px;
  border-radius: var(--border-radius-sm);
  font-weight: 600;
  font-family: inherit;
  font-size: 0.95rem;
  border: none;
  cursor: pointer;
  transition: all var(--transition-fast) ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-primary {
  background-color: var(--primary);
  color: white;
  box-shadow: var(--shadow-sm);
}

.btn-primary:hover:not(:disabled) {
  background-color: var(--primary-hover);
  box-shadow: var(--shadow-md);
  transform: translateY(-1px);
}

.btn-secondary {
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover:not(:disabled) {
  background-color: var(--border-color);
}

.btn-success {
  background-color: var(--success);
  color: white;
}

.btn-danger {
  background-color: var(--danger);
  color: white;
}

.btn-block {
  width: 100%;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Alerts and badges */
.alert {
  padding: 12px 16px;
  border-radius: var(--border-radius-sm);
  font-size: 0.95rem;
  line-height: 1.4;
  border-width: 1px;
  border-style: solid;
  margin-top: 15px;
}

.alert-success {
  background-color: var(--success-light);
  color: var(--success);
  border-color: var(--border-color);
}

.alert-danger {
  background-color: var(--danger-light);
  color: var(--danger);
  border-color: var(--border-color);
}

.alert-warning {
  background-color: var(--warning-light);
  color: var(--warning);
  border-color: var(--border-color);
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
}

.mt-3 { margin-top: 1rem; }
.mt-4 { margin-top: 1.5rem; }
.mb-4 { margin-bottom: 1.5rem; }
.text-danger { color: var(--danger) !important; }
.text-secondary { color: var(--text-secondary); }
.border-danger { border-color: var(--danger) !important; }

/* Line Icons & Tab Heading Helpers */
.tab-heading {
  display: flex;
  align-items: center;
  gap: 10px;
}

.tab-line-icon, .inline-line-icon {
  flex-shrink: 0;
  stroke: currentColor;
}
</style>
