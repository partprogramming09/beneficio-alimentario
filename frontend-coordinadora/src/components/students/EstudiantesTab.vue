<template>
  <div class="tab-content">
    <!-- Buscador, filtro por estado y por grupo en tiempo real -->
    <div class="filter-controls-row mb-3">
      <div class="search-bar-wrapper">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Buscar por nombre, documento o grupo..."
          class="search-input"
        />
      </div>

      <div class="filter-select-wrapper">
        <select v-model="selectedStatusFilter" class="select-filter-input">
          <option value="ALL">Todos los Estados</option>
          <option value="Sin Registrar">Sin Registrar</option>
          <option value="Activo">Activo</option>
          <option value="Suspendido">Suspendido</option>
          <option value="Inactivo">Inactivo</option>
          <option value="Retirado">Retirado</option>
        </select>
      </div>

      <div class="filter-select-wrapper">
        <select v-model="selectedGroupFilter" class="select-filter-input">
          <option value="ALL">Todos los Grupos ({{ availableGroups.length }})</option>
          <option v-for="grp in availableGroups" :key="grp" :value="grp">
            Grupo {{ grp }}
          </option>
        </select>
      </div>
    </div>

    <!-- Indicador de total y paginador top -->
    <div class="data-summary-bar mb-3" v-if="filteredStudents.length > 0">
      <span class="total-badge">Mostrando {{ paginatedStudents.length }} de {{ filteredStudents.length }} estudiantes</span>
      <div class="page-size-selector">
        <label>Mostrar:</label>
        <select v-model.number="pageSize" class="select-pagesize">
          <option :value="10">10</option>
          <option :value="15">15</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
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
          v-for="student in paginatedStudents" 
          :key="'card-' + student.documento" 
          class="data-card-item"
          @click="$emit('select-student', student)"
        >
          <div class="data-card-header">
            <span class="badge-doc-highlight">Doc: {{ student.documento }}</span>
            <span :class="['badge-status', getStatusClass(student.estado)]">
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
              👁️ Ver Ficha
            </button>
            <button class="btn btn-danger btn-xs" @click.stop="remove(student.documento)" :disabled="loading">
              🗑️ Eliminar
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
              <th>Estado Beneficio</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in paginatedStudents" :key="student.documento" @click="$emit('select-student', student)" class="clickable-row">
              <td><span class="badge-doc-highlight">{{ student.documento }}</span></td>
              <td><strong>{{ student.nombres }} {{ student.apellidos }}</strong></td>
              <td><span class="badge-group">{{ student.grupo }}</span></td>
              <td>
                <span :class="['badge-status', getStatusClass(student.estado)]">
                  {{ student.estado }}
                </span>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <button class="btn btn-secondary btn-sm" @click.stop="$emit('select-student', student)">
                    👁️ Ver Ficha
                  </button>
                  <button class="btn btn-danger btn-sm" @click.stop="remove(student.documento)" :disabled="loading">
                    🗑️ Eliminar
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginador Footer -->
      <div class="pagination-footer mt-3" v-if="totalPages > 1">
        <button 
          class="btn btn-secondary btn-sm" 
          :disabled="currentPage === 1" 
          @click="currentPage--"
        >
          &laquo; Anterior
        </button>
        <span class="page-indicator">Página <strong>{{ currentPage }}</strong> de <strong>{{ totalPages }}</strong></span>
        <button 
          class="btn btn-secondary btn-sm" 
          :disabled="currentPage >= totalPages" 
          @click="currentPage++"
        >
          Siguiente &raquo;
        </button>
      </div>

    </div>

    <AlertBox :message="message" :isError="isError" />

    <ConfirmModal
      :is-open="showDeleteModal"
      title="Eliminar Estudiante"
      :message="'¿Estás seguro de que deseas eliminar permanentemente de la base de datos al estudiante con documento ' + deleteTarget + '?'"
      confirm-text="Eliminar"
      type="danger"
      @confirm="removeStudent"
      @close="showDeleteModal = false"
    />
  </div>
</template>

<script>
import { deleteStudent } from '../../services/api'
import AlertBox from '../common/AlertBox.vue'
import ConfirmModal from '../common/ConfirmModal.vue'
import { getStatusClass } from '../../utils/statusHelper'

export default {
  name: 'EstudiantesTab',
  components: {
    AlertBox,
    ConfirmModal
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
      selectedStatusFilter: 'ALL',
      selectedGroupFilter: 'ALL',
      currentPage: 1,
      pageSize: 15,
      loading: false,
      message: '',
      isError: false,
      showDeleteModal: false,
      deleteTarget: null
    }
  },

  computed: {
    availableGroups() {
      const set = new Set(this.students.map(s => s.grupo).filter(Boolean))
      return Array.from(set).sort()
    },
    filteredStudents() {
      let list = this.students;

      if (this.selectedStatusFilter !== 'ALL') {
        list = list.filter(s => String(s.estado).toLowerCase() === this.selectedStatusFilter.toLowerCase())
      }

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
    },
    totalPages() {
      return Math.ceil(this.filteredStudents.length / this.pageSize) || 1
    },
    paginatedStudents() {
      const start = (this.currentPage - 1) * this.pageSize
      return this.filteredStudents.slice(start, start + this.pageSize)
    }
  },

  watch: {
    searchQuery() {
      this.currentPage = 1
    },
    selectedStatusFilter() {
      this.currentPage = 1
    },
    selectedGroupFilter() {
      this.currentPage = 1
    },
    pageSize() {
      this.currentPage = 1
    }
  },

  methods: {
    getStatusClass,
    clearMessages() {
      this.message = ''
      this.isError = false
    },
    async remove(doc) {
      this.deleteTarget = doc
      this.showDeleteModal = true
    },
    async removeStudent() {
      const doc = this.deleteTarget
      this.showDeleteModal = false
      this.deleteTarget = null

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

.filter-select-wrapper {
  min-width: 170px;
}

.select-filter-input {
  width: 100%;
  padding: 10px 16px;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
  font-size: 0.92rem;
  font-weight: 600;
  outline: none;
  cursor: pointer;
  transition: border-color var(--transition-fast);
}

.select-filter-input:focus {
  border-color: var(--primary);
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

.data-summary-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
  color: var(--text-muted);
  padding: 4px 6px;
}

.total-badge {
  font-weight: 600;
}

.page-size-selector {
  display: flex;
  align-items: center;
  gap: 6px;
}

.select-pagesize {
  padding: 4px 8px;
  border-radius: var(--border-radius-sm);
  border: 1px solid var(--border-color);
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
  font-size: 0.85rem;
  outline: none;
}

.pagination-footer {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  padding: 12px 0;
}

.page-indicator {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.mobile-only {
  display: none;
}

.desktop-only {
  display: block;
}

.badge-doc-highlight {
  background-color: var(--primary-light);
  color: var(--primary);
  border: 1px solid var(--border-color);
  padding: 4px 10px;
  border-radius: var(--border-radius-pill);
  font-weight: 700;
  font-family: monospace, sans-serif;
  font-size: 0.88rem;
  display: inline-block;
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
    gap: 12px;
  }
  .desktop-only {
    display: none;
  }
}
</style>
