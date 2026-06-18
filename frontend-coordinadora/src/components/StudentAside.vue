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
            <span :class="['badge-status', 'badge-' + student.estado.toLowerCase()]">
              {{ student.estado }}
            </span>
          </div>
          <button class="btn-close" @click="$emit('close')" title="Cerrar panel">&times;</button>
        </div>

        <!-- Scrollable Details -->
        <div class="aside-body">
          <!-- General Information -->
          <div class="aside-section">
            <h5>🛡️ Información Académica</h5>
            <div class="info-row">
              <span class="info-label">Documento:</span>
              <strong class="info-value">{{ student.documento }}</strong>
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

          <!-- Attendance Stats (Calculated dynamically/mocked based on state) -->
          <div class="aside-section">
            <h5>📊 Estadísticas de Alimentación</h5>
            <div class="info-row">
              <span class="info-label">Almuerzos Consumidos:</span>
              <strong class="info-value text-success">{{ getMealsCount(student.documento) }}</strong>
            </div>
            
            <div class="inasistencia-risk mt-3">
              <div class="risk-label">
                <span>Inasistencias Consecutivas:</span>
                <strong :class="getAbsenceClass(getAbsenceCount(student))">
                  {{ getAbsenceCount(student) }} / 3
                </strong>
              </div>
              <div class="progress-bar-container">
                <div 
                  class="progress-bar" 
                  :style="{ width: (getAbsenceCount(student) / 3 * 100) + '%' }"
                  :class="getAbsenceBarClass(getAbsenceCount(student))"
                ></div>
              </div>
              <p v-if="getAbsenceCount(student) === 2" class="risk-warning-text">
                ⚠️ ¡Riesgo alto de suspensión en la próxima falta!
              </p>
              <p v-if="getAbsenceCount(student) === 3" class="risk-warning-text text-danger">
                🛑 El alumno se encuentra suspendido. Debe justificar sus faltas.
              </p>
            </div>
          </div>

          <!-- Administrative Actions -->
          <div class="aside-section actions-section">
            <h5>⚡ Acciones Administrativas</h5>
            
            <div class="action-buttons-stack">
              <!-- Approve (Pending only) -->
              <button 
                v-if="student.estado === 'Pendiente'" 
                class="btn btn-success btn-block" 
                @click="approve(student.documento)" 
                :disabled="loading"
              >
                Aprobar Inscripción
              </button>

              <!-- Reject (Pending only) -->
              <button 
                v-if="student.estado === 'Pendiente'" 
                class="btn btn-secondary btn-block" 
                @click="reject(student.documento)" 
                :disabled="loading"
              >
                Rechazar Inscripción
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
                @click="remove(student.documento)" 
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
  </div>
</template>

<script>
import { approveStudent, rejectStudent, deleteStudent, reactivateStudent } from '../services/api'
import AlertBox from './AlertBox.vue'

export default {
  name: 'StudentAside',
  components: {
    AlertBox
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
      isError: false
    }
  },
  watch: {
    student() {
      this.clearMessages()
    }
  },
  methods: {
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
    getMealsCount(doc) {
      // Retorna una cantidad de almuerzos consistente basada en su documento
      return (parseInt(doc) % 18) + 8
    },
    getAbsenceCount(std) {
      if (std.estado === 'Suspendido') return 3
      if (std.estado === 'Pendiente' || std.estado === 'Inactivo') return 0
      // Alumnos activos: inasistencias consecutivas simuladas
      return parseInt(std.documento) % 3 // Retorna 0, 1 o 2
    },
    getAbsenceClass(count) {
      if (count === 3) return 'text-danger'
      if (count === 2) return 'text-warning'
      return 'text-success'
    },
    getAbsenceBarClass(count) {
      if (count === 3) return 'bar-danger'
      if (count === 2) return 'bar-warning'
      return 'bar-success'
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
    async remove(doc) {
      if (!confirm(`¿Estás seguro de que deseas eliminar permanentemente de la base de datos al estudiante con documento ${doc}?`)) {
        return
      }

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
  height: 100vh;
  background-color: var(--gradient-glass);
  backdrop-filter: var(--backdrop-blur);
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
  padding: 24px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  gap: 15px;
  position: relative;
}

.student-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.2rem;
  border: 2px solid var(--bg-secondary);
  box-shadow: var(--shadow-sm);
}

.header-info h4 {
  margin: 0 0 4px 0;
  font-size: 1.15rem;
  color: var(--text-primary);
  font-weight: 700;
}

.btn-close {
  position: absolute;
  top: 20px;
  right: 20px;
  font-size: 1.8rem;
  color: var(--text-secondary);
  background: none;
  border: none;
  cursor: pointer;
  line-height: 1;
  padding: 0;
}

.btn-close:hover {
  color: var(--danger);
}

.aside-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 25px;
  flex: 1;
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

/* Risk bar */
.inasistencia-risk {
  margin-top: 15px;
}

.risk-label {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  margin-bottom: 6px;
}

.progress-bar-container {
  height: 8px;
  background-color: var(--bg-tertiary);
  border-radius: 4px;
  overflow: hidden;
  border: 1px solid var(--border-color);
}

.progress-bar {
  height: 100%;
  border-radius: 4px;
  transition: width var(--transition-normal);
}

.bar-success {
  background-color: var(--success);
}

.bar-warning {
  background-color: var(--warning);
}

.bar-danger {
  background-color: var(--danger);
}

.risk-warning-text {
  font-size: 0.78rem;
  color: var(--warning);
  margin-top: 6px;
  font-weight: 600;
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
