<template>
  <div class="landing-container">
    <div class="card header-card landing-hero">
      <h2>Bienvenido al Portal del Estudiante 👋</h2>
      <p class="hero-desc">
        Consulta tu estado, regístrate en el programa de alimentación, marca tu asistencia diaria y obtén tu ticket digital de almuerzo para la
        <strong>Institución Educativa Enrique Vélez Escobar</strong>.
      </p>
      <button class="btn btn-primary btn-cta mt-4" @click="$emit('enter-portal')">
        Ingresar al Portal del Estudiante &rarr;
      </button>
    </div>

    <div class="card info-card">
      <h3>¿Cómo funciona tu beneficio de alimentación? 🍽️</h3>
      <p class="mb-4 text-secondary">
        El programa busca asegurar la alimentación saludable de nuestros estudiantes. Ten presente las siguientes pautas del comedor escolar:
      </p>
      
      <div class="rules-grid">
        <div class="rule-box">
          <div class="rule-icon">📝</div>
          <h4>1. Inscripción</h4>
          <p>
            Verifica tu documento, completa tus datos de perfil para solicitar tu inscripción. Espera la aprobación de la Coordinadora.
          </p>
        </div>
        
        <div class="rule-box">
          <div class="rule-icon">⏰</div>
          <h4>2. Asistencia Diaria</h4>
          <p>
            Marca tu asistencia diariamente antes de entrar al comedor escolar. El sistema generará tu ticket digital.
          </p>
        </div>

        <div class="rule-box">
          <div class="rule-icon">⚠️</div>
          <h4>3. Suspensión Automática</h4>
          <p>
            ¡Evita el desperdicio! Si acumulas <strong>3 inasistencias consecutivas</strong>, tu estado cambiará a <strong>Suspendido</strong>.
          </p>
        </div>

        <div class="rule-box">
          <div class="rule-icon">📄</div>
          <h4>4. Justificaciones</h4>
          <p>
            Si tienes una falta médica o justificada, cárgala a tiempo en el sistema para reactivar o proteger tu beneficio.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LandingPage'
}
</script>

<style scoped>
.landing-container {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.landing-hero {
  background: var(--gradient-primary);
  color: white;
  border-left: none;
  padding: 40px;
  text-align: center;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-lg);
}

.landing-hero h2 {
  color: white;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 15px;
}

.hero-desc {
  font-size: 1.15rem;
  line-height: 1.6;
  max-width: 800px;
  margin: 0 auto;
  opacity: 0.9;
}

.btn-cta {
  background-color: white;
  color: var(--primary);
  padding: 14px 28px;
  font-size: 1.05rem;
  border-radius: var(--border-radius-sm);
  box-shadow: var(--shadow-sm);
  transition: transform var(--transition-fast), box-shadow var(--transition-fast);
}

.btn-cta:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  background-color: var(--bg-primary);
}

.info-card {
  border-left: 6px solid var(--primary);
}

.rules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-top: 15px;
}

.rule-box {
  background-color: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
  padding: 24px;
  transition: transform var(--transition-fast), box-shadow var(--transition-fast);
}

.rule-box:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

.rule-icon {
  font-size: 2rem;
  margin-bottom: 12px;
}

.rule-box h4 {
  font-size: 1.05rem;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--primary);
}

.rule-box p {
  font-size: 0.9rem;
  line-height: 1.45;
  color: var(--text-secondary);
}

@media (max-width: 768px) {
  .rules-grid {
    grid-template-columns: 1fr;
  }
}
</style>
