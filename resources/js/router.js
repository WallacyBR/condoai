import { createRouter, createWebHistory } from 'vue-router';
import LoginView from './components/LoginView.vue';
import DashboardView from './components/DashboardView.vue';
import UnitsView from './components/UnitsView.vue';

const routes = [
  { path: '/', component: LoginView },
  { path: '/dashboard', component: DashboardView },
  { path: '/units', component: UnitsView }
];

export default createRouter({
  history: createWebHistory(),
  routes
});
