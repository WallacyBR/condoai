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
    component: () => import('../components/UnitsStructureView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/units-management',
    name: 'units-management',
    component: () => import('../components/UnitsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/units/:id/edit',
    name: 'edit-unit',
    component: () => import('../pages/EditUnitPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/residents',
    name: 'residents',
    component: () => import('../components/ResidentsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/residents/new',
    name: 'new-resident',
    component: () => import('../pages/ResidentFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/residents/:id/edit',
    name: 'edit-resident',
    component: () => import('../pages/ResidentFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/units-list',
    name: 'units-list',
    component: () => import('../components/UnitsListView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/parking-spots',
    name: 'parking-spots.index',
    component: () => import('../components/ParkingSpotsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/parking-spots/create',
    name: 'parking-spots.create',
    component: () => import('../components/ParkingSpotCreateView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/vehicles',
    name: 'vehicles',
    component: () => import('../components/VehiclesView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/vehicles/create',
    name: 'vehicles.create',
    component: () => import('../pages/VehicleFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/vehicles/:id/edit',
    name: 'vehicles.edit',
    component: () => import('../pages/VehicleFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/pets',
    name: 'pets',
    component: () => import('../components/PetsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/pets/create',
    name: 'pets.create',
    component: () => import('../pages/PetFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/pets/:id/edit',
    name: 'pets.edit',
    component: () => import('../pages/PetFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/packages',
    name: 'packages',
    component: () => import('../components/PackagesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/packages/new',
    name: 'packages.new',
    component: () => import('../pages/PackageFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/packages/:id/edit',
    name: 'packages.edit',
    component: () => import('../pages/PackageFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/packages/deliver',
    name: 'packages.deliver',
    component: () => import('../pages/PackageDeliveryPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Rotas de Áreas Comuns
  {
    path: '/areas',
    name: 'areas',
    component: () => import('../pages/AreasPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Redirecionamento para compatibilidade com rota antiga
  {
    path: '/common-areas',
    redirect: '/areas'
  },
  {
    path: '/common-areas/new',
    name: 'common-areas.new',
    component: () => import('../pages/CommonAreaFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/common-areas/:id/edit',
    name: 'common-areas.edit',
    component: () => import('../pages/CommonAreaFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Rotas de Reservas
  {
    path: '/bookings',
    name: 'bookings',
    component: () => import('../pages/BookingsPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/bookings/new',
    name: 'bookings.new',
    component: () => import('../pages/BookingFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/bookings/:id/edit',
    name: 'bookings.edit',
    component: () => import('../pages/BookingFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/bookings/:id',
    name: 'bookings.show',
    component: () => import('../pages/BookingDetailPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/common-areas/:id/bookings',
    name: 'common-areas.bookings',
    component: () => import('../pages/AreaBookingsPage.vue'),
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
    component: () => import('../pages/ProfileManagementPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/perfis/dashboard',
    name: 'profiles.dashboard',
    component: () => import('../pages/ProfileAnalyticsDashboardPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/perfis/novo',
    name: 'profiles.create',
    component: () => import('../pages/ProfileFormPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/perfis/:id/editar',
    name: 'profiles.edit',
    component: () => import('../pages/ProfileFormPage.vue'),
    meta: { requiresAuth: true }
  },
  // Rotas para Avisos
  {
    path: '/notices',
    name: 'notices.list',
    component: () => import('../pages/NoticeListPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/notices/create',
    name: 'notices.create',
    component: () => import('../pages/NoticeCreatePage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/notices/:id/view', // ou apenas /notices/:id
    name: 'notices.view',
    component: () => import('../pages/NoticeViewPage.vue'),
    props: true, // Passa route.params como props para o componente
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Rota para Documentos
  {
    path: '/documents',
    name: 'documents.list',
    component: () => import('../pages/DocumentListPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Finance Module Routes
  {
    path: '/finance/accounts',
    name: 'finance.accounts',
    component: () => import('../finance/AccountsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/finance/transactions',
    name: 'finance.transactions',
    component: () => import('../finance/FinancialTransactionsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/finance/expense-requests',
    name: 'finance.expense-requests',
    component: () => import('../finance/ExpenseRequestsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/finance/reports',
    name: 'finance.reports',
    component: () => import('../finance/FinancialReportsView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/finance/dashboard',
    name: 'finance.dashboard',
    component: () => import('../finance/FinancialDashboardView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/finance/cash-flow',
    name: 'finance.cash-flow',
    component: () => import('../finance/CashFlowView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Maintenance Module Routes
  {
    path: '/maintenance/dashboard',
    name: 'maintenance.dashboard',
    component: () => import('../pages/maintenance/MaintenanceDashboardView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/areas',
    name: 'maintenance.areas',
    component: () => import('../pages/maintenance/MaintenanceAreasView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/areas/create',
    name: 'maintenance.areas.create',
    component: () => import('../pages/maintenance/MaintenanceAreaFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/areas/:id/edit',
    name: 'maintenance.areas.edit',
    component: () => import('../pages/maintenance/MaintenanceAreaFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/equipment',
    name: 'maintenance.equipment',
    component: () => import('../pages/maintenance/MaintenanceEquipmentView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/equipment/create',
    name: 'maintenance.equipment.create',
    component: () => import('../pages/maintenance/MaintenanceEquipmentFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/equipment/:id/edit',
    name: 'maintenance.equipment.edit',
    component: () => import('../pages/maintenance/MaintenanceEquipmentFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/schedules',
    name: 'maintenance.schedules',
    component: () => import('../pages/maintenance/MaintenanceSchedulesView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/schedules/create',
    name: 'maintenance.schedules.create',
    component: () => import('../pages/maintenance/MaintenanceScheduleFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/schedules/:id/edit',
    name: 'maintenance.schedules.edit',
    component: () => import('../pages/maintenance/MaintenanceScheduleFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/records/new/:scheduleId?',
    name: 'maintenance.records.new',
    component: () => import('../pages/maintenance/MaintenanceRecordForm.vue'),
    props: true,
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/records/:id',
    name: 'maintenance.records.detail',
    props: true,
    component: () => import('../pages/maintenance/MaintenanceRecordDetailView.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/my-today-tasks',
    name: 'maintenance.my-today-tasks',
    component: () => import('../pages/maintenance/MyDailyMaintenanceTasks.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/maintenance/my-completed',
    name: 'maintenance.my-completed',
    component: () => import('../pages/maintenance/MyCompletedMaintenances.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Public QR Code Report Page
  {
    path: '/qr/:hash',
    name: 'public.qr.report',
    component: () => import('../pages/PublicEquipmentReportPage.vue'),
    meta: { requiresAuth: false, requiresCondo: false }
  },
  {
    path: '/signature-test',
    name: 'signature-test',
    component: () => import('../pages/SignatureTestPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/fileupload-test',
    name: 'fileupload-test',
    component: () => import('../pages/FileUploadTestPage.vue'),
    meta: { requiresAuth: true }
  },
  // Rotas para Classificados
  {
    path: '/classifieds',
    name: 'classifieds.index',
    component: () => import('../pages/Classifieds/ClassifiedsIndexPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/classifieds/:id',
    name: 'classifieds.show',
    component: () => import('../pages/Classifieds/AdDetailsPage.vue'),
    props: true,
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/classifieds/create',
    name: 'classifieds.create',
    component: () => import('../pages/Classifieds/AdFormPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/classifieds/:id/edit',
    name: 'classifieds.edit',
    component: () => import('../pages/Classifieds/AdFormPage.vue'),
    props: true,
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/my-ads',
    name: 'classifieds.my-ads',
    component: () => import('../pages/Classifieds/MyAdsPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Occurrence Routes
  {
    path: '/occurrences',
    name: 'OccurrenceIndex',
    component: () => import('../pages/occurrences/OccurrenceIndexPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/occurrences/new',
    name: 'OccurrenceCreate',
    component: () => import('../pages/occurrences/OccurrenceCreatePage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/occurrences/:id/edit',
    name: 'OccurrenceEdit',
    component: () => import('../pages/occurrences/OccurrenceEditPage.vue'),
    props: true,
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/occurrences/:id',
    name: 'OccurrenceShow',
    component: () => import('../pages/occurrences/OccurrenceShowPage.vue'),
    props: true,
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Access Control Module Routes
  {
    path: '/access-control/dashboard',
    name: 'AccessControlDashboard',
    component: () => import('../pages/AccessControl/AccessControlDashboardPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true } // Portaria precisa de condomínio selecionado
  },
  {
    path: '/access-control/new-request',
    name: 'NewAccessRequest',
    component: () => import('../pages/AccessControl/NewAccessRequestPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Rota alternativa para compatibilidade
  {
    path: '/access-requests/create',
    name: 'AccessRequestsCreate',
    component: () => import('../pages/AccessControl/NewAccessRequestPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/access-control/requests/:id/record-entry',
    name: 'RecordAccessEntry',
    component: () => import('../pages/AccessControl/RecordAccessEntryPage.vue'), // Será criado
    props: true,
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Rota alternativa para compatibilidade
  {
    path: '/access-requests/create',
    name: 'AccessRequestsCreate',
    component: () => import('../pages/AccessControl/NewAccessRequestPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
   {
    path: '/access-control/requests/:id', // Rota de detalhes, se necessária
    name: 'AccessRequestDetails',
    component: () => import('../pages/AccessControl/AccessRequestDetailsPage.vue'), // Será criado
    props: true,
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Protective Measures Routes
  {
    path: '/protective-measures',
    name: 'ProtectiveMeasuresList',
    component: () => import('../pages/AccessControl/ProtectiveMeasuresListPage.vue'), // Será criado
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/protective-measures/new',
    name: 'NewProtectiveMeasure',
    component: () => import('../pages/AccessControl/ProtectiveMeasureFormPage.vue'), // Será criado
    meta: { requiresAuth: true, requiresCondo: true }
  },
  {
    path: '/protective-measures/:id/edit',
    name: 'EditProtectiveMeasure',
    component: () => import('../pages/AccessControl/ProtectiveMeasureFormPage.vue'), // Será criado
    props: true,
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Resident's Access Authorizations Page
  {
    path: '/my-authorizations',
    name: 'MyAuthorizations',
    component: () => import('../pages/AccessControl/MyAuthorizationsPage.vue'), // Será criado
    meta: { requiresAuth: true, requiresCondo: true } // Morador precisa de condomínio selecionado
  },
  // Access Request History Page
  {
    path: '/access-requests/history',
    name: 'AccessRequestHistory',
    component: () => import('../pages/AccessControl/AccessRequestHistoryPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
  // Authorized Visitors Page
  {
    path: '/visitors/authorized',
    name: 'AuthorizedVisitors',
    component: () => import('../pages/AccessControl/AuthorizedVisitorsPage.vue'),
    meta: { requiresAuth: true, requiresCondo: true }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation Guard
router.beforeEach(async (to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token');
  let hasCondo = !!localStorage.getItem('selectedCondominium');

  console.log('=== Navigation Guard Debug ===');
  console.log('De:', from.path);
  console.log('Para:', to.path);
  console.log('Autenticado:', isAuthenticated);
  console.log('Tem condomínio:', hasCondo);
  console.log('Meta da rota:', to.meta);
  console.log('=============================');

  // Se não está autenticado e a rota requer autenticação
  if (to.meta.requiresAuth && !isAuthenticated) {
    console.log('Redirecionando para login - não autenticado');
    next('/login');
    return;
  } 

  // Se está autenticado mas não tem condomínio, tentar buscar do backend
  if (isAuthenticated && !hasCondo && to.path !== '/login') {
    console.log('Usuário autenticado sem condomínio, buscando do backend...');
    try {
      // Importar dinamicamente o serviço de auth para evitar dependência circular
      const authModule = await import('../services/auth');
      const auth = authModule.default;
      const condominium = await auth.fetchCurrentCondominium();
      if (condominium) {
        hasCondo = true;
        console.log('Condomínio obtido do backend:', condominium.name);
      }
    } catch (error) {
      console.error('Erro ao buscar condomínio do backend:', error);
    }
  }
  
  // Se está na página de login e já está autenticado
  if (to.path === '/login' && isAuthenticated) {
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
