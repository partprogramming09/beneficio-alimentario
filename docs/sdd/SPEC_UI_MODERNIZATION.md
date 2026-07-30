# 🎨 SPEC: Visual Escudo Institucional & Rediseño UI/UX Estilo Moderno

> **Proyecto:** Sistema de Beneficio Alimentario - I.E. Enrique Vélez Escobar  
> **Fecha:** 2026-07-30  
> **Objetivo:** Garantizar la visualización perfecta del escudo institucional en todas las pantallas y elevar el diseño visual de los portales de Estudiantes y Coordinadora al nivel de interfaces web modernas (Tailwind/Modern UI aesthetics).

---

## 🎯 1. Requerimientos de la Imagen del Escudo

1. **Garantizar Carga de Imagen Sin Errores**:
   - Reemplazar las importaciones frágiles de archivos fuera del root (`../../../frontend-core/src/assets/escudo.png`) por la ruta pública de Vite (`/escudo.png`).
   - Mantener copias actualizadas en `public/escudo.png` de `frontend-estudiante` y `frontend-coordinadora`.
2. **Estilizado Premium del Escudo**:
   - Encapsular la imagen del escudo en un contenedor con clase `.school-shield-badge`.
   - Aplicar resplandor sutil (glow effect HSL), bordes sutiles y animación hover con elevación suave (`transform: translateY(-2px)`).
   - Asegurar contraste tanto en modo claro (Light Mode) como en modo oscuro (Dark Mode).

---

## 🎨 2. Requerimientos de Rediseño Visual (Estilo Modern Web UI)

1. **Tipografía Moderna y Tokens HSL**:
   - Importar fuentes modernas de Google Fonts (`Outfit` y `Plus Jakarta Sans`).
   - Aplicar un esquema de colores Tailored HSL con soporte fluido para temas oscuros/claros.
2. **Componentes con Glassmorphism & Micro-interacciones**:
   - Tarjetas de estado, formulación y resumen con `backdrop-filter: blur(16px)` y bordes translúcidos (`1px solid rgba(..., 0.15)`).
   - Sombras multicapa suaves (`box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08)`).
   - Micro-animaciones fluidas en botones, pestañas activas y campos de entrada.
3. **Optimización Responsive**:
   - Ajuste perfecto en móviles, tablets y monitores de alta resolución.
