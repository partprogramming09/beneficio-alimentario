/**
 * Módulo Helper para Normalización de Estados SDD
 * I.E. Enrique Vélez Escobar - Beneficio Alimentario
 */

export function getStatusClass(status) {
  if (!status) return 'badge-sin-registrar';
  const s = String(status).trim().toLowerCase();
  if (s === 'sin registrar' || s === 'sin_registrar' || s === 'unregistered') return 'badge-sin-registrar';
  if (s === 'activo' || s === 'active') return 'badge-activo';
  if (s === 'suspendido' || s === 'suspended') return 'badge-suspendido';
  if (s === 'inactivo' || s === 'inactive') return 'badge-inactivo';
  if (s === 'retirado' || s === 'withdrawn') return 'badge-retirado';
  if (s === 'pendiente' || s === 'pending') return 'badge-pendiente';
  return 'badge-' + s.replace(/\s+/g, '-');
}

export function getStatusLabel(status) {
  if (!status) return 'Sin Registrar';
  return status;
}
