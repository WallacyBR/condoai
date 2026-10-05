<template>
  <div class="min-h-screen bg-slate-100 dark:bg-slate-950 flex items-center justify-center p-6 transition-colors">
    <div class="w-full max-w-5xl">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="mb-4">
          <div class="flex justify-center mb-3">
            <img 
              :src="logoImage" 
              alt="PrediHub Logo" 
              class="h-16 w-auto object-contain"
              @error="handleImageError"
            />
          </div>
        </div>
        
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-2">
          Bem-vindo de volta
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
          Selecione o condomínio que deseja gerenciar para acessar o painel do sistema
        </p>
      </div>

      <!-- Condominium Selection Card -->
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
          <div class="flex items-center">
            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
              Escolha seu Condomínio
            </h3>
          </div>
        </div>

        <!-- Card Content -->
        <div class="p-6">
          <CondominiumList selectable @selected="selectCondominium" />
        </div>
      </div>

      <!-- Footer -->
      <div class="text-center mt-6">
        <p class="text-slate-400 text-xs">
          PrediHub • Gestão Inteligente para Condomínios
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import CondominiumList from './CondominiumList.vue';
import { useRouter } from 'vue-router';
import { useTenantStore } from '../stores/tenant';
import { usePredihubLogo } from '../composables/usePredihubLogo.js';

export default {
  name: 'CondominiumSelectView',
  components: { CondominiumList },
  setup() {
    const router = useRouter();
    const tenantStore = useTenantStore();
    const { logo: logoImage } = usePredihubLogo();

    const selectCondominium = (condo) => {
      const result = tenantStore.select(condo);
      if (result.ok) {
        router.push('/dashboard');
      }
    };

    const handleImageError = () => {
      console.error('Erro ao carregar a imagem do logo');
    };

    return { 
      selectCondominium,
      handleImageError,
      logoImage
    };
  }
};
</script>
