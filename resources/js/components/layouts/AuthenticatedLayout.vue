<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- Sidebar -->
    <aside 
      class="fixed top-0 left-0 h-screen bg-gradient-to-b from-gray-900 to-gray-800 shadow-2xl transform transition-all duration-300 ease-in-out z-40 rounded-r-3xl backdrop-blur-xl border-r-2 border-cyan-500/30 flex flex-col"
      :class="{ 
        '-translate-x-full': !isSidebarOpen, 
        'translate-x-0': isSidebarOpen, 
        'w-64': !isCollapsed && isSidebarOpen, 
        'w-20': isCollapsed && isSidebarOpen,
        'w-0': !isSidebarOpen
      }"
    >
      <!-- Logo -->
      <div class="h-16 flex flex-col items-center justify-center border-b border-cyan-500/20 bg-gradient-to-r from-gray-900/80 to-gray-800/80 rounded-tr-3xl flex-shrink-0">
        <div v-if="!isCollapsed && isSidebarOpen" class="flex items-center justify-center">
          <img 
            :src="logoImage" 
            alt="CondoAI Logo" 
            class="h-8 w-8 object-contain"
            @error="handleImageError"
          />
        </div>
        <div v-if="isCollapsed && isSidebarOpen" class="flex items-center justify-center">
          <img 
            :src="logoImage" 
            alt="CondoAI Logo" 
            class="h-8 w-8 object-contain"
            @error="handleImageError"
          />
        </div>
        <div v-if="selectedCondoName && !isCollapsed && isSidebarOpen" class="mt-1 flex items-center gap-1.5">
          <div class="h-1 w-1 rounded-full bg-cyan-400/60"></div>
          <span class="text-xs text-cyan-400/80 truncate max-w-[180px]">{{ selectedCondoName }}</span>
        </div>
      </div>

      <!-- Menu - Agora com scroll -->
      <nav class="flex-1 overflow-y-auto glass-scrollbar glass-scrollbar-cyan" v-if="isSidebarOpen">
        <div class="px-2 py-4 space-y-2">
          <template v-for="item in menuItems" :key="item.path">
            <!-- Item principal -->
            <div>
              <router-link
                v-if="!item.children"
                :to="item.path"
                class="flex items-center px-4 py-3 text-gray-300 rounded-2xl shadow-md transition-all duration-300 hover:bg-gradient-to-r hover:from-cyan-500/30 hover:to-pink-500/30 hover:text-cyan-400 group backdrop-blur-md relative"
                :class="{
                  'bg-gradient-to-r from-cyan-500/30 to-pink-500/30 text-cyan-400 shadow-xl': isCurrentRoute(item.path),
                  'justify-center': isCollapsed
                }"
              >
                <span v-html="item.icon" class="h-5 w-5 transition-all duration-250" :class="{ 'mr-3': isFullyExpanded }"></span>
                <span
                  class="transition-all duration-250 overflow-hidden whitespace-nowrap flex-1"
                  :class="{
                    'w-0 opacity-0': !isFullyExpanded,
                    'w-auto opacity-100': isFullyExpanded
                  }"
                >
                  {{ item.name }}
                </span>
                <!-- Badge de Notificação -->
                <span 
                  v-if="item.badge && isFullyExpanded" 
                  class="ml-auto px-2 py-0.5 text-xs font-semibold text-white rounded-full animate-pulse"
                  :class="item.badgeColor || 'bg-red-500'"
                >
                  {{ item.badge }}
                </span>
                <span 
                  v-if="item.badge && isCollapsed" 
                  class="absolute -top-2 -right-2 px-1.5 py-0.5 text-xs font-semibold text-white rounded-full animate-pulse"
                  :class="item.badgeColor || 'bg-red-500'"
                >
                  {{ item.badge }}
                </span>
                <span v-if="isCollapsed" class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-cyan-400 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg z-50">
                  {{ item.name }}
                  <span v-if="item.badge" class="ml-2 px-1.5 py-0.5 text-xs font-semibold text-white rounded-full" :class="item.badgeColor || 'bg-red-500'">
                    {{ item.badge }}
                  </span>
                </span>
              </router-link>

              <!-- Item com submenu -->
              <div v-else>
                <button
                  @click="toggleSubmenu(item.path)"
                  class="w-full flex items-center px-4 py-3 text-gray-300 rounded-2xl shadow-md transition-all duration-300 hover:bg-gradient-to-r hover:from-cyan-500/30 hover:to-pink-500/30 hover:text-cyan-400 group backdrop-blur-md relative"
                  :class="{
                    'bg-gradient-to-r from-cyan-500/30 to-pink-500/30 text-cyan-400 shadow-xl': isCurrentRoute(item.path) || item.children.some(child => isCurrentRoute(child.path)) || expandedSubmenus[item.path],
                    'justify-center': isCollapsed
                  }"
                >
                  <span v-html="item.icon" class="h-5 w-5 transition-all duration-250" :class="{ 'mr-3': isFullyExpanded }"></span>
                  <span
                    class="transition-all duration-250 overflow-hidden whitespace-nowrap flex-1 text-left"
                    :class="{
                      'w-0 opacity-0': !isFullyExpanded,
                      'w-auto opacity-100': isFullyExpanded
                    }"
                  >
                    {{ item.name }}
                  </span>
                  <!-- Badge de Notificação para submenu expandido -->
                  <span 
                    v-if="item.badge && isFullyExpanded" 
                    class="px-2 py-0.5 text-xs font-semibold text-white rounded-full animate-pulse mr-2"
                    :class="item.badgeColor || 'bg-red-500'"
                  >
                    {{ item.badge }}
                  </span>
                  <!-- Ícone de expansão -->
                  <svg 
                    v-if="isFullyExpanded"
                    class="h-4 w-4 transition-transform duration-200"
                    :class="{ 'rotate-90': expandedSubmenus[item.path] }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <!-- Badge de Notificação para submenu colapsado -->
                  <span 
                    v-if="item.badge && isCollapsed" 
                    class="absolute -top-2 -right-2 px-1.5 py-0.5 text-xs font-semibold text-white rounded-full animate-pulse"
                    :class="item.badgeColor || 'bg-red-500'"
                  >
                    {{ item.badge }}
                  </span>
                  <span v-if="isCollapsed" class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-cyan-400 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg z-50">
                    {{ item.name }}
                    <span v-if="item.badge" class="ml-2 px-1.5 py-0.5 text-xs font-semibold text-white rounded-full" :class="item.badgeColor || 'bg-red-500'">
                      {{ item.badge }}
                    </span>
                  </span>
                </button>

                <!-- Submenu -->
                <div 
                  v-if="item.children && (expandedSubmenus[item.path] || (isCollapsed && hoveredSubmenu === item.path))"
                  class="transition-all duration-300 ease-in-out overflow-hidden"
                  :class="{
                    'ml-4 mt-2 space-y-1 border-l-2 border-cyan-500/30 pl-4': !isCollapsed,
                    'absolute left-full top-0 ml-2 min-w-48 bg-gray-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-cyan-500/20 p-2 space-y-1 z-50': isCollapsed
                  }"
                  @mouseenter="handleSubmenuHover(item.path, true)"
                  @mouseleave="handleSubmenuHover(item.path, false)"
                >
                  <router-link
                    v-for="child in item.children"
                    :key="child.path"
                    :to="child.path"
                    class="flex items-center px-3 py-2 text-gray-400 rounded-xl transition-all duration-200 hover:bg-gradient-to-r hover:from-cyan-500/20 hover:to-pink-500/20 hover:text-cyan-300 group relative"
                    :class="{
                      'bg-gradient-to-r from-cyan-500/20 to-pink-500/20 text-cyan-300 shadow-lg': isCurrentRoute(child.path)
                    }"
                  >
                    <span v-html="child.icon" class="h-4 w-4 transition-all duration-250 mr-2 flex-shrink-0"></span>
                    <span class="text-sm font-medium truncate flex-1">{{ child.name }}</span>
                    <!-- Badge de Notificação para subitem -->
                    <span 
                      v-if="child.badge" 
                      class="ml-auto px-1.5 py-0.5 text-xs font-semibold text-white rounded-full animate-pulse"
                      :class="child.badgeColor || 'bg-red-500'"
                    >
                      {{ child.badge }}
                    </span>
                  </router-link>
                </div>
              </div>
            </div>
          </template>
        </div>
      </nav>

      <!-- User Info -->
      <div class="border-t border-cyan-500/20 bg-gradient-to-r from-gray-900/80 to-gray-800/80 rounded-br-3xl p-4 flex-shrink-0" v-if="isSidebarOpen">
        <div class="flex items-center gap-3" :class="{ 'justify-center': isCollapsed }">
          <div class="flex-shrink-0">
            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-cyan-500 to-pink-500 p-1 shadow-xl">
              <div class="h-full w-full rounded-full bg-gray-800 flex items-center justify-center">
                <span class="text-cyan-400 font-medium text-lg">{{ userInitials }}</span>
              </div>
            </div>
          </div>
          <div 
            class="transition-all duration-250 overflow-hidden"
            :class="{ 
              'w-0 opacity-0': !isFullyExpanded,
              'w-auto opacity-100 ml-2': isFullyExpanded
            }"
          >
            <p class="text-base font-medium text-gray-300">{{ userName }}</p>
            <button @click="logout" class="text-sm text-gray-400 hover:text-cyan-400 transition-colors duration-200 rounded-full px-3 py-1 mt-1 bg-gray-900/60 hover:bg-cyan-500/20 shadow">
              Sair
            </button>
          </div>
        </div>
      </div>

      <!-- Collapse Button - Apenas em desktop -->
      <button 
        v-if="isSidebarOpen && isDesktop"
        @click="toggleCollapse"
        class="absolute -right-6 top-20 p-3 rounded-full bg-gray-800/60 hover:bg-gray-700/80 transition-all duration-200 border border-cyan-500/30 hover:border-cyan-400/50 backdrop-blur-sm shadow-lg hover:shadow-cyan-500/20 group"
      >
        <svg class="h-5 w-5 text-cyan-400 group-hover:text-cyan-300 transform transition-all duration-200" 
             :class="{ 'rotate-180': isCollapsed }"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </aside>

    <!-- Overlay -->
    <div
      v-if="isSidebarOpen && !isDesktop"
      @click="toggleSidebar"
      class="fixed inset-0 bg-black/50 z-30"
    ></div>

    <!-- Mobile Top Bar -->
    <header
      id="mobile-top-bar"
      class="lg:hidden fixed top-0 left-0 right-0 h-16 flex items-center justify-between bg-gradient-to-r from-gray-900/95 to-gray-800/95 backdrop-blur-xl px-4 shadow-lg z-20 border-b border-cyan-500/20"
    >
      <h1 class="text-cyan-400 font-semibold text-lg">{{ pageTitle }}</h1>
      <button
        @click="toggleSidebar"
        class="relative p-2.5 rounded-xl bg-gray-800/60 hover:bg-gray-700/80 transition-all duration-200 border border-cyan-500/30 hover:border-cyan-400/50 backdrop-blur-sm group"
      >
        <div class="flex flex-col items-center justify-center w-6 h-6 space-y-1">
          <span 
            class="block w-5 h-0.5 bg-cyan-400 transition-all duration-200 group-hover:bg-cyan-300"
            :class="{ 'rotate-45 translate-y-1.5': isSidebarOpen }"
          ></span>
          <span 
            class="block w-5 h-0.5 bg-cyan-400 transition-all duration-200 group-hover:bg-cyan-300"
            :class="{ 'opacity-0': isSidebarOpen }"
          ></span>
          <span 
            class="block w-5 h-0.5 bg-cyan-400 transition-all duration-200 group-hover:bg-cyan-300"
            :class="{ '-rotate-45 -translate-y-1.5': isSidebarOpen }"
          ></span>
        </div>
      </button>
    </header>

    <!-- Main Content -->
    <main 
      class="min-h-screen transition-all duration-300 pt-16 lg:pt-0" 
      :class="{ 
        'lg:ml-64': isSidebarOpen && !isCollapsed && isDesktop,
        'lg:ml-20': isSidebarOpen && isCollapsed && isDesktop,
        'lg:ml-0': !isSidebarOpen || !isDesktop
      }"
    >
      <!-- Header -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 lg:py-8">
        <h2 class="hidden lg:block text-2xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-pink-500 mb-6">
          {{ pageTitle }}
        </h2>
        <slot></slot>
      </div>
    </main>
    
    <!-- Toast Container -->
    <ToastContainer />
  </div>
