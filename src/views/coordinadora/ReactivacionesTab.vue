<template>
  <div class="tab-content">
    <h3>Justificaciones y Control de Suspendidos</h3>
    
    <div class="grid-2">
      <!-- Suspended Students List -->
      <div class="card sub-card">
        <h4>Alumnos Suspendidos (3 Inasistencias) ⚠️</h4>
        <p class="description">Estudiantes bloqueados por acumular 3 faltas. Revisa sus excusas a la derecha para reactivarlos.</p>

        <div v-if="suspendedStudents.length === 0" class="empty-state">
          <p>No hay alumnos suspendidos actualmente. 🌟</p>
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
              <tr v-for="student in suspendedStudents" :key="student.documento">
                <td><strong>{{ student.documento }}</strong></td>
                <td>{{ student.nombres }}</td>
                <td>
                  <button class="btn btn-success btn-sm" @click="reactivate(student.documento)" :disabled="loading">
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
        <h4>Excusas Recibidas 📄</h4>
        <p class="description">Justificaciones enviadas por los estudiantes por inasistencia.</p>

        <div v-if="justifications.length === 0" class="empty-state">
          <p>No se han recibido justificaciones.</p>
        </div>

        <div v-else class="justifications-list">
          <div v-for="excuse in justifications" :key="excuse.id" class="excuse-item">
            <div class="excuse-header">
              <strong>{{ excuse.nombres }} {{ excuse.apellidos }} ({{ excuse.grupo }})</strong>
              <span class="excuse-date">Inasistencia: {{ excuse.fecha_inasistencia }}</span>
            </div>
            <p class="excuse-reason">"{{ excuse.motivo }}"</p>
            <div class="excuse-footer">
              <span>Estado actual alumno: 
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
import { reactivateStudent, getAdminJustifications } from '../../services/api'
import AlertBox from '../../components/AlertBox.vue'

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
