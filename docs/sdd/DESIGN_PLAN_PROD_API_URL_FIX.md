# 🏛️ DESIGN PLAN: Solución Arquitectónica de API URL en Producción

> **Proyecto:** Sistema de Beneficio Alimentario - I.E. Enrique Vélez Escobar  
> **Fecha:** 2026-07-30  

---

## 🏗️ 1. Modificación de `frontend-core/src/api/client.js`

```javascript
let apiURL = import.meta.env?.VITE_API_URL;

if (!apiURL) {
  if (typeof window !== 'undefined' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
    apiURL = 'https://beneficio-alimentario-api.onrender.com';
  } else {
    apiURL = 'http://localhost:8000';
  }
}

if (typeof window !== 'undefined' && window.location.hostname.endsWith('use.devtunnels.ms')) {
  const hostname = window.location.hostname;
  const apiHostname = hostname.replace(/-(5173|5174)/, '-8000');
  apiURL = `https://${apiHostname}`;
}
```

---

## 📁 2. Archivos `.env.production` y `.env` a Crear

1. **`frontend-estudiante/.env.production`**:
   `VITE_API_URL=https://beneficio-alimentario-api.onrender.com`

2. **`frontend-coordinadora/.env.production`**:
   `VITE_API_URL=https://beneficio-alimentario-api.onrender.com`

3. **`frontend-estudiante/.env`**:
   `VITE_API_URL=http://localhost:8000`

4. **`frontend-coordinadora/.env`**:
   `VITE_API_URL=http://localhost:8000`
