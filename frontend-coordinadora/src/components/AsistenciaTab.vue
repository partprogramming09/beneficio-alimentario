<template>
  <div class="tab-content attendance-tab-wrapper">
    <div class="attendance-input-box">
      <div class="form-group inline-form">
        <label for="doc-asis">Documento Estudiante:</label>
        <input 
          id="doc-asis" 
          type="text" 
          v-model="attendanceDoc" 
          placeholder="Ej: 1001" 
          @keyup.enter="markAttendance"
          ref="docInput"
        />
        <button class="btn btn-primary" @click="markAttendance" :disabled="loading">
          {{ loading ? 'Registrando...' : 'Marcar Asistencia' }}
        </button>
      </div>
    </div>

    <AlertBox :message="message" :isError="isError" />

    <!-- Receipt Display Card -->
    <ReceiptCard v-if="receipt" :receipt="receipt" badgeText="REGISTRADO EN FILA" />
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
  mounted() {
    this.$refs.docInput?.focus()
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
.attendance-tab-wrapper {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.attendance-input-box {
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
  padding: 24px;
}

.inline-form {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.inline-form label {
  font-weight: 700;
  font-size: 0.95rem;
  color: var(--text-primary);
}

.inline-form input {
  flex: 1;
  min-width: 220px;
  padding: 12px 16px;
  border-radius: var(--border-radius-sm);
  border: 1px solid var(--border-color);
  background-color: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 1rem;
  outline: none;
  transition: all var(--transition-fast);
}

.inline-form input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-light);
}
</style>

