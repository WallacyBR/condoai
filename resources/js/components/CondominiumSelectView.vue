<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-6xl">
      <!-- Header -->
      <div class="text-center mb-12">
        <div class="mb-6">
          <div class="flex justify-center mb-4">
            <img 
              :src="logoImage" 
              alt="CondoAI Logo" 
              class="h-24 w-auto object-contain"
              @error="handleImageError"
            />
          </div>
          <div class="w-24 h-1 bg-gradient-to-r from-cyan-400 to-pink-500 mx-auto rounded-full"></div>
        </div>
        
        <h2 class="text-3xl font-semibold text-white mb-4">
          Bem-vindo de volta!
        </h2>
        <p class="text-xl text-gray-300 max-w-2xl mx-auto leading-relaxed">
          Selecione o condomínio que deseja gerenciar para acessar todas as funcionalidades do sistema
        </p>
      </div>

      <!-- Condominium Selection Card -->
      <div class="bg-gradient-to-br from-gray-900/80 via-gray-800/80 to-gray-900/80 backdrop-blur-xl rounded-3xl shadow-2xl border-2 border-cyan-500/20 overflow-hidden">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-cyan-500/10 to-pink-500/10 px-8 py-6 border-b border-cyan-500/20">
          <div class="flex items-center justify-center">
            <svg class="h-8 w-8 text-cyan-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <h3 class="text-2xl font-semibold text-cyan-300">
              Escolha seu Condomínio
            </h3>
          </div>
        </div>

        <!-- Card Content -->
        <div class="p-8">
          <CondominiumList selectable @selected="selectCondominium" />
        </div>
      </div>

      <!-- Footer -->
      <div class="text-center mt-8">
        <p class="text-gray-400 text-sm">
          Precisa de ajuda? Entre em contato com o suporte técnico
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import CondominiumList from './CondominiumList.vue';
import { useRouter } from 'vue-router';
import { useTenantStore } from '../stores/tenant';
import logoImage from '../../images/condo_ai_horizontal.png';

export default {
  name: 'CondominiumSelectView',
  components: { CondominiumList },
  setup() {
    const router = useRouter();
    const tenantStore = useTenantStore();

    const selectCondominium = (condo) => {
      tenantStore.select(condo);
      router.push('/dashboard');
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
