<template>
  <div class="tab-content">
    <h3>Recuperar Comprobante del Día 📄</h3>
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

    <AlertBox :message="message" :isError="isError" />

    <!-- Receipt Display -->
    <ReceiptCard v-if="receipt" :receipt="receipt" badgeText="ACTUAL" />
  </div>
</template>

<script>
import { getReceipt } from '../../services/api'
import ReceiptCard from '../../components/ReceiptCard.vue'
import AlertBox from '../../components/AlertBox.vue'

export default {
  name: 'RecuperarTab',
  components: {
    ReceiptCard,
    AlertBox
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
  methods: {
    clearMessages() {
      this.message = ''
      this.isError = false
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
