# Guía de Flujo de Trabajo del Proyecto

Esta guía describe el flujo de desarrollo, ejecución y pruebas para el **Sistema de Control de Beneficio Alimentario**.

---

## 🏗️ Arquitectura General

El sistema se compone de una arquitectura desacoplada organizada en un monorepo pnpm y contenerizada mediante Docker Compose:

```text
                               +-----------------------------+
                               |    DOCKER COMPOSE ROUTER    |
                               +-----------------------------+
                                      /        |        \
                                     /         |         \
                                    v          v          v
                       +-------------+   +-------------+   +-------------+
                       |  PORTAL ESTU |   |  PANEL ADMIN|   |  LARAVEL 13 |
                       |  Puerto 5173|   |  Puerto 5174|   |  Puerto 8000|
                       +-------------+   +-------------+   +-------------+
                              \                /                  |
                               \              /                   v
                                v            v             +-------------+
                              +----------------+           |  MYSQL 8.0  |
                              |  @shared/core  |           |  Puerto 3306|
                              +----------------+           +-------------+
```

---

## 🛠️ Ejecución Local con Docker

Para iniciar todo el entorno de base de datos, backend y portales frontales:

```bash
# Construir y arrancar contenedores en segundo plano
docker compose up --build -d

# Detener los contenedores conservando el volumen de MySQL
docker compose down

# Apagar limpiando los contenedores huérfanos o volúmenes temporales
docker compose down --remove-orphans -v
```

### Puertos de Acceso Local
* **Portal del Estudiante:** [http://localhost:5173](http://localhost:5173) (Público/Inscripción/Marcación).
* **Panel de la Coordinadora:** [http://localhost:5174](http://localhost:5174) (Aprobación/Reportes/Reactivación).
* **Backend API (Laravel):** [http://localhost:8000](http://localhost:8000) (Servidor de Desarrollo).
* **Base de Datos MySQL:** `localhost:3306` (Credenciales: `root`/`root`).

---

## 📦 Gestión del Monorepo (pnpm)

Si deseas ejecutar o compilar los portales de frontend de manera nativa en el host:

```bash
# Instalar y vincular todas las dependencias
pnpm install

# Compilar todos los proyectos del frontend (estudiante, coordinadora, core)
pnpm -r build

# Levantar portal estudiantil en desarrollo
pnpm dev:estudiante

# Levantar panel de coordinadora en desarrollo
pnpm dev:coordinadora
```

---

## 🧪 Suite de Pruebas (Tests)

Las validaciones y reglas de negocio se ejecutan mediante tests automatizados de Laravel:

```bash
# Ejecutar todas las pruebas dentro del contenedor de Docker
docker compose exec backend php artisan test

# O de forma directa si cuentas con PHP en tu host
cd backend
php artisan test
```
