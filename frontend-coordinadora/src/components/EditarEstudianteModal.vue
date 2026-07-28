<template>
  <div v-if="isOpen && student" class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-card">
      <div class="modal-header">
        <h4>✏️ Editar Estudiante Matriculado</h4>
        <button class="btn-close" @click="$emit('close')">&times;</button>
      </div>

      <div class="modal-body">
        <form @submit.prevent="saveChanges" class="form-stack">
          <div class="form-group">
            <label for="edit-doc">Documento de Identidad (TI / CC) (*)</label>
            <input 
              id="edit-doc" 
              type="text" 
              v-model="form.documento" 
              required
            />
          </div>

          <div class="form-group">
            <label for="edit-nom">Nombre Completo (*)</label>
            <input 
              id="edit-nom" 
              type="text" 
              v-model="form.nombre_completo" 
              required
            />
          </div>

          <div class="form-group">
            <label for="edit-gru">Grado / Grupo (*)</label>
            <input 
              id="edit-gru" 
              type="text" 
              v-model="form.grupo" 
              required
            />
          </div>

          <AlertBox :message="message" :isError="isError" class="mt-2" />

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              {{ loading ? 'Guardando...' : '💾 Guardar Cambios' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { updateStudent } from '../services/api'
import AlertBox from './AlertBox.vue'

export default {
  name: 'EditarEstudianteModal',
  components: {
    AlertBox
  },
  props: {
    isOpen: {
      type: Boolean,
      default: false
    },
    student: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      form: {
        documento_original: '',
        documento: '',
        nombre_completo: '',
        grupo: ''
      },
      loading: false,
      message: '',
      isError: false
    }
  },
  watch: {
    student: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.form = {
            documento_original: newVal.documento,
            documento: newVal.documento,
            nombre_completo: newVal.nombre_completo,
            grupo: newVal.grupo
          }
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
    async saveChanges() {
      if (!this.form.documento || !this.form.nombre_completo || !this.form.grupo) {
        this.message = 'Por favor completa todos los campos requeridos.'
        this.isError = true
        return
      }

      this.loading = true
      this.clearMessages()

      try {
        const res = await updateStudent(this.form)
        this.message = res.message
        this.isError = false
        this.$emit('refresh-students')
        setTimeout(() => {
          this.$emit('close')
        }, 1000)
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
</style>
