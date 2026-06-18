<template>
  <div class="tab-content">
    <h3>Registrar Asistencia en Fila 🍽️</h3>
    <p class="description">Ingrese el documento del estudiante en la fila del comedor escolar para marcar su asistencia en tiempo real.</p>

    <div class="form-group inline-form">
      <label for="doc-asis">Documento del Estudiante:</label>
      <input 
        id="doc-asis" 
        type="text" 
        v-model="attendanceDoc" 
        placeholder="Ingrese el documento" 
        @keyup.enter="markAttendance"
      />
      <button class="btn btn-primary" @click="markAttendance" :disabled="loading">
        {{ loading ? 'Registrando...' : 'Marcar Asistencia' }}
      </button>
    </div>

    <AlertBox :message="message" :isError="isError" />

    <!-- Receipt Display Card -->
    <ReceiptCard v-if="receipt" :receipt="receipt" badgeText="REGISTRADO" />
  </div>
</template>

<script>
import { markAttendance } from '../services/api'
import ReceiptCard from './ReceiptCard.vue'
import AlertBox from './AlertBox.vue'

export default {
  name: 'AsistenciaTab',
  components: {
    ReceiptCard,
    AlertBox
  },
  data() {
    return {
      attendanceDoc: '',
      receipt: null,
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
    async markAttendance() {
      if (!this.attendanceDoc.trim()) {
        this.message = 'Por favor, ingrese el documento.'
        this.isError = true
        return
      }

      this.loading = true
      this.clearMessages()
      this.receipt = null

      try {
        const data = await markAttendance(this.attendanceDoc)

        this.message = data.message
        this.isError = false
        this.receipt = data.comprobante
        this.attendanceDoc = ''
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
