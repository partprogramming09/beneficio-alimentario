<template>
  <div class="tab-content">
    <h3 class="tab-heading">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
        <polyline points="14 2 14 8 20 8"></polyline>
        <line x1="16" y1="13" x2="8" y2="13"></line>
        <line x1="16" y1="17" x2="8" y2="17"></line>
      </svg>
      Justificación de Inasistencias y Renuncia
    </h3>
    
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
        <div class="form-group">
          <label>Archivo Adjunto (opcional):</label>
          <FileUpload v-model="archivo" label="Adjuntar evidencia" />
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
          <strong>Atención:</strong> Esta acción es irreversible. Tendrás que solicitar el beneficio nuevamente si deseas reingresar.
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
import { submitJustification, confirmRenounce } from '../../services/api'
import { FileUpload } from '@shared/core'
import AlertBox from '../common/AlertBox.vue'

export default {
  name: 'GestionTab',
  components: {
    AlertBox,
    FileUpload
  },
  props: {
    studentDoc: { type: String, default: '' }
  },
  data() {
    return {
      justification: {
        documento: '',
        fecha_inasistencia: '',
        motivo: ''
      },
      archivo: null,
      renounceDoc: '',
      loading: false,
      message: '',
      isError: false
    }
  },
  mounted() {
    if (this.studentDoc) {
      this.justification.documento = this.studentDoc
      this.renounceDoc = this.studentDoc
    }
  },
  watch: {
    studentDoc(newVal) {
      if (newVal) {
        this.justification.documento = newVal
        this.renounceDoc = newVal
      }
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
        const data = await submitJustification(this.justification, this.archivo)
        this.message = data.message
        this.isError = false
        this.justification = { documento: this.studentDoc || '', fecha_inasistencia: '', motivo: '' }
        this.archivo = null
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
