<template>
  <div v-if="isOpen" class="modal-backdrop" @click.self="$emit('close')">
    <div :class="['modal-card', 'modal-' + type]">
      <div class="modal-header">
        <div class="modal-icon-wrapper">
          <svg v-if="type === 'danger'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          <svg v-else-if="type === 'warning'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="16 12 12 8 8 12"></polyline>
            <line x1="12" y1="16" x2="12" y2="8"></line>
          </svg>
        </div>
        <h4>{{ title }}</h4>
        <button class="btn-close" @click="$emit('close')">&times;</button>
      </div>

      <div class="modal-body">
        <p class="modal-message">{{ message }}</p>
        <slot></slot>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" @click="$emit('close')">
          {{ cancelText }}
        </button>
        <button
          :class="['btn', confirmBtnClass]"
          :disabled="!canConfirm"
          @click="$emit('confirm')"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConfirmModal',
  props: {
    isOpen: { type: Boolean, default: false },
    title: { type: String, default: 'Confirmar acción' },
    message: { type: String, default: '¿Estás seguro de que deseas continuar?' },
    confirmText: { type: String, default: 'Confirmar' },
    cancelText: { type: String, default: 'Cancelar' },
    type: { type: String, default: 'warning' },
    canConfirm: { type: Boolean, default: true },
  },
  emits: ['confirm', 'close'],
  computed: {
    confirmBtnClass() {
      const map = {
        danger: 'btn-danger',
        warning: 'btn-warning',
        success: 'btn-success',
      };
      return map[this.type] || 'btn-primary';
    },
  },
  watch: {
    isOpen(val) {
      if (val) {
        document.addEventListener('keydown', this.onEscape);
      } else {
        document.removeEventListener('keydown', this.onEscape);
      }
    },
  },
  beforeUnmount() {
    document.removeEventListener('keydown', this.onEscape);
  },
  methods: {
    onEscape(e) {
      if (e.key === 'Escape') this.$emit('close');
    },
  },
};
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

.modal-card {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
  width: 90%;
  max-width: 460px;
  box-shadow: var(--shadow-lg);
  animation: slideUp 0.25s ease;
}

.modal-danger { border-left: 5px solid var(--danger); }
.modal-warning { border-left: 5px solid var(--warning); }
.modal-success { border-left: 5px solid var(--success); }

.modal-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 20px;
  border-bottom: 1px solid var(--border-color);
}

.modal-icon-wrapper {
  flex-shrink: 0;
}

.modal-danger .modal-icon-wrapper { color: var(--danger); }
.modal-warning .modal-icon-wrapper { color: var(--warning); }
.modal-success .modal-icon-wrapper { color: var(--success); }

.modal-header h4 {
  flex: 1;
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-primary);
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--text-muted);
  padding: 0 4px;
  line-height: 1;
}

.modal-body {
  padding: 20px;
}

.modal-message {
  font-size: 0.95rem;
  color: var(--text-secondary);
  line-height: 1.5;
  margin: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 14px 20px;
  border-top: 1px solid var(--border-color);
}

.btn-warning {
  background-color: var(--warning);
  color: white;
}

.btn-warning:hover:not(:disabled) {
  opacity: 0.9;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px) scale(0.97); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>
