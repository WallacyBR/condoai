<template>
  <div class="p-6">
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>
    
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600">
      {{ error }}
    </div>

    <div v-else>
      <div v-if="condominiums.length === 0" class="text-center py-8 text-gray-500">
        Nenhum condomínio encontrado
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="condo in condominiums" :key="condo.id" 
             class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl border-2 border-cyan-500/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
          <div class="p-8">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-cyan-300">{{ condo.name }}</h3>
              <span class="px-3 py-1 text-sm rounded-full font-semibold shadow"
                    :class="condo.status === 'active' ? 'bg-green-900/40 text-green-300 border border-green-400' : 'bg-gray-800/60 text-gray-400 border border-gray-500'">
                {{ condo.status === 'active' ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
            
            <div class="space-y-2 text-cyan-100">
              <p class="flex items-center">
                <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ condo.city }}/{{ condo.state }}
              </p>
              <p class="flex items-center">
                <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                {{ condo.units }} unidades
              </p>
            </div>

            <div class="mt-6 pt-6 border-t border-cyan-800/40 flex">
              <NeonButton class="flex-1">
                Gerenciar
              </NeonButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import NeonButton from './NeonButton.vue';

export default {
  name: 'CondominiumList',
  components: {
    NeonButton
  },
  emits: ['update-stats'],
  setup(props, { emit }) {
    const condominiums = ref([]);
    const error = ref('');
    const loading = ref(true);

    const loadData = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await fetch('/api/condominiums', {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.ok) {
          condominiums.value = await response.json();
          // Emitir estatísticas para o componente pai
          emit('update-stats', {
            total: condominiums.value.length,
            active: condominiums.value.filter(c => c.status === 'active').length
          });
        } else {
          error.value = 'Falha ao carregar dados';
        }
      } catch (e) {
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    onMounted(loadData);

    return { condominiums, error, loading };
  }
};
</script>
