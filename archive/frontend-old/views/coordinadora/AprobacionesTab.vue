<template>
  <div class="tab-content">
    <h3>Solicitudes Pendientes de Aprobación</h3>
    <p class="description">Estudiantes que se registraron y esperan aprobación para poder marcar asistencia en el comedor.</p>

    <div v-if="students.length === 0" class="empty-state">
      <p>No hay solicitudes pendientes de aprobación en este momento. ✨</p>
    </div>

    <div v-else class="table-container">
      <table>
        <thead>
          <tr>
            <th>Documento</th>
            <th>Nombre Completo</th>
            <th>Grupo</th>
            <th>Fecha Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students" :key="student.documento">
            <td><strong>{{ student.documento }}</strong></td>
            <td>{{ student.nombres }} {{ student.apellidos }}</td>
            <td><span class="badge-group">{{ student.grupo }}</span></td>
            <td>{{ student.creado_en }}</td>
            <td>
              <div class="action-buttons">
                <button class="btn btn-success btn-sm" @click="approve(student.documento)" :disabled="loading">Aprobar</button>
                <button class="btn btn-secondary btn-sm" @click="reject(student.documento)" :disabled="loading">Rechazar</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <AlertBox :message="message" :isError="isError" />
  </div>
</template>

<script>
import { approveStudent, rejectStudent } from '../../services/api'
import AlertBox from '../../components/AlertBox.vue'

export default {
  name: 'AprobacionesTab',
  components: {
    AlertBox
  },
  props: {
    students: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
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
    async approve(doc) {
      this.loading = true
      this.clearMessages()
      try {
        const data = await approveStudent(doc)
        this.message = data.message
        this.isError = false
        this.$emit('refresh-students')
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    },
    async reject(doc) {
      this.loading = true
      this.clearMessages()
      try {
        const data = await rejectStudent(doc)
        this.message = data.message
        this.isError = false
        this.$emit('refresh-students')
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
