<template>
  <div class="tab-content">
    <h3>Justificación de Inasistencias y Renuncia ⚙️</h3>
    
    <div class="grid-2 mt-3">
      <!-- Justifications Form -->
      <div class="card sub-card">
        <h4>Justificar Inasistencia</h4>
        <p class="description">Carga tu justificación para evitar ser suspendido del beneficio por faltas.</p>
        
        <div class="form-group">
          <label>Documento de Identidad:</label>
          <input type="text" v-model="justification.documento" placeholder="Ingresa tu documento" />
        </div>
        <div class="form-group">
          <label>Fecha de la Inasistencia:</label>
          <input type="date" v-model="justification.fecha_inasistencia" />
        </div>
        <div class="form-group">
          <label>Motivo / Justificación:</label>
          <textarea v-model="justification.motivo" placeholder="Ej: Cita médica, calamidad familiar, etc." rows="3"></textarea>
        </div>
        <button class="btn btn-primary btn-block" @click="submitJustification" :disabled="loading">
          {{ loading ? 'Enviando...' : 'Enviar Justificación' }}
        </button>
      </div>

      <!-- Renunciation Panel -->
      <div class="card sub-card border-danger">
        <h4 class="text-danger">Renuncia Voluntaria</h4>
        <p class="description">¿Ya no necesitas el beneficio? Renuncia voluntariamente para liberar el cupo a otro compañero.</p>
        
        <div class="form-group">
          <label>Documento de Identidad:</label>
          <input type="text" v-model="renounceDoc" placeholder="Ingresa tu documento" />
        </div>
        
        <div class="alert alert-warning">
          ⚠️ <strong>Atención:</strong> Esta acción es irreversible. Tendrás que solicitar el beneficio nuevamente si deseas reingresar.
        </div>
        
        <button class="btn btn-danger btn-block" @click="confirmRenounce" :disabled="loading">
          {{ loading ? 'Procesando...' : 'Renunciar al Beneficio' }}
        </button>
      </div>
    </div>

    <AlertBox :message="message" :isError="isError" />
  </div>
</template>

<script>
import { submitJustification, confirmRenounce } from '../services/api'
import AlertBox from './AlertBox.vue'

export default {
  name: 'GestionTab',
  components: {
    AlertBox
  },
  data() {
    return {
      justification: {
        documento: '',
        fecha_inasistencia: '',
        motivo: ''
      },
      renounceDoc: '',
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
    async submitJustification() {
      const { documento, fecha_inasistencia, motivo } = this.justification
      if (!documento.trim() || !fecha_inasistencia || !motivo.trim()) {
        this.message = 'Por favor, completa todos los campos de la justificación.'
        this.isError = true
        return
      }

      this.loading = true
      this.clearMessages()

      try {
        const data = await submitJustification(this.justification)

        this.message = data.message
        this.isError = false
        this.justification = { documento: '', fecha_inasistencia: '', motivo: '' }
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    },
    async confirmRenounce() {
      if (!this.renounceDoc.trim()) {
        this.message = 'Por favor, ingresa tu documento.'
        this.isError = true
        return
      }

      if (!confirm('¿Estás seguro de que deseas renunciar de forma definitiva al beneficio de almuerzo?')) {
        return
      }

      this.loading = true
      this.clearMessages()

      try {
        const data = await confirmRenounce(this.renounceDoc)

        this.message = data.message
        this.isError = false
        this.renounceDoc = ''
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
