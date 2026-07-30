import axios from 'axios';

let apiURL = import.meta.env?.VITE_API_URL;

if (!apiURL) {
  if (typeof window !== 'undefined' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
    apiURL = 'https://beneficio-alimentario.onrender.com';
  } else {
    apiURL = 'http://localhost:8000';
  }
}

if (typeof window !== 'undefined' && window.location.hostname.endsWith('use.devtunnels.ms')) {
  const hostname = window.location.hostname;
  const apiHostname = hostname.replace(/-(5173|5174)/, '-8000');
  apiURL = `https://${apiHostname}`;
}

const FRIENDLY_MESSAGES = {
  400: 'Datos no válidos. Verifica la información e intenta de nuevo.',
  401: 'Sesión expirada. Recarga la página e inicia sesión nuevamente.',
  403: 'No tienes permiso para realizar esta acción.',
  404: 'El recurso solicitado no fue encontrado.',
  405: 'Método no permitido.',
  408: 'La solicitud tardó demasiado. Verifica tu conexión e intenta de nuevo.',
  419: 'Sesión expirada. Recarga la página.',
  422: 'Los datos enviados no son válidos.',
  429: 'Demasiadas solicitudes. Espera un momento e intenta de nuevo.',
  500: 'Error del servidor. Intenta de nuevo en unos momentos.',
  502: 'El servidor no está disponible temporalmente. Intenta de nuevo.',
  503: 'Servicio no disponible. Intenta de nuevo en unos momentos.',
  504: 'El servidor tardó demasiado en responder.',
};

const apiClient = axios.create({
  baseURL: apiURL,
  withCredentials: false,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response ? error.response.status : null;
    const data = error.response?.data;

    let friendlyMessage = '';

    if (data) {
      friendlyMessage = data.error || data.message || '';
    }

    if (!friendlyMessage && status) {
      friendlyMessage = FRIENDLY_MESSAGES[status] || 'Ocurrió un error inesperado. Intenta de nuevo.';
    }

    if (!friendlyMessage) {
      if (!error.response) {
        friendlyMessage = `No se pudo conectar con el servidor backend (${apiURL}). Asegúrate de que el servicio esté en ejecución e intenta de nuevo.`;
      } else {
        friendlyMessage = 'Ocurrió un error inesperado. Intenta de nuevo.';
      }
    }

    const customError = new Error(friendlyMessage);
    customError.status = status;
    customError.data = data;

    console.error(`[API Error] Status: ${status} | Message: ${friendlyMessage}`);

    return Promise.reject(customError);
  }
);

export default apiClient;
