# 🏛️ DESIGN PLAN: Arquitectura Estética y Componentes UI/UX

> **Proyecto:** Sistema de Beneficio Alimentario - I.E. Enrique Vélez Escobar  
> **Fecha:** 2026-07-30  

---

## 🎨 1. Sistema de Tokens de Diseño (CSS Variables)

Definiremos en los archivos CSS principales (`index.css` / `main.css` / layouts):

```css
:root {
  /* Escudo e Identidad Institucional */
  --shield-glow: 0 0 20px rgba(59, 130, 246, 0.25);
  
  /* Fuentes Modernas */
  --font-heading: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
  --font-body: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  
  /* HSL Tailored Palette */
  --primary-hsl: 220, 90%, 56%;
  --primary-accent: hsl(var(--primary-hsl));
  --surface-glass: rgba(255, 255, 255, 0.75);
  --surface-border: rgba(226, 232, 240, 0.8);
  --shadow-soft: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
}

.dark-mode {
  --surface-glass: rgba(15, 23, 42, 0.85);
  --surface-border: rgba(51, 65, 85, 0.7);
  --shadow-soft: 0 10px 30px -5px rgba(0, 0, 0, 0.4);
}
```

---

## 🛡️ 2. Componente de Escudo Institucional (`ShieldBadge`)

Firma HTML/Vue del escudo estilizado:

```html
<div class="school-shield-badge">
  <img src="/escudo.png" alt="Escudo I.E. Enrique Vélez Escobar" class="shield-img" />
</div>
```

Estilos CSS asociados:
```css
.school-shield-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  padding: 4px;
  background: var(--surface-glass);
  border: 1px solid var(--surface-border);
  border-radius: 12px;
  box-shadow: var(--shield-glow), var(--shadow-soft);
  backdrop-filter: blur(12px);
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease;
}

.school-shield-badge:hover {
  transform: translateY(-2px) scale(1.03);
}

.shield-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
```

---

## 📱 3. Vistas a Actualizar

1. `frontend-estudiante/src/layouts/StudentLayout.vue`
2. `frontend-estudiante/src/pages/LandingPage.vue`
3. `frontend-coordinadora/src/pages/DashboardPage.vue`
