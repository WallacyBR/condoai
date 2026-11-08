<template>
  <header
    class="sticky top-0 z-30 flex h-16 w-full items-center justify-between border-b border-slate-200/80 bg-white/80 px-4 backdrop-blur-md transition-colors dark:border-slate-800/80 dark:bg-slate-900/80 sm:px-6 lg:px-8"
  >
    <!-- Lado Esquerdo: Toggle de Sidebar & Breadcrumb -->
    <div class="flex items-center space-x-3">
      <!-- Botão de alternar Sidebar (Mobile & Desktop) -->
      <button
        @click="handleToggleSidebar"
        type="button"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
        title="Recolher / Expandir Menu"
        aria-label="Alternar Menu"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Breadcrumbs / Título da Tela -->
      <nav class="hidden sm:flex items-center space-x-2 text-sm" aria-label="Breadcrumb">
        <router-link
          to="/dashboard"
          class="font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors"
        >
          PrediHub
        </router-link>
        <span class="text-slate-400 dark:text-slate-600">/</span>
        <span class="font-semibold text-slate-900 dark:text-slate-100">
          {{ pageTitle }}
        </span>
      </nav>
      <span class="sm:hidden font-semibold text-slate-900 dark:text-slate-100 truncate max-w-[160px]">
        {{ pageTitle }}
      </span>
    </div>

    <!-- Centro: Seletor / Indicador de Condomínio Ativo -->
    <div class="hidden md:flex items-center">
      <router-link
        to="/condominium-select"
        class="group flex items-center space-x-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-1.5 text-xs font-medium text-slate-700 transition hover:border-indigo-300 hover:bg-indigo-50/50 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300 dark:hover:border-indigo-500/50 dark:hover:bg-slate-800"
        title="Trocar Condomínio Ativo"
      >
        <svg class="h-3.5 w-3.5 text-indigo-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span class="truncate max-w-[200px]">{{ selectedCondoName || 'Selecionar Condomínio' }}</span>
        <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </router-link>
    </div>

    <!-- Lado Direito: Notificações, Tema e Perfil -->
    <div class="flex items-center space-x-2 sm:space-x-3">
      <!-- Central de Notificações -->
      <div class="relative" ref="notificationDropdownRef">
        <button
          @click="toggleNotificationDropdown"
          type="button"
          class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
          title="Notificações"
          aria-label="Abrir notificações"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span
            v-if="inboxUnreadCount > 0"
            class="absolute top-1.5 right-1.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white shadow-sm"
          >
            {{ inboxUnreadCount > 99 ? '99+' : inboxUnreadCount }}
          </span>
        </button>

        <!-- Dropdown de Notificações -->
        <div
          v-if="isNotificationOpen"
          class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-800 dark:bg-slate-900 z-50 transition-all"
        >
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
            <div class="flex items-center space-x-2">
              <h3 class="font-semibold text-slate-900 dark:text-slate-100">Notificações</h3>
              <span
                v-if="inboxUnreadCount > 0"
                class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300"
              >
                {{ inboxUnreadCount }} novas
              </span>
            </div>
            <button
              v-if="inboxUnreadCount > 0"
              @click="handleMarkAllInboxAsRead"
              class="text-xs text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400"
            >
              Limpar todas
            </button>
          </div>

          <div class="mt-3 space-y-2 max-h-72 overflow-y-auto">
            <div
              v-if="inboxUnreadCount === 0"
              class="py-8 text-center text-sm text-slate-500 dark:text-slate-400"
            >
              <svg class="mx-auto h-8 w-8 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              Nenhuma notificação pendente no momento.
            </div>

            <component
              v-for="item in inboxItems"
              :key="item.id"
              :is="item.action_url ? 'router-link' : 'div'"
              :to="item.action_url || undefined"
              @click="handleInboxItemClick(item)"
              class="flex items-start justify-between rounded-xl p-2.5 transition hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer"
            >
              <div class="flex items-start space-x-3 min-w-0">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                  🔔
                </div>
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ item.title }}</p>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2">{{ item.body }}</p>
                </div>
              </div>
            </component>
          </div>
        </div>
      </div>

      <!-- Alternador de Tema (Theme Switcher) -->
      <div class="relative" ref="themeDropdownRef">
        <button
          @click="toggleThemeDropdown"
          type="button"
          class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
          :title="`Tema atual: ${themeLabel}`"
          aria-label="Alternar tema visual"
        >
          <!-- Ícone Sol (Modo Claro) -->
          <svg v-if="theme === 'light'" class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <!-- Ícone Lua (Modo Escuro) -->
          <svg v-else-if="theme === 'dark'" class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
          </svg>
          <!-- Ícone Monitor (Automático / Sistema) -->
          <svg v-else class="h-5 w-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </button>

        <div
          v-if="isThemeOpen"
          class="absolute right-0 mt-2 w-36 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg dark:border-slate-800 dark:bg-slate-900 z-50"
        >
          <button
            @click="selectTheme('light')"
            class="flex w-full items-center space-x-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
            :class="{ 'text-indigo-600 dark:text-indigo-400 font-semibold': theme === 'light' }"
          >
            <span>☀️</span>
            <span>Claro</span>
          </button>
          <button
            @click="selectTheme('dark')"
            class="flex w-full items-center space-x-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
            :class="{ 'text-indigo-600 dark:text-indigo-400 font-semibold': theme === 'dark' }"
          >
            <span>🌙</span>
            <span>Escuro</span>
          </button>
          <button
            @click="selectTheme('system')"
            class="flex w-full items-center space-x-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
            :class="{ 'text-indigo-600 dark:text-indigo-400 font-semibold': theme === 'system' }"
          >
            <span>💻</span>
            <span>Sistema</span>
          </button>
        </div>
      </div>

      <!-- Menu do Usuário -->
      <div class="relative" ref="userDropdownRef">
        <button
          @click="toggleUserDropdown"
          type="button"
          class="flex items-center space-x-2 rounded-full p-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white shadow-sm">
            {{ userInitials }}
          </div>
          <span class="hidden lg:block text-xs font-medium text-slate-700 dark:text-slate-300 max-w-[120px] truncate">
            {{ userName }}
          </span>
          <svg class="hidden lg:block h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Dropdown de Usuário -->
        <div
          v-if="isUserOpen"
          class="absolute right-0 mt-2 w-48 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-800 dark:bg-slate-900 z-50"
        >
          <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-800">
            <p class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate">{{ userName }}</p>
            <p class="text-[11px] text-slate-400">Usuário do Sistema</p>
          </div>

          <div class="py-1">
            <router-link
              to="/security/my-passkeys"
              @click="isUserOpen = false"
              class="flex w-full items-center space-x-2 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/60"
            >
              <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
              </svg>
              <span>Minhas Passkeys</span>
            </router-link>

            <button
              @click="handleLogout"
              class="flex w-full items-center space-x-2 rounded-xl px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
            >
              <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span>Sair do Sistema</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useNotifications } from '../../composables/useNotifications.js';
