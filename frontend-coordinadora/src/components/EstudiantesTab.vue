<template>
  <div class="tab-content">
    <!-- Buscador y filtro en tiempo real -->
    <div class="search-bar-wrapper mb-3">
      <input 
        type="text" 
        v-model="searchQuery" 
        placeholder="🔍 Buscar estudiante por nombre, documento o grupo..."
        class="search-input"
      />
    </div>

    <div v-if="filteredStudents.length === 0" class="empty-state">
      <p>No se encontraron estudiantes con el criterio ingresado.</p>
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
          <tr v-for="student in filteredStudents" :key="student.documento" @click="$emit('select-student', student)" class="clickable-row">
            <td><strong>{{ student.documento }}</strong></td>
            <td>{{ student.nombres }} {{ student.apellidos }}</td>
            <td><span class="badge-group">{{ student.grupo }}</span></td>
            <td>
              <span :class="['badge-status', 'badge-' + student.estado.toLowerCase()]">
                {{ student.estado }}
              </span>
            </td>
            <td>
              <button class="btn btn-danger btn-sm" @click.stop="remove(student.documento)" :disabled="loading">
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
import { deleteStudent } from '../services/api'
import AlertBox from './AlertBox.vue'

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
      searchQuery: '',
      loading: false,
      message: '',
      isError: false
    }
  },
  computed: {
    filteredStudents() {
      if (!this.searchQuery.trim()) return this.students;
      const query = this.searchQuery.toLowerCase();
      return this.students.filter(s => 
        (s.documento && s.documento.toLowerCase().includes(query)) ||
        (s.nombres && s.nombres.toLowerCase().includes(query)) ||
        (s.apellidos && s.apellidos.toLowerCase().includes(query)) ||
        (s.grupo && s.grupo.toLowerCase().includes(query))
      );
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

<style scoped>
.search-bar-wrapper {
  display: flex;
}
.search-input {
  width: 100%;
  padding: 10px 16px;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
  font-size: 0.95rem;
  outline: none;
  transition: all var(--transition-fast);
}
.search-input:focus {
  border-color: var(--primary);
  background-color: var(--bg-secondary);
  box-shadow: 0 0 0 3px var(--primary-light);
}
.clickable-row {
  cursor: pointer;
  transition: background-color var(--transition-fast);
}
.clickable-row:hover td {
  background-color: var(--primary-light);
}
</style>

