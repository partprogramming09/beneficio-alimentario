<template>
  <div class="tab-content">
    <div v-if="students.length === 0" class="empty-state">
      <p>✨ No se han registrado nuevos estudiantes el día de hoy.</p>
    </div>

    <div v-else class="table-container">
      <table>
        <thead>
          <tr>
            <th>Documento</th>
            <th>Nombre Completo</th>
            <th>Grupo</th>
            <th>Fecha Registro</th>
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

    <AlertBox :message="message" :isError="isError" />
  </div>
</template>

<script>
import AlertBox from './AlertBox.vue'

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
</style>

