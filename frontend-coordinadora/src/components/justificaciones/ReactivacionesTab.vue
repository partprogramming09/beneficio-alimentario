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
                <th>Documento</th>
                <th>Nombre</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="student in suspendedStudents" :key="student.documento" @click="$emit('select-student', student)" class="clickable-row">
                <td><strong>{{ student.documento }}</strong></td>
                <td>{{ student.nombres }}</td>
                <td>
                  <button class="btn btn-success btn-sm" @click.stop="confirmReactivate(student.documento)" :disabled="loading">
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

            <div v-if="excuse.archivo_adjunto" class="excuse-attachment">
              <button class="btn btn-secondary btn-xs" @click="downloadFile(excuse.id, excuse.archivo_adjunto)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="7 10 12 15 17 10"></polyline>
                  <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Descargar Adjunto
              </button>
            </div>

            <div class="excuse-footer">
              <span>Estado:
                <strong :class="'text-' + excuse.estado.toLowerCase()">{{ excuse.estado }}</strong>
              </span>
              <div class="excuse-actions">
                <button
                  v-if="excuse.estado === 'Pendiente'"
                  class="btn btn-success btn-xs"
                  @click="handleApproveJustification(excuse.id)"
                  :disabled="loading"
                >
                  Aprobar
                </button>
                <button
                  v-if="excuse.estado === 'Pendiente'"
                  class="btn btn-danger btn-xs"
                  @click="confirmReject(excuse.id)"
                  :disabled="loading"
                >
                  Rechazar
                </button>
                <button
                  v-if="excuse.estado_estudiante === 'Suspendido'"
                  class="btn btn-success btn-xs"
                  @click="confirmReactivate(excuse.documento)"
                  :disabled="loading"
                >
                  Reingresar Alumno
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <AlertBox :message="message" :isError="isError" class="mt-3" />

    <ConfirmModal
      :is-open="showReactivateModal"
      title="Reingresar Alumno"
      :message="'¿Estas seguro de reactivar al estudiante con documento ' + targetDoc + '?'"
      confirm-text="Si, reingresar"
      type="success"
      @confirm="doReactivate"
      @close="showReactivateModal = false"
    />

    <ConfirmModal
      :is-open="showRejectModal"
      title="Rechazar Justificacion"
      message="¿Estas seguro de rechazar esta justificacion?"
      confirm-text="Rechazar"
      type="danger"
      @confirm="doReject"
      @close="showRejectModal = false"
    />
  </div>
</template>

<script>
import {
  reactivateStudent,
  getAdminJustifications,
  downloadJustificationFile,
  approveJustification,
  rejectJustification
} from '../../services/api'
import AlertBox from '../common/AlertBox.vue'
import ConfirmModal from '../common/ConfirmModal.vue'

export default {
  name: 'ReactivacionesTab',
  components: {
    AlertBox,
    ConfirmModal
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
      isError: false,
      showReactivateModal: false,
      showRejectModal: false,
      targetDoc: null,
      targetRejectId: null,
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
    async downloadFile(id, fileName) {
      try {
        const response = await downloadJustificationFile(id)
        const blob = new Blob([response.data])
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = fileName
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
      } catch (err) {
        this.message = err.message || 'No se pudo descargar el archivo.'
        this.isError = true
      }
    },
    confirmReactivate(doc) {
      this.targetDoc = doc
      this.showReactivateModal = true
    },
    async doReactivate() {
      const doc = this.targetDoc
      this.showReactivateModal = false
      this.targetDoc = null
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
    },
    confirmReject(id) {
      this.targetRejectId = id
      this.showRejectModal = true
    },
    async doReject() {
      const id = this.targetRejectId
      this.showRejectModal = false
      this.targetRejectId = null
      this.loading = true
      this.clearMessages()
      try {
        const data = await rejectJustification(id)
        this.message = data.message
        this.isError = false
        await this.loadJustifications()
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    },
    async handleApproveJustification(id) {
      this.loading = true
      this.clearMessages()
      try {
        const data = await approveJustification(id)
        this.message = data.message
        this.isError = false
        await this.loadJustifications()
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    },
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

.excuse-attachment {
  margin-bottom: 8px;
}

.excuse-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.82rem;
  flex-wrap: wrap;
  gap: 6px;
}

.excuse-actions {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.text-pendiente { color: var(--warning); }
.text-aprobado { color: var(--success); }
.text-rechazado { color: var(--danger); }
.text-suspendido { color: var(--danger); }
.text-activo { color: var(--success); }

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