</template>


<script>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useNotifications } from '../../composables/useNotifications.js';
import ToastContainer from '../ToastContainer.vue';
import logoImage from '../../../images/condo_ai_quadrado.png';

export default {
  name: 'AuthenticatedLayout',
  components: {
    ToastContainer
  },
  props: { pageTitle: { type: String, required: true } },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const { notifications, getBadgeColor, simulateNotificationUpdate, loadNotificationsFromServer } = useNotifications();
    const isSidebarOpen = ref(window.innerWidth >= 1024);
    const isCollapsed = ref(false);
    const isDesktop = ref(window.innerWidth >= 1024);
    const isFullyExpanded = ref(false);

    const selectedCondoId = ref(localStorage.getItem('selectedCondominium'));
    const selectedCondoName = ref(localStorage.getItem('selectedCondominiumName'));
    
    const updateSelected = () => {
      selectedCondoId.value = localStorage.getItem('selectedCondominium');
      selectedCondoName.value = localStorage.getItem('selectedCondominiumName');
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
      
      // Simular atualizações de notificação (para demonstração)
      simulateNotificationUpdate();
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
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm00 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>`
      },
      {
        name: 'Correspondências',
        path: '/packages',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>`,
        badge: notifications.value.packages,
        badgeColor: getBadgeColor('packages', notifications.value.packages)
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
        path: '/common-areas',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7h18M3 12h18M3 17h18" />
               </svg>`,
        badge: notifications.value.areas > 0 ? notifications.value.areas : null,
        badgeColor: getBadgeColor('areas', notifications.value.areas),
        children: [
          {
            name: 'Áreas Comuns',
            path: '/common-areas',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>`
          },
          {
            name: 'Nova Área',
            path: '/common-areas/new',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>`
          }
        ]
      },
      {
        name: 'Relatórios',
        path: '/reports',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>`
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
<<<<<<< Updated upstream
      },
      {
        name: 'Manutenções',
        path: '/maintenance/dashboard',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h3m-6.75 3h9.75m-1.5 3h4.5m-6.75 3h9.75M17.25 21L3 7.5m14.25 13.5L3 7.5V3.75C3 3.336 3.336 3 3.75 3h16.5c.414 0 .75.336.75.75v16.5c0 .414-.336.75-.75.75h-3.75Z"></path></svg>`,
        badge: notifications.value.maintenance,
        badgeColor: getBadgeColor('maintenance', notifications.value.maintenance),
        children: [
          {
            name: 'Dashboard',
            path: '/maintenance/dashboard',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>`
          },
          {
            name: 'Minhas Tarefas Hoje',
            path: '/maintenance/my-today-tasks',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>`,
            badge: notifications.value.maintenanceTasks,
            badgeColor: getBadgeColor('maintenanceTasks', notifications.value.maintenanceTasks)
          },
          {
            name: 'Manutenções Concluídas',
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
            name: 'Áreas de Manutenção',
            path: '/maintenance/areas',
            icon: `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path></svg>`
          }
        ]
=======
>>>>>>> Stashed changes
      }
    ]);

    const menuItems = computed(() => {
      if (!selectedCondoId.value) {
        return baseItems;
      }
      return [...baseItems, ...condoItems.value];
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

    const logout = () => {
      localStorage.removeItem('token');
      localStorage.removeItem('selectedCondominium');
      localStorage.removeItem('selectedCondominiumName');
      localStorage.removeItem('user');
      
      router.push('/login');
    };

    const userName = computed(() => 'Usuário Exemplo');
    const userInitials = computed(() => 'UE');

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
  @apply bg-gradient-to-r from-cyan-500/20 to-pink-500/20 text-cyan-400;
}



/* Animação suave para submenus */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
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
    transform: translateY(-10px);
  }
}

