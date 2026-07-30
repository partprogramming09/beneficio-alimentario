<template>
  <div>
    <!-- Backdrop overlay -->
    <div v-if="student" class="aside-backdrop" @click="$emit('close')"></div>

    <!-- Aside drawer -->
    <aside :class="['student-aside', { 'aside-open': student }]">
      <div v-if="student" class="aside-container">
        <!-- Header -->
        <div class="aside-header">
          <div class="student-avatar">
            {{ student.nombres.charAt(0) }}{{ student.apellidos.charAt(0) }}
          </div>
          <div class="header-info">
            <h4>{{ student.nombres }} {{ student.apellidos }}</h4>
            <div class="header-doc-badge mb-1">Doc: <strong>{{ student.documento }}</strong></div>
            <span :class="['badge-status', getStatusClass(student.estado)]">
              {{ student.estado }}
            </span>
          </div>
          <button class="btn-close" @click="$emit('close')" title="Cerrar panel">&times;</button>
        </div>

        <!-- Scrollable Details -->
        <div class="aside-body">
          <!-- General Information -->
          <div class="aside-section">
            <h5>Informacion Academica</h5>
            <div class="info-row">
              <span class="info-label">Documento Identidad:</span>
              <strong class="info-value text-primary font-mono">{{ student.documento }}</strong>
            </div>

            <div class="info-row">
              <span class="info-label">Grado/Grupo:</span>
              <span class="badge-group">{{ student.grupo }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Fecha de Registro:</span>
              <span class="info-value">{{ formatDate(student.creado_en) }}</span>
            </div>
          </div>

          <!-- Administrative Actions -->
          <div class="aside-section actions-section">
            <h5>Acciones Administrativas</h5>
            
            <div class="action-buttons-stack">
              <!-- Approve (Pending only) -->
              <button 
                v-if="student.estado === 'Pendiente'" 
                class="btn btn-success btn-block" 
                @click="approve(student.documento)" 
                :disabled="loading"
              >
                Aprobar Inscripcion
              </button>

              <!-- Reject (Pending only) -->
              <button 
                v-if="student.estado === 'Pendiente'" 
                class="btn btn-secondary btn-block" 
                @click="reject(student.documento)" 
                :disabled="loading"
              >
                Rechazar Inscripcion
              </button>

              <!-- Reactivate/Reingresar (Suspended only) -->
              <button 
                v-if="student.estado === 'Suspendido'" 
                class="btn btn-success btn-block" 
                @click="reactivate(student.documento)" 
                :disabled="loading"
              >
                Aprobar Reingreso
              </button>

              <!-- Delete (All states) -->
              <button 
                class="btn btn-danger btn-block" 
                @click="showDeleteModal = true" 
                :disabled="loading"
              >
                Eliminar Beneficiario
              </button>
            </div>

            <!-- Feedback Messages -->
            <AlertBox :message="message" :isError="isError" class="mt-3" />
          </div>
        </div>
      </div>
    </aside>

    <ConfirmModal
      :is-open="showDeleteModal"
      title="Eliminar Beneficiario"
      :message="'¿Estas seguro de que deseas eliminar permanentemente de la base de datos al estudiante con documento ' + (student ? student.documento : '') + '?'"
      confirm-text="Eliminar"
      type="danger"
      @confirm="doDelete"
      @close="showDeleteModal = false"
    />
  </div>
</template>

<script>
import { approveStudent, rejectStudent, deleteStudent, reactivateStudent } from '../../services/api'
import AlertBox from '../common/AlertBox.vue'
import ConfirmModal from '../common/ConfirmModal.vue'
import { getStatusClass } from '../../utils/statusHelper'

export default {
  name: 'StudentAside',
  components: {
    AlertBox,
    ConfirmModal
  },
  props: {
    student: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      loading: false,
      message: '',
      isError: false,
      showDeleteModal: false,
    }
  },
  watch: {
    student() {
      this.clearMessages()
    }
  },
  mounted() {
    document.addEventListener('keydown', this.onEscape)
  },
  beforeUnmount() {
    document.removeEventListener('keydown', this.onEscape)
  },
  methods: {
    getStatusClass,
    onEscape(e) {
      if (e.key === 'Escape' && this.student) {
        this.$emit('close')
      }
    },
    clearMessages() {
      this.message = ''
      this.isError = false
    },
    formatDate(dateStr) {
      if (!dateStr) return 'No registrado'
      const date = new Date(dateStr)
      return date.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },
    async approve(doc) {
      this.loading = true
      this.clearMessages()
      try {
        const data = await approveStudent(doc)
        this.message = data.message
        this.isError = false
        if (data.estudiante) {
          this.$emit('update-student', data.estudiante)
        } else {
          this.$emit('refresh')
        }
        setTimeout(() => this.$emit('close'), 1000)
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    },
    async reject(doc) {
      this.loading = true
      this.clearMessages()
      try {
        const data = await rejectStudent(doc)
        this.message = data.message
        this.isError = false
        this.$emit('remove-student', doc)
        setTimeout(() => this.$emit('close'), 1000)
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    },
    async reactivate(doc) {
      this.loading = true
      this.clearMessages()
      try {
        const data = await reactivateStudent(doc)
        this.message = data.message
        this.isError = false
        if (data.estudiante) {
          this.$emit('update-student', data.estudiante)
        } else {
          this.$emit('refresh')
        }
        setTimeout(() => this.$emit('close'), 1000)
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    },
    async doDelete() {
      this.showDeleteModal = false
      const doc = this.student.documento
      this.loading = true
      this.clearMessages()
      try {
        const data = await deleteStudent(doc)
        this.message = data.message
        this.isError = false
        this.$emit('remove-student', doc)
        setTimeout(() => this.$emit('close'), 1000)
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.aside-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.4);
  z-index: 99;
  backdrop-filter: blur(4px);
  animation: fadeIn var(--transition-fast) forwards;
}

.student-aside {
  position: fixed;
  top: 0;
  right: 0;
  width: 420px;
  max-width: 100vw;
  height: 100vh;
  background-color: var(--bg-secondary);
  border-left: 1px solid var(--border-color);
  box-shadow: var(--shadow-lg);
  z-index: 100;
  transform: translateX(100%);
  transition: transform var(--transition-normal) cubic-bezier(0.4, 0, 0.2, 1);
  overflow-y: auto;
}

.student-aside.aside-open {
  transform: translateX(0);
}

.aside-container {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.aside-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  gap: 15px;
  position: relative;
}

.student-avatar {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background-color: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.1rem;
  border: 2px solid var(--bg-secondary);
  box-shadow: var(--shadow-sm);
  flex-shrink: 0;
}

.header-info h4 {
  margin: 0 0 2px 0;
  font-size: 1.05rem;
  color: var(--text-primary);
  font-weight: 700;
}

.header-doc-badge {
  font-size: 0.85rem;
  color: var(--primary);
  font-family: monospace, sans-serif;
}

.font-mono {
  font-family: monospace, sans-serif;
}

.btn-close {
  position: absolute;
  top: 15px;
  right: 15px;
  width: 40px;
  height: 40px;
  font-size: 1.8rem;
  color: var(--text-secondary);
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--transition-fast);
}

.btn-close:hover {
  color: var(--danger);
  background-color: var(--danger-light);
}

.aside-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  flex: 1;
}

@media (max-width: 576px) {
  .student-aside {
    width: 100vw;
  }
  .aside-header {
    padding: 16px;
  }
  .aside-body {
    padding: 16px;
  }
}

.aside-section {
  background-color: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
  padding: 20px;
  box-shadow: var(--shadow-sm);
}

.aside-section h5 {
  margin: 0 0 15px 0;
  font-size: 0.95rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--primary);
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 8px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px dashed var(--border-color);
}

.info-row:last-of-type {
  border-bottom: none;
}

.info-label {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.info-value {
  font-size: 0.9rem;
  color: var(--text-primary);
}

.action-buttons-stack {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@media (max-width: 480px) {
  .student-aside {
    width: 100vw;
  }
}
</style>
