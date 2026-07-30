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
  border: 2px dashed var(--primary);
  background-color: var(--bg-secondary);
  border-radius: var(--border-radius-md);
  padding: 20px;
  max-width: 400px;
  margin: 20px auto 0 auto;
  box-shadow: var(--shadow-md);
  text-align: left;
  border-color: var(--primary);
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
  color: var(--primary);
  font-size: 1.1rem;
  font-weight: 700;
}

.receipt-body p {
  margin: 8px 0;
  font-size: 0.95rem;
  color: var(--text-primary);
}

.receipt-code {
  background-color: var(--bg-tertiary);
  padding: 10px;
  border-radius: var(--border-radius-sm);
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
  color: var(--text-secondary);
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
  background-color: var(--success-light);
  color: var(--success);
}

.badge-warning {
  background-color: var(--warning-light);
  color: var(--warning);
}
</style>
