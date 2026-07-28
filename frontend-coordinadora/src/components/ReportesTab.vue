<template>
  <div class="tab-content">
    <div class="report-header-nav mb-4">
      <div class="report-pill-selector">
        <button 
          :class="['report-pill-btn', { active: reportType === 'diario' }]"
          @click="reportType = 'diario'"
        >
          📅 Asistencia Diaria
        </button>
        <button 
          :class="['report-pill-btn', { active: reportType === 'semanal' }]"
          @click="reportType = 'semanal'"
        >
          📊 Resumen Semanal
        </button>
      </div>
    </div>

    <!-- Reporte Diario -->
    <div v-if="reportType === 'diario'">
      <div class="filter-bar mb-3">
        <label for="rep-fecha">Seleccionar Fecha: </label>
        <input id="rep-fecha" type="date" v-model="dailyDate" @change="loadDailyReport" class="date-input ml-2" />
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
.report-header-nav {
  display: flex;
}

.report-pill-selector {
  display: inline-flex;
  background: var(--bg-tertiary);
  padding: 4px;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
  gap: 4px;
}

.report-pill-btn {
  padding: 8px 18px;
  font-size: 0.88rem;
  font-weight: 600;
  border-radius: var(--border-radius-pill);
  background: transparent;
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.report-pill-btn.active {
  background-color: var(--bg-secondary);
  color: var(--primary);
  box-shadow: var(--shadow-sm);
}

.date-input {
  padding: 6px 12px;
  border-radius: var(--border-radius-sm);
  border: 1px solid var(--border-color);
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
}

.badge-count-success {
  background-color: var(--success-light);
  color: var(--success);
  padding: 4px 10px;
  border-radius: var(--border-radius-pill);
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
</style>

