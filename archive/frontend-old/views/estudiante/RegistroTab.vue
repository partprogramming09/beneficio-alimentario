<template>
  <div class="tab-content">
    <h3>Crear Perfil de Beneficiario</h3>
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
        {{ loading ? 'Validando...' : 'Verificar Matrícula' }}
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
</template>

<script>
import { validateStudent, registerProfile } from '../../services/api'
import AlertBox from '../../components/AlertBox.vue'

export default {
  name: 'RegistroTab',
  components: {
    AlertBox
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
      isError: false
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
        this.registration = { documento: '', nombres: '', apellidos: '', grupo: '' }
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
