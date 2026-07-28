<template>
  <div class="tab-content">
    <!-- Barra Superior de Carga y Gestión de Matriculados -->
    <div class="top-actions-bar mb-3">
      <div class="actions-title">
        <h5 class="m-0">🏫 Gestión de Matrícula Institucional</h5>
        <span class="text-muted small">Carga de base de datos o registro de alumnos</span>
      </div>

      <div class="actions-buttons-group">
        <button class="btn btn-primary btn-sm" @click="isAddModalOpen = true">
          ➕ Agregar Estudiante
        </button>
        <button class="btn btn-secondary btn-sm" @click="isImportModalOpen = true">
          📁 Importar Excel / CSV
        </button>
      </div>
    </div>

    <!-- Header de Filtros por Grupo y Estatus -->
    <div class="courses-filter-bar mb-4">
      <div class="filter-item">
        <label for="select-grupo">Filtrar por Curso/Grupo:</label>
        <select id="select-grupo" v-model="selectedGroupFilter" class="select-input">
          <option value="ALL">🏫 Todos los Cursos ({{ groups.length }} Grupos)</option>
          <option v-for="g in groups" :key="g.nombre_grupo" :value="g.nombre_grupo">
            Grupo {{ g.nombre_grupo }} ({{ g.total_matriculados }} alumnos)
          </option>
        </select>
      </div>

      <div class="filter-item">
        <label>Ver Estatus en Comedor:</label>
        <div class="status-pills">
          <button 
            :class="['pill-btn', { active: statusFilter === 'ALL' }]"
            @click="statusFilter = 'ALL'"
          >
            👥 Todos
          </button>
          <button 
            :class="['pill-btn', { active: statusFilter === 'SI' }]"
            @click="statusFilter = 'SI'"
          >
            ✅ En Comedor (SÍ)
          </button>
          <button 
            :class="['pill-btn', { active: statusFilter === 'NO' }]"
            @click="statusFilter = 'NO'"
          >
            ❌ Sin Inscribir (NO)
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="empty-state">
      <p>🔍 Cargando estructura de cursos y grupos...</p>
    </div>

    <div v-else-if="filteredGroups.length === 0" class="empty-state">
      <p>No se encontraron datos para el filtro seleccionado.</p>
    </div>

    <!-- Contenido de Grupos Organizados -->
    <div v-else class="groups-stack">
      <div v-for="grp in filteredGroups" :key="grp.nombre_grupo" class="group-card mb-4">
        <div class="group-card-header">
          <div class="group-title-info">
            <span class="group-icon font-weight-bold">🏫 Grupo {{ grp.nombre_grupo }}</span>
          </div>

          <div class="group-stats-badges">
            <span class="badge-stat badge-total">🎓 Total: {{ grp.total_matriculados }}</span>
            <span class="badge-stat badge-yes">✅ Inscritos (SÍ): {{ grp.total_inscritos }}</span>
            <span class="badge-stat badge-no">❌ Sin Inscribir (NO): {{ grp.total_sin_inscribir }}</span>
          </div>
        </div>

        <div class="group-card-body">
          <div v-if="getFilteredStudents(grp.estudiantes).length === 0" class="p-3 text-muted text-center">
            No hay estudiantes que coincidan con el filtro en este grupo.
          </div>

          <div v-else>
            <!-- Vista DataCards Móvil -->
            <div class="data-cards-grid mobile-only p-3">
              <div 
                v-for="st in getFilteredStudents(grp.estudiantes)" 
                :key="'c-card-' + st.documento" 
                class="data-card-item"
              >
                <div class="data-card-header">
                  <span class="badge-doc-highlight">🪪 Doc: {{ st.documento }}</span>
                  <span :class="st.esta_inscrito ? 'badge-status-yes' : 'badge-status-no'">
                    {{ st.esta_inscrito ? 'SÍ (En Comedor)' : 'NO (Sin Inscribir)' }}
                  </span>
                </div>
                <div class="data-card-body">
                  <div class="card-name">{{ st.nombre_completo }}</div>
                  <div class="card-meta">Doc Identidad: <strong class="text-primary font-mono">🪪 {{ st.documento }}</strong></div>
                  <div class="card-meta">Estado Beneficio: <strong>{{ st.estado }}</strong></div>
                </div>
                <div class="data-card-actions mt-2">
                  <button v-if="!st.esta_inscrito" class="btn btn-primary btn-xs" @click.stop="manualActivate(st.documento)">
                    ⚡ Activar
                  </button>
                  <button class="btn btn-secondary btn-xs" @click.stop="openEditModal(st)">
                    ✏️ Editar
                  </button>
                  <button class="btn btn-danger btn-xs" @click.stop="removeStudent(st.documento)">
                    🗑️ Eliminar
                  </button>
                </div>
              </div>
            </div>

            <!-- Vista Tabla Escritorio -->
            <div class="table-container desktop-only">
              <table>
                <thead>
                  <tr>
                    <th>Documento (TI / CC)</th>
                    <th>Estudiante</th>
                    <th>Grupo</th>
                    <th>¿Está en Comedor?</th>
                    <th>Estado en Beneficio</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="st in getFilteredStudents(grp.estudiantes)" :key="st.documento">
                    <td><span class="badge-doc-highlight">🪪 {{ st.documento }}</span></td>
                    <td><strong>{{ st.nombre_completo }}</strong></td>
                    <td><span class="badge-group">{{ st.grupo }}</span></td>
                    <td>
                      <span :class="st.esta_inscrito ? 'badge-status-yes' : 'badge-status-no'">
                        {{ st.esta_inscrito ? 'SÍ (Inscrito)' : 'NO (Sin Registrar)' }}
                      </span>
                    </td>
                    <td>
                      <span :class="['badge-status', 'badge-' + st.estado.toLowerCase().replace(' ', '-')]">
                        {{ st.estado }}
                      </span>
                    </td>
                    <td>
                      <div class="table-actions-cell">
                        <button 
                          v-if="!st.esta_inscrito" 
                          class="btn btn-primary btn-xs" 
                          @click.stop="manualActivate(st.documento)"
                          title="Activar directamente en el comedor"
                        >
                          ⚡ Activar
                        </button>
                        <button 
                          class="btn btn-secondary btn-xs" 
                          @click.stop="openEditModal(st)"
                          title="Editar datos del estudiante"
                        >
                          ✏️ Editar
                        </button>
                        <button 
                          class="btn btn-danger btn-xs" 
                          @click.stop="removeStudent(st.documento)"
                          title="Eliminar de la institución"
                        >
                          🗑️ Eliminar
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>



          </div>
        </div>
      </div>
    </div>

    <AlertBox :message="message" :isError="isError" />

    <!-- Modales de Carga, Edición e Ingreso -->
    <AgregarEstudianteModal 
      :is-open="isAddModalOpen" 
      @close="isAddModalOpen = false" 
      @refresh-students="onDataChanged" 
    />

    <ImportarEstudiantesModal 
      :is-open="isImportModalOpen" 
      @close="isImportModalOpen = false" 
      @refresh-students="onDataChanged" 
    />

    <EditarEstudianteModal 
      :is-open="isEditModalOpen" 
      :student="selectedStudentForEdit" 
      @close="isEditModalOpen = false" 
      @refresh-students="onDataChanged" 
    />
  </div>
