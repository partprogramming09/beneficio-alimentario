FROM node:20-alpine

# Instalar pnpm globalmente en la imagen
RUN npm install -g pnpm

WORKDIR /usr/src/app

# Copiar configuraciones del monorepo para la instalación
COPY package.json pnpm-workspace.yaml pnpm-lock.yaml ./

# Copiar el core compartido y la aplicación de estudiante
COPY frontend-core/ ./frontend-core/
COPY frontend-estudiante/ ./frontend-estudiante/

# Instalar dependencias completas del monorepo
RUN pnpm install --frozen-lockfile

# Ajustar directorio de ejecución
WORKDIR /usr/src/app/frontend-estudiante

# Exponer puerto del portal estudiantil
EXPOSE 5173

# Arrancar el servidor de desarrollo de Vite con host expuesto
CMD ["pnpm", "dev", "--host"]
