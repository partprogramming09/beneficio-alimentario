<template>
  <div class="landing-container">
    <!-- Hero Banner Minimalista -->
    <div class="landing-hero-card">
      <div class="hero-bg-glow"></div>
      <div class="hero-content">
        <div class="hero-badge">
          <span class="badge-dot"></span> Comedor Escolar Activo
        </div>
        <h1 class="hero-title">Beneficio Alimentario</h1>
        <p class="school-name">Institución Educativa Enrique Vélez Escobar</p>
        <p class="hero-desc">
          Gestiona tu cupo diario, consulta tus tickets de almuerzo y envía justificaciones de inasistencia de forma rápida.
        </p>
        <div class="hero-action-container">
          <button class="btn btn-cta" @click="$emit('enter-portal')">
            <span>Ingresar al Portal Estudiante</span>
            <span class="cta-arrow">&rarr;</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Quick Features & Rules Grid -->
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon-wrapper primary-icon">
          <span>📝</span>
        </div>
        <div class="feature-text">
          <h4>Inscripción Rápida</h4>
          <p>Valida tu documento e inscríbete en el programa con tu grupo.</p>
        </div>
      </div>

      <div class="feature-card">
        <div class="feature-icon-wrapper success-icon">
          <span>🎫</span>
        </div>
        <div class="feature-text">
          <h4>Ticket Diario</h4>
          <p>Obtén y recupera tu comprobante de almuerzo escaneable al instante.</p>
        </div>
      </div>

      <div class="feature-card">
        <div class="feature-icon-wrapper warning-icon">
          <span>🛡️</span>
        </div>
        <div class="feature-text">
          <h4>Protección de Cupo</h4>
          <p>Evita 3 fallas seguidas justificando tus inasistencias a tiempo.</p>
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
  animation: fadeIn var(--transition-normal);
}

/* Hero Moderno con Glassmorphism & Soft Glow */
.landing-hero-card {
  position: relative;
  background: var(--gradient-primary);
  color: white;
  padding: 48px 32px;
  text-align: center;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-glow), var(--shadow-lg);
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.hero-bg-glow {
  position: absolute;
  top: -40%;
  left: 50%;
  transform: translateX(-50%);
  width: 140%;
  height: 180%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 70%);
  pointer-events: none;
}

.hero-content {
  position: relative;
  z-index: 2;
  max-width: 680px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: var(--backdrop-blur);
  padding: 6px 16px;
  border-radius: var(--border-radius-pill);
  font-size: 0.85rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  margin-bottom: 16px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.badge-dot {
  width: 8px;
  height: 8px;
  background-color: hsl(var(--secondary-hue), 80%, 50%);
  border-radius: 50%;
  box-shadow: 0 0 8px hsl(var(--secondary-hue), 80%, 50%);
}

.hero-title {
  font-size: 2.3rem;
  font-weight: 800;
  letter-spacing: -0.5px;
  margin-bottom: 6px;
  color: white;
  line-height: 1.15;
}

.school-name {
  font-size: 1.05rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.85);
  margin-bottom: 20px;
  letter-spacing: 0.5px;
}

.hero-desc {
  font-size: 1.05rem;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.92);
  margin-bottom: 30px;
}

.hero-action-container {
  display: flex;
  justify-content: center;
}

.btn-cta {
  background: #ffffff;
  color: var(--primary);
  padding: 14px 32px;
  font-size: 1rem;
  font-weight: 700;
  border-radius: var(--border-radius-pill);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  border: none;
  transition: all var(--transition-fast);
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.btn-cta:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 14px 30px rgba(0, 0, 0, 0.3);
  background-color: #ffffff;
}

.cta-arrow {
  transition: transform var(--transition-fast);
}

.btn-cta:hover .cta-arrow {
  transform: translateX(4px);
}

/* Features Grid */
.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 20px;
}

.feature-card {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
  padding: 24px 20px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-normal);
}

.feature-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
  border-color: rgba(0, 0, 0, 0.12);
}

.feature-icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: var(--border-radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  flex-shrink: 0;
}

.primary-icon {
  background-color: var(--primary-light);
}

.success-icon {
  background-color: var(--success-light);
}

.warning-icon {
  background-color: var(--warning-light);
}

.feature-text h4 {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 4px;
}

.feature-text p {
  font-size: 0.9rem;
  line-height: 1.4;
  color: var(--text-secondary);
  margin: 0;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
  .landing-hero-card {
    padding: 32px 20px;
  }
  .hero-title {
    font-size: 1.8rem;
  }
}
</style>

