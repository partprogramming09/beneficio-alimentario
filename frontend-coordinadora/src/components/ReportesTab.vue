<template>
  <div class="tab-content">
    <h3>Reportes de Asistencia</h3>
    
    <div class="report-selection mb-4">
      <button 
        :class="['btn', reportType === 'diario' ? 'btn-primary' : 'btn-secondary']"
        @click="reportType = 'diario'"
      >
        Asistencia Diaria
      </button>
      <button 
        :class="['btn', reportType === 'semanal' ? 'btn-primary' : 'btn-secondary', 'ml-2']"
        @click="reportType = 'semanal'"
      >
        Resumen Semanal
      </button>
    </div>

    <!-- Reporte Diario -->
    <div v-if="reportType === 'diario'">
      <div class="filter-bar mb-3">
        <label for="rep-fecha">Seleccionar Fecha: </label>
        <input id="rep-fecha" type="date" v-model="dailyDate" @change="loadDailyReport" class="ml-2" />
      </div>

      <div v-if="dailyReport.length === 0" class="empty-state">
        <p>No se registraron asistencias para la fecha: <strong>{{ dailyDate }}</strong>.</p>
      </div>

      <div v-else class="table-container">
        <table>
          <thead>
            <tr>
              <th>Documento</th>
              <th>Estudiante</th>
              <th>Grupo</th>
              <th>Hora Registro</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in dailyReport" :key="row.id">
              <td><strong>{{ row.documento }}</strong></td>
              <td>{{ row.nombres }} {{ row.apellidos }}</td>
              <td><span class="badge-group">{{ row.grupo }}</span></td>
              <td>{{ row.hora }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Reporte Semanal -->
    <div v-if="reportType === 'semanal'">
      <p class="description">Muestra el número total de almuerzos recibidos por estudiante en los últimos 7 días de servicio.</p>
      
      <div v-if="weeklyReport.report.length === 0" class="empty-state">
        <p>No hay datos de asistencia acumulada para esta semana.</p>
      </div>

      <div v-else class="table-container">
        <table>
          <thead>
            <tr>
              <th>Documento</th>
              <th>Estudiante</th>
              <th>Grupo</th>
              <th>Almuerzos Recibidos</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in weeklyReport.report" :key="row.documento">
              <td><strong>{{ row.documento }}</strong></td>
              <td>{{ row.nombres }} {{ row.apellidos }}</td>
              <td><span class="badge-group">{{ row.grupo }}</span></td>
              <td>
                <span class="badge-count-success">{{ row.total_asistencias }} / {{ weeklyReport.dateList.length }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <AlertBox :message="message" :isError="isError" class="mt-3" />
  </div>
</template>

<script>
import { getAdminDailyReport, getAdminWeeklyReport } from '../services/api'
import AlertBox from './AlertBox.vue'

export default {
  name: 'ReportesTab',
  components: {
    AlertBox
  },
  data() {
    return {
      reportType: 'diario',
      dailyDate: new Date().toISOString().split('T')[0],
      dailyReport: [],
      weeklyReport: { dateList: [], report: [] },
      message: '',
      isError: false
    }
  },
  mounted() {
    this.loadDailyReport()
    this.loadWeeklyReport()
  },
  methods: {
    clearMessages() {
      this.message = ''
      this.isError = false
    },
    async loadDailyReport() {
      try {
        const data = await getAdminDailyReport(this.dailyDate)
        this.dailyReport = data
      } catch (err) {
        console.error(err)
        this.message = err.message
        this.isError = true
      }
    },
    async loadWeeklyReport() {
      try {
        const data = await getAdminWeeklyReport()
        this.weeklyReport = data
      } catch (err) {
        console.error(err)
        this.message = err.message
        this.isError = true
      }
    }
  }
}
</script>

<style scoped>
.badge-count-success {
  background-color: var(--success-light);
  color: var(--success);
  padding: 4px 8px;
  border-radius: 12px;
  font-weight: bold;
  font-size: 0.85rem;
}

.filter-bar {
  display: flex;
  align-items: center;
  gap: 10px;
}

.ml-2 {
  margin-left: 0.5rem;
}

.report-selection {
  display: flex;
  gap: 10px;
}
</style>
