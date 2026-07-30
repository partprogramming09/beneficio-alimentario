// Punto de entrada para exportaciones del core compartido
export { default as apiClient } from './api/client.js';
export { formatDate, validateId, formatTime } from './utils/helpers.js';
export { default as FileUpload } from './components/FileUpload.vue';
export { useFileUpload } from './composables/useFileUpload.js';
