<template>
  <div class="home-container">
    <!-- Hero / Welcome Banner -->
    <div class="card header-card landing-hero">
      <h2>Bienvenido al Portal del Comedor Escolar 👋</h2>
      <p class="hero-desc">
        Sistema oficial de control y gestión del beneficio alimentario para los estudiantes de la 
        <strong>Institución Educativa Enrique Vélez Escobar</strong>. Este prototipo interactivo 
        permite experimentar y validar las reglas del programa.
      </p>
    </div>

    <!-- How it works (Business Rules) -->
    <div class="card info-card">
      <h3>¿Cómo funciona el Programa de Alimentación? 🍽️</h3>
      <p class="mb-4">
        El programa tiene como objetivo garantizar la nutrición de nuestros estudiantes activos. 
        Para asegurar un uso eficiente del beneficio, el sistema aplica las siguientes reglas de negocio automatizadas:
      </p>
      
      <div class="rules-grid">
        <div class="rule-box">
          <div class="rule-icon">📝</div>
          <h4>Inscripción y Aprobación</h4>
          <p>
            Los estudiantes deben registrarse con su documento de identidad. Cada solicitud debe ser validada 
            y aprobada por la Coordinadora antes de poder acceder al comedor.
          </p>
        </div>
        
        <div class="rule-box">
          <div class="rule-icon">⏰</div>
          <h4>Asistencia Diaria</h4>
          <p>
            Los estudiantes aprobados marcan su asistencia una vez al día usando su cédula. El sistema genera 
            un ticket digital que sirve como comprobante del beneficio.
          </p>
        </div>

        <div class="rule-box">
          <div class="rule-icon">⚠️</div>
          <h4>Suspensión por Inasistencias</h4>
          <p>
            Para evitar el desperdicio de raciones, si un estudiante acumula <strong>3 inasistencias consecutivas</strong> 
            en días de servicio, su beneficio es <strong>suspendido automáticamente</strong> por el sistema.
          </p>
        </div>

        <div class="rule-box">
          <div class="rule-icon">📄</div>
          <h4>Justificación y Reactivación</h4>
          <p>
            Un estudiante suspendido puede enviar una justificación (excusa médica o de fuerza mayor). 
            La Coordinadora revisará la excusa y podrá reactivar su estado a "Activo" en un solo clic.
          </p>
        </div>
      </div>
    </div>

    <!-- Portals / Roles Grid -->
    <div class="grid-2 mt-4">
      <!-- Student Portal Card -->
      <div class="card role-card student-role">
        <div class="role-header">
          <span class="role-icon">🎓</span>
          <h3>Portal del Estudiante</h3>
        </div>
        <p class="role-desc">
          Accede a tu cuenta de estudiante para gestionar tu beneficio, registrar tus almuerzos diarios, 
          o justificar tus faltas.
        </p>
        <ul class="features-list">
          <li><strong>Crear Perfil:</strong> Regístrate con tus datos institucionales.</li>
          <li><strong>Marcar Asistencia:</strong> Registra tu almuerzo en segundos.</li>
          <li><strong>Recuperar Ticket:</strong> Vuelve a descargar tu comprobante del día.</li>
          <li><strong>Justificar Faltas:</strong> Envía excusas para evitar o corregir suspensiones.</li>
        </ul>
        <button class="btn btn-primary btn-block mt-4" @click="enterRole('estudiante')">
          Ingresar al Portal del Estudiante &rarr;
        </button>
      </div>

      <!-- Coordinator Panel Card -->
      <div class="card role-card admin-role">
        <div class="role-header">
          <span class="role-icon">👩‍💼</span>
          <h3>Panel de la Coordinadora</h3>
        </div>
        <p class="role-desc">
          Panel de control y administración general del comedor escolar. Supervisa y gestiona a los beneficiarios.
        </p>
        <ul class="features-list">
          <li><strong>Aprobaciones:</strong> Aprueba o rechaza nuevos perfiles de estudiantes.</li>
          <li><strong>Control de Estudiantes:</strong> Consulta la lista general de beneficiarios.</li>
          <li><strong>Reportes en Vivo:</strong> Visualiza asistencia diaria y resúmenes semanales.</li>
          <li><strong>Reactivaciones:</strong> Revisa excusas y reactiva estudiantes suspendidos.</li>
          <li><strong>Simulador:</strong> Simula el paso de los días escolares y verifica las reglas.</li>
        </ul>
        <button class="btn btn-primary btn-block btn-admin-cta mt-4" @click="enterRole('coordinadora')">
          Ingresar al Panel de la Coordinadora &rarr;
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HomeView',
  methods: {
    enterRole(role) {
      this.$emit('change-view', role)
    }
  }
}
</script>

<style scoped>
.home-container {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.landing-hero {
  background: linear-gradient(135deg, rgba(15, 76, 129, 0.05) 0%, rgba(211, 47, 47, 0.05) 100%);
  border-left-width: 6px;
}

.hero-desc {
  font-size: 1.1rem;
  line-height: 1.6;
  color: var(--text-muted);
}

.info-card {
  border-left: 6px solid var(--border-color);
}

.rules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-top: 15px;
}

.rule-box {
  background-color: var(--card-bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 20px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.rule-box:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

.rule-icon {
  font-size: 2rem;
  margin-bottom: 10px;
}

.rule-box h4 {
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--primary-color);
}

.rule-box p {
  font-size: 0.88rem;
  line-height: 1.4;
  color: var(--text-muted);
}

.role-card {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.student-role {
  border-top: 5px solid var(--primary-color);
}

.admin-role {
  border-top: 5px solid var(--accent-color);
}

.role-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 15px;
}

.role-icon {
  font-size: 2.2rem;
}

.role-header h3 {
  border-bottom: none;
  padding-bottom: 0;
  margin-bottom: 0;
  font-size: 1.3rem;
  color: var(--text-color);
}

.role-desc {
  font-size: 0.95rem;
  color: var(--text-muted);
  margin-bottom: 20px;
  flex-grow: 0;
}

.features-list {
  list-style: none;
  padding-left: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex-grow: 1;
}

.features-list li {
  font-size: 0.9rem;
  position: relative;
  padding-left: 20px;
  color: var(--text-color);
}

.features-list li::before {
  content: "✓";
  color: var(--success-color);
  font-weight: bold;
  position: absolute;
  left: 0;
  top: 0;
}

.btn-admin-cta {
  background-color: var(--accent-color);
}

.btn-admin-cta:hover {
  background-color: var(--danger-color);
}

@media (max-width: 768px) {
  .rules-grid {
    grid-template-columns: 1fr;
  }
}
</style>
