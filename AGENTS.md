# 🤖 Antigravity Agents Workspace Context & Memory Guide (`AGENTS.md`)

> **Propósito:** Este archivo mantiene el contexto persistente, la arquitectura de agentes, las habilidades activas, las reglas de negocio y el historial de cambios globales del proyecto **Beneficio Alimentario (I.E. Enrique Vélez Escobar)**.

---

## 🏛️ 1. Resumen de Arquitectura y Stack del Proyecto

- **Sistema Operativo / Shell:** Windows (PowerShell / CMD).
- **Backend Core:** PHP 8.3 / Laravel 11 (Arquitectura por Módulos: `Student`, `Attendance`, `Admin`, `Webhook`).
- **Frontends:** Vue 3 (Options API / Composition), Vite, Vanilla CSS con tokens dinámicos HSL.
  - `frontend-estudiante`: Portal para autoregistro voluntario, consulta de ticket diario y gestión de excusas/inasistencias.
  - `frontend-coordinadora`: Panel de supervisión de matrícula, aprobación de excusas, simulación de días escolares y reportes de asistencia.
  - `frontend-core`: Librería compartida de cliente API Axios y componentes base.
- **Bases de Datos:**
  - **Desarrollo Local:** MySQL local WAMP (`localhost:3306`, BD: `beneficio_alimentario`).
  - **Producción (Render):** TiDB Cloud MySQL 8.0 desplegado en la nube a través de `render.yaml` y Docker.

---

## 🧠 2. Reglas de Negocio del Sistema Comedor

1. **Matrícula Institucional vs. Registro Voluntario:**
   - La Coordinadora carga la lista oficial de matriculados (`institucion_estudiantes`).
   - El estudiante realiza voluntariamente su registro personal en el portal del estudiante (`estudiantes`).
   - La Coordinadora supervisa en tiempo real quiénes se han registrado (*Sin Registrar*, *Activo*, *Suspendido*, *Inactivo*) sin forzar registros automáticos involuntarios.

2. **Control de Asistencia y Ticket Diario:**
   - Cada estudiante puede generar como máximo **1 ticket de asistencia diario**.
   - Si el estudiante ingresa a "Verificar Registro" contando ya con un perfil activo, el portal lo identifica e inicia sesión mostrándole directamente su **Ticket Diario scaneable**.

3. **Regla de Suspensión Automática por Inasistencias:**
   - Si un estudiante activo acumula **3 inasistencias consecutivas** en días hábiles escolares (evaluados mediante el calendario festivo colombiano), su estado cambia automáticamente a `Suspendido`.

4. **Reactivación e Inasistencias Justificadas:**
   - Las solicitudes de excusa/justificación enviadas por el estudiante en el portal son revisadas por la Coordinadora; al ser aprobadas, el estudiante recupera inmediatamente su estado `Activo`.

---

## ⚡ 3. Slash Commands Recomendados

| Tipo de Tarea | Comando Recomendado | Propósito / Beneficio |
| :--- | :--- | :--- |
| **Refactorización Compleja** | `/plan` | Genera un plan de implementación estructurado en artefactos. |
| **Alinear Requerimientos** | `/grill-me` | Entrevista interactiva corta para resolver ambigüedades técnicas. |
| **Ejecución Extensa / Autónomas** | `/goal` | Trabajo profundo sin detenerse hasta completar el objetivo. |
| **Recordatorios / Temporizadores** | `/schedule` | Programa notificaciones o ejecuciones recurrentes. |

---

## 🛠️ 4. Estructura Modular del Proyecto

```text
beneficio-alimentario/
├── backend/
│   ├── app/Modules/
│   │   ├── Admin/         # Controladores, rutas y servicios administrativos
│   │   ├── Attendance/    # Reglas de suspensión y registro de asistencias
│   │   ├── Student/       # Validación y autoregistro voluntario de alumnos
│   │   └── Webhook/       # Disparo de eventos integrados
│   ├── app/Services/      # ColombianCalendarService (Cálculo agnóstico de Pascua y festivos)
│   ├── config/            # app.php (America/Bogota), database.php, etc.
│   └── database/          # Migraciones y seeders MySQL
├── frontend-coordinadora/  # Portal Vue 3 de supervisión y gestión institucional
├── frontend-estudiante/    # Portal Vue 3 de autoregistro, ticket diario y excusas
└── frontend-core/          # Cliente API centralizado (apiClient)
```

