import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';

import AppLayout from './layouts/AppLayout.vue';
import Login from './pages/auth/Login.vue';
import FormField from './components/FormField.vue';
import ActionButton from './components/ActionButton.vue';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

const routes = [
    { path: '/', name: 'login', component: Login }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

const app = createApp({
    template: '<AppLayout />'
});

app.component('FormField', FormField);
app.component('ActionButton', ActionButton);
app.component('AppLayout', AppLayout);

app.use(router);
app.config.globalProperties.$http = axios;

window.showFlash = (type, message) => {
    // Aviso simples para feedback
    alert(message);
};

app.mount('#app');
