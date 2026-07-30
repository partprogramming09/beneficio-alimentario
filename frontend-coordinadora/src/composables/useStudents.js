import { ref, computed } from 'vue';
import { getAdminStudents } from '../services/api';

export function useStudents() {
  const allStudents = ref([]);
  const loading = ref(false);
  const error = ref('');

  const pendingStudents = computed(() => {
    const hoyStr = new Date().toISOString().split('T')[0];
    return allStudents.value.filter(s => s.creado_en && s.creado_en.startsWith(hoyStr));
  });

  const suspendedStudents = computed(() =>
    allStudents.value.filter(s => s.estado === 'Suspendido')
  );

  const activeStudentsForSim = computed(() =>
    allStudents.value.filter(s => s.estado === 'Activo' || s.estado === 'Suspendido')
  );

  async function loadStudents() {
    loading.value = true;
    error.value = '';
    try {
      allStudents.value = await getAdminStudents();
    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  }

  function updateStudentState(updatedStudent) {
    const index = allStudents.value.findIndex(s => s.documento === updatedStudent.documento);
    if (index !== -1) {
      allStudents.value.splice(index, 1, updatedStudent);
    }
  }

  function removeStudentState(doc) {
    allStudents.value = allStudents.value.filter(s => s.documento !== doc);
  }

  return {
    allStudents,
    loading,
    error,
    pendingStudents,
    suspendedStudents,
    activeStudentsForSim,
    loadStudents,
    updateStudentState,
    removeStudentState,
  };
}
