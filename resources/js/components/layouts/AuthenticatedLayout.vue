<template>
  <div class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-150 overflow-x-hidden">
    <!-- Sidebar -->
    <aside 
      class="fixed top-0 left-0 h-screen bg-white dark:bg-slate-900 shadow-lg transform transition-all duration-200 ease-in-out z-40 border-r border-slate-200 dark:border-slate-800 flex flex-col overflow-x-hidden"
      :class="{ 
        '-translate-x-full': !isSidebarOpen, 
        'translate-x-0': isSidebarOpen, 
        'w-64': !isCollapsed && isSidebarOpen, 
        'w-20': isCollapsed && isSidebarOpen,
        'w-0': !isSidebarOpen
      }"
    >
      <!-- Logo -->
      <div class="h-16 flex flex-col items-center justify-center border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex-shrink-0 px-2 overflow-hidden">
        <div class="flex items-center justify-center">
          <img 
            :src="logoImage" 
            alt="PrediHub Logo" 
            :class="isCollapsed ? 'h-8 w-8 object-contain' : 'h-12 w-auto object-contain'"
            @error="handleImageError"
          />
        </div>
        <div v-if="selectedCondoName && !isCollapsed && isSidebarOpen" class="mt-1 flex items-center gap-1.5 overflow-hidden">
          <div class="h-1.5 w-1.5 rounded-full bg-indigo-500 flex-shrink-0"></div>
          <span class="text-xs text-slate-500 dark:text-slate-400 font-medium truncate max-w-[180px]">{{ selectedCondoName }}</span>
        </div>
      </div>

      <!-- Menu de Navegação -->
      <nav class="flex-1 overflow-y-auto overflow-x-hidden glass-scrollbar p-3 space-y-1" v-if="isSidebarOpen">
        <template v-for="item in menuItems" :key="item.path">
          <!-- Item principal sem filhos -->
          <div v-if="!item.children">
            <router-link
              :to="item.path"
              @click="handleNavigate"
              class="flex items-center px-3 py-2 text-slate-600 dark:text-slate-400 rounded-lg transition-colors hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100 group relative font-medium text-sm"
              :class="{
                'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-semibold': isCurrentRoute(item.path),
                'justify-center': isCollapsed
              }"
              :title="isCollapsed ? item.name : ''"
            >
              <span v-html="item.icon" class="h-5 w-5 transition-all flex-shrink-0" :class="{ 'mr-3': !isCollapsed }"></span>
              <span v-if="!isCollapsed" class="truncate flex-1">{{ item.name }}</span>
              <!-- Badge de Notificação -->
              <span 
                v-if="item.badge && !isCollapsed" 
                class="ml-auto px-2 py-0.5 text-xs font-semibold text-white rounded-full"
                :class="item.badgeColor || 'bg-red-500'"
              >
                {{ item.badge }}
              </span>
              <span 
                v-if="item.badge && isCollapsed" 
                class="absolute -top-1 -right-1 px-1.5 py-0.5 text-[10px] font-semibold text-white rounded-full"
                :class="item.badgeColor || 'bg-red-500'"
              >
                {{ item.badge }}
              </span>
            </router-link>
          </div>

          <!-- Item com submenu -->
          <div v-else class="relative group">
            <div
              @click="handleParentClick(item)"
              class="w-full flex items-center px-3 py-2 text-slate-600 dark:text-slate-400 rounded-lg transition-colors hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100 group font-medium text-sm cursor-pointer"
              :class="{
                'bg-indigo-50/50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 font-semibold': isCurrentRoute(item.path) || item.children.some(child => isCurrentRoute(child.path)) || expandedSubmenus[item.path],
                'justify-center': isCollapsed
              }"
              :title="isCollapsed ? item.name : ''"
            >
              <span v-html="item.icon" class="h-5 w-5 transition-all flex-shrink-0" :class="{ 'mr-3': !isCollapsed }"></span>
              <span v-if="!isCollapsed" class="truncate flex-1 text-left">
                {{ item.name }}
              </span>
              <!-- Badge -->
              <span 
                v-if="item.badge && !isCollapsed" 
                class="px-2 py-0.5 text-xs font-semibold text-white rounded-full mr-2"
                :class="item.badgeColor || 'bg-red-500'"
              >
                {{ item.badge }}
              </span>
              <!-- Ícone de expansão -->
              <svg 
                v-if="!isCollapsed"
                class="h-4 w-4 transition-transform duration-200 text-slate-400 flex-shrink-0"
                :class="{ 'rotate-90': expandedSubmenus[item.path] }"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>

            <!-- Submenu Inline (quando expandido) -->
            <div 
              v-if="!isCollapsed && item.children && expandedSubmenus[item.path]"
              class="ml-3 mt-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 pl-3 transition-all duration-200"
            >
              <router-link
                v-for="child in item.children"
                :key="child.path"
                :to="child.path"
                @click="handleNavigate"
                class="flex items-center px-2.5 py-1.5 text-slate-500 dark:text-slate-400 rounded-md text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100 group relative"
                :class="{
                  'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold': isCurrentRoute(child.path)
                }"
              >
                <span v-html="child.icon" class="h-4 w-4 mr-2 flex-shrink-0"></span>
                <span class="truncate flex-1">{{ child.name }}</span>
                <span 
                  v-if="child.badge" 
                  class="ml-auto px-1.5 py-0.5 text-[10px] font-semibold text-white rounded-full"
                  :class="child.badgeColor || 'bg-red-500'"
                >
                  {{ child.badge }}
                </span>
              </router-link>
            </div>

            <!-- Submenu Flyout (quando recolhido, acessível no hover) -->
            <div 
              v-if="isCollapsed && item.children"
              class="hidden group-hover:block absolute left-full top-0 ml-2 min-w-48 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 p-2 space-y-1 z-50 pointer-events-auto"
            >
              <div class="px-2.5 py-1 text-xs font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 mb-1">
                {{ item.name }}
              </div>
              <router-link
                v-for="child in item.children"
                :key="child.path"
                :to="child.path"
                @click="handleNavigate"
                class="flex items-center px-2.5 py-1.5 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100"
                :class="{
                  'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold': isCurrentRoute(child.path)
                }"
              >
                <span v-html="child.icon" class="h-4 w-4 mr-2 flex-shrink-0"></span>
                <span class="truncate flex-1">{{ child.name }}</span>
              </router-link>
            </div>
          </div>
        </template>
      </nav>

      <!-- User Info (Sóbrio, sem botão redundante de sair) -->
      <div class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-3.5 flex-shrink-0 overflow-hidden" v-if="isSidebarOpen">
        <div class="flex items-center gap-3" :class="{ 'justify-center': isCollapsed }">
          <div class="flex-shrink-0">
            <div class="h-9 w-9 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
              {{ userInitials }}
            </div>
          </div>
          <div v-if="!isCollapsed" class="overflow-hidden">
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate max-w-[130px]">{{ userName }}</p>
            <p class="text-xs text-slate-400 truncate max-w-[130px]">{{ selectedCondoName || 'PrediHub' }}</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Overlay Mobile -->
    <div
      v-if="isSidebarOpen && !isDesktop"
      @click="toggleSidebar"
      class="fixed inset-0 bg-black/50 z-30"
    ></div>

    <!-- Main Content com TopBar Global -->
    <div 
      class="min-h-screen transition-all duration-300 flex flex-col"
      :class="{ 
        'lg:ml-64': isSidebarOpen && !isCollapsed && isDesktop,
        'lg:ml-20': isSidebarOpen && isCollapsed && isDesktop,
        'lg:ml-0': !isSidebarOpen || !isDesktop
      }"
    >
      <!-- TopBar Global Unificado -->
      <AppTopBar
        id="mobile-top-bar"
        :page-title="pageTitle"
        :selected-condo-name="selectedCondoName"
        :user-name="userName"
        :user-initials="userInitials"
        :is-desktop="isDesktop"
        :is-sidebar-open="isSidebarOpen"
        :is-collapsed="isCollapsed"
        @toggle-sidebar="toggleSidebar"
        @toggle-collapse="toggleCollapse"
        @logout="logout"
      />

      <!-- Área de Conteúdo da Tela -->
      <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <slot></slot>
      </main>
    </div>
    
    <!-- Toast Container -->
    <ToastContainer />
  </div>
