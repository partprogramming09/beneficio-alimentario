<template>
  <div class="tab-content">
    <h3>Marcar Asistencia Diaria 🍽️</h3>
    <p class="description">Recuerda marcar tu asistencia antes de entrar al comedor escolar. Debes tener estado <strong>Activo</strong>.</p>

    <div class="form-group inline-form">
      <label for="doc-asis">Documento de Identidad:</label>
      <input 
        id="doc-asis" 
        type="text" 
        v-model="attendanceDoc" 
        placeholder="Ingresa tu documento" 
        @keyup.enter="markAttendance"
      />
      <button class="btn btn-primary" @click="markAttendance" :disabled="loading">
        {{ loading ? 'Marcando...' : 'Registrar Asistencia' }}
      </button>
    </div>

    <AlertBox :message="message" :isError="isError" />

    <!-- Receipt Display Card -->
    <ReceiptCard v-if="receipt" :receipt="receipt" badgeText="REGISTRADO" />
  </div>
</template>

<script>
import { markAttendance } from '../../services/api'
import ReceiptCard from '../../components/ReceiptCard.vue'
import AlertBox from '../../components/AlertBox.vue'

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
        this.message = 'Por favor, ingresa tu documento.'
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
