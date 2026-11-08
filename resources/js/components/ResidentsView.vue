<template>
  <AuthenticatedLayout page-title="Moradores">
    <div class="space-y-6">
      <!-- Header Actions -->
      <div class="flex justify-between items-center">
        <div class="flex space-x-4">
          <div class="relative">
            <input
              type="text"
              v-model="search"
              placeholder="Buscar moradores..."
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
            <option value="owner">Proprietários</option>
            <option value="tenant">Inquilinos</option>
          </select>
        </div>
        <button
          @click="openNewResidentModal"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center"
        >
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Morador
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
      <div v-else-if="filteredResidents.length === 0" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum morador encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ search ? 'Tente ajustar sua busca' : 'Comece adicionando um novo morador' }}
        </p>
        <div class="mt-6">
          <button
            @click="openNewResidentModal"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Morador
          </button>
        </div>
      </div>

      <!-- Residents Table -->
      <div v-else class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nome
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tipo
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Unidade
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Contato
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Ações</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="resident in filteredResidents" :key="resident.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                      <span class="text-indigo-600 font-medium">
                        {{ resident.name.split(' ').map(n => n[0]).join('').toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ resident.name }}</div>
                    <div class="text-sm text-gray-500">{{ resident.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="resident.type === 'owner' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                  {{ resident.type === 'owner' ? 'Proprietário' : 'Inquilino' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ resident.unit }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ resident.phone }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="resident.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                  {{ resident.status === 'active' ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</button>
                <button class="text-red-600 hover:text-red-900">Excluir</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';

export default {
  name: 'ResidentsView',
  components: { AuthenticatedLayout },
  setup() {
    const residents = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const filter = ref('all');

    const loadData = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await fetch('/api/residents', {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.ok) {
          residents.value = await response.json();
        } else {
          error.value = 'Falha ao carregar dados';
        }
      } catch (e) {
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    const filteredResidents = computed(() => {
      return residents.value.filter(resident => {
        const matchesSearch = resident.name.toLowerCase().includes(search.value.toLowerCase()) ||
                            resident.email.toLowerCase().includes(search.value.toLowerCase());
        const matchesFilter = filter.value === 'all' || resident.type === filter.value;
        return matchesSearch && matchesFilter;
      });
    });

    const openNewResidentModal = () => {
      // Implementar lógica para abrir modal de novo morador
      console.log('Abrir modal de novo morador');
    };

    // Carregar dados ao montar o componente
    loadData();

    return {
      residents,
      loading,
      error,
      search,
      filter,
      filteredResidents,
      openNewResidentModal
    };
  }
};
</script> 