</template>


<script>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useNotifications } from '../../composables/useNotifications.js';
import { useUser } from '../../composables/useUser.js';
import auth from '../../services/auth.js';
import ToastContainer from '../ToastContainer.vue';
import AppTopBar from './AppTopBar.vue';
import { usePredihubLogo } from '../../composables/usePredihubLogo.js';

export default {
  name: 'AuthenticatedLayout',
  components: {
    ToastContainer,
    AppTopBar
  },
  props: { pageTitle: { type: String, required: true } },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const { notifications, getBadgeColor, loadNotificationsFromServer } = useNotifications();
    const { user } = useUser();
    const { logo: logoImage } = usePredihubLogo();
    const isSidebarOpen = ref(window.innerWidth >= 1024);
    const isCollapsed = ref(false);
    const isDesktop = ref(window.innerWidth >= 1024);
    const isFullyExpanded = ref(false);

    const selectedCondoId = ref(localStorage.getItem('selectedCondominium'));
    const selectedCondoName = ref(localStorage.getItem('selectedCondominiumName'));

    const allowedModules = computed(() => {
      const permissions = {};
      if (user.value && user.value.profile && user.value.profile.modules) {
        user.value.profile.modules.forEach(m => {
          if (m.pivot && m.pivot.can_view) {
            permissions[m.name] = true;
          }
        });
      }
      return permissions;
    });
    
    const updateSelected = () => {
      selectedCondoId.value = localStorage.getItem('selectedCondominium');
      selectedCondoName.value = localStorage.getItem('selectedCondominiumName');
      loadNotificationsFromServer();
    };
    
    // Escutar mudanças no localStorage
    window.addEventListener('storage', updateSelected);
    
    // Também escutar mudanças customizadas
    window.addEventListener('condominiumSelected', updateSelected);

    const handleResize = () => {
      const isLargeScreen = window.innerWidth >= 1024;
      isDesktop.value = isLargeScreen;
      
      if (isLargeScreen) {
        isSidebarOpen.value = true;
        // Se não estiver colapsado, definir como totalmente expandido após a animação
        if (!isCollapsed.value) {
          setTimeout(() => {
            isFullyExpanded.value = true;
          }, 200);
        }
      } else {
        isSidebarOpen.value = false;
        isCollapsed.value = false; // Reset collapse em mobile
        isFullyExpanded.value = false;
      }
    };

    window.addEventListener('resize', handleResize);
    onMounted(() => {
      handleResize();
      // Inicializar isFullyExpanded baseado no estado inicial
      if (isSidebarOpen.value && !isCollapsed.value) {
        isFullyExpanded.value = true;
      }
      
      // Carregar notificações do servidor
      loadNotificationsFromServer();
    });
    onBeforeUnmount(() => {
      window.removeEventListener('resize', handleResize);
      window.removeEventListener('storage', updateSelected);
      window.removeEventListener('condominiumSelected', updateSelected);
    });

    const baseItems = [
      {
        name: 'Dashboard',
        path: '/dashboard',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>`
      }
    ];

    const condoItems = computed(() => [
      {
        name: 'Unidades',
        path: '/units-list',
        module: 'Unidades',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>`,
        children: [
          {
            name: 'Visualizar Unidades',
            path: '/units-list',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>`
          },
          {
            name: 'Gerenciar Estrutura',
            path: '/units',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  </svg>`
          }
        ]
      },
      {
        name: 'Moradores',
        path: '/residents',
        module: 'Moradores',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm00 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>`
      },
      {
        name: 'Correspondências',
        path: '/packages',
        module: 'Correspondências',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>`,
        badge: notifications.value.packages,
        badgeColor: getBadgeColor('packages', notifications.value.packages)
      },
      {
        name: 'Ocorrências',
        path: '/occurrences',
        module: 'Ocorrências',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
        // badge: notifications.value.occurrences, // Adicionar se houver sistema de notificação para ocorrências
        // badgeColor: getBadgeColor('occurrences', notifications.value.occurrences)
      },
      {
        name: 'Vagas',
        path: '/parking-spots',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 17v-6h5a3 3 0 000-6H6v14h2zm2-8h3a1 1 0 010 2h-3V9z" />
              </svg>`
      },
      {
        name: 'Veículos',
        path: '/vehicles',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM21 17a2 2 0 11-4 0 2 2 0 014 0zM13 6h5l3 4v6a1 1 0 01-1 1h-2m-10 0H3a1 1 0 01-1-1v-6l3-4h5m0 0V3a1 1 0 011-1h2a1 1 0 011 1v3" />
              </svg>`,
        badge: notifications.value.vehicles > 0 ? notifications.value.vehicles : null,
        badgeColor: getBadgeColor('vehicles', notifications.value.vehicles),
        children: [
          {
            name: 'Listar Veículos',
            path: '/vehicles',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>`
          },
          {
            name: 'Cadastrar Veículo',
            path: '/vehicles/create',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>`
          }
        ]
      },
      {
        name: 'Pets',
        path: '/pets',
        icon: `<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM16.5 6C17.6 6 18.5 6.9 18.5 8C18.5 9.1 17.6 10 16.5 10C15.4 10 14.5 9.1 14.5 8C14.5 6.9 15.4 6 16.5 6ZM7.5 6C8.6 6 9.5 6.9 9.5 8C9.5 9.1 8.6 10 7.5 10C6.4 10 5.5 9.1 5.5 8C5.5 6.9 6.4 6 7.5 6ZM12 11.5C15 11.5 17.5 14 17.5 17.5C17.5 20 15.5 22 12 22C8.5 22 6.5 20 6.5 17.5C6.5 14 9 11.5 12 11.5Z"/>
              </svg>`,
        badge: notifications.value.pets > 0 ? notifications.value.pets : null,
        badgeColor: getBadgeColor('pets', notifications.value.pets),
        children: [
          {
            name: 'Listar Pets',
            path: '/pets',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>`
          },
          {
            name: 'Cadastrar Pet',
            path: '/pets/create',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>`
          }
        ]
      },
      {
        name: 'Áreas',
        path: '/areas',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7h18M3 12h18M3 17h18" />
               </svg>`,
        badge: notifications.value.areas > 0 ? notifications.value.areas : null,
        badgeColor: getBadgeColor('areas', notifications.value.areas),
        children: [
          {
            name: 'Áreas Comuns',
            path: '/areas',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>`
          },
          {
            name: 'Reservas',
            path: '/bookings',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>`
          }
        ]
      },
      {
        name: 'Assembleias',
        path: '/assemblies',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
              </svg>`
      },
      {
        name: 'Relatórios',
        path: '/reports',
        module: 'Relatórios',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>`
      },
      {
        name: 'Financeiro',
        path: '/finance',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0-6a2 2 0 00-2 2v1H9a1 1 0 000 2h1v2H9a1 1 0 100 2h1v2H9a1 1 0 100 2h1v1a2 2 0 104 0v-1h1a1 1 0 100-2h-1v-2h1a1 1 0 100-2h-1V7h1a1 1 0 100-2h-1V4a2 2 0 00-2-2z" />
               </svg>`,
        children: [
          {
            name: 'Dashboard',
            path: '/finance/dashboard',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>`
          },
          {
            name: 'Contas',
            path: '/finance/accounts',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4c0 .55.45 1 1 1h3v13h4V8h3c.55 0 1-.45 1-1V3H5z" />
                  </svg>`
          },
          {
            name: 'Lançamentos',
            path: '/finance/transactions',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>`
          },
          {
            name: 'Fluxo de Caixa',
            path: '/finance/cash-flow',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>`
          },
          {
            name: 'Solicitações de Despesa',
            path: '/finance/expense-requests',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>`
          },
          {
            name: 'Orçamento Anual',
            path: '/finance/budget',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 14h.01M9 11h.01M12 11h.01M15 11h.01M5.75 21H18.25a2 2 0 002-2V7.75a2 2 0 00-2-2H5.75a2 2 0 00-2 2v11.25a2 2 0 002 2z"></path></svg>`
          },
          {
            name: 'Cotações e Compras',
            path: '/finance/procurements',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0-6a2 2 0 00-2 2v1H9a1 1 0 000 2h1v2H9a1 1 0 100 2h1v2H9a1 1 0 100-2h-1v-2h-1V7h1a1 1 0 100-2h-1V4a2 2 0 00-2-2z"></path></svg>`
          },
          {
            name: 'Relatórios',
            path: '/finance/reports',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>`
          }
        ]
      },
      {
        name: 'Perfis e Privilégios',
        path: '/perfis',
        module: 'Usuários',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m0 0a4 4 0 11-8 0 4 4 0 018 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              </svg>`,
        children: [
          {
            name: 'Gerenciar Perfis',
            path: '/perfis',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>`
          },
          {
            name: 'Dashboard de Análise',
            path: '/perfis/dashboard',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>`
          }
        ]
      },
      {
        name: 'Segurança',
        path: '/security/my-passkeys',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l7 4v5c0 4.2-2.7 8-7 9-4.3-1-7-4.8-7-9V7l7-4z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
              </svg>`,
        children: [
          {
            name: 'Logs de Acesso',
            path: '/security/access-logs',
            module: 'Segurança',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                  </svg>`
          },
          {
            name: 'Passkeys dos Usuários',
            path: '/security/user-passkeys',
            module: 'Segurança',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a4 4 0 11-8 0 4 4 0 018 0zM3 21a8 8 0 0116 0" />
                  </svg>`
          },
          {
            name: 'Minhas Passkeys',
            path: '/security/my-passkeys',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.74 5.74L11 17H9v2H7v2H4v-3l6.26-6.26A6 6 0 1121 9z" />
                  </svg>`
          }
        ]
      },
      {
        name: 'Avisos',
        path: '/notices',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V4a2 2 0 10-4 0v1.882a5.002 5.002 0 00-4.472 4.975 4.993 4.993 0 003.386 4.613V20a1 1 0 001 1h2a1 1 0 001-1v-4.53a4.993 4.993 0 003.386-4.613A5.002 5.002 0 0011 5.882z"></path></svg>`,
        badge: notifications.value.notices,
        badgeColor: getBadgeColor('notices', notifications.value.notices)
      },
      {
        name: 'Documentos',
        path: '/documents',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>`
      },
      {
        name: 'Classificados',
        path: '/classifieds',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>`
      },
      {
        name: 'Manutenção',
        path: '/maintenance/dashboard',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h3m-6.75 3h9.75m-1.5 3h4.5m-6.75 3h9.75M17.25 21L3 7.5m14.25 13.5L3 7.5V3.75C3 3.336 3.336 3 3.75 3h16.5c.414 0 .75.336.75.75v16.5c0 .414-.336.75-.75.75h-3.75Z"></path></svg>`,
        badge: notifications.value.maintenance || notifications.value.maintenanceTasks,
        badgeColor: getBadgeColor('maintenance', notifications.value.maintenance || notifications.value.maintenanceTasks),
        children: [
          {
            name: 'Dashboard',
            path: '/maintenance/dashboard',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>`
          },
          {
            name: 'Minhas Tarefas',
            path: '/maintenance/my-today-tasks',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 12l2 2 4-4"></path></svg>`,
            badge: notifications.value.maintenanceTasks,
            badgeColor: getBadgeColor('maintenanceTasks', notifications.value.maintenanceTasks)
          },
          {
            name: 'Tarefas de Hoje',
            path: '/maintenance/my-today-tasks',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>`,
            badge: notifications.value.maintenanceTasks,
            badgeColor: getBadgeColor('maintenanceTasks', notifications.value.maintenanceTasks)
          },
          {
            name: 'Concluídas',
            path: '/maintenance/my-completed',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
          },
          {
            name: 'Agendamentos',
            path: '/maintenance/schedules',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>`
          },
          {
            name: 'Equipamentos',
            path: '/maintenance/equipment',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>`
          },
          {
            name: 'Áreas',
            path: '/maintenance/areas',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path></svg>`
          }
        ]
      },
      // Itens específicos da Portaria/Admin - Idealmente condicionados por perfil
      {
        name: 'Controle de Acesso', // Portaria
        path: '/access-control/dashboard',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>`,
        // profile: ['Portaria', 'Admin'] // Exemplo de como poderia ser
      },
      {
        name: 'Medidas Protetivas', // Portaria/Admin
        path: '/protective-measures',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`,
        // profile: ['Portaria', 'Admin']
      },
      // Item para Morador
      {
        name: 'Minhas Autorizações', // Morador
        path: '/my-authorizations',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1 0 21 9z"></path></svg>`,
        // profile: ['Morador']
        children: [
          {
            name: 'Minhas Autorizações',
            path: '/my-authorizations',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>`
          },
          {
            name: 'Nova Autorização',
            path: '/access-requests/create',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>`
          },
          {
            name: 'Histórico de Autorizações',
            path: '/access-requests/history',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
          },
          {
            name: 'Visitantes Autorizados',
            path: '/visitors/authorized',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>`
          }
        ]
      }
    ]);

    const filteredCondoItems = computed(() => {
      return condoItems.value
        .map(item => {
          if (item.children) {
            const children = item.children.filter(child => {
              if (!child) {
                return false;
              }

              return !child.module || allowedModules.value[child.module];
            });
            return { ...item, children };
          }
          return item;
        })
        .filter(item => {
          if (item.module && !allowedModules.value[item.module]) {
            return item.children && item.children.length > 0;
          }
          return true;
        });
    });

    const menuItems = computed(() => {
      if (!selectedCondoId.value) {
        return baseItems;
      }
      return [...baseItems, ...filteredCondoItems.value];
    });

    const toggleSidebar = () => { 
      isSidebarOpen.value = !isSidebarOpen.value; 
      // Em mobile, sempre expandir quando abrir
      if (isSidebarOpen.value && !isDesktop.value) {
        isCollapsed.value = false;
      }
      
      // Controlar quando o menu está totalmente expandido
      if (isSidebarOpen.value && !isCollapsed.value) {
        // Delay para mostrar o texto apenas após a animação terminar
        setTimeout(() => {
          isFullyExpanded.value = true;
        }, 200); // Reduzido para resposta mais rápida
      } else {
        isFullyExpanded.value = false;
      }
    };
    
    const toggleCollapse = () => { 
      isCollapsed.value = !isCollapsed.value; 
      
      // Controlar quando o menu está totalmente expandido
      if (!isCollapsed.value) {
        // Delay para mostrar o texto apenas após a animação terminar
        setTimeout(() => {
          isFullyExpanded.value = true;
        }, 200); // Reduzido para resposta mais rápida
      } else {
        isFullyExpanded.value = false;
      }
    };

    const isCurrentRoute = (path) => {
      if (path === route.path) return true;
      // Check if the current route is a child of the given path
      if (path.endsWith('/')) {
        return route.path.startsWith(path);
      } else {
        return route.path.startsWith(path + '/');
      }
    };

    const logout = async () => {
      await auth.logout();
    };

    const userName = computed(() => user.value?.name || 'Usuário');
    const userInitials = computed(() => {
      if (!user.value?.name) return 'U';
      return user.value.name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
    });

    const handleImageError = (event) => {
      console.error('Erro ao carregar imagem:', event.target.src);
      // Fallback para texto se a imagem falhar
      event.target.style.display = 'none';
    };

    const expandedSubmenus = ref({});
    const hoveredSubmenu = ref(null);

    // Auto-expandir submenu baseado na rota atual
    const initializeSubmenus = () => {
      const currentPath = route.path;
      
      // Para cada item com children, verificar se a rota atual pertence a esse submenu
      const itemsWithChildren = menuItems.value.filter(item => item.children);
      
      itemsWithChildren.forEach(item => {
        const hasActiveChild = item.children.some(child => isCurrentRoute(child.path));
        if (hasActiveChild || isCurrentRoute(item.path)) {
          expandedSubmenus.value[item.path] = true;
        }
      });
    };

    const toggleSubmenu = (path) => {
      expandedSubmenus.value[path] = !expandedSubmenus.value[path];
    };

    const handleSubmenuHover = (path, isHovered) => {
      hoveredSubmenu.value = isHovered ? path : null;
    };

    const handleNavigate = () => {
      if (!isDesktop.value) {
        isSidebarOpen.value = false;
      }
    };

    const handleParentClick = (item) => {
      if (isCollapsed.value) {
        if (item.path) {
          router.push(item.path);
        } else if (item.children && item.children.length > 0) {
          router.push(item.children[0].path);
        }
        if (!isDesktop.value) {
          isSidebarOpen.value = false;
        }
      } else {
        expandedSubmenus.value[item.path] = !expandedSubmenus.value[item.path];
      }
    };

    // Observar mudanças de rota para atualizar submenus
    watch(route, () => {
      initializeSubmenus();
    }, { immediate: true });

    return {
      isSidebarOpen,
      isCollapsed,
      isDesktop,
      isFullyExpanded,
      menuItems,
      selectedCondoName,
      toggleSidebar,
      toggleCollapse,
      isCurrentRoute,
      handleNavigate,
      handleParentClick,
      logout,
      userName,
      userInitials,
      handleImageError,
      logoImage,
      expandedSubmenus,
      hoveredSubmenu,
      toggleSubmenu,
      handleSubmenuHover,
      initializeSubmenus
    };
  }
};
</script>
<style scoped>
.router-link-active {
  @apply bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-semibold;
}

/* Animação suave para submenus */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideUp {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-6px);
  }
}

.submenu-enter-active {
  animation: slideDown 0.2s ease-out;
}

.submenu-leave-active {
  animation: slideUp 0.15s ease-in;
}
</style>
