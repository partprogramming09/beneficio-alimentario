<template>
  <div v-if="isOpen" class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-card">
      <div class="modal-header">
        <h4>Agregar Estudiante Individual</h4>
        <button class="btn-close" @click="$emit('close')">&times;</button>
      </div>

      <div class="modal-body">
        <form @submit.prevent="saveStudent" class="form-stack">
          <div class="form-group">
            <label for="doc-ind">Documento de Identidad (*)</label>
            <input 
              id="doc-ind" 
              type="text" 
              v-model="form.documento" 
              placeholder="Ej: 1098765432" 
              required
            />
          </div>

          <div class="form-group">
            <label for="nom-ind">Nombres (*)</label>
            <input 
              id="nom-ind" 
              type="text" 
              v-model="form.nombres" 
              placeholder="Ej: Laura Sofía" 
              required
            />
          </div>

          <div class="form-group">
            <label for="ape-ind">Apellidos (*)</label>
            <input 
              id="ape-ind" 
              type="text" 
              v-model="form.apellidos" 
              placeholder="Ej: Ramírez Gómez" 
              required
            />
          </div>

          <div class="form-group">
            <label for="gru-ind">Grado / Grupo (*)</label>
            <input 
              id="gru-ind" 
              type="text" 
              v-model="form.grupo" 
              placeholder="Ej: 10-1" 
              required
            />
          </div>

          <AlertBox :message="message" :isError="isError" class="mt-2" />

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              {{ loading ? 'Guardando...' : 'Guardar y Activar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { createSingleStudent } from '../services/api'
import AlertBox from './AlertBox.vue'

export default {
  name: 'AgregarEstudianteModal',
  components: {
    AlertBox
  },
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      form: {
        documento: '',
        nombres: '',
        apellidos: '',
        grupo: ''
      },
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
    async saveStudent() {
      if (!this.form.documento || !this.form.nombres || !this.form.apellidos || !this.form.grupo) {
        this.message = 'Por favor completa todos los campos requeridos.'
        this.isError = true
        return
      }

      this.loading = true
      this.clearMessages()

      try {
        const res = await createSingleStudent(this.form)
        this.message = res.message
        this.isError = false
        this.$emit('refresh-students')
        setTimeout(() => {
          this.form = { documento: '', nombres: '', apellidos: '', grupo: '' }
          this.$emit('close')
        }, 1200)
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
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(4px);
  z-index: 120;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  animation: fadeIn var(--transition-fast) forwards;
}

.modal-card {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
  box-shadow: var(--shadow-lg);
  width: 100%;
  max-width: 480px;
  overflow: hidden;
}

.modal-header {
  padding: 18px 24px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h4 {
  margin: 0;
  font-size: 1.1rem;
  color: var(--text-primary);
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.6rem;
  color: var(--text-muted);
  cursor: pointer;
}

.modal-body {
  padding: 24px;
}

.form-stack {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
