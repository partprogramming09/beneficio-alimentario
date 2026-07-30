<template>
  <div class="tab-content">
    <div v-if="receipt">
      <div class="alert alert-success text-center">
        🎉 ¡Perfil registrado y asistencia diaria marcada con éxito!
      </div>
      <ReceiptCard :receipt="receipt" badgeText="REGISTRADO" />
      <div class="form-actions mt-4 text-center">
        <button class="btn btn-secondary" @click="resetForm">Registrar Otro Estudiante</button>
      </div>
    </div>
    
    <div v-else>
      <h3 class="tab-heading">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
          <circle cx="8.5" cy="7" r="4"></circle>
          <line x1="20" y1="8" x2="20" y2="14"></line>
          <line x1="17" y1="11" x2="23" y2="11"></line>
        </svg>
        Crear Perfil de Beneficiario
      </h3>
      <p class="description">Ingresa tu número de documento para validar que estás matriculado e iniciar el registro.</p>
      
      <div v-if="!isValidated" class="form-group inline-form">
        <label for="doc-val">Documento de Identidad:</label>
        <input 
          id="doc-val" 
          type="text" 
          v-model="registration.documento" 
          placeholder="Ej: 1001" 
          @keyup.enter="validateStudent"
        />
        <button class="btn btn-primary" @click="validateStudent" :disabled="loading">
          {{ loading ? 'Verificando...' : 'Verificar Registro' }}
        </button>
      </div>

      <div v-else class="validated-form">
        <div class="alert alert-success">
          ✔️ Documento verificado. Estudiante matriculado: <strong>{{ validatedName }}</strong> (Grupo: {{ validatedGroup }}).
        </div>
        
        <div class="form-group">
          <label>Nombres:</label>
          <input type="text" v-model="registration.nombres" placeholder="Ingresa tus nombres" />
        </div>
        <div class="form-group">
          <label>Apellidos:</label>
          <input type="text" v-model="registration.apellidos" placeholder="Ingresa tus apellidos" />
        </div>
        <div class="form-group">
          <label>Grupo:</label>
          <input type="text" v-model="registration.grupo" readonly />
        </div>

        <div class="form-actions">
          <button class="btn btn-secondary" @click="resetRegistration">Cancelar</button>
          <button class="btn btn-primary" @click="registerProfile" :disabled="loading">
            {{ loading ? 'Registrando...' : 'Confirmar Registro' }}
          </button>
        </div>
      </div>

      <AlertBox :message="message" :isError="isError" />
    </div>
  </div>
</template>

<script>
import { validateStudent, registerProfile } from '../../services/api'
import AlertBox from '../common/AlertBox.vue'
import ReceiptCard from '../tickets/ReceiptCard.vue'

export default {
  name: 'RegistroTab',
  components: {
    AlertBox,
    ReceiptCard
  },
  data() {
    return {
      registration: {
        documento: '',
        nombres: '',
        apellidos: '',
        grupo: ''
      },
      isValidated: false,
      validatedName: '',
      validatedGroup: '',
      loading: false,
      message: '',
      isError: false,
      receipt: null
    }
  },
  methods: {
    clearMessages() {
      this.message = ''
      this.isError = false
    },
    async validateStudent() {
      if (!this.registration.documento.trim()) {
        this.message = 'Por favor, ingresa tu documento de identidad.'
        this.isError = true
        return
      }

      this.loading = true
      this.clearMessages()

      try {
        const data = await validateStudent(this.registration.documento)

        if (data.registrado) {
          this.message = data.message || '¡Ya estás registrado en el sistema! Cargando tu ticket de asistencia diario...'
          this.isError = false
          setTimeout(() => {
            this.$emit('session-started', { documento: data.documento, nombre: data.nombre_completo })
          }, 1200)
          return
        }

        this.isValidated = true
        this.validatedName = data.nombre_completo
        this.validatedGroup = data.grupo
        
        // Auto-split name for convenience
        const parts = data.nombre_completo.split(' ')
        this.registration.nombres = parts[0] || ''
        this.registration.apellidos = parts.slice(1).join(' ') || ''
        this.registration.grupo = data.grupo
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    },
    resetRegistration() {
      this.registration = { documento: '', nombres: '', apellidos: '', grupo: '' }
      this.isValidated = false
      this.clearMessages()
    },
    async registerProfile() {
      if (!this.registration.nombres.trim() || !this.registration.apellidos.trim()) {
        this.message = 'Por favor, completa tus nombres y apellidos.'
        this.isError = true
        return
      }

      this.loading = true
      this.clearMessages()

      try {
        const data = await registerProfile(this.registration)

        this.message = data.message
        this.isError = false
        this.isValidated = false
        this.receipt = data.comprobante
        this.registration = { documento: '', nombres: '', apellidos: '', grupo: '' }
        
        // Auto iniciar sesión del estudiante recién registrado
        this.$emit('session-started', { documento: data.estudiante.documento, nombre: data.comprobante.nombre })
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    },
    resetForm() {
      this.receipt = null
      this.resetRegistration()
    }
  }
}
</script>
