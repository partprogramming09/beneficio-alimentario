import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5174,
    watch: {
      usePolling: true
    }
  },
  optimizeDeps: {
    // Evita el pre-empaquetamiento del core compartido para permitir recarga en tiempo real
    exclude: ['@shared/core'],
  },
});
