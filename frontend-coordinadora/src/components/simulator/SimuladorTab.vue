<template>
  <div class="tab-content">
    <div class="card sub-card simulation-box">
      <div class="sim-header">
        <h4 class="card-title-sm">Simular Día Escolar de Comedor</h4>
      </div>
      
      <div class="form-group inline-form mt-2">
        <label for="sim-fecha">Fecha del Día Escolar:</label>
        <input id="sim-fecha" type="date" v-model="simDate" class="date-input" />
      </div>

      <div class="student-select-grid mt-3">
        <label class="section-label">Estudiantes que ASISTIERON en este día (los no marcados contarán como inasistentes):</label>
        
        <div v-if="activeStudents.length === 0" class="alert alert-warning">
          No hay estudiantes en estado <strong>Activo</strong> para simular.
        </div>

        <div v-else class="checkbox-list">
          <label v-for="student in activeStudents" :key="student.documento" class="checkbox-item">
            <input type="checkbox" :value="student.documento" v-model="simAttendees" />
            <span>{{ student.documento }} - {{ student.nombres }} {{ student.apellidos }} ({{ student.grupo }})</span>
          </label>
        </div>
      </div>

      <button 
        class="btn btn-primary btn-block mt-4" 
        @click="runSimulation" 
        :disabled="loading || activeStudents.length === 0"
      >
        {{ loading ? 'Simulando...' : 'Ejecutar Simulación del Día' }}
      </button>
    </div>

    <AlertBox :message="message" :isError="isError" class="mt-3" />
  </div>
</template>

<script>
import { runSimulation } from '../../services/api'
import AlertBox from '../common/AlertBox.vue'

export default {
  name: 'SimuladorTab',
  components: {
    AlertBox
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
      isError: false
    }
  },
  methods: {
    clearMessages() {
      this.message = ''
      this.isError = false
    },
    async runSimulation() {
      if (!this.simDate) {
        this.message = 'Por favor, selecciona una fecha para la simulación.'
        this.isError = true
        return
      }

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

.sub-card {
  padding: 20px;
  border-radius: var(--border-radius-sm);
  background: var(--bg-secondary);
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
  gap: 8px;
  max-height: 220px;
  overflow-y: auto;
  margin-top: 10px;
  border: 1px solid var(--border-color);
  padding: 12px;
  border-radius: var(--border-radius-sm);
  background-color: var(--bg-tertiary);
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  font-size: 0.9rem;
  padding: 6px 10px;
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
</style>

