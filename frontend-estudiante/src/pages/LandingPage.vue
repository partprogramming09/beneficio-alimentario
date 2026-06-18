<template>
  <div class="landing-container">
    <!-- Hero Banner with Institutional Style -->
    <div class="landing-hero-card">
      <div class="hero-pattern"></div>
      <div class="hero-content">
        <div class="school-shield-badge">🛡️</div>
        <h2>Sistema de Control de Beneficio Alimentario</h2>
        <h3 class="school-name">Institución Educativa Enrique Vélez Escobar</h3>
        <p class="hero-desc">
          Bienvenido al Portal del Estudiante. Desde aquí podrás solicitar tu inscripción al programa, justificar inasistencias y recuperar tu comprobante de almuerzo diario emitido en el comedor escolar.
        </p>
        <div class="hero-action-container">
          <button class="btn btn-cta" @click="$emit('enter-portal')">
            Ingresar al Portal del Estudiante &rarr;
          </button>
        </div>
      </div>
    </div>

    <!-- How it Works section -->
    <div class="card info-card">
      <div class="info-header">
        <span class="info-icon">🏛️</span>
        <div>
          <h3>Guía General del Beneficio Alimentario</h3>
          <p class="subtitle-text">Pautas obligatorias para el correcto funcionamiento del comedor escolar</p>
        </div>
      </div>
      
      <div class="rules-grid">
        <!-- Step 1 -->
        <div class="rule-card">
          <div class="rule-badge">1</div>
          <div class="rule-card-header">
            <span class="rule-card-icon">📝</span>
            <h4>Inscripción al Programa</h4>
          </div>
          <p>
            Valida tu documento, completa tu información de perfil y solicita el beneficio. La coordinación revisará y aprobará tu solicitud.
          </p>
        </div>
        
        <!-- Step 2 -->
        <div class="rule-card">
          <div class="rule-badge">2</div>
          <div class="rule-card-header">
            <span class="rule-card-icon">🍽️</span>
            <h4>Registro en Fila</h4>
          </div>
          <p>
            Al ingresar al comedor escolar, presenta tu documento. La coordinadora registrará tu asistencia en vivo para emitir tu ticket.
          </p>
        </div>

        <!-- Step 3 -->
        <div class="rule-card">
          <div class="rule-badge">3</div>
          <div class="rule-card-header">
            <span class="rule-card-icon">⚠️</span>
            <h4>Evitar Absencias</h4>
          </div>
          <p>
            ¡Cuida tu cupo! Si acumulas <strong>3 inasistencias consecutivas</strong> sin justificación, el sistema suspenderá automáticamente tu beneficio.
          </p>
        </div>

        <!-- Step 4 -->
        <div class="rule-card">
          <div class="rule-badge">4</div>
          <div class="rule-card-header">
            <span class="rule-card-icon">📄</span>
            <h4>Justificación Médica</h4>
          </div>
          <p>
            Si faltas, reporta y sube tu excusa a través de este portal para justificar la inasistencia y reactivar o proteger tu cupo.
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
  gap: 35px;
  animation: fadeIn var(--transition-normal);
}

/* Institutional Hero Styling */
.landing-hero-card {
  position: relative;
  background: linear-gradient(135deg, hsl(var(--primary-hue), 79%, 25%) 0%, hsl(var(--primary-hue), 85%, 15%) 100%);
  color: white;
  padding: 50px 30px;
  text-align: center;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-lg);
  border: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
}

.hero-pattern {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: radial-gradient(rgba(255, 255, 255, 0.15) 1.5px, transparent 1.5px);
  background-size: 24px 24px;
  opacity: 0.3;
  pointer-events: none;
}

.hero-content {
  position: relative;
  z-index: 2;
  max-width: 800px;
  margin: 0 auto;
}

.school-shield-badge {
  font-size: 3rem;
  margin-bottom: 15px;
  filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
  animation: float 4s ease-in-out infinite;
  display: inline-block;
}

.landing-hero-card h2 {
  font-size: 1.85rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
  color: white;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.school-name {
  font-size: 1.35rem;
  font-weight: 500;
  color: var(--accent);
  margin-bottom: 25px;
  text-transform: uppercase;
  letter-spacing: 1px;
  text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.hero-desc {
  font-size: 1.08rem;
  line-height: 1.6;
  opacity: 0.92;
  margin-bottom: 35px;
}

.hero-action-container {
  display: flex;
  justify-content: center;
}

.btn-cta {
  background-color: var(--accent);
  color: hsl(var(--primary-hue), 80%, 10%);
  padding: 14px 32px;
  font-size: 1.05rem;
  font-weight: 700;
  border-radius: var(--border-radius-sm);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all var(--transition-normal);
}

.btn-cta:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.35);
  background-color: white;
  color: var(--primary);
}

/* Info Card & Grid Styling */
.info-card {
  border-top: 4px solid var(--accent);
  padding: 35px;
}

.info-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 30px;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 15px;
}

.info-icon {
  font-size: 2.2rem;
}

.info-header h3 {
  border-bottom: none !important;
  margin-bottom: 0px !important;
  padding-bottom: 0px !important;
  font-size: 1.4rem;
}

.subtitle-text {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.rules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 25px;
}

.rule-card {
  position: relative;
  background-color: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
  padding: 25px 20px 20px;
  transition: all var(--transition-normal);
}

.rule-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
  border-color: var(--accent);
}

.rule-badge {
  position: absolute;
  top: -12px;
  left: 20px;
  background-color: var(--primary);
  color: white;
  font-weight: 800;
  font-size: 0.85rem;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--bg-secondary);
}

.rule-card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}

.rule-card-icon {
  font-size: 1.5rem;
}

.rule-card h4 {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0;
}

.rule-card p {
  font-size: 0.9rem;
  line-height: 1.5;
  color: var(--text-secondary);
  margin: 0;
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-6px); }
}

@media (max-width: 768px) {
  .rules-grid {
    grid-template-columns: 1fr;
  }
  .landing-hero-card {
    padding: 35px 20px;
  }
}
</style>
