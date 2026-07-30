<template>
  <div class="landing-container">
    <!-- Hero Banner Moderno con Escudo Oficial y Glassmorphism -->
    <div class="landing-hero-card">
      <div class="hero-bg-glow"></div>
      <div class="hero-content">
        <!-- Badge Oficial -->
        <div class="hero-badge">
          <span class="badge-dot"></span>
          <span>Comedor Escolar — PAE Activo</span>
        </div>

        <!-- Escudo Oficial de la Escuela con Fondo Blanco Alrededor -->
        <div class="emblem-hero-wrapper">
          <div class="emblem-glow-ring"></div>
          <div class="emblem-white-badge">
            <img 
              :src="escudoImg" 
              alt="Escudo Oficial I.E. Enrique Vélez Escobar" 
              class="hero-school-shield"
            />
          </div>
        </div>

        <h1 class="hero-title">Beneficio Alimentario Escolar</h1>
        <p class="school-name">Institución Educativa Enrique Vélez Escobar</p>
        
        <p class="hero-desc">
          Plataforma oficial para la gestión digital de cupos de alimentación, consulta instantánea de tickets QR y recepción de justificaciones en línea.
        </p>

        <div class="hero-actions">
          <button class="btn btn-cta primary-cta" @click="$emit('enter-portal')">
            <svg class="line-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
              <polyline points="10 17 15 12 10 7"></polyline>
              <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            <span>Ingresar al Portal Estudiante</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Barra de Métricas e Impacto del Programa -->
    <div class="metrics-bar">
      <div class="metric-item">
        <div class="metric-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
        </div>
        <div class="metric-info">
          <strong>100% Digital</strong>
          <span>Sin filas impresas ni turnos en papel</span>
        </div>
      </div>

      <div class="metric-item">
        <div class="metric-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
            <line x1="7" y1="8" x2="17" y2="8"></line>
            <line x1="7" y1="12" x2="13" y2="12"></line>
            <line x1="7" y1="16" x2="10" y2="16"></line>
          </svg>
        </div>
        <div class="metric-info">
          <strong>Ticket QR Diario</strong>
          <span>Lectura inmediata en el comedor</span>
        </div>
      </div>

      <div class="metric-item">
        <div class="metric-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
          </svg>
        </div>
        <div class="metric-info">
          <strong>Protección de Cupo</strong>
          <span>Garantía de equidad e inclusión</span>
        </div>
      </div>
    </div>

    <!-- Grid de Servicios con Íconos Lineales Simples -->
    <div class="section-header">
      <h3 class="section-title">Servicios Principales</h3>
      <p class="section-subtitle">Todo lo que necesitas para administrar tu beneficio escolar</p>
    </div>

    <div class="features-grid">
      <!-- Tarjeta 1: Inscripción -->
      <div class="feature-card">
        <div class="feature-icon-wrapper primary-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="8.5" cy="7" r="4"></circle>
            <line x1="20" y1="8" x2="20" y2="14"></line>
            <line x1="17" y1="11" x2="23" y2="11"></line>
          </svg>
        </div>
        <div class="feature-text">
          <h4>Inscripción en Línea</h4>
          <p>Registra tus datos con tu documento de identidad para ingresar al programa de alimentación.</p>
        </div>
      </div>

      <!-- Tarjeta 2: Ticket QR -->
      <div class="feature-card">
        <div class="feature-icon-wrapper success-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="6" width="20" height="12" rx="2"></rect>
            <path d="M6 12h.01M10 12h4M18 12h.01"></path>
          </svg>
        </div>
        <div class="feature-text">
          <h4>Mi Ticket Diario</h4>
          <p>Obtén y consulta tu código QR o número de comprobante listo para reclamar tu almuerzo.</p>
        </div>
      </div>

      <!-- Tarjeta 3: Excusas -->
      <div class="feature-card">
        <div class="feature-icon-wrapper warning-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
          </svg>
        </div>
        <div class="feature-text">
          <h4>Justificación de Faltas</h4>
          <p>Carga excusas médicas o justificativos para conservar activo tu beneficio sin suspensiones.</p>
        </div>
      </div>
    </div>

    <!-- Guía Informativa del Reglamento -->
    <div class="rules-card">
      <div class="rules-header">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <h4>Reglamento Importante del Beneficio</h4>
      </div>
      <ul class="rules-list">
        <li>
          <svg class="check-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          <span><strong>Tres inasistencias consecutivas sin justificar</strong> provocan la suspensión temporal del beneficio.</span>
        </li>
        <li>
          <svg class="check-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          <span>Las excusas deben radicarse a través del portal en un plazo máximo de <strong>48 horas</strong> hábiles.</span>
        </li>
        <li>
          <svg class="check-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          <span>Presenta siempre tu número de documento o Ticket QR al ingresar al comedor escolar.</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import escudoImg from '../../../frontend-core/src/assets/escudo.png'

