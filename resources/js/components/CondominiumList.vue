<template>
  <div>
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="flex flex-col items-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-400 mb-4"></div>
        <p class="text-gray-400">Carregando condomínios...</p>
      </div>
    </div>
    
    <div v-else-if="error" class="bg-red-900/50 border border-red-500/20 rounded-lg p-6 text-red-300 text-center">
      <svg class="h-12 w-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
      </svg>
      <h3 class="text-lg font-semibold mb-2">Erro ao carregar condomínios</h3>
      <p>{{ error }}</p>
    </div>

    <div v-else>
      <div v-if="condominiums.length === 0" class="text-center py-12">
        <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <h3 class="text-xl font-semibold text-gray-300 mb-2">Nenhum condomínio encontrado</h3>
        <p class="text-gray-400">Entre em contato com o administrador do sistema</p>
      </div>

      <div v-else>
        <!-- Informações gerais -->
        <div v-if="!selectable" class="mb-6 text-center">
          <p class="text-gray-400">
            {{ condominiums.length }} condomínio{{ condominiums.length !== 1 ? 's' : '' }} encontrado{{ condominiums.length !== 1 ? 's' : '' }}
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="(condo, index) in condominiums" :key="condo.id"
               class="group relative bg-gradient-to-br from-gray-800/60 via-gray-700/60 to-gray-800/60 backdrop-blur-sm rounded-2xl shadow-xl border border-cyan-500/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-cyan-400/40 overflow-hidden animate-fade-in"
               :style="{ animationDelay: `${index * 100}ms` }">
            
            <!-- Card Background Effect -->
            <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-pink-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <!-- Card Content -->
            <div class="relative p-6">
              <!-- Header -->
              <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                  <h3 class="text-xl font-bold text-white mb-1 group-hover:text-cyan-300 transition-colors">
                    {{ condo.name }}
                  </h3>
                  <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full"
                        :class="condo.status === 'active' 
                          ? 'bg-green-500/20 text-green-300 border border-green-400/30' 
                          : 'bg-gray-600/20 text-gray-400 border border-gray-500/30'">
                    <div class="w-2 h-2 rounded-full mr-2 animate-pulse"
                         :class="condo.status === 'active' ? 'bg-green-400' : 'bg-gray-400'"></div>
                    {{ condo.status === 'active' ? 'Ativo' : 'Inativo' }}
                  </span>
                </div>
              </div>
              
              <!-- Details -->
              <div class="space-y-3 mb-6">
                <div class="flex items-center text-gray-300">
                  <svg class="h-5 w-5 mr-3 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  <span class="font-medium">{{ condo.city }}, {{ condo.state }}</span>
                </div>
                
                <div class="flex items-center text-gray-300">
                  <svg class="h-5 w-5 mr-3 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                  <span class="font-medium">{{ condo.units }} unidades</span>
                </div>
              </div>

              <!-- Action Button -->
              <div class="pt-4 border-t border-gray-600/30">
                <NeonButton 
                  class="w-full" 
                  @click="handleAction(condo)"
                  :extraClass="selectable ? 'bg-gradient-to-r from-cyan-500/20 to-pink-500/20 hover:from-cyan-500/30 hover:to-pink-500/30' : ''"
                >
                  <svg v-if="selectable" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  {{ selectable ? 'Selecionar Condomínio' : 'Gerenciar' }}
                </NeonButton>
              </div>
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
  props: {
    selectable: { type: Boolean, default: false }
  },
  emits: ['update-stats', 'selected'],
  setup(props, { emit }) {
    const condominiums = ref([]);
    const error = ref('');
    const loading = ref(true);

    const loadData = async () => {
      console.log('CondominiumList: Iniciando carregamento de dados...');
      try {
        const token = localStorage.getItem('token');
        console.log('CondominiumList: Token encontrado:', !!token);
        
        const response = await fetch('/api/condominiums', {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        console.log('CondominiumList: Response status:', response.status);
        
        if (response.ok) {
          condominiums.value = await response.json();
          console.log('CondominiumList: Dados carregados da API:', condominiums.value);
          // Emitir estatísticas para o componente pai
          emit('update-stats', {
            total: condominiums.value.length,
            active: condominiums.value.filter(c => c.status === 'active').length
          });
        } else {
          console.warn('CondominiumList: API não disponível, usando dados de teste');
          // Dados de teste para desenvolvimento
          condominiums.value = [
            {
              id: 1,
              name: 'Condomínio Exemplo 1',
              city: 'São Paulo',
              state: 'SP',
              units: 50,
              status: 'active'
            },
            {
              id: 2,
              name: 'Condomínio Exemplo 2',
              city: 'Rio de Janeiro',
              state: 'RJ',
              units: 30,
              status: 'active'
            }
          ];
          console.log('CondominiumList: Usando dados de teste:', condominiums.value);
          emit('update-stats', {
            total: condominiums.value.length,
            active: condominiums.value.filter(c => c.status === 'active').length
          });
        }
      } catch (e) {
        console.warn('CondominiumList: Erro na API, usando dados de teste:', e);
        // Dados de teste para desenvolvimento
        condominiums.value = [
          {
            id: 1,
            name: 'Condomínio Exemplo 1',
            city: 'São Paulo',
            state: 'SP',
            units: 50,
            status: 'active'
          },
          {
            id: 2,
            name: 'Condomínio Exemplo 2',
            city: 'Rio de Janeiro',
            state: 'RJ',
            units: 30,
            status: 'active'
          }
        ];
        console.log('CondominiumList: Usando dados de teste após erro:', condominiums.value);
        emit('update-stats', {
          total: condominiums.value.length,
          active: condominiums.value.filter(c => c.status === 'active').length
        });
      } finally {
        loading.value = false;
        console.log('CondominiumList: Carregamento finalizado. Loading:', loading.value, 'Condominiums:', condominiums.value.length);
      }
    };

    const handleAction = (condo) => {
      console.log('handleAction chamado:', { condo, selectable: props.selectable });
      if (props.selectable) {
        console.log('Emitindo evento selected:', condo);
        emit('selected', condo);
      } else {
        console.log('Modo não selecionável - não fazendo nada');
      }
    };

    onMounted(() => {
      console.log('CondominiumList montado com props:', props);
      console.log('Prop selectable:', props.selectable);
      loadData();
    });

    return { 
      condominiums, 
      error, 
      loading, 
      handleAction,
      selectable: props.selectable
    };
  }
};
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out forwards;
  opacity: 0;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
