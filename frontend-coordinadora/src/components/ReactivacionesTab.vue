<template>
  <div class="tab-content">
    <div class="grid-2">
      <!-- Suspended Students List -->
      <div class="card sub-card">
        <h4 class="card-title-sm">Alumnos Suspendidos</h4>

        <div v-if="suspendedStudents.length === 0" class="empty-state">
          <p>No hay alumnos suspendidos actualmente.</p>
        </div>

        <div v-else class="table-container">
          <table>
            <thead>
              <tr>
                <th>Doc</th>
                <th>Nombre</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="student in suspendedStudents" :key="student.documento" @click="$emit('select-student', student)" class="clickable-row">
                <td><strong>{{ student.documento }}</strong></td>
                <td>{{ student.nombres }}</td>
                <td>
                  <button class="btn btn-success btn-sm" @click.stop="reactivate(student.documento)" :disabled="loading">
                    Reingresar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Justifications List -->
      <div class="card sub-card">
        <h4 class="card-title-sm">Excusas Recibidas</h4>

        <div v-if="justifications.length === 0" class="empty-state">
          <p>No se han recibido justificaciones.</p>
        </div>

        <div v-else class="justifications-list">
          <div v-for="excuse in justifications" :key="excuse.id" class="excuse-item">
            <div class="excuse-header">
              <strong>{{ excuse.nombres }} {{ excuse.apellidos }} ({{ excuse.grupo }})</strong>
              <span class="excuse-date">Falla: {{ excuse.fecha_inasistencia }}</span>
            </div>
            <p class="excuse-reason">"{{ excuse.motivo }}"</p>
            <div class="excuse-footer">
              <span>Estado: 
                <strong :class="'text-' + excuse.estado.toLowerCase()">{{ excuse.estado }}</strong>
              </span>
              <button 
                v-if="excuse.estado === 'Suspendido'" 
                class="btn btn-success btn-xs" 
                @click="reactivate(excuse.documento)"
                :disabled="loading"
              >
                Aprobar Reingreso
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <AlertBox :message="message" :isError="isError" class="mt-3" />
  </div>
</template>

<script>
import { reactivateStudent, getAdminJustifications } from '../services/api'
import AlertBox from './AlertBox.vue'

export default {
  name: 'ReactivacionesTab',
  components: {
    AlertBox
  },
  props: {
    suspendedStudents: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      justifications: [],
      loading: false,
      message: '',
      isError: false
    }
  },
  mounted() {
    this.loadJustifications()
  },
  methods: {
    clearMessages() {
      this.message = ''
      this.isError = false
    },
    async loadJustifications() {
      try {
        const data = await getAdminJustifications()
        this.justifications = data
      } catch (err) {
        console.error(err)
        this.message = err.message
        this.isError = true
      }
    },
    async reactivate(doc) {
      this.loading = true
      this.clearMessages()
      try {
        const data = await reactivateStudent(doc)
        this.message = data.message
        this.isError = false
        this.$emit('refresh-students')
        await this.loadJustifications()
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
.card-title-sm {
  font-size: 1.05rem;
  font-weight: 700;
  margin-bottom: 12px;
  color: var(--text-primary);
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 8px;
}

.sub-card {
  padding: 18px;
  border-radius: var(--border-radius-sm);
  background: var(--bg-secondary);
}

.justifications-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 400px;
  overflow-y: auto;
  padding-right: 4px;
}

.excuse-item {
  background-color: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-sm);
  padding: 12px 14px;
}

.excuse-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.88rem;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 4px;
  margin-bottom: 6px;
  flex-wrap: wrap;
  gap: 5px;
}

.excuse-date {
  color: var(--text-secondary);
  font-size: 0.8rem;
}

.excuse-reason {
  font-style: italic;
  color: var(--text-primary);
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.excuse-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.82rem;
}

.text-suspendido {
  color: var(--danger);
}

.text-activo {
  color: var(--success);
}

.clickable-row {
  cursor: pointer;
  transition: background-color var(--transition-fast);
}
.clickable-row:hover td {
  background-color: var(--primary-light);
}

.empty-state {
  text-align: center;
  padding: 30px 15px;
  color: var(--text-muted);
  font-size: 0.95rem;
}
</style>

