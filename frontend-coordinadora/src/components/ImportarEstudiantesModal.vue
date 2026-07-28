<template>
  <div v-if="isOpen" class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-card wide-modal">
      <div class="modal-header">
        <h4>📁 Carga Masiva de Estudiantes (Excel / CSV)</h4>
        <button class="btn-close" @click="$emit('close')">&times;</button>
      </div>

      <div class="modal-body">
        <!-- Banner de Instrucciones y Advertencias -->
        <div class="instructions-box mb-3">
          <div class="instructions-header">
            <span class="info-icon">ℹ️</span>
            <strong>Instrucciones y Formato Aceptado:</strong>
          </div>
          <p class="small text-muted mb-2">
            Puedes cargar archivos <strong>.csv, .tsv o .txt</strong> exportados desde Excel. El archivo debe incluir las siguientes columnas o seguir este orden de posición:
          </p>
          <div class="columns-badge-row">
            <span class="col-badge">1: Documento</span>
            <span class="col-badge">2: Nombres</span>
            <span class="col-badge">3: Apellidos</span>
            <span class="col-badge">4: Grupo</span>
          </div>
          <div class="download-template-action mt-3">
            <button class="btn btn-secondary btn-xs" @click="downloadTemplate">
              📥 Descargar Plantilla de Ejemplo (.csv)
            </button>
          </div>
        </div>

        <!-- Zone Drop Input -->
        <div class="drop-zone" @dragover.prevent @drop.prevent="handleFileDrop">
          <input 
            type="file" 
            ref="fileInput" 
            accept=".csv, .tsv, .txt" 
            class="hidden-file-input" 
            @change="handleFileSelect"
          />
          <div class="drop-zone-content" @click="$refs.fileInput.click()">
            <span class="drop-icon">📄</span>
            <p v-if="!fileName">Arrastra y suelta aquí tu archivo de Excel (.csv) o haz clic para seleccionar</p>
            <p v-else class="file-name-highlight">✓ Archivo Seleccionado: <strong>{{ fileName }}</strong></p>
          </div>
        </div>

        <!-- Vista Previa de Registros Detectados -->
        <div v-if="parsedData.length > 0" class="preview-section mt-4">
          <div class="preview-header">
            <h5>🔍 Vista Previa: {{ parsedData.length }} Estudiantes Detectados</h5>
            <span class="valid-count">Registros Listos: {{ validCount }}</span>
          </div>

          <div class="table-container preview-table">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Documento</th>
                  <th>Nombres</th>
                  <th>Apellidos</th>
                  <th>Grupo</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(row, idx) in parsedData.slice(0, 100)" 
                  :key="idx"
                  :class="{ 'invalid-row': !row.isValid }"
                >
                  <td>{{ idx + 1 }}</td>
                  <td><strong>{{ row.documento || 'FALTANTE' }}</strong></td>
                  <td>{{ row.nombres }}</td>
                  <td>{{ row.apellidos }}</td>
                  <td><span class="badge-group">{{ row.grupo }}</span></td>
                  <td>
                    <span v-if="row.isValid" class="badge-status-active">Válido</span>
                    <span v-else class="badge-status-error">Error Documento</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-if="parsedData.length > 100" class="small text-muted text-center mt-2">
            Mostrando las primeras 100 filas de {{ parsedData.length }} totales...
          </p>
        </div>

        <AlertBox :message="message" :isError="isError" class="mt-3" />

        <div class="modal-footer mt-4">
          <button class="btn btn-secondary" @click="$emit('close')">Cancelar</button>
          <button 
            class="btn btn-primary" 
            @click="confirmImport" 
            :disabled="loading || validCount === 0"
          >
            {{ loading ? 'Importando...' : `⚡ Confirmar e Importar (${validCount} Alumnos)` }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { importBulkStudents } from '../services/api'
import AlertBox from './AlertBox.vue'

export default {
  name: 'ImportarEstudiantesModal',
  components: {
    AlertBox
  },
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      fileName: '',
      parsedData: [],
      loading: false,
      message: '',
      isError: false
    }
  },
  computed: {
    validCount() {
      return this.parsedData.filter(d => d.isValid).length
    }
  },
  methods: {
    clearMessages() {
      this.message = ''
      this.isError = false
    },
    downloadTemplate() {
      const csvContent = "data:text/csv;charset=utf-8,documento,nombres,apellidos,grupo\n1001001,Juan Esteban,Pérez Calle,10-1\n1001002,María José,Gómez Restrepo,10-2\n";
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "plantilla_estudiantes_ejemplo.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    handleFileSelect(e) {
      const file = e.target.files[0]
      if (file) this.processFile(file)
    },
    handleFileDrop(e) {
      const file = e.dataTransfer.files[0]
      if (file) this.processFile(file)
    },
    processFile(file) {
      this.fileName = file.name
      this.clearMessages()
      
      const reader = new FileReader()
      reader.onload = (evt) => {
        const text = evt.target.result
        this.parseCSV(text)
      }
      reader.readAsText(file)
    },
    parseCSV(text) {
      const lines = text.split(/\r\n|\n/).map(l => l.trim()).filter(l => l.length > 0)
      if (lines.length === 0) {
        this.message = 'El archivo se encuentra vacío.'
        this.isError = true
        return
      }

      // Separador por coma, punto y coma o tabulador
      const delimiter = lines[0].includes(';') ? ';' : (lines[0].includes('\t') ? '\t' : ',')
      
      const firstLine = lines[0].toLowerCase()
      let hasHeader = firstLine.includes('doc') || firstLine.includes('nom') || firstLine.includes('grupo')
      
      let headerIndices = { doc: 0, nombres: 1, apellidos: 2, grupo: 3 }
      
      let startIdx = 0
      if (hasHeader) {
        startIdx = 1
        const cols = lines[0].split(delimiter).map(c => c.trim().toLowerCase().replace(/"/g, ''))
        cols.forEach((col, idx) => {
          if (col.includes('ape') || col.includes('apellido')) {
            headerIndices.apellidos = idx
          } else if (col.includes('doc') || col.includes('cedula') || col.includes('identificac') || col === 'id') {
            headerIndices.doc = idx
          } else if (col.includes('nom') || col.includes('nombre')) {
            headerIndices.nombres = idx
          } else if (col.includes('grup') || col.includes('grad') || col.includes('curs')) {
            headerIndices.grupo = idx
          }
        })
      }


      const rows = []
      for (let i = startIdx; i < lines.length; i++) {
        const cols = lines[i].split(delimiter).map(c => c.trim().replace(/^"|"$/g, ''))
        if (cols.length === 0 || (cols.length === 1 && !cols[0])) continue

        const doc = cols[headerIndices.doc] || cols[0] || ''
        const nom = cols[headerIndices.nombres] || cols[1] || ''
        const ape = cols[headerIndices.apellidos] || cols[2] || ''
        const gru = cols[headerIndices.grupo] || cols[3] || 'Sin Grupo'

        const isValid = Boolean(doc && doc.length >= 3)
        rows.push({
          documento: doc,
          nombres: nom,
          apellidos: ape,
          grupo: gru,
          isValid
        })
      }

      this.parsedData = rows
      if (rows.length === 0) {
        this.message = 'No se detectaron registros válidos de estudiantes en el archivo.'
        this.isError = true
      }
    },
    async confirmImport() {
      const validRows = this.parsedData.filter(d => d.isValid)
      if (validRows.length === 0) return

      this.loading = true
      this.clearMessages()

      try {
        const res = await importBulkStudents(validRows)
        this.message = res.message
        this.isError = false
        this.$emit('refresh-students')
        setTimeout(() => {
          this.parsedData = []
          this.fileName = ''
          this.$emit('close')
        }, 1500)
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
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(4px);
  z-index: 120;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.wide-modal {
  max-width: 720px !important;
}

.instructions-box {
  background-color: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-sm);
  padding: 14px 18px;
}

.instructions-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
  font-size: 0.95rem;
}

.columns-badge-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.col-badge {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  padding: 4px 10px;
  border-radius: var(--border-radius-pill);
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--primary);
}

.drop-zone {
  border: 2px dashed var(--border-color);
  border-radius: var(--border-radius-md);
  padding: 30px 20px;
  text-align: center;
  background-color: var(--bg-tertiary);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.drop-zone:hover {
  border-color: var(--primary);
  background-color: var(--primary-light);
}

.hidden-file-input {
  display: none;
}

.drop-icon {
  font-size: 2.5rem;
  display: block;
  margin-bottom: 8px;
}

.file-name-highlight {
  color: var(--success);
  font-weight: 700;
}

.preview-table {
  max-height: 220px;
  overflow-y: auto;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.preview-header h5 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
}

.valid-count {
  font-size: 0.82rem;
  color: var(--success);
  font-weight: 700;
}

.invalid-row td {
  background-color: var(--danger-light) !important;
  color: var(--danger) !important;
}

.badge-status-error {
  background-color: var(--danger-light);
  color: var(--danger);
  padding: 2px 8px;
  border-radius: var(--border-radius-pill);
  font-size: 0.75rem;
  font-weight: 700;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
