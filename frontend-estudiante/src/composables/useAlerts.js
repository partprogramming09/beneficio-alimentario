import { ref } from 'vue';

export function useAlerts() {
  const message = ref('');
  const isError = ref(false);

  function showError(msg) {
    message.value = msg;
    isError.value = true;
  }

  function showSuccess(msg) {
    message.value = msg;
    isError.value = false;
  }

  function clearMessages() {
    message.value = '';
    isError.value = false;
  }

  return { message, isError, showError, showSuccess, clearMessages };
}
