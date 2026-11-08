<template>
  <div>
    <div v-if="loading" class="flex justify-center items-center py-16">
      <div class="flex flex-col items-center">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-300 border-t-indigo-600 dark:border-slate-700 dark:border-t-indigo-400 mb-3"></div>
        <p class="text-xs text-slate-500 dark:text-slate-400">Carregando condomínios...</p>
      </div>
    </div>
    
    <div v-else-if="error" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-5 text-rose-700 dark:text-rose-300 text-center shadow-sm">
      <svg class="h-8 w-8 text-rose-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
      </svg>
      <h3 class="text-sm font-semibold mb-1">Erro ao carregar condomínios</h3>
      <p class="text-xs">{{ error }}</p>
    </div>

    <div v-else>
      <div v-if="condominiums.length === 0" class="text-center py-16">
        <svg class="h-12 w-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-1">Nenhum condomínio encontrado</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">Entre em contato com o suporte ou administrador do sistema.</p>
      </div>

      <div v-else>
        <div v-if="!selectable" class="mb-4 text-left">
          <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
            {{ condominiums.length }} condomínio{{ condominiums.length !== 1 ? 's' : '' }} encontrado{{ condominiums.length !== 1 ? 's' : '' }}
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div 
            v-for="condo in condominiums" 
            :key="condo.id"
            class="bg-white dark:bg-slate-800/80 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700/80 hover:border-slate-300 dark:hover:border-slate-600 transition-colors p-5 flex flex-col justify-between"
          >
            <div>
              <div class="flex items-start justify-between mb-3 gap-2">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 truncate flex-1">
                  {{ condo.name }}
                </h3>
                <span 
                  class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full border shrink-0"
                  :class="isActive(condo)
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50' 
                    : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'"
                >
                  <span 
                    class="w-1.5 h-1.5 rounded-full mr-1.5"
                    :class="isActive(condo) ? 'bg-emerald-500' : 'bg-slate-400'"
                  ></span>
                  {{ isActive(condo) ? 'Ativo' : 'Inativo' }}
                </span>
              </div>
              
              <div class="space-y-2 mb-4">
                <div v-if="condo.city || condo.state" class="flex items-center text-xs text-slate-500 dark:text-slate-400">
                  <svg class="h-4 w-4 mr-1.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  <span>{{ condo.city }}<template v-if="condo.city && condo.state">, </template>{{ condo.state }}</span>
                </div>
              </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60">
              <AppButton 
                class="w-full justify-center" 
                @click="handleAction(condo)"
                :variant="selectable ? 'primary' : 'secondary'"
                size="sm"
              >
                <svg v-if="selectable" class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ selectable ? 'Selecionar Condomínio' : 'Gerenciar' }}
              </AppButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import AppButton from './ui/AppButton.vue';
import NeonButton from './NeonButton.vue';
import api from '../utils/api';
import { useTenantStore } from '../stores/tenant';

export default {
  name: 'CondominiumList',
  components: {
    AppButton,
    NeonButton
  },
  props: {
    selectable: { type: Boolean, default: false }
  },
  emits: ['update-stats', 'selected'],
  setup(props, { emit }) {
    const tenantStore = useTenantStore();
    const condominiums = ref([]);
    const error = ref('');
    const loading = ref(true);

    const isActive = (condo) => {
      if (typeof condo.active === 'boolean') {
        return condo.active;
      }

      return condo.status === 'active';
    };

    const emitStats = (items) => {
      emit('update-stats', {
        total: items.length,
        active: items.filter((condo) => isActive(condo)).length,
      });
    };

    const loadData = async () => {
      loading.value = true;
      error.value = '';

      try {
        if (props.selectable) {
          condominiums.value = [...tenantStore.memberships];

          if (condominiums.value.length === 0) {
            const authModule = await import('../services/auth');
            const hydration = await authModule.default.fetchCurrentUser();
            condominiums.value = [...tenantStore.memberships];

            if (condominiums.value.length === 0 && hydration?.needsSelection) {
              error.value = 'Nenhum condomínio disponível para sua conta.';
            }
          }
        } else {
          const response = await api.get('/condominiums');
          condominiums.value = response.data;
        }

        emitStats(condominiums.value);
      } catch (requestError) {
        console.error('CondominiumList: erro ao carregar condomínios:', requestError);
        error.value = requestError?.message || 'Não foi possível carregar os condomínios.';
        condominiums.value = [];
      } finally {
        loading.value = false;
      }
    };

    const handleAction = (condo) => {
      if (props.selectable) {
        emit('selected', condo);
      }
    };

    onMounted(loadData);

    return { 
      condominiums, 
      error, 
      loading, 
      handleAction,
      isActive,
      selectable: props.selectable
    };
  }
};
</script>