---

## 🚀 6. Reglas Críticas de Deploy en Render

### Nombres de Servicios (NO cambiar sin verificar)
| Servicio | Nombre en Render | URL |
|----------|-----------------|-----|
| Backend API | `beneficio-alimentario` | `https://beneficio-alimentario.onrender.com` |
| Frontend Estudiantes | `portal-estudiante` | `https://portal-estudiante.onrender.com` |
| Frontend Coordinadora | `portal-coordinadora` | `https://portal-coordinadora.onrender.com` |

### CORS (`backend/config/cors.php`)
Los `allowed_origins` DEBEN coincidir EXACTAMENTE con las URLs de los frontends:
- `https://portal-coordinadora.onrender.com`
- `https://portal-estudiante.onrender.com`
- `http://localhost:5173` / `http://localhost:5174`

### Dockerfile — Generación de `.env`
El `backend/Dockerfile` SIEMPRE genera el `.env` desde las env vars de Render al iniciar.
NUNCA depender del `.env` del repositorio para producción.

### VITE_API_URL (Frontends estáticos)
- Render inyecta `VITE_API_URL` al hacer **build** del frontend.
- Esta variable se configura en el **dashboard de Render** → Environment del servicio.
- Si el build usa una URL incorrecta, el JavaScript compilado la tiene hardcodeada.
- **NUNCA** usar `beneficio-alimentario-api.onrender.com` (URL fantasma, no existe).
- **SIEMPRE** usar `https://beneficio-alimentario.onrender.com`.

### Render.yaml vs Dashboard
- `render.yaml` define servicios con nombres `beneficio-*`.
- Los servicios reales en el dashboard se llaman `portal-*`.
- Render **IGNORA** el `render.yaml` para servicios que ya existen con otros nombres.
- Para actualizar env vars de `portal-*`, ir al **dashboard de Render manualmente**.

---

## 📝 5. Historial de Mantenimiento (Changelog)

- **2026-07-30:**
  - Optimización de assets de la imagen del escudo institucional (`/escudo.png`) garantizando su renderizado sin errores en `frontend-estudiante` y `frontend-coordinadora`.
  - Elaboración y ejecución del plan SDD de modernización UI/UX (`SPEC_UI_MODERNIZATION.md`, `DESIGN_PLAN_UI_ESTHETICS.md`, `TASK_LIST_UI_EXECUTION.md`).
  - Verificación de Builds de producción de Vite en ambos frontends compitiendo en <1.5s de forma limpia.
  - Resolución integral de conflictos de registro e integridad transaccional mediante el flujo SDD.
  - Modularización del monolito `AdminService.php` en 3 servicios especializados: `StudentManagementService`, `AttendanceReportService` y `AttendanceSimulationService`.
  - Implementación de cascada transaccional (`DB::transaction` con desactivación segura de FK) en la actualización de documentos (`updateStudent`), garantizando la preservación del 100% del historial de asistencias, comprobantes y excusas.
  - Corrección de `toggleCupo` para usar el estado `Inactivo` bloqueando el autoregistro no autorizado del estudiante sin borrar su historial histórico.
  - Implementación de `formatNames()` para el manejo seguro de nombres y apellidos compuestos.
  - Cobertura de pruebas unitarias/integración de 9/9 (63 aserciones) en `DiningHallTest.php` pasadas al 100%.
  - Creación del archivo `AGENTS.md` del espacio de trabajo.
  - Corrección de la zona horaria predeterminada del backend a `America/Bogota` (UTC-5).
  - Cálculo agnóstico del Domingo de Pascua en `ColombianCalendarService.php` para eliminar cualquier dependencia de la extensión `easter_date`.
  - Rediseño de Cursos y Grupos en `frontend-coordinadora` a panel visual de supervisión sin alteración indebida de cuentas.
  - Implementación del flujo amigable `Verificar Registro` en `frontend-estudiante` con autologin a Ticket Diario para alumnos activos.
  - Corrección de Dockerfile para SIEMPRE generar `.env` desde env vars de Render (eliminado `if [ ! -f .env ]`).
  - Corrección de `healthCheckPath` de `/` a `/up` en render.yaml.
  - Resolución de error CORS: orígenes en `cors.php` alineados con URLs reales de Render (`portal-*`).
  - Documentación de reglas críticas de deployment en AGENTS.md.
