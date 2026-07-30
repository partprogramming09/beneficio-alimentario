import { apiClient } from '@shared/core';

export async function getAdminStudents() {
  const response = await apiClient.get('/api/admin/estudiantes');
  return response.data;
}

export async function getAdminJustifications() {
  const response = await apiClient.get('/api/admin/justificaciones');
  return response.data;
}

export async function downloadJustificationFile(id) {
  const response = await apiClient.get(`/api/admin/justificaciones/${id}/descarga`, {
    responseType: 'blob',
  });
  return response;
}

export async function approveJustification(id) {
  const response = await apiClient.post(`/api/admin/justificaciones/${id}/aprobar`);
  return response.data;
}

export async function rejectJustification(id) {
  const response = await apiClient.post(`/api/admin/justificaciones/${id}/rechazar`);
  return response.data;
}

export async function getAdminDailyReport(fecha) {
  const response = await apiClient.get(`/api/admin/asistencia/diaria?fecha=${fecha}`);
  return response.data;
}

export async function getAdminWeeklyReport() {
  const response = await apiClient.get('/api/admin/asistencia/semanal');
  return response.data;
}

export async function approveStudent(documento) {
  const response = await apiClient.post('/api/admin/estudiantes/aprobar', { documento });
  return response.data;
}

export async function rejectStudent(documento) {
  const response = await apiClient.post('/api/admin/estudiantes/rechazar', { documento });
  return response.data;
}

export async function deleteStudent(documento) {
  const response = await apiClient.post('/api/admin/estudiantes/eliminar', { documento });
  return response.data;
}

export async function reactivateStudent(documento) {
  const response = await apiClient.post('/api/admin/estudiantes/reingresar', { documento });
  return response.data;
}

export async function runSimulation(simData) {
  const response = await apiClient.post('/api/admin/simular-dia', simData);
  return response.data;
}

export async function markAttendance(documento) {
  const response = await apiClient.post('/api/asistencia', { documento });
  return response.data;
}

export async function createSingleStudent(data) {
  const response = await apiClient.post('/api/admin/estudiantes/crear-individual', data);
  return response.data;
}

export async function importBulkStudents(estudiantes) {
  const response = await apiClient.post('/api/admin/estudiantes/importar-masivo', { estudiantes });
  return response.data;
}

export async function getAdminGroups() {
  const response = await apiClient.get('/api/admin/grupos');
  return response.data;
}

export async function activateStudentManually(documento) {
  const response = await apiClient.post('/api/admin/estudiantes/activar-manual', { documento });
  return response.data;
}

export async function updateStudent(data) {
  const response = await apiClient.post('/api/admin/estudiantes/actualizar', data);
  return response.data;
}

export async function deleteInstitutionalStudent(documento) {
  const response = await apiClient.post('/api/admin/estudiantes/eliminar-institucional', { documento });
  return response.data;
}

export async function toggleCupo(documento) {
  const response = await apiClient.post('/api/admin/estudiantes/toggle-cupo', { documento });
  return response.data;
}

export async function cambiarEstadoBeneficio(documento, estado) {
  const response = await apiClient.post('/api/admin/estudiantes/cambiar-estado', { documento, estado });
  return response.data;
}
