import { apiClient } from '@shared/core';

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

export async function submitJustification(justificationData, archivo = null) {
  const formData = new FormData();
  formData.append('documento', justificationData.documento);
  formData.append('fecha_inasistencia', justificationData.fecha_inasistencia);
  formData.append('motivo', justificationData.motivo);

  if (archivo) {
    formData.append('archivo', archivo);
  }

  const response = await apiClient.post('/api/justificaciones', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return response.data;
}

export async function confirmRenounce(documento) {
  const response = await apiClient.post('/api/estudiantes/renunciar', { documento });
  return response.data;
}