import { useTheme } from '../../composables/useTheme.js';

const props = defineProps({
  pageTitle: { type: String, required: true },
  selectedCondoName: { type: String, default: '' },
  userName: { type: String, default: 'Usuário' },
  userInitials: { type: String, default: 'U' },
  isDesktop: { type: Boolean, default: true },
  isSidebarOpen: { type: Boolean, default: true },
  isCollapsed: { type: Boolean, default: false }
});

const emit = defineEmits(['toggle-sidebar', 'toggle-collapse', 'logout']);

const {
  inboxUnreadCount,
  inboxItems,
  markInboxNotificationRead,
  markAllInboxAsRead,
  loadInboxFromServer,
} = useNotifications();
const { theme, setTheme } = useTheme();

const isNotificationOpen = ref(false);
const isThemeOpen = ref(false);
const isUserOpen = ref(false);

const notificationDropdownRef = ref(null);
const themeDropdownRef = ref(null);
const userDropdownRef = ref(null);

const themeLabel = computed(() => {
  if (theme.value === 'light') return 'Claro';
  if (theme.value === 'dark') return 'Escuro';
  return 'Automático (Sistema)';
});

const handleToggleSidebar = () => {
  if (props.isDesktop) {
    emit('toggle-collapse');
  } else {
    emit('toggle-sidebar');
  }
};

const toggleNotificationDropdown = async () => {
  isNotificationOpen.value = !isNotificationOpen.value;
  isThemeOpen.value = false;
  isUserOpen.value = false;

  if (isNotificationOpen.value) {
    await loadInboxFromServer();
  }
};

const handleMarkAllInboxAsRead = async () => {
  await markAllInboxAsRead();
};

const handleInboxItemClick = async (item) => {
  if (!item.read_at) {
    await markInboxNotificationRead(item.id);
  }
  isNotificationOpen.value = false;
};

const toggleThemeDropdown = () => {
  isThemeOpen.value = !isThemeOpen.value;
  isNotificationOpen.value = false;
  isUserOpen.value = false;
};

const toggleUserDropdown = () => {
  isUserOpen.value = !isUserOpen.value;
  isNotificationOpen.value = false;
  isThemeOpen.value = false;
};

const selectTheme = (newTheme) => {
  setTheme(newTheme);
  isThemeOpen.value = false;
};

const handleLogout = () => {
  isUserOpen.value = false;
  emit('logout');
};

const handleClickOutside = (event) => {
  if (notificationDropdownRef.value && !notificationDropdownRef.value.contains(event.target)) {
    isNotificationOpen.value = false;
  }
  if (themeDropdownRef.value && !themeDropdownRef.value.contains(event.target)) {
    isThemeOpen.value = false;
  }
  if (userDropdownRef.value && !userDropdownRef.value.contains(event.target)) {
    isUserOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
