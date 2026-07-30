<template>
  <div class="tab-content">
    <div class="report-header-nav mb-4">
      <div class="report-pill-selector">
        <button 
          :class="['report-pill-btn', { active: reportType === 'diario' }]"
          @click="reportType = 'diario'"
        >
          <svg class="tab-line-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
          <span>Asistencia Diaria</span>
        </button>
        <button 
          :class="['report-pill-btn', { active: reportType === 'semanal' }]"
          @click="reportType = 'semanal'"
        >
          <svg class="tab-line-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="20" x2="18" y2="10"></line>
            <line x1="12" y1="20" x2="12" y2="4"></line>
            <line x1="6" y1="20" x2="6" y2="14"></line>
          </svg>
          <span>Resumen Semanal</span>
        </button>
      </div>

      <button class="btn btn-secondary btn-sm export-btn" @click="exportCSV" v-if="hasData">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        Exportar CSV
      </button>
    </div>

    <!-- Stats bar -->
    <div v-if="hasData" class="stats-bar mb-3">
      <div class="stat-pill">
        <span class="stat-label">Total:</span>
        <span class="stat-value">{{ currentData.length }}</span>
      </div>
      <div v-if="reportType === 'semanal' && weeklyReport.dateList.length" class="stat-pill stat-success">
        <span class="stat-label">Promedio asistencia:</span>
        <span class="stat-value">{{ averageAttendance }}</span>
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

      <div v-else>
        <div class="data-cards-grid mobile-only">
          <div v-for="row in dailyReport" :key="'rep-d-' + row.id" class="data-card-item clickable-row" @click="$emit('select-student', row)">
            <div class="data-card-header">
              <span class="card-doc"><strong>{{ row.documento }}</strong></span>
              <span class="badge-group">{{ row.grupo }}</span>
            </div>
            <div class="data-card-body">
              <div class="card-name">{{ row.nombres }} {{ row.apellidos }}</div>
              <div class="card-time text-muted">Hora: {{ row.hora }}</div>
            </div>
          </div>
        </div>

        <div class="table-container desktop-only">
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
              <tr v-for="row in dailyReport" :key="row.id" class="clickable-row" @click="$emit('select-student', row)">
                <td><strong>{{ row.documento }}</strong></td>
                <td>{{ row.nombres }} {{ row.apellidos }}</td>
                <td><span class="badge-group">{{ row.grupo }}</span></td>
                <td>{{ row.hora }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Reporte Semanal -->
    <div v-if="reportType === 'semanal'">
      <div v-if="weeklyReport.report.length === 0" class="empty-state">
        <p>No hay datos de asistencia acumulada para esta semana.</p>
      </div>

      <div v-else>
        <div class="data-cards-grid mobile-only">
          <div v-for="row in weeklyReport.report" :key="'rep-w-' + row.documento" class="data-card-item clickable-row" @click="$emit('select-student', row)">
            <div class="data-card-header">
              <span class="card-doc"><strong>{{ row.documento }}</strong></span>
              <span class="badge-count-success">{{ row.total_asistencias }} / {{ weeklyReport.dateList.length }}</span>
            </div>
            <div class="data-card-body">
              <div class="card-name">{{ row.nombres }} {{ row.apellidos }}</div>
              <div class="card-meta">Grupo: {{ row.grupo }}</div>
            </div>
          </div>
        </div>

        <div class="table-container desktop-only">
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
              <tr v-for="row in weeklyReport.report" :key="row.documento" class="clickable-row" @click="$emit('select-student', row)">
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
    </div>

    <AlertBox :message="message" :isError="isError" class="mt-3" />
  </div>
</template>

<script>
import { getAdminDailyReport, getAdminWeeklyReport } from '../../services/api'
import AlertBox from '../common/AlertBox.vue'

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
  computed: {
    hasData() {
      if (this.reportType === 'diario') return this.dailyReport.length > 0
      return this.weeklyReport.report.length > 0
    },
    currentData() {
      if (this.reportType === 'diario') return this.dailyReport
      return this.weeklyReport.report
    },
    averageAttendance() {
      if (!this.weeklyReport.report.length || !this.weeklyReport.dateList.length) return '0%'
      const total = this.weeklyReport.report.reduce((sum, r) => sum + r.total_asistencias, 0)
      const max = this.weeklyReport.report.length * this.weeklyReport.dateList.length
      return Math.round((total / max) * 100) + '%'
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
    },
    exportCSV() {
      const data = this.currentData
      if (!data.length) return

      let headers, rows
      if (this.reportType === 'diario') {
        headers = ['Documento', 'Nombres', 'Apellidos', 'Grupo', 'Hora']
        rows = data.map(r => [r.documento, r.nombres, r.apellidos, r.grupo, r.hora])
      } else {
        headers = ['Documento', 'Nombres', 'Apellidos', 'Grupo', 'Asistencias', 'DiasTotales']
        rows = data.map(r => [r.documento, r.nombres, r.apellidos, r.grupo, r.total_asistencias, this.weeklyReport.dateList.length])
      }

      const csv = [headers, ...rows].map(row => row.map(c => `"${c}"`).join(',')).join('\n')
      const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `reporte_${this.reportType}_${this.dailyDate}.csv`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      URL.revokeObjectURL(url)
    }
  }
}
</script>

<style scoped>
.report-header-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.report-pill-selector {
  display: inline-flex;
  background: var(--bg-tertiary);
  padding: 4px;
  border-radius: var(--border-radius-pill);
  border: 1px solid var(--border-color);
  gap: 4px;
  flex-wrap: wrap;
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

.export-btn {
  display: flex;
  align-items: center;
  gap: 6px;
}

.stats-bar {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.stat-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  padding: 6px 14px;
  border-radius: var(--border-radius-pill);
  font-size: 0.85rem;
}

.stat-pill.stat-success {
  background: var(--success-light);
  border-color: var(--success);
  color: var(--success);
}

.stat-label {
  font-weight: 600;
}

.stat-value {
  font-weight: 800;
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
  flex-wrap: wrap;
}

.mobile-only {
  display: none;
}

.desktop-only {
  display: block;
}

.clickable-row {
  cursor: pointer;
  transition: background-color 0.15s;
}

.clickable-row:hover {
  background-color: var(--bg-tertiary);
}

.card-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-primary);
}

.card-time, .card-meta {
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