</template>

<script>
import { getAdminGroups, activateStudentManually, deleteInstitutionalStudent } from '../services/api'
import AlertBox from './AlertBox.vue'
import AgregarEstudianteModal from './AgregarEstudianteModal.vue'
import ImportarEstudiantesModal from './ImportarEstudiantesModal.vue'
import EditarEstudianteModal from './EditarEstudianteModal.vue'

export default {
  name: 'CursosTab',
  components: {
    AlertBox,
    AgregarEstudianteModal,
    ImportarEstudiantesModal,
    EditarEstudianteModal
  },
  data() {
    return {
      groups: [],
      selectedGroupFilter: 'ALL',
      statusFilter: 'ALL',
      isAddModalOpen: false,
      isImportModalOpen: false,
      isEditModalOpen: false,
      selectedStudentForEdit: null,
      loading: false,
      message: '',
      isError: false
    }
  },
  computed: {
    filteredGroups() {
      if (this.selectedGroupFilter === 'ALL') {
        return this.groups
      }
      return this.groups.filter(g => g.nombre_grupo === this.selectedGroupFilter)
    }
  },
  mounted() {
    this.loadGroups()
  },
  methods: {
    async loadGroups() {
      this.loading = true
      try {
        const data = await getAdminGroups()
        this.groups = data
      } catch (err) {
        this.message = err.message
        this.isError = true
      } finally {
        this.loading = false
      }
    },
    openEditModal(st) {
      this.selectedStudentForEdit = st
      this.isEditModalOpen = true
    },
    async removeStudent(doc) {
      if (!confirm(`¿Estás seguro de eliminar permanentemente al estudiante con documento ${doc} de la institución?`)) {
        return
      }
      try {
        const res = await deleteInstitutionalStudent(doc)
        this.message = res.message
        this.isError = false
        this.onDataChanged()
      } catch (err) {
        this.message = err.message
        this.isError = true
      }
    },
    async manualActivate(doc) {
      try {
        const res = await activateStudentManually(doc)
        this.message = res.message
        this.isError = false
        this.onDataChanged()
      } catch (err) {
        this.message = err.message
        this.isError = true
      }
    },

    async manualActivate(doc) {
      try {
        const res = await activateStudentManually(doc)
        this.message = res.message
        this.isError = false
        this.onDataChanged()
      } catch (err) {
        this.message = err.message
        this.isError = true
      }
    },

    onDataChanged() {
      this.loadGroups()
      this.$emit('refresh-students')
    },
    getFilteredStudents(students) {
      if (this.statusFilter === 'SI') {
        return students.filter(s => s.esta_inscrito && s.estado === 'Activo')
      }
      if (this.statusFilter === 'NO') {
        return students.filter(s => !s.esta_inscrito || s.estado !== 'Activo')
      }
      return students
    }
  }
}
</script>


