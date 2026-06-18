# server/Dockerfile 
    FROM node:20-slim 
     
    # Instalar pnpm globalmente
    RUN npm install -g pnpm

    WORKDIR /usr/src/app 
     
    # Copiar archivos de dependencias desde la raíz (el contexto de construcción es la raíz del proyecto) 
    COPY package.json pnpm-lock.yaml* ./ 
     
    # Instalar dependencias (incluyendo sqlite3 y devDependencies para nodemon si estamos en desarrollo) 
    RUN pnpm install 
     
    # Copiar el código del backend (la carpeta server y la base de datos) 
    COPY . . 
     
    # Exponer el puerto 3001 configurado en tu server/index.js 
    EXPOSE 3001 
     
    # Comando para iniciar el backend usando nodemon para desarrollo local 
    CMD ["pnpm", "run", "dev:backend"]