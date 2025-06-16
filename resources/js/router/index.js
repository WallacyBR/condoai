import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../components/LoginView.vue';
import DashboardView from '../components/DashboardView.vue';
import CondominiumSelectView from '../components/CondominiumSelectView.vue';

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
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/selecionar-condominio',
    name: 'select-condominium',
    component: CondominiumSelectView,
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
  },
  {
    path: '/perfis',
    name: 'profiles',
    component: () => import('../components/ProfilesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/areas-comuns',
    name: 'common-areas',
    component: () => import('../components/CommonAreaList.vue'),
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
  const hasCondo = !!localStorage.getItem('selectedCondominium');

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login');
  } else if (to.path === '/login' && isAuthenticated) {
    next(hasCondo ? '/dashboard' : '/selecionar-condominio');
  } else if (to.meta.requiresCondo && !hasCondo) {
    next('/selecionar-condominio');
  } else {
    next();
  }
});

export default router;
