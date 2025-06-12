<template>
  <AuthenticatedLayout page-title="Condomínios">
    <div class="space-y-6">
      <!-- Header Actions -->
      <div class="flex justify-between items-center">
        <div class="flex space-x-4">
          <div class="relative">
            <input
              type="text"
              v-model="search"
              placeholder="Buscar condomínios..."
              class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          <select
            v-model="filter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="all">Todos</option>
            <option value="active">Ativos</option>
            <option value="inactive">Inativos</option>
          </select>
        </div>
        <button
          @click="openNewCondominiumModal"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center"
        >
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Condomínio
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600">
        {{ error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredCondominiums.length === 0" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum condomínio encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ search ? 'Tente ajustar sua busca' : 'Comece adicionando um novo condomínio' }}
        </p>
        <div class="mt-6">
          <button
            @click="openNewCondominiumModal"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Condomínio
          </button>
        </div>
      </div>

      <!-- Condominiums Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="condo in filteredCondominiums" :key="condo.id" 
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
            <div class="mt-6 pt-6 border-t border-cyan-800/40 flex space-x-2">
              <button class="flex-1 px-4 py-2 bg-gradient-to-r from-cyan-500 to-pink-500 text-white font-semibold rounded-2xl shadow-md hover:from-pink-500 hover:to-cyan-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-cyan-400/50">
                Gerenciar
              </button>
              <button class="p-2 rounded-full bg-gray-900/60 text-cyan-300 hover:bg-cyan-900/40 hover:text-pink-400 transition-all duration-200 shadow-md">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';

export default {
  name: 'CondominiumsView',
  components: { AuthenticatedLayout },
  setup() {
    const condominiums = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const filter = ref('all');

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
        } else {
          error.value = 'Falha ao carregar dados';
        }
      } catch (e) {
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    const filteredCondominiums = computed(() => {
      return condominiums.value.filter(condo => {
        const matchesSearch = condo.name.toLowerCase().includes(search.value.toLowerCase()) ||
                            condo.city.toLowerCase().includes(search.value.toLowerCase());
        const matchesFilter = filter.value === 'all' || condo.status === filter.value;
        return matchesSearch && matchesFilter;
      });
    });

    const openNewCondominiumModal = () => {
      // Implementar lógica para abrir modal de novo condomínio
      console.log('Abrir modal de novo condomínio');
    };

    // Carregar dados ao montar o componente
    loadData();

    return {
      condominiums,
      loading,
      error,
      search,
      filter,
      filteredCondominiums,
      openNewCondominiumModal
    };
  }
};
</script> 