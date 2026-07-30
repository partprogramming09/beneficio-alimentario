import { ref } from 'vue';

export function useSession() {
  const studentDoc = ref(localStorage.getItem('studentDoc') || null);
  const studentName = ref(localStorage.getItem('studentName') || null);

  function startSession(session) {
    studentDoc.value = session.documento;
    studentName.value = session.nombre;
    localStorage.setItem('studentDoc', session.documento);
    localStorage.setItem('studentName', session.nombre);
  }

  function logout() {
    studentDoc.value = null;
    studentName.value = null;
    localStorage.removeItem('studentDoc');
    localStorage.removeItem('studentName');
  }

  return { studentDoc, studentName, startSession, logout };
}
