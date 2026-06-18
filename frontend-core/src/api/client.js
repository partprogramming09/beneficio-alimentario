import axios from 'axios';

// Instancia global de Axios configurada para consumir la API de Laravel
const apiClient = axios.create({
  baseURL: import.meta.env?.VITE_API_URL || 'http://localhost:8000',
  withCredentials: true, // Requerido para Laravel Sanctum cookies de sesión
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Interceptor de respuesta para manejar errores globales de API
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response ? error.response.status : null;
    const message = error.response?.data?.message || error.message;

    // Reporte de error en consola
    console.error(`[API Error] Status: ${status} | Message: ${message}`, error);

    if (status === 401) {
      // Manejar desautenticación global (opcional: limpiar sesión o redirigir)
      console.warn('Sesión no autorizada o expirada.');
    } else if (status === 419) {
      // CSRF token mismatch en Laravel
      console.warn('Falla de validación de token CSRF. Reintentando login.');
    }

    return Promise.reject(error);
  }
);

export default apiClient;
