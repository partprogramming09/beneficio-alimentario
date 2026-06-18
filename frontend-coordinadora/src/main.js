import { createApp } from 'vue';
import App from './App.vue';

// Importar estilos de diseño compartidos globales del core
import '@shared/core/src/assets/css/variables.css';
import '@shared/core/src/assets/css/reset.css';

createApp(App).mount('#app');
