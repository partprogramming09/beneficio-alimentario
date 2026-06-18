<template>
  <div class="tab-content">
    <h3>Estudiantes en el Programa</h3>
    <p class="description">Control de estudiantes inscritos y sus estados operativos actuales.</p>

    <div v-if="students.length === 0" class="empty-state">
      <p>No hay estudiantes registrados en el programa.</p>
    </div>

    <div v-else class="table-container">
      <table>
        <thead>
          <tr>
            <th>Documento</th>
            <th>Nombre Completo</th>
            <th>Grupo</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students" :key="student.documento">
            <td><strong>{{ student.documento }}</strong></td>
            <td>{{ student.nombres }} {{ student.apellidos }}</td>
            <td><span class="badge-group">{{ student.grupo }}</span></td>
            <td>
              <span :class="['badge-status', 'badge-' + student.estado.toLowerCase()]">
                {{ student.estado }}
              </span>
            </td>
            <td>
              <button class="btn btn-danger btn-sm" @click="remove(student.documento)" :disabled="loading">
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <AlertBox :message="message" :isError="isError" />
  </div>
</template>

<script>
import { deleteStudent } from '../../services/api'
import AlertBox from '../../components/AlertBox.vue'

export default {
  name: 'EstudiantesTab',
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
    async remove(doc) {
      if (!confirm(`¿Estás seguro de que deseas eliminar permanentemente de la base de datos al estudiante con documento ${doc}?`)) {
        return
      }

      this.loading = true
      this.clearMessages()
      try {
        const data = await deleteStudent(doc)
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
