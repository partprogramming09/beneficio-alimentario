<template>
  <div class="tab-content">
    <div v-if="students.length === 0" class="empty-state">
      <p>No se han registrado nuevos estudiantes el día de hoy.</p>
    </div>

    <div v-else>
      <!-- Vista de DataCards para Móviles (<768px) -->
      <div class="data-cards-grid mobile-only">
        <div 
          v-for="student in students" 
          :key="'card-' + student.documento" 
          class="data-card-item"
          @click="$emit('select-student', student)"
        >
          <div class="data-card-header">
            <span class="card-doc"><strong>Doc: {{ student.documento }}</strong></span>
            <span class="badge-status-active">Activo</span>
          </div>
          <div class="data-card-body">
            <div class="card-name">{{ student.nombres }} {{ student.apellidos }}</div>
            <div class="card-meta">
              <span class="badge-group">Grupo: {{ student.grupo }}</span>
            </div>
            <div class="card-date text-muted">Ingresó: {{ student.creado_en }}</div>
          </div>
        </div>
      </div>

      <!-- Vista de Tabla para Escritorios (≥768px) -->
      <div class="table-container desktop-only">
        <table>
          <thead>
            <tr>
              <th>Documento</th>
              <th>Nombre</th>
              <th>Grupo</th>
              <th>Fecha</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in students" :key="student.documento" @click="$emit('select-student', student)" class="clickable-row">
              <td><strong>{{ student.documento }}</strong></td>
              <td>{{ student.nombres }} {{ student.apellidos }}</td>
              <td><span class="badge-group">{{ student.grupo }}</span></td>
              <td>{{ student.creado_en }}</td>
              <td>
                <span class="badge-status-active">Activo</span>
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
import AlertBox from '../common/AlertBox.vue'

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
      message: '',
      isError: false
    }
  }
}
</script>

<style scoped>
.clickable-row {
  cursor: pointer;
  transition: background-color var(--transition-fast);
}

.clickable-row:hover td {
  background-color: var(--primary-light);
}

.badge-status-active {
  background-color: var(--success-light);
  color: var(--success);
  padding: 4px 10px;
  border-radius: var(--border-radius-pill);
  font-size: 0.8rem;
  font-weight: 700;
  display: inline-block;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-muted);
  font-size: 1rem;
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

.card-meta, .card-date {
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .mobile-only {
    display: grid;
  }
  .desktop-only {
    display: none;
  }
}
</style>


