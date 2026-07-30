# 📋 TASK LIST: Lista de Tareas de Resolución de API URL en Producción

> **Proyecto:** Sistema de Beneficio Alimentario - I.E. Enrique Vélez Escobar  
> **Fecha:** 2026-07-30  
> **Estado:** 100% Completado (Builds Verificados)

---

## 🚀 Fase 1: Implementación de la Detección Inteligente de API URL

- [x] **Tarea 1.1**: Actualizar `frontend-core/src/api/client.js` para incluir la resolución dinámica de dominio (`hostname !== 'localhost'`).
- [x] **Tarea 1.2**: Crear `frontend-estudiante/.env.production` y `frontend-estudiante/.env`.
- [x] **Tarea 1.3**: Crear `frontend-coordinadora/.env.production` y `frontend-coordinadora/.env`.

---

## 🧪 Fase 2: Verificación de Builds y Publicación en Git

- [x] **Tarea 2.1**: Ejecutar `npm run build` en `frontend-coordinadora` para verificar la inserción de la URL (Build OK en 1.52s).
- [x] **Tarea 2.2**: Ejecutar `npm run build` en `frontend-estudiante` (Build OK en 1.28s).
- [x] **Tarea 2.3**: Commit & Push a la rama `master`.
