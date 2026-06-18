# Sistema de Control de Beneficio Alimentario — I.E. Enrique Vélez Escobar

Este es el repositorio oficial del prototipo interactivo para la gestión y control del comedor escolar de la **Institución Educativa Enrique Vélez Escobar (E.V.E.)**.

El sistema permite regular el beneficio alimentario de los estudiantes, previniendo el desperdicio de raciones mediante reglas automáticas de inasistencias y facilitando la administración a la coordinadora del programa.

---

## 🚀 Características Principales

El portal cuenta con tres vistas principales:
1.  **Página de Inicio (Landing Page)**: Introducción al sistema, explicación interactiva del funcionamiento de las reglas de negocio y accesos rápidos a los portales.
2.  **Portal del Estudiante**:
    *   **Crear Perfil**: Registro en el programa mediante validación de documento institucional.
    *   **Marcar Asistencia**: Registro del almuerzo diario con obtención de ticket comprobante.
    *   **Recuperar Ticket**: Búsqueda del comprobante digital generado en el día actual.
    *   **Gestión de Faltas**: Envío de justificaciones (excusas) y renuncia voluntaria al programa.
3.  **Panel de la Coordinadora**:
    *   **Aprobaciones**: Validación de nuevos registros de estudiantes.
    *   **Estudiantes**: Listado general de beneficiarios inscritos y sus estados.
    *   **Reportes**: Consulta de asistencia diaria y resumen semanal de almuerzos acumulados.
    *   **Reactivaciones**: Gestión de justificaciones y reingreso de alumnos suspendidos.
    *   **Simulador**: Herramienta de pruebas para avanzar fechas y registrar asistencia simulada de alumnos para auditar las reglas del negocio.

---

## 🧠 Reglas de Negocio Implementadas

*   **Validación de Matrícula**: Solo se pueden registrar estudiantes cuyos documentos coincidan con la base de datos pre-cargada de la institución.
*   **Límite de Asistencia**: Un estudiante solo puede registrar una asistencia por día.
*   **Regla de Suspensión Automática**: Si un estudiante activo acumula **3 inasistencias consecutivas** en días de servicio escolar, el sistema cambia automáticamente su estado a **Suspendido** y le bloquea la marcación de asistencia.
*   **Reingreso por Justificación**: Un estudiante suspendido recupera su estado **Activo** una vez que envía una justificación de inasistencia y esta es aprobada por la Coordinadora en el panel.

---

## 🛠️ Tecnologías Utilizadas

*   **Frontend**: Vue 3 (Options API), Vite, HTML5 Semántico y CSS Vanilla con variables dinámicas de tema (Modo Claro / Oscuro con alto contraste para legibilidad).
*   **Backend**: Node.js, Express API, CORS.
*   **Base de Datos**: SQLite3 (base de datos relacional ligera).

---

## 📁 Estructura del Proyecto (Modular)

La estructura del código frontend se ha refactorizado para separar las responsabilidades de las vistas en componentes individuales:

```text
src/
├── assets/
│   └── css/                  # Hojas de estilo estructuradas (estudiante, coordinadora, etc.)
├── components/
│   └── AlertBox.vue          # Componente reutilizable para alertas
├── services/
│   └── api.js                # Cliente de peticiones HTTP centralizado
├── views/
│   ├── HomeView.vue          # Landing page informativa
│   ├── EstudianteView.vue    # Orquestador del portal de estudiantes
│   ├── CoordinadoraView.vue  # Orquestador del panel de la coordinadora
│   ├── estudiante/           # Componentes hijos del estudiante
│   │   ├── RegistroTab.vue
│   │   ├── AsistenciaTab.vue
│   │   ├── RecuperarTab.vue
│   │   └── GestionTab.vue
│   └── coordinadora/         # Componentes hijos de la coordinadora
│       ├── AprobacionesTab.vue
│       ├── EstudiantesTab.vue
│       ├── ReportesTab.vue
│       ├── ReactivacionesTab.vue
│       └── SimuladorTab.vue
└── App.vue                   # Componente principal y layout global
```

---

## 💻 Instrucciones para Desarrolladores

### 1. Instalación de Dependencias
Asegúrate de tener instalado [Node.js](https://nodejs.org/). Luego, corre en la raíz del proyecto:
```bash
npm install
```

### 2. Ejecutar el Proyecto en Desarrollo
Levanta el servidor backend (Express en puerto `3001`) y el dev-server del frontend (Vite en puerto `5173`) de forma concurrente:
```bash
npm run dev
```
Accede al portal web en http://localhost:5173.

### 3. Ejecutar Pruebas de Integración
La suite de pruebas automatizadas restablece la base de datos de pruebas SQLite y ejecuta de forma secuencial 18 aserciones de integración que cubren el backend:
```bash
npm test
```

### 4. Compilación de Producción
Para compilar y optimizar los recursos estáticos para el despliegue:
```bash
npm run build
```
Los archivos optimizados se generarán en la carpeta `/dist`.