.submenu-enter-active {
  animation: slideDown 0.3s ease-out;
}

.submenu-leave-active {
  animation: slideUp 0.3s ease-in;
}

/* Efeitos de hover melhorados */
.group:hover .group-hover\:shadow-cyan-500\/20 {
  box-shadow: 0 0 20px rgba(34, 211, 238, 0.2);
}

/* Tooltip para items colapsados */
.tooltip {
  visibility: hidden;
  opacity: 0;
  position: absolute;
  z-index: 1000;
  left: 100%;
  margin-left: 8px;
  padding: 8px 12px;
  background: rgba(31, 41, 55, 0.95);
  color: #22d3ee;
  border-radius: 8px;
  font-size: 0.875rem;
  white-space: nowrap;
  backdrop-filter: blur(8px);
  border: 1px solid rgba(34, 211, 238, 0.2);
  transition: all 0.2s ease;
}

.group:hover .tooltip {
  visibility: visible;
  opacity: 1;
}

/* Melhor indicador visual para item ativo */
.active-indicator {
  position: relative;
}

.active-indicator::before {
  content: '';
  position: absolute;
  left: -8px;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 24px;
  background: linear-gradient(to bottom, #22d3ee, #ec4899);
  border-radius: 2px;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.active-indicator.active::before {
  opacity: 1;
}

/* Efeito de pulsação para notificações */
@keyframes pulse-cyan {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(34, 211, 238, 0.4);
  }
  50% {
    box-shadow: 0 0 0 8px rgba(34, 211, 238, 0);
  }
}

.pulse-notification {
  animation: pulse-cyan 2s infinite;
}
</style> 