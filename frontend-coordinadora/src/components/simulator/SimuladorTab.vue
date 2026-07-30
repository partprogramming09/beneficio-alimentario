<template>
  <div class="tab-content">
    <div class="card sub-card simulation-box">
      <div class="sim-header">
        <h4 class="card-title-sm">Simular Dia Escolar de Comedor</h4>
        <p class="sim-description">Herramienta administrativa para cargar asistencia historica o probar la logica de suspension por inasistencias.</p>
      </div>
      
      <div class="sim-controls">
        <div class="form-group">
          <label for="sim-fecha">Fecha del Dia Escolar:</label>
          <input id="sim-fecha" type="date" v-model="simDate" class="date-input" />
        </div>

        <div class="sim-stats" v-if="activeStudents.length > 0">
          <span class="stat-pill">
            Total activos: <strong>{{ activeStudents.length }}</strong>
          </span>
          <span class="stat-pill stat-info">
            Seleccionados: <strong>{{ simAttendees.length }}</strong>
          </span>
          <button class="btn btn-secondary btn-xs" @click="toggleAll" type="button">
            {{ simAttendees.length === activeStudents.length ? 'Desmarcar Todos' : 'Marcar Todos' }}
          </button>
        </div>
      </div>

      <div class="student-select-grid mt-3">
        <label class="section-label">Estudiantes que ASISTIERON en este dia (los no marcados contarán como inasistentes):</label>
        
        <div v-if="activeStudents.length === 0" class="alert alert-warning">
          No hay estudiantes en estado <strong>Activo</strong> para simular.
        </div>

        <div v-else class="checkbox-list">
          <label v-for="student in activeStudents" :key="student.documento" class="checkbox-item">
            <input type="checkbox" :value="student.documento" v-model="simAttendees" />
            <span class="checkbox-info">
              <span class="checkbox-doc">{{ student.documento }}</span>
              <span class="checkbox-name">{{ student.nombres }} {{ student.apellidos }}</span>
              <span class="checkbox-group">{{ student.grupo }}</span>
            </span>
          </label>
        </div>
      </div>

      <div class="sim-actions mt-4">
        <button 
          class="btn btn-primary btn-block" 
          @click="confirmRun" 
          :disabled="loading || activeStudents.length === 0 || simAttendees.length === 0"
        >
          {{ loading ? 'Ejecutando...' : 'Ejecutar Simulacion del Dia' }}
        </button>
      </div>
    </div>

    <AlertBox :message="message" :isError="isError" class="mt-3" />

    <ConfirmModal
      :is-open="showConfirmModal"
      title="Ejecutar Simulacion"
      :message="'Se registrara asistencia para ' + simAttendees.length + ' estudiantes en la fecha ' + simDate + '. Los estudiantes no marcados seran contados como inasistentes. Los no activos no seran registrados. Continuar?'"
      confirm-text="Si, ejecutar"
      type="warning"
      @confirm="doRunSimulation"
      @close="showConfirmModal = false"
    />
  </div>
</template>

<script>
import { runSimulation } from '../../services/api'
import AlertBox from '../common/AlertBox.vue'
import ConfirmModal from '../common/ConfirmModal.vue'

export default {
  name: 'SimuladorTab',
  components: {
    AlertBox,
    ConfirmModal
  },
  props: {
    activeStudents: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      simDate: new Date().toISOString().split('T')[0],
      simAttendees: [],
      loading: false,
      message: '',
      isError: false,
      showConfirmModal: false,
    }
  },
  methods: {
    clearMessages() {
      this.message = ''
      this.isError = false
    },
    toggleAll() {
      if (this.simAttendees.length === this.activeStudents.length) {
        this.simAttendees = []
      } else {
        this.simAttendees = this.activeStudents.map(s => s.documento)
      }
    },
    confirmRun() {
      if (!this.simDate) {
        this.message = 'Por favor, selecciona una fecha para la simulacion.'
        this.isError = true
        return
      }
      if (this.simAttendees.length === 0) {
        this.message = 'Selecciona al menos un estudiante asistente.'
        this.isError = true
        return
      }
      this.showConfirmModal = true
    },
    async doRunSimulation() {
      this.showConfirmModal = false
      this.loading = true
      this.clearMessages()

      try {
        const data = await runSimulation({
          fecha: this.simDate,
          asistentes: this.simAttendees
        })

        this.message = data.message
        this.isError = false
        this.simAttendees = []
        
        const current = new Date(this.simDate)
        current.setDate(current.getDate() + 1)
        this.simDate = current.toISOString().split('T')[0]
        
        this.$emit('refresh-students')
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
.card-title-sm {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.sim-description {
  font-size: 0.85rem;
  color: var(--text-secondary);
  margin: 6px 0 0 0;
}

.sub-card {
  padding: 20px;
  border-radius: var(--border-radius-sm);
  background: var(--bg-secondary);
}

.sim-controls {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
  flex-wrap: wrap;
  margin-top: 16px;
}

.sim-stats {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}

.stat-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  padding: 5px 12px;
  border-radius: var(--border-radius-pill);
  font-size: 0.82rem;
}

.stat-pill.stat-info {
  background: var(--primary-light);
  border-color: var(--primary);
  color: var(--primary);
}

.section-label {
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--text-primary);
  display: block;
}

.date-input {
  padding: 8px 14px;
  border-radius: var(--border-radius-sm);
  border: 1px solid var(--border-color);
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
}

.checkbox-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
  max-height: 280px;
  overflow-y: auto;
  margin-top: 10px;
  border: 1px solid var(--border-color);
  padding: 10px;
  border-radius: var(--border-radius-sm);
  background-color: var(--bg-tertiary);
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  font-size: 0.88rem;
  padding: 7px 10px;
  border-radius: var(--border-radius-sm);
  transition: background-color var(--transition-fast);
}

.checkbox-item:hover {
  background-color: var(--border-color);
}

.checkbox-item input {
  cursor: pointer;
  width: 16px;
  height: 16px;
  accent-color: var(--primary);
}

.checkbox-info {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}

.checkbox-doc {
  font-weight: 700;
  font-family: monospace, sans-serif;
  color: var(--primary);
}

.checkbox-name {
  color: var(--text-primary);
}

.checkbox-group {
  font-size: 0.8rem;
  color: var(--text-secondary);
  background: var(--bg-secondary);
  padding: 2px 8px;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
}

.sim-actions {
  display: flex;
  justify-content: flex-end;
}
</style>
