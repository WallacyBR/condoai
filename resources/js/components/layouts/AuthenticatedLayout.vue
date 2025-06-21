<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- Sidebar -->
    <aside 
      class="fixed top-0 left-0 h-screen bg-gradient-to-b from-gray-900 to-gray-800 shadow-2xl transform transition-all duration-300 ease-in-out z-30 rounded-r-3xl backdrop-blur-xl border-r-2 border-cyan-500/30"
      :class="{ '-translate-x-full': !isSidebarOpen, 'translate-x-0': isSidebarOpen, 'w-64': !isCollapsed, 'w-20': isCollapsed }"
    >
      <!-- Logo -->
      <div class="h-16 flex flex-col items-center justify-center border-b border-cyan-500/20 bg-gradient-to-r from-gray-900/80 to-gray-800/80 rounded-tr-3xl">
        <div v-if="!isCollapsed" class="flex items-center justify-center">
          <img 
            :src="logoImage" 
            alt="CondoAI Logo" 
            class="h-8 w-8 object-contain"
            @error="handleImageError"
          />
        </div>
        <span v-if="isCollapsed" class="text-xl font-bold text-cyan-400">C</span>
        <div v-if="selectedCondoName && !isCollapsed" class="mt-1 flex items-center gap-1.5">
          <div class="h-1 w-1 rounded-full bg-cyan-400/60"></div>
          <span class="text-xs text-cyan-400/80 truncate max-w-[180px]">{{ selectedCondoName }}</span>
        </div>
      </div>

      <!-- Menu -->
      <nav class="mt-8">
        <div class="px-2 space-y-3">
          <router-link 
            v-for="item in menuItems" 
            :key="item.path"
            :to="item.path"
            class="flex items-center px-4 py-3 text-gray-300 rounded-2xl shadow-md transition-all duration-300 hover:bg-gradient-to-r hover:from-cyan-500/30 hover:to-pink-500/30 hover:text-cyan-400 group backdrop-blur-md"
            :class="{ 
              'bg-gradient-to-r from-cyan-500/30 to-pink-500/30 text-cyan-400 shadow-xl': isCurrentRoute(item.path),
              'justify-center': isCollapsed
            }"
          >
            <span v-html="item.icon" class="h-5 w-5" :class="{ 'mr-3': !isCollapsed }"></span>
            <span :class="{ 'hidden': isCollapsed }">{{ item.name }}</span>
            <span v-if="isCollapsed" class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-cyan-400 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg">
              {{ item.name }}
            </span>
          </router-link>
        </div>
      </nav>

      <!-- User Info -->
      <div class="absolute bottom-0 w-full p-4 border-t border-cyan-500/20 bg-gradient-to-r from-gray-900/80 to-gray-800/80 rounded-br-3xl">
        <div class="flex items-center gap-3" :class="{ 'justify-center': isCollapsed }">
          <div class="flex-shrink-0">
            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-cyan-500 to-pink-500 p-1 shadow-xl">
              <div class="h-full w-full rounded-full bg-gray-800 flex items-center justify-center">
                <span class="text-cyan-400 font-medium text-lg">{{ userInitials }}</span>
              </div>
            </div>
          </div>
          <div class="ml-2" :class="{ 'hidden': isCollapsed }">
            <p class="text-base font-medium text-gray-300">{{ userName }}</p>
            <button @click="logout" class="text-sm text-gray-400 hover:text-cyan-400 transition-colors duration-200 rounded-full px-3 py-1 mt-1 bg-gray-900/60 hover:bg-cyan-500/20 shadow">
              Sair
            </button>
          </div>
        </div>
      </div>

      <!-- Collapse Button -->
      <button 
        @click="toggleCollapse"
        class="absolute -right-4 top-20 bg-gradient-to-r from-cyan-500 to-pink-500 p-2 rounded-full shadow-xl hover:shadow-cyan-500/25 transition-all duration-200 border-4 border-gray-900"
      >
        <svg class="h-4 w-4 text-white transform transition-transform duration-200" 
             :class="{ 'rotate-180': isCollapsed }"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </aside>

    <!-- Overlay -->
    <div
      v-if="isSidebarOpen"
      @click="toggleSidebar"
      class="fixed inset-0 bg-black/50 z-20 lg:hidden"
    ></div>

    <!-- Mobile Top Bar -->
    <header
      id="mobile-top-bar"
      class="lg:hidden fixed top-0 left-0 right-0 h-16 flex items-center justify-between bg-gradient-to-r from-gray-900 to-gray-800 px-4 shadow z-30"
    >
      <h1 class="text-cyan-400 font-semibold">{{ pageTitle }}</h1>
      <button
        @click="toggleSidebar"
        class="p-2 rounded-lg bg-gradient-to-r from-cyan-500 to-pink-500"
      >
        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </header>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen transition-all duration-300 pt-16 lg:pt-0" :class="{ 'lg:ml-20': isCollapsed }">
      <!-- Header -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-2xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-pink-500 mb-6">
          {{ pageTitle }}
        </h2>
        <slot></slot>
      </div>
    </main>
  </div>
</template>


<script>
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import logoImage from '../../../images/condo_ai_quadrado.png';

export default {
  name: 'AuthenticatedLayout',
  props: { pageTitle: { type: String, required: true } },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const isSidebarOpen = ref(false);
    const isCollapsed = ref(false);

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

    const condoItems = [
      {
        name: 'Unidades',
        path: '/units-list',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>`
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
              </svg>`
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
        name: 'Áreas',
        path: '/common-areas',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7h18M3 12h18M3 17h18" />
               </svg>`
      },
      {
        name: 'Relatórios',
        path: '/reports',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>`
      }
    ];

    const menuItems = computed(() => {
      if (!selectedCondoId.value) {
        return baseItems;
      }
      return [...baseItems, ...condoItems];
    });

    const toggleSidebar = () => { isSidebarOpen.value = !isSidebarOpen.value; };
    const toggleCollapse = () => { isCollapsed.value = !isCollapsed.value; };
    const isCurrentRoute = (path) => route.path === path;

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

    return {
      isSidebarOpen,
      isCollapsed,
      menuItems,
      selectedCondoName,
      toggleSidebar,
      toggleCollapse,
      isCurrentRoute,
      logout,
      userName,
      userInitials,
      handleImageError,
      logoImage
    };
  }
};
</script>
<style scoped>
.router-link-active {
  @apply bg-gradient-to-r from-cyan-500/20 to-pink-500/20 text-cyan-400;
}
</style> 