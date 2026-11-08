import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../components/LoginView.vue';
import DashboardView from '../components/DashboardView.vue';

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },
  {
    path: '/condominiums',
    name: 'condominiums',
    component: () => import('../components/CondominiumsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/residents',
    name: 'residents',
    component: () => import('../components/ResidentsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/reports',
    name: 'reports',
    component: () => import('../components/ReportsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('../components/SettingsView.vue'),
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation Guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token');
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login');
  } else if (to.path === '/login' && isAuthenticated) {
    next('/dashboard');
  } else {
    next();
  }
});

export default router; 