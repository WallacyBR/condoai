import { createApp } from 'vue';
import LoginView from './components/LoginView.vue';

// Criar aplicação Vue
const app = createApp(LoginView);

// Montar aplicação no elemento #app
app.mount('#app');

// Log para debug
console.log('Vue app montada com sucesso!'); 