import { ref } from 'vue';

const ALLOWED_TYPES = [
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
const ALLOWED_EXTENSIONS = ['.pdf', '.doc', '.docx'];
const MAX_SIZE_MB = 5;

export function useFileUpload() {
  const file = ref(null);
  const fileName = ref('');
  const error = ref('');
  const isDragging = ref(false);

  function validateFile(selected) {
    if (!selected) return false;

    if (selected.size > MAX_SIZE_MB * 1024 * 1024) {
      error.value = `El archivo no debe superar los ${MAX_SIZE_MB} MB.`;
      return false;
    }

    if (!ALLOWED_TYPES.includes(selected.type)) {
      const ext = '.' + selected.name.split('.').pop().toLowerCase();
      if (!ALLOWED_EXTENSIONS.includes(ext)) {
        error.value = 'El archivo debe ser un PDF o Word (.doc, .docx).';
        return false;
      }
    }

    error.value = '';
    return true;
  }

  function onFileSelect(event) {
    const selected = event.target.files?.[0];
    if (selected && validateFile(selected)) {
      file.value = selected;
      fileName.value = selected.name;
    }
  }

  function onDrop(event) {
    isDragging.value = false;
    const selected = event.dataTransfer?.files?.[0];
    if (selected && validateFile(selected)) {
      file.value = selected;
      fileName.value = selected.name;
    }
  }

  function onDragOver(event) {
    event.preventDefault();
    isDragging.value = true;
  }

  function onDragLeave() {
    isDragging.value = false;
  }

  function clearFile() {
    file.value = null;
    fileName.value = '';
    error.value = '';
  }

  return {
    file,
    fileName,
    error,
    isDragging,
    onFileSelect,
    onDrop,
    onDragOver,
    onDragLeave,
    clearFile,
  };
}
