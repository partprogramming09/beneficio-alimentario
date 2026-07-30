<template>
  <div class="tab-content">
    <h3 class="tab-heading">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
        <path d="M6 12h.01M10 12h4M18 12h.01"></path>
      </svg>
      Mi Ticket de Asistencia Diario
    </h3>
    
    <!-- Vista cuando no está identificado -->
    <div v-if="!studentDoc">
      <p class="description">¿Cerraste la pestaña? Ingresa tu documento para recuperar tu comprobante de asistencia del día de hoy.</p>
      <div class="form-group inline-form">
        <label for="doc-rec">Documento de Identidad:</label>
        <input 
          id="doc-rec" 
          type="text" 
          v-model="retrieveDoc" 
          placeholder="Ingresa tu documento"
          @keyup.enter="getReceipt"
        />
        <button class="btn btn-primary" @click="getReceipt" :disabled="loading">
          {{ loading ? 'Recuperando...' : 'Recuperar' }}
        </button>
      </div>
    </div>

    <!-- Vista identificada con auto-carga -->
    <div v-else-if="loading" class="text-center py-4">
      <p>Cargando tu ticket diario...</p>
    </div>

    <AlertBox :message="message" :isError="isError" />

    <!-- Receipt Display -->
    <ReceiptCard v-if="receipt" :receipt="receipt" badgeText="ACTUAL" />
    
    <!-- Mensaje amigable cuando da 404 siendo estudiante identificado -->
    <div v-else-if="studentDoc && !loading && !receipt" class="alert alert-warning text-center mt-4">
      <p><strong>Aún no se ha registrado tu almuerzo hoy. 🍽️</strong></p>
      <p style="font-size: 0.9rem; margin-top: 5px; color: var(--text-secondary);">
        Tu perfil está activo, pero debes marcar tu ingreso en la fila del comedor escolar para emitir tu ticket del día.
      </p>
    </div>
  </div>
</template>

<script>
import { getReceipt } from '../services/api'
import ReceiptCard from './ReceiptCard.vue'
import AlertBox from './AlertBox.vue'

export default {
  name: 'RecuperarTab',
  components: {
    ReceiptCard,
    AlertBox
  },
  props: {
    studentDoc: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      retrieveDoc: '',
      receipt: null,
      loading: false,
      message: '',
      isError: false
    }
  },
  watch: {
    studentDoc: {
      immediate: true,
      handler(newDoc) {
        if (newDoc) {
          this.autoGetReceipt(newDoc)
        } else {
          this.receipt = null
          this.clearMessages()
        }
      }
    }
  },
  methods: {
    clearMessages() {
      this.message = ''
      this.isError = false
    },
    async autoGetReceipt(doc) {
      this.loading = true
      this.clearMessages()
      this.receipt = null

      try {
        const data = await getReceipt(doc)
        this.receipt = data
        this.isError = false
      } catch (err) {
        // En auto-carga silenciosa, no mostramos error 404 como crítico
        if (err.response && err.response.status === 404) {
          this.receipt = null
        } else {
          this.message = err.message
          this.isError = true
        }
      } finally {
        this.loading = false
      }
    },
    async getReceipt() {
      if (!this.retrieveDoc.trim()) {
        this.message = 'Por favor, ingresa tu documento.'
        this.isError = true
        return
      }

      this.loading = true
      this.clearMessages()
      this.receipt = null

      try {
        const data = await getReceipt(this.retrieveDoc)
        this.receipt = data
        this.isError = false
        
        // Emitir inicio de sesión al componente padre
        this.$emit('session-started', { documento: this.retrieveDoc, nombre: data.nombre })
        this.retrieveDoc = ''
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
