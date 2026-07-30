<template>
  <div class="file-upload-wrapper">
    <label v-if="label" class="file-upload-label">{{ label }}</label>
    <div
      :class="['file-upload-zone', { 'is-dragging': isDragging, 'has-file': fileName }]"
      @drop.prevent="onDrop"
      @dragover="onDragOver"
      @dragleave="onDragLeave"
      @click="triggerFileInput"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        class="file-input-hidden"
        @change="onFileSelect"
      />
      <div v-if="!fileName" class="file-upload-placeholder">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="17 8 12 3 7 8"></polyline>
          <line x1="12" y1="3" x2="12" y2="15"></line>
        </svg>
        <span>Arrastra un archivo o <strong>selecciona</strong></span>
        <span class="file-upload-hint">PDF o Word (máx. 5 MB)</span>
      </div>
      <div v-else class="file-upload-selected">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
        </svg>
        <span class="file-name">{{ fileName }}</span>
        <button class="btn-remove-file" @click.stop="removeFile" title="Eliminar archivo">&times;</button>
      </div>
    </div>
    <span v-if="error" class="file-upload-error">{{ error }}</span>
  </div>
</template>

<script>
export default {
  name: 'FileUpload',
  props: {
    modelValue: { type: File, default: null },
    label: { type: String, default: '' },
    accept: { type: String, default: '.pdf,.doc,.docx' },
  },
  emits: ['update:modelValue'],
  data() {
    return {
      file: this.modelValue,
      fileName: this.modelValue?.name || '',
      error: '',
      isDragging: false,
    };
  },
  watch: {
    modelValue(val) {
      this.file = val;
      this.fileName = val?.name || '';
    },
  },
  methods: {
    validateFile(selected) {
      if (!selected) return false;
      if (selected.size > 5 * 1024 * 1024) {
        this.error = 'El archivo no debe superar los 5 MB.';
        return false;
      }
      const allowed = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
      const ext = '.' + selected.name.split('.').pop().toLowerCase();
      if (!allowed.includes(selected.type) && !['.pdf', '.doc', '.docx'].includes(ext)) {
        this.error = 'El archivo debe ser un PDF o Word (.doc, .docx).';
        return false;
      }
      this.error = '';
      return true;
    },
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    onFileSelect(event) {
      const selected = event.target.files?.[0];
      if (selected && this.validateFile(selected)) {
        this.file = selected;
        this.fileName = selected.name;
        this.$emit('update:modelValue', selected);
      }
    },
    onDrop(event) {
      this.isDragging = false;
      const selected = event.dataTransfer?.files?.[0];
      if (selected && this.validateFile(selected)) {
        this.file = selected;
        this.fileName = selected.name;
        this.$emit('update:modelValue', selected);
      }
    },
    onDragOver(event) {
      event.preventDefault();
      this.isDragging = true;
    },
    onDragLeave() {
      this.isDragging = false;
    },
    removeFile() {
      this.file = null;
      this.fileName = '';
      this.error = '';
      this.$refs.fileInput.value = '';
      this.$emit('update:modelValue', null);
    },
  },
};
</script>

<style scoped>
.file-upload-wrapper {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.file-upload-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-primary);
}

.file-upload-zone {
  border: 2px dashed var(--border-color);
  border-radius: var(--border-radius-sm);
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: all var(--transition-fast);
  background: var(--bg-tertiary);
}

.file-upload-zone:hover,
.file-upload-zone.is-dragging {
  border-color: var(--primary);
  background: var(--primary-light);
}

.file-upload-zone.has-file {
  border-color: var(--success);
  background: var(--success-light);
}

.file-input-hidden {
  display: none;
}

.file-upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  color: var(--text-secondary);
  font-size: 0.88rem;
}

.file-upload-placeholder svg {
  color: var(--text-muted);
}

.file-upload-hint {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.file-upload-selected {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: var(--success);
  font-weight: 600;
  font-size: 0.88rem;
}

.file-name {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.btn-remove-file {
  background: none;
  border: none;
  color: var(--danger);
  font-size: 1.3rem;
  cursor: pointer;
  padding: 0 4px;
  line-height: 1;
  font-weight: 700;
}

.file-upload-error {
  font-size: 0.78rem;
  color: var(--danger);
  font-weight: 500;
}
</style>
