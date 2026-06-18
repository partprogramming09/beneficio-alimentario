<template>
  <div class="receipt-card mt-4">
    <div class="receipt-header">
      <h4>COMPROBANTE DE ASISTENCIA</h4>
      <span :class="['badge', badgeClass]">{{ badgeText }}</span>
    </div>
    <div class="receipt-body">
      <p><strong>Estudiante:</strong> {{ receipt.nombre }}</p>
      <p><strong>Grupo:</strong> {{ receipt.grupo }}</p>
      <p><strong>Fecha:</strong> {{ receipt.fecha }}</p>
      <p><strong>Hora:</strong> {{ receipt.hora }}</p>
      <p class="receipt-code"><strong>Código:</strong> <span>{{ receipt.codigo }}</span></p>
    </div>
    <div class="receipt-footer">
      <p>Presenta este comprobante en la fila del comedor escolar.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReceiptCard',
  props: {
    receipt: {
      type: Object,
      required: true
    },
    badgeText: {
      type: String,
      default: 'REGISTRADO'
    }
  },
  computed: {
    badgeClass() {
      return this.badgeText === 'REGISTRADO' || this.badgeText === 'ACTUAL' ? 'badge-success' : 'badge-warning'
    }
  }
}
</script>

<style scoped>
.receipt-card {
  border: 2px dashed var(--primary-color);
  background-color: var(--card-bg-secondary, #fafafa);
  border-radius: 12px;
  padding: 20px;
  max-width: 400px;
  margin: 20px auto 0 auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  text-align: left;
}

.receipt-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid var(--border-color);
  padding-bottom: 10px;
  margin-bottom: 15px;
}

.receipt-header h4 {
  margin: 0;
  color: var(--primary-color);
  font-size: 1.1rem;
  font-weight: 700;
}

.receipt-body p {
  margin: 8px 0;
  font-size: 0.95rem;
  color: var(--text-color);
}

.receipt-code {
  background-color: var(--bg-color);
  padding: 10px;
  border-radius: 6px;
  text-align: center;
  border: 1px solid var(--border-color);
  margin-top: 15px !important;
}

.receipt-code span {
  font-family: monospace;
  font-size: 1.2rem;
  font-weight: bold;
  letter-spacing: 1px;
}

.receipt-footer {
  border-top: 1px solid var(--border-color);
  padding-top: 10px;
  margin-top: 15px;
  text-align: center;
  font-size: 0.85rem;
  color: var(--text-muted);
  font-style: italic;
}

.badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: bold;
  text-transform: uppercase;
}

.badge-success {
  background-color: rgba(46, 204, 113, 0.15);
  color: #2e7d32;
}

.badge-warning {
  background-color: rgba(243, 156, 18, 0.15);
  color: #d84315;
}

/* Dark Theme Overrides for Receipt (HU04 Ticket) */
.dark-theme .receipt-card {
  background-color: #0b0f19;
  border-color: #38bdf8;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.dark-theme .receipt-header {
  border-bottom-color: #334155;
}

.dark-theme .receipt-header h4 {
  color: #38bdf8;
}

.dark-theme .receipt-body p {
  color: #ffffff;
}

.dark-theme .receipt-code {
  background-color: #1e293b;
  border-color: #475569;
}

.dark-theme .receipt-code span {
  color: #38bdf8;
}

.dark-theme .receipt-footer {
  border-top-color: #334155;
  color: #94a3b8;
}

.dark-theme .badge-success {
  background-color: rgba(16, 185, 129, 0.25);
  color: #34d399;
}

.dark-theme .badge-warning {
  background-color: rgba(245, 158, 11, 0.25);
  color: #fbbf24;
}
</style>
