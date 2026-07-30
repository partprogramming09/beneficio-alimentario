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
          Agregar Estudiante
        </button>
        <button class="btn btn-secondary btn-sm" @click="isImportModalOpen = true">
          Importar Excel / CSV
        </button>
      </div>
    </div>

    <!-- Header de Filtros por Grupo y Estatus -->
    <div class="courses-filter-bar mb-4">
      <div class="filter-item">
        <label for="select-grupo">Filtrar por Curso/Grupo:</label>
        <select id="select-grupo" v-model="selectedGroupFilter" class="select-input">
          <option value="ALL">Todos los Cursos ({{ groups.length }} Grupos)</option>
          <option v-for="g in groups" :key="g.nombre_grupo" :value="g.nombre_grupo">
            Grupo {{ g.nombre_grupo }} ({{ g.total_matriculados }} alumnos)
          </option>
        </select>
      </div>

      <div class="filter-item">
        <label>Estado de Inscripción al Beneficio:</label>
        <div class="status-pills">
          <button 
            :class="['pill-btn', { active: statusFilter === 'ALL' }]"
            @click="statusFilter = 'ALL'"
          >
            Todos
          </button>
          <button 
            :class="['pill-btn', { active: statusFilter === 'SI' }]"
            @click="statusFilter = 'SI'"
          >
            Inscritos (SÍ)
          </button>
          <button 
            :class="['pill-btn', { active: statusFilter === 'NO' }]"
            @click="statusFilter = 'NO'"
          >
            Sin Registrar (NO)
          </button>
        </div>
      </div>

      <div class="filter-item">
        <button class="btn btn-secondary btn-sm" @click="toggleAllGroups">
          {{ areAllCollapsed ? 'Desplegar Todos' : 'Contraer Todos' }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="empty-state">
      <p>Cargando estructura de cursos y grupos...</p>
    </div>

    <div v-else-if="filteredGroups.length === 0" class="empty-state">
      <p>No se encontraron datos para el filtro seleccionado.</p>
    </div>

    <!-- Contenido de Grupos Organizados -->
    <div v-else class="groups-grid">
      <div v-for="grp in filteredGroups" :key="grp.nombre_grupo" class="group-card mb-4">
        <div class="group-card-header clickable-header" @click="toggleGroupCollapse(grp.nombre_grupo)">
          <div class="group-title">
            <span class="collapse-icon">{{ isGroupCollapsed(grp.nombre_grupo) ? '▶' : '▼' }}</span>
            <span class="group-icon font-weight-bold">Grupo {{ grp.nombre_grupo }}</span>
          </div>

          <div class="group-stats-badges">
            <span class="badge-stat badge-total">Total Matriculados: {{ grp.total_matriculados }}</span>
            <span class="badge-stat badge-yes">Inscritos: {{ grp.total_inscritos }}</span>
            <span class="badge-stat badge-no">Sin Registrar: {{ grp.total_sin_inscribir }}</span>
          </div>
        </div>

        <div v-show="!isGroupCollapsed(grp.nombre_grupo)" class="group-card-body">

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
                  <span class="badge-doc-highlight">Doc: {{ st.documento }}</span>
                  <span 
                    class="badge-status" 
                    :class="'badge-' + (st.estado ? st.estado.toLowerCase().replace(' ', '-') : 'sin-registrar')"
                  >
                    {{ getEstadoLabel(st) }}
                  </span>
                </div>
                <div class="data-card-body">
                  <div class="card-name">{{ st.nombre_completo }}</div>
                  <div class="card-meta">Doc Identidad: <strong class="text-primary font-mono">{{ st.documento }}</strong></div>
                  <div class="card-meta text-muted small mt-1">
                    <span v-if="!st.esta_inscrito">🟡 Habilitado para autoregistro</span>
                    <span v-else-if="st.estado === 'Activo'">🟢 Registrado voluntariamente</span>
                    <span v-else-if="st.estado === 'Suspendido'">🔴 Suspendido por inasistencia</span>
                    <span v-else>⚪ {{ st.estado }}</span>
                  </div>
                </div>
                <div class="data-card-actions mt-3">
                  <button class="btn btn-secondary btn-xs" @click.stop="openEditModal(st)">
                    ✏️ Editar
                  </button>
                  <button 
                    v-if="!st.esta_inscrito || st.estado !== 'Activo'"
                    class="btn btn-warning btn-xs" 
                    @click.stop="confirmManualActivate(st)"
                    title="Activar de forma directa en caso de excepción médica o problema técnico"
                  >
                    ⚡ Activar por Excepción
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
                    <th>Documento</th>
                    <th>Nombre Completo</th>
                    <th>Grupo</th>
                    <th>Estado de Registro Alumno</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="st in getFilteredStudents(grp.estudiantes)" :key="st.documento">
                    <td><span class="badge-doc-highlight">{{ st.documento }}</span></td>
                    <td><strong>{{ st.nombre_completo }}</strong></td>
                    <td><span class="badge-group">{{ st.grupo }}</span></td>
                    <td>
                      <span 
                        class="badge-status" 
                        :class="'badge-' + (st.estado ? st.estado.toLowerCase().replace(' ', '-').replace('/', '-') : 'sin-registrar')"
                      >
                        {{ getEstadoLabel(st) }}
                      </span>
                    </td>
                    <td>
                      <div class="table-actions-cell">
                        <button 
                          class="btn btn-secondary btn-xs" 
                          @click.stop="openEditModal(st)"
                          title="Editar datos de matrícula"
                        >
                          ✏️ Editar
                        </button>
                        <button 
                          v-if="!st.esta_inscrito || st.estado !== 'Activo'"
                          class="btn btn-warning btn-xs" 
                          @click.stop="confirmManualActivate(st)"
                          title="Activar por excepción en caso de falla técnica o caso especial"
                        >
                          ⚡ Activar por Excepción
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
import { getAdminGroups, activateStudentManually, deleteInstitutionalStudent, toggleCupo, cambiarEstadoBeneficio } from '../services/api'
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
      collapsedGroups: {},
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
    },
    areAllCollapsed() {
      if (this.filteredGroups.length === 0) return false
      return this.filteredGroups.every(g => this.collapsedGroups[g.nombre_grupo])
    }
  },
  mounted() {
    this.loadGroups()
  },
  methods: {
    isGroupCollapsed(grpName) {
      return !!this.collapsedGroups[grpName]
    },
    toggleGroupCollapse(grpName) {
      this.collapsedGroups = {
        ...this.collapsedGroups,
        [grpName]: !this.collapsedGroups[grpName]
      }
    },
    toggleAllGroups() {
      const shouldCollapse = !this.areAllCollapsed
      const nextObj = {}
      if (shouldCollapse) {
        this.filteredGroups.forEach(g => {
          nextObj[g.nombre_grupo] = true
        })
      }
      this.collapsedGroups = nextObj
    },
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

    async confirmManualActivate(st) {
      if (confirm(`¿Deseas activar manualmente por excepción a ${st.nombre_completo} (Doc: ${st.documento})?\n\nNota: Esto registrará al estudiante en el beneficio alimentario sin que deba ingresar al portal.`)) {
        await this.manualActivate(st.documento)
      }
    },

    getEstadoLabel(st) {
      if (!st.esta_inscrito || st.estado === 'Sin Registrar') {
        return '🟡 Sin Registrar'
      }
      if (st.estado === 'Activo') {
        return '🟢 Activo / Registrado'
      }
      if (st.estado === 'Suspendido') {
        return '🔴 Suspendido'
      }
      if (st.estado === 'Inactivo') {
        return '⚪ Inactivo'
      }
      return st.estado
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

.clickable-header {
  cursor: pointer;
  user-select: none;
  transition: background-color var(--transition-fast);
}

.clickable-header:hover {
  background-color: var(--bg-tertiary);
}

.collapse-icon {
  font-size: 0.82rem;
  color: var(--primary);
  margin-right: 8px;
  display: inline-block;
  transition: transform var(--transition-fast);
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

/* Toggle Switch */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  cursor: pointer;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.3s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
  background-color: #22c55e;
}

.toggle-switch input:checked + .toggle-slider:before {
  transform: translateX(20px);
}

/* Badges de Estado del Beneficio */
.badge-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.3px;
}

.badge-sin-registrar {
  background-color: #fef3c7;
  color: #d97706;
  border: 1px solid #fde68a;
}

.badge-activo {
  background-color: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.badge-suspendido {
  background-color: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
}

.badge-inactivo {
  background-color: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.badge-pendiente {
  background-color: #ffedd5;
  color: #c2410c;
  border: 1px solid #fed7aa;
}

/* Estado Select */
.estado-select {
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid var(--border-color);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  background-color: var(--bg-secondary);
  color: var(--text-primary);
}

.estado-select:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.estado-activo {
  border-color: #22c55e;
  color: #22c55e;
}

.estado-pendiente {
  border-color: #f59e0b;
  color: #f59e0b;
}

.estado-suspendido {
  border-color: #ef4444;
  color: #ef4444;
}

.estado-inactivo {
  border-color: #6b7280;
  color: #6b7280;
}

.estado-sin-registrar {
  border-color: #9ca3af;
  color: #9ca3af;
}

/* Card rows para móvil */
.card-row-toggle,
.card-row-select {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-top: 8px;
}

.card-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-secondary);
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
