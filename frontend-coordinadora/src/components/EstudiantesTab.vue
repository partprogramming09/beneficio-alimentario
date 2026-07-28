<template>
  <div class="tab-content">
    <!-- Buscador y filtro por grupo en tiempo real -->
    <div class="filter-controls-row mb-3">
      <div class="search-bar-wrapper">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="🔍 Buscar por nombre o documento..."
          class="search-input"
        />
      </div>

      <div class="group-select-wrapper">
        <select v-model="selectedGroupFilter" class="select-group-input">
          <option value="ALL">🏫 Todos los Grupos ({{ availableGroups.length }})</option>
          <option v-for="grp in availableGroups" :key="grp" :value="grp">
            Grupo {{ grp }}
          </option>
        </select>
      </div>
    </div>

    <div v-if="filteredStudents.length === 0" class="empty-state">
      <p>No se encontraron estudiantes beneficiarios con el criterio ingresado.</p>
    </div>

    <div v-else>
      <!-- Vista de DataCards para Móviles (<768px) -->
      <div class="data-cards-grid mobile-only">
        <div 
          v-for="student in filteredStudents" 
          :key="'card-' + student.documento" 
          class="data-card-item"
          @click="$emit('select-student', student)"
        >
          <div class="data-card-header">
            <span class="card-doc"><strong>Doc: {{ student.documento }}</strong></span>
            <span :class="['badge-status', 'badge-' + student.estado.toLowerCase()]">
              {{ student.estado }}
            </span>
          </div>
          <div class="data-card-body">
            <div class="card-name">{{ student.nombres }} {{ student.apellidos }}</div>
            <div class="card-meta">
              <span class="badge-group">Grupo: {{ student.grupo }}</span>
            </div>
          </div>
          <div class="data-card-actions">
            <button class="btn btn-secondary btn-xs" @click.stop="$emit('select-student', student)">
              Ver Ficha
            </button>
            <button class="btn btn-danger btn-xs" @click.stop="remove(student.documento)" :disabled="loading">
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Vista de Tabla para Escritorios (≥768px) -->
      <div class="table-container desktop-only">
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
      selectedGroupFilter: 'ALL',
      loading: false,
      message: '',
      isError: false
    }
  },

  computed: {
    availableGroups() {
      const set = new Set(this.students.map(s => s.grupo).filter(Boolean))
      return Array.from(set).sort()
    },
    filteredStudents() {
      let list = this.students;

      if (this.selectedGroupFilter !== 'ALL') {
        list = list.filter(s => s.grupo === this.selectedGroupFilter)
      }

      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase();
        list = list.filter(s => 
          (s.documento && s.documento.toLowerCase().includes(query)) ||
          (s.nombres && s.nombres.toLowerCase().includes(query)) ||
          (s.apellidos && s.apellidos.toLowerCase().includes(query)) ||
          (s.grupo && s.grupo.toLowerCase().includes(query))
        );
      }

      return list
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
.filter-controls-row {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.search-bar-wrapper {
  flex: 1;
  min-width: 240px;
}

.select-group-input {
  padding: 10px 16px;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
  font-size: 0.92rem;
  font-weight: 600;
  outline: none;
  cursor: pointer;
}

.search-input {

  width: 100%;
  padding: 10px 16px;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
  font-size: 0.92rem;
  outline: none;
  transition: all var(--transition-fast);
}

.search-input:focus {
  border-color: var(--primary);
  background-color: var(--bg-secondary);
  box-shadow: 0 0 0 3px var(--primary-light);
}

.mobile-only {
  display: none;
}

.desktop-only {
  display: block;
}

.card-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-primary);
}

.card-meta {
  font-size: 0.85rem;
}

.clickable-row {
  cursor: pointer;
  transition: background-color var(--transition-fast);
}

.clickable-row:hover td {
  background-color: var(--primary-light);
}

@media (max-width: 768px) {
  .mobile-only {
    display: grid;
  }
  .desktop-only {
    display: none;
  }
  .action-bar-header {
    flex-direction: column;
    align-items: stretch;
  }
  .actions-buttons-group {
    justify-content: flex-start;
  }
}
</style>



