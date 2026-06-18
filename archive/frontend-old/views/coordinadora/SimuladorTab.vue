<template>
  <div class="tab-content">
    <h3>Simulador de Fechas y Asistencias 🚀</h3>
    <p class="description">
      Como el sistema suspende a los estudiantes que tienen <strong>3 inasistencias consecutivas</strong>, 
      puedes usar este simulador para registrar asistencias en días pasados y verificar que las reglas de sanción 
      se ejecuten automáticamente.
    </p>

    <div class="card sub-card simulation-box">
      <h4>Simular un Día Escolar de Comedor</h4>
      
      <div class="form-group inline-form">
        <label for="sim-fecha">Fecha del Día Escolar:</label>
        <input id="sim-fecha" type="date" v-model="simDate" />
      </div>

      <div class="student-select-grid mt-3">
        <h5>Selecciona los estudiantes activos que ASISTIERON en este día simulado:</h5>
        <p class="text-muted small">Los estudiantes activos que NO selecciones contarán como inasistentes en esta fecha.</p>
        
        <div v-if="activeStudents.length === 0" class="alert alert-warning">
          No hay estudiantes en estado <strong>Activo</strong> para simular. Registra y aprueba estudiantes primero.
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
import AlertBox from '../../components/AlertBox.vue'

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
        
        // Auto increment simDate by 1 day for testing convenience
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
