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
    path: '/condominium-select',
    name: 'condominium-select',
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
    path: '/units',
    name: 'units',
    component: () => import('../components/UnitsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/residents',
    name: 'residents',
    component: () => import('../components/ResidentsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/parking-spots',
    name: 'parking-spots',
    component: () => import('../components/ParkingSpotsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/vehicles',
    name: 'vehicles',
    component: () => import('../components/VehiclesView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/pets',
    name: 'pets',
    component: () => import('../components/PetsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/packages',
    name: 'packages',
    component: () => import('../components/PackagesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/common-areas',
    name: 'common-areas',
    component: () => import('../components/CommonAreasView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/reports',
    name: 'reports',
    component: () => import('../components/ReportsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
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

  console.log('Navigation Guard:', { to: to.path, isAuthenticated, hasCondo });

  // Se não está autenticado e a rota requer autenticação
  if (to.meta.requiresAuth && !isAuthenticated) {
    console.log('Redirecionando para login - não autenticado');
    next('/login');
  } 
  // Se está na página de login e já está autenticado
  else if (to.path === '/login' && isAuthenticated) {
    console.log('Já autenticado, redirecionando...');
    next(hasCondo ? '/dashboard' : '/condominium-select');
  } 
  // Se a rota requer condomínio e não tem condomínio selecionado
  else if (to.meta.requiresCondo && !hasCondo) {
    console.log('Redirecionando para seleção de condomínio - sem condomínio');
    next('/condominium-select');
  } 
  // Se está indo para a raiz e está autenticado
  else if (to.path === '/' && isAuthenticated) {
    console.log('Redirecionando da raiz...');
    next(hasCondo ? '/dashboard' : '/condominium-select');
  }
  // Caso contrário, prosseguir normalmente
  else {
    console.log('Prosseguindo normalmente para:', to.path);
    next();
  }
});

export default router;
