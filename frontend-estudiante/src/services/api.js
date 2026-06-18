import { apiClient } from '@shared/core';

// Student Portal Endpoints

export async function validateStudent(documento) {
  const response = await apiClient.post('/api/estudiantes/validar', { documento });
  return response.data;
}

export async function registerProfile(profileData) {
  const response = await apiClient.post('/api/estudiantes/registro', profileData);
  return response.data;
}

export async function markAttendance(documento) {
  const response = await apiClient.post('/api/asistencia', { documento });
  return response.data;
}

export async function getReceipt(documento) {
  const response = await apiClient.get(`/api/comprobante/${documento}`);
  return response.data;
}

export async function submitJustification(justificationData) {
  const response = await apiClient.post('/api/justificaciones', justificationData);
  return response.data;
}

export async function confirmRenounce(documento) {
  const response = await apiClient.post('/api/estudiantes/renunciar', { documento });
  return response.data;
}
