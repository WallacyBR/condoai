import { createRouter, createWebHistory } from 'vue-router';
import LoginView from './components/LoginView.vue';
import DashboardView from './components/DashboardView.vue';

const routes = [
  { path: '/', component: LoginView },
  { path: '/dashboard', component: DashboardView }
];

export default createRouter({
  history: createWebHistory(),
  routes
});
