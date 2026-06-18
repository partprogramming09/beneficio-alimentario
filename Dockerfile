# Dockerfile (en la raíz para el Frontend) 
    FROM node:20-alpine 
     
    # Instalar pnpm globalmente
    RUN npm install -g pnpm

    WORKDIR /usr/src/app 
     
    # Copiar dependencias e instalarlas 
    COPY package.json pnpm-lock.yaml* ./ 
    RUN pnpm install 
     
    # Copiar todo el código del proyecto 
    COPY . . 
     
    # Exponer el puerto por defecto de Vite (5173) 
    EXPOSE 5173 
     
    # Arrancar el servidor de desarrollo de Vite 
    CMD ["pnpm", "run", "dev:frontend", "--", "--host"] 