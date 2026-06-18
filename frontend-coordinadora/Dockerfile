FROM node:20-alpine

# Instalar pnpm globalmente en la imagen
RUN npm install -g pnpm

WORKDIR /usr/src/app

# Copiar configuraciones del monorepo para la instalación
COPY package.json pnpm-workspace.yaml pnpm-lock.yaml ./

# Copiar el core compartido y la aplicación de coordinadora
COPY frontend-core/ ./frontend-core/
COPY frontend-coordinadora/ ./frontend-coordinadora/

# Instalar dependencias completas del monorepo
RUN pnpm install --frozen-lockfile

# Ajustar directorio de ejecución
WORKDIR /usr/src/app/frontend-coordinadora

# Exponer puerto del panel administrativo
EXPOSE 5174

# Arrancar el servidor de desarrollo de Vite con host expuesto
CMD ["pnpm", "dev", "--host"]
