/**
 * API Service Client
 * Centralizes all HTTP calls for the school dining hall benefit control system.
 */

async function apiFetch(url, options = {}) {
  try {
    const res = await fetch(url, options);
    const contentType = res.headers.get('content-type');
    
    let data = {};
    if (contentType && contentType.includes('application/json')) {
      data = await res.json();
    } else {
      const text = await res.text();
      throw new Error(`Respuesta no válida del servidor (${res.status}): ${text.substring(0, 80)}`);
    }
    
    if (!res.ok) {
      throw new Error(data.error || `Error del servidor (${res.status})`);
    }
    return data;
  } catch (err) {
    if (err.message.includes('Failed to fetch') || err.message.includes('fetch failed')) {
      throw new Error('No se pudo conectar con el servidor. Por favor, asegúrate de que el backend está corriendo (corre "npm run dev").');
    }
    throw err;
  }
}

// Student Portal Endpoints
export async function validateStudent(documento) {
  return apiFetch('/api/estudiantes/validar', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ documento })
  });
}

export async function registerProfile(profileData) {
  return apiFetch('/api/estudiantes/registro', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(profileData)
  });
}

export async function markAttendance(documento) {
  return apiFetch('/api/asistencia', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ documento })
  });
}

export async function getReceipt(documento) {
  return apiFetch(`/api/comprobante/${documento}`);
}

export async function submitJustification(justificationData) {
  return apiFetch('/api/justificaciones', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(justificationData)
  });
}

export async function confirmRenounce(documento) {
  return apiFetch('/api/estudiantes/renunciar', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ documento })
  });
}

// Coordinator Panel Endpoints
export async function getAdminStudents() {
  return apiFetch('/api/admin/estudiantes');
}

export async function getAdminJustifications() {
  return apiFetch('/api/admin/justificaciones');
}

export async function getAdminDailyReport(fecha) {
  return apiFetch(`/api/admin/asistencia/diaria?fecha=${fecha}`);
}

export async function getAdminWeeklyReport() {
  return apiFetch('/api/admin/asistencia/semanal');
}

export async function approveStudent(documento) {
  return apiFetch('/api/admin/estudiantes/aprobar', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ documento })
  });
}

export async function rejectStudent(documento) {
  return apiFetch('/api/admin/estudiantes/rechazar', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ documento })
  });
}

export async function deleteStudent(documento) {
  return apiFetch('/api/admin/estudiantes/eliminar', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ documento })
  });
}

export async function reactivateStudent(documento) {
  return apiFetch('/api/admin/estudiantes/reingresar', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ documento })
  });
}

export async function runSimulation(simData) {
  return apiFetch('/api/admin/simular-dia', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(simData)
  });
}
