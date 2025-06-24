<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- Sidebar -->
    <aside 
      class="fixed top-0 left-0 h-screen bg-gradient-to-b from-gray-900 to-gray-800 shadow-2xl transform transition-all duration-300 ease-in-out z-40 rounded-r-3xl backdrop-blur-xl border-r-2 border-cyan-500/30"
      :class="{ 
        '-translate-x-full': !isSidebarOpen, 
        'translate-x-0': isSidebarOpen, 
        'w-64': !isCollapsed && isSidebarOpen, 
        'w-20': isCollapsed && isSidebarOpen,
        'w-0': !isSidebarOpen
      }"
    >
      <!-- Logo -->
      <div class="h-16 flex flex-col items-center justify-center border-b border-cyan-500/20 bg-gradient-to-r from-gray-900/80 to-gray-800/80 rounded-tr-3xl">
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

      <!-- Menu -->
      <nav class="mt-8" v-if="isSidebarOpen">
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
            <span v-html="item.icon" class="h-5 w-5 transition-all duration-250" :class="{ 'mr-3': isFullyExpanded }"></span>
            <span 
              class="transition-all duration-250 overflow-hidden whitespace-nowrap"
              :class="{ 
                'w-0 opacity-0': !isFullyExpanded,
                'w-auto opacity-100': isFullyExpanded
              }"
            >
              {{ item.name }}
            </span>
            <span v-if="isCollapsed" class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-cyan-400 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg">
              {{ item.name }}
            </span>
          </router-link>
        </div>
      </nav>

      <!-- User Info -->
      <div class="absolute bottom-0 w-full p-4 border-t border-cyan-500/20 bg-gradient-to-r from-gray-900/80 to-gray-800/80 rounded-br-3xl" v-if="isSidebarOpen">
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
  </div>
</template>


<script>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import logoImage from '../../../images/condo_ai_quadrado.png';

export default {
  name: 'AuthenticatedLayout',
  props: { pageTitle: { type: String, required: true } },
  setup() {
    const router = useRouter();
    const route = useRoute();
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
      },
      {
        name: 'Avisos',
        path: '/notices',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V4a2 2 0 10-4 0v1.882a5.002 5.002 0 00-4.472 4.975 4.993 4.993 0 003.386 4.613V20a1 1 0 001 1h2a1 1 0 001-1v-4.53a4.993 4.993 0 003.386-4.613A5.002 5.002 0 0011 5.882z"></path></svg>`
      },
      {
        name: 'Documentos',
        path: '/documents',
        icon: `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>`
      }
    ];

    const menuItems = computed(() => {
      if (!selectedCondoId.value) {
        return baseItems;
      }
      return [...baseItems, ...condoItems];
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