export default {
  name: 'LandingPage',
  data() {
    return {
      escudoImg
    }
  }
}
</script>

<style scoped>
.landing-container {
  display: flex;
  flex-direction: column;
  gap: 28px;
  animation: fadeIn var(--transition-normal);
}

/* Hero Moderno con Glassmorphism */
.landing-hero-card {
  position: relative;
  background: var(--gradient-primary);
  color: white;
  padding: 42px 28px;
  text-align: center;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-glow), var(--shadow-lg);
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.18);
}

.hero-bg-glow {
  position: absolute;
  top: -40%;
  left: 50%;
  transform: translateX(-50%);
  width: 140%;
  height: 180%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
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
  background: rgba(255, 255, 255, 0.18);
  backdrop-filter: var(--backdrop-blur);
  padding: 6px 16px;
  border-radius: var(--border-radius-pill);
  font-size: 0.85rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  margin-bottom: 20px;
  border: 1px solid rgba(255, 255, 255, 0.25);
}

.badge-dot {
  width: 8px;
  height: 8px;
  background-color: hsl(var(--secondary-hue), 90%, 55%);
  border-radius: 50%;
  box-shadow: 0 0 8px hsl(var(--secondary-hue), 90%, 55%);
}

/* Escudo Oficial de la Escuela */
.emblem-hero-wrapper {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.emblem-glow-ring {
  position: absolute;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.35) 0%, rgba(255, 255, 255, 0) 70%);
  filter: blur(10px);
  animation: pulseGlow 3s infinite ease-in-out;
}

.emblem-white-badge {
  position: relative;
  background: #ffffff;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  padding: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25), 0 0 0 4px rgba(255, 255, 255, 0.3);
  border: 2px solid rgba(255, 255, 255, 0.8);
  transition: transform var(--transition-normal);
}

.emblem-white-badge:hover {
  transform: scale(1.06) rotate(2deg);
}

.hero-school-shield {
  width: 100%;
  height: 100%;
  object-fit: contain;
  filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.15));
}

.hero-title {
  font-size: 2.2rem;
  font-weight: 800;
  letter-spacing: -0.5px;
  margin-bottom: 4px;
  color: white;
  line-height: 1.2;
}

.school-name {
  font-size: 1.05rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 16px;
  letter-spacing: 0.5px;
}

.hero-desc {
  font-size: 1rem;
  line-height: 1.55;
  color: rgba(255, 255, 255, 0.92);
  margin-bottom: 26px;
}

.hero-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
}

.btn-cta {
  background: #ffffff;
  color: var(--primary);
  padding: 13px 28px;
  font-size: 0.98rem;
  font-weight: 700;
  border-radius: var(--border-radius-pill);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  border: none;
  transition: all var(--transition-fast);
  display: inline-flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.btn-cta:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 30px rgba(0, 0, 0, 0.3);
  background-color: #ffffff;
}

/* Metrics Bar */
.metrics-bar {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}

.metric-item {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: var(--shadow-sm);
  transition: transform var(--transition-fast);
}

.metric-item:hover {
  transform: translateY(-2px);
}

.metric-icon-box {
  width: 44px;
  height: 44px;
  border-radius: var(--border-radius-sm);
  background: var(--primary-light);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.metric-info {
  display: flex;
  flex-direction: column;
}

.metric-info strong {
  font-size: 0.95rem;
  color: var(--text-primary);
}

.metric-info span {
  font-size: 0.82rem;
  color: var(--text-muted);
}

/* Section Header */
.section-header {
  margin-top: 6px;
  text-align: left;
}

.section-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 2px;
}

.section-subtitle {
  font-size: 0.88rem;
  color: var(--text-secondary);
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
  width: 46px;
  height: 46px;
  border-radius: var(--border-radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.primary-icon {
  background-color: var(--primary-light);
  color: var(--primary);
}

.success-icon {
  background-color: var(--success-light);
  color: var(--success);
}

.warning-icon {
  background-color: var(--warning-light);
  color: var(--warning);
}

.feature-text h4 {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 4px;
}

.feature-text p {
  font-size: 0.9rem;
  line-height: 1.45;
  color: var(--text-secondary);
  margin: 0;
}

/* Rules Card */
.rules-card {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-left: 4px solid var(--primary);
  border-radius: var(--border-radius-md);
  padding: 20px 24px;
  box-shadow: var(--shadow-sm);
}

.rules-header {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--primary);
  margin-bottom: 12px;
}

.rules-header h4 {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0;
}

.rules-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.rules-list li {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-size: 0.92rem;
  color: var(--text-secondary);
  line-height: 1.4;
}

.check-icon {
  color: var(--success);
  margin-top: 2px;
  flex-shrink: 0;
}

@keyframes pulseGlow {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.9; transform: scale(1.08); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
  .landing-hero-card {
    padding: 32px 18px;
  }
  .hero-title {
    font-size: 1.75rem;
  }
  .hero-school-shield {
    width: 90px;
  }
}
</style>