<style scoped>
.top-actions-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  background: var(--bg-tertiary);
  padding: 16px 20px;
  border-radius: var(--border-radius-md);
  border: 1px solid var(--border-color);
}

.actions-title h5 {
  color: var(--text-primary);
  font-weight: 700;
}

.actions-buttons-group {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.table-actions-cell {
  display: flex;
  gap: 6px;
  align-items: center;
  flex-wrap: wrap;
}

.courses-filter-bar {


  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  background-color: var(--bg-tertiary);
  padding: 16px 20px;
  border-radius: var(--border-radius-md);
  border: 1px solid var(--border-color);
}

.filter-item {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.filter-item label {
  font-weight: 700;
  font-size: 0.9rem;
  color: var(--text-primary);
}

.select-input {
  padding: 8px 14px;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
  background-color: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 0.9rem;
  outline: none;
}

.status-pills {
  display: inline-flex;
  gap: 4px;
  background: var(--bg-secondary);
  padding: 4px;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
}

.pill-btn {
  padding: 6px 14px;
  font-size: 0.82rem;
  font-weight: 600;
  border-radius: var(--border-radius-pill);
  background: transparent;
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.pill-btn.active {
  background-color: var(--primary);
  color: white;
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

.font-mono {
  font-family: monospace, sans-serif;
}


.group-card {
  background-color: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.group-card-header {
  padding: 16px 20px;
  background-color: var(--bg-tertiary);
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.group-title-info .group-icon {
  font-size: 1.1rem;
  color: var(--text-primary);
}

.group-stats-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.badge-stat {
  padding: 4px 10px;
  border-radius: var(--border-radius-pill);
  font-size: 0.8rem;
  font-weight: 700;
}

.badge-total {
  background-color: var(--bg-secondary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.badge-yes {
  background-color: var(--success-light);
  color: var(--success);
}

.badge-no {
  background-color: var(--danger-light);
  color: var(--danger);
}

.badge-status-yes {
  background-color: var(--success-light);
  color: var(--success);
  padding: 4px 10px;
  border-radius: var(--border-radius-pill);
  font-weight: 700;
  font-size: 0.8rem;
}

.badge-status-no {
  background-color: var(--danger-light);
  color: var(--danger);
  padding: 4px 10px;
  border-radius: var(--border-radius-pill);
  font-weight: 700;
  font-size: 0.8rem;
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

@media (max-width: 768px) {
  .mobile-only {
    display: grid;
  }
  .desktop-only {
    display: none;
  }
  .courses-filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
