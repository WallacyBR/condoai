import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';

// Layout
import AppLayout from './layouts/AppLayout.vue';

// Pages
import Dashboard from './pages/Dashboard.vue';
import Login from './pages/auth/Login.vue';
import Register from './pages/auth/Register.vue';

// CRUD Pages
import ResidentsIndex from './pages/residents/Index.vue';
import ResidentsCreate from './pages/residents/Create.vue';
import ResidentsEdit from './pages/residents/Edit.vue';
import ResidentsShow from './pages/residents/Show.vue';

import ApartmentsIndex from './pages/apartments/Index.vue';
import ApartmentsCreate from './pages/apartments/Create.vue';
import ApartmentsEdit from './pages/apartments/Edit.vue';
import ApartmentsShow from './pages/apartments/Show.vue';

import BlocksIndex from './pages/blocks/Index.vue';
import BlocksCreate from './pages/blocks/Create.vue';
import BlocksEdit from './pages/blocks/Edit.vue';
import BlocksShow from './pages/blocks/Show.vue';

import TowersIndex from './pages/towers/Index.vue';
import TowersCreate from './pages/towers/Create.vue';
import TowersEdit from './pages/towers/Edit.vue';
import TowersShow from './pages/towers/Show.vue';

import CondominiumsIndex from './pages/condominiums/Index.vue';
import CondominiumsCreate from './pages/condominiums/Create.vue';
import CondominiumsEdit from './pages/condominiums/Edit.vue';
import CondominiumsShow from './pages/condominiums/Show.vue';

// Componentes globais
import ResourceList from './components/ResourceList.vue';
import FormField from './components/FormField.vue';
import ActionButton from './components/ActionButton.vue';
import StatsCard from './components/StatsCard.vue';

// Configurar Axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Rotas
const routes = [
    { path: '/', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/login', name: 'login', component: Login, meta: { guest: true } },
    { path: '/register', name: 'register', component: Register, meta: { guest: true } },
    
    // Residents
    { path: '/residents', name: 'residents.index', component: ResidentsIndex, meta: { requiresAuth: true } },
    { path: '/residents/create', name: 'residents.create', component: ResidentsCreate, meta: { requiresAuth: true } },
    { path: '/residents/:id', name: 'residents.show', component: ResidentsShow, meta: { requiresAuth: true } },
    { path: '/residents/:id/edit', name: 'residents.edit', component: ResidentsEdit, meta: { requiresAuth: true } },
    
    // Apartments
    { path: '/apartments', name: 'apartments.index', component: ApartmentsIndex, meta: { requiresAuth: true } },
    { path: '/apartments/create', name: 'apartments.create', component: ApartmentsCreate, meta: { requiresAuth: true } },
    { path: '/apartments/:id', name: 'apartments.show', component: ApartmentsShow, meta: { requiresAuth: true } },
    { path: '/apartments/:id/edit', name: 'apartments.edit', component: ApartmentsEdit, meta: { requiresAuth: true } },
    
    // Blocks
    { path: '/blocks', name: 'blocks.index', component: BlocksIndex, meta: { requiresAuth: true } },
    { path: '/blocks/create', name: 'blocks.create', component: BlocksCreate, meta: { requiresAuth: true } },
    { path: '/blocks/:id', name: 'blocks.show', component: BlocksShow, meta: { requiresAuth: true } },
    { path: '/blocks/:id/edit', name: 'blocks.edit', component: BlocksEdit, meta: { requiresAuth: true } },
    
    // Towers
    { path: '/towers', name: 'towers.index', component: TowersIndex, meta: { requiresAuth: true } },
    { path: '/towers/create', name: 'towers.create', component: TowersCreate, meta: { requiresAuth: true } },
    { path: '/towers/:id', name: 'towers.show', component: TowersShow, meta: { requiresAuth: true } },
    { path: '/towers/:id/edit', name: 'towers.edit', component: TowersEdit, meta: { requiresAuth: true } },
    
    // Condominiums
    { path: '/condominiums', name: 'condominiums.index', component: CondominiumsIndex, meta: { requiresAuth: true } },
    { path: '/condominiums/create', name: 'condominiums.create', component: CondominiumsCreate, meta: { requiresAuth: true } },
    { path: '/condominiums/:id', name: 'condominiums.show', component: CondominiumsShow, meta: { requiresAuth: true } },
    { path: '/condominiums/:id/edit', name: 'condominiums.edit', component: CondominiumsEdit, meta: { requiresAuth: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Guards de navegação
router.beforeEach((to, from, next) => {
    const isAuthenticated = document.querySelector('meta[name="user-authenticated"]')?.content === 'true';
    
    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'login' });
    } else if (to.meta.guest && isAuthenticated) {
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

const app = createApp({
    template: '<AppLayout />'
});

// Componentes globais
app.component('ResourceList', ResourceList);
app.component('FormField', FormField);
app.component('ActionButton', ActionButton);
app.component('StatsCard', StatsCard);
app.component('AppLayout', AppLayout);

app.use(router);

// Disponibilizar axios globalmente
app.config.globalProperties.$http = axios;

app.mount('#app');
