/**
 * Formatea una fecha ISO en formato legible local
 * @param {string|Date} dateVal - Valor de fecha
 * @returns {string} Fecha formateada en español
 */
export function formatDate(dateVal) {
  if (!dateVal) return '';
  const dateObj = new Date(dateVal);
  if (isNaN(dateObj.getTime())) return '';
  return dateObj.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

/**
 * Valida si un documento de identidad (cédula) es numérico y cumple con la longitud mínima
 * @param {string|number} idVal - Valor del ID
 * @returns {boolean}
 */
export function validateId(idVal) {
  if (!idVal) return false;
  const cleaned = String(idVal).trim();
  // Validar que solo contenga números y tenga entre 6 y 10 dígitos
  return /^\d{6,10}$/.test(cleaned);
}

/**
 * Formatea la hora en formato local 12 horas (AM/PM)
 * @param {string} dateVal - Valor de fecha o fecha/hora
 * @returns {string} Hora formateada
 */
export function formatTime(dateVal) {
  if (!dateVal) return '';
  const dateObj = new Date(dateVal);
  if (isNaN(dateObj.getTime())) return '';
  return dateObj.toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  });
}
