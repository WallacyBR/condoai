<template>
  <AuthenticatedLayout page-title="Moradores">
    <div class="space-y-6">
      <!-- Aviso quando nenhum condomínio está selecionado -->
      <div v-if="!selectedCondominiumName && !loading" class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <span class="text-yellow-300 font-medium">Nenhum condomínio selecionado</span>
        </div>
        <p class="text-yellow-200 text-sm mt-1">Selecione um condomínio para visualizar os moradores.</p>
      </div>

      <!-- Cards de Resumo -->
      <div v-if="selectedCondominiumName && !loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total de Proprietários -->
        <div class="bg-gradient-to-br from-purple-900/50 via-purple-800/50 to-purple-900/50 rounded-2xl p-6 border-2 border-purple-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-purple-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-300 text-sm font-medium">Proprietários</p>
              <p class="text-2xl font-bold text-purple-100">{{ stats.proprietarios }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-purple-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Total de Moradores -->
        <div class="bg-gradient-to-br from-blue-900/50 via-blue-800/50 to-blue-900/50 rounded-2xl p-6 border-2 border-blue-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-blue-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-300 text-sm font-medium">Moradores</p>
              <p class="text-2xl font-bold text-blue-100">{{ stats.moradores }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Total de Inquilinos -->
        <div class="bg-gradient-to-br from-orange-900/50 via-orange-800/50 to-orange-900/50 rounded-2xl p-6 border-2 border-orange-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-orange-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-300 text-sm font-medium">Inquilinos</p>
              <p class="text-2xl font-bold text-orange-100">{{ stats.inquilinos }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-orange-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Total de Ativos -->
        <div class="bg-gradient-to-br from-green-900/50 via-green-800/50 to-green-900/50 rounded-2xl p-6 border-2 border-green-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-green-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-300 text-sm font-medium">Ativos</p>
              <p class="text-2xl font-bold text-green-100">{{ stats.ativos }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Total de Inativos -->
        <div class="bg-gradient-to-br from-gray-900/50 via-gray-800/50 to-gray-900/50 rounded-2xl p-6 border-2 border-gray-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-gray-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-300 text-sm font-medium">Inativos</p>
              <p class="text-2xl font-bold text-gray-100">{{ stats.inativos }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-gray-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Header Actions -->
      <div v-if="selectedCondominiumName" class="flex justify-between items-center">
        <div class="flex space-x-4">
          <div class="relative">
            <input
              type="text"
              v-model="search"
              placeholder="Buscar moradores..."
              class="w-64 pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
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
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todos</option>
            <option value="active">Ativos</option>
            <option value="inactive">Inativos</option>
          </select>
          <select
            v-model="classificationFilter"
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todas as Classificações</option>
            <option value="proprietario">Proprietários</option>
            <option value="morador">Moradores</option>
            <option value="inquilino">Inquilinos</option>
          </select>
          <NeonButton 
            v-if="isFilterActive" 
            @click="clearFilters" 
            size="sm" 
            class="flex items-center bg-gray-600/40 hover:bg-gray-600/60"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Limpar
          </NeonButton>
        </div>
        <NeonButton @click="openNewResidentPage" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Morador
        </NeonButton>
      </div>

      <!-- Filter Status -->
      <div v-if="isFilterActive && selectedCondominiumName" class="bg-indigo-500/10 border border-indigo-500/30 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
              <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
              </svg>
              <span class="text-indigo-300 font-medium">Filtros Ativos:</span>
            </div>
            <div class="flex space-x-2">
              <span v-if="search" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
                Busca: "{{ search }}"
              </span>
              <span v-if="filter !== 'all'" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
                Tipo: {{ filter === 'active' ? 'Ativos' : 'Inativos' }}
              </span>
              <span v-if="classificationFilter !== 'all'" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
                Classificação: {{ getClassificationLabel(classificationFilter) }}
              </span>
            </div>
          </div>
          <div class="text-sm text-indigo-400">
            {{ filteredResidents.length }} de {{ residents.length }} moradores
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
        {{ error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredResidents.length === 0 && selectedCondominiumName" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhum morador encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ search ? 'Tente ajustar sua busca' : 'Comece adicionando um novo morador' }}
        </p>
        <div class="mt-6">
          <NeonButton @click="openNewResidentPage" size="md">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Morador
          </NeonButton>
        </div>
      </div>

      <!-- Residents Cards Grid -->
      <div v-else-if="selectedCondominiumName" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="resident in filteredResidents" 
          :key="resident.id" 
          class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300 hover:shadow-2xl hover:scale-105"
        >
          <!-- Header com Avatar e Status -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center border-2 border-indigo-500/30">
                <span class="text-indigo-600 font-bold text-lg">
                  {{ resident.name.split(' ').map(n => n[0]).join('').toUpperCase() }}
                </span>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">{{ resident.name }}</h3>
                <p class="text-sm text-gray-400">{{ resident.email }}</p>
              </div>
            </div>
            <div class="flex flex-col items-end space-y-1">
              <span class="px-2 py-1 text-xs font-semibold rounded-full"
                    :class="resident.active ? 'bg-green-500/20 text-green-300 border border-green-500/30' : 'bg-gray-500/20 text-gray-300 border border-gray-500/30'">
                {{ resident.active ? 'Ativo' : 'Inativo' }}
              </span>
              <span class="px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getClassificationBadgeClass(resident.classification)">
                {{ getClassificationLabel(resident.classification) }}
              </span>
            </div>
          </div>

          <!-- Informações de Contato -->
          <div class="bg-gray-800/50 rounded-lg p-4 mb-4 border border-gray-700/50">
            <div class="flex items-center space-x-2 mb-2">
              <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <span class="text-sm font-medium text-green-300">Contato</span>
            </div>
            <p class="text-white">{{ resident.phone }}</p>
          </div>

          <!-- Ações -->
          <div class="flex space-x-2">
            <NeonButton @click="editResident(resident)" size="sm" class="flex-1 bg-blue-600/40 hover:bg-blue-600/60">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </NeonButton>
            <NeonButton @click="deleteResident(resident)" size="sm" class="flex-1 bg-red-600/40 hover:bg-red-600/60">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Excluir
            </NeonButton>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';

export default {
  name: 'ResidentsView',
  components: { AuthenticatedLayout, NeonButton },
  setup() {
    const router = useRouter();
    const residents = ref([]);
    const condominiums = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const filter = ref('all');
    const classificationFilter = ref('all');

    // Variáveis para gerenciar filtro por condomínio
    const selectedCondominiumId = ref(localStorage.getItem('selectedCondominium'));
    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName'));

    // Função para atualizar as informações do condomínio selecionado
    const updateSelectedCondominium = () => {
      selectedCondominiumId.value = localStorage.getItem('selectedCondominium');
      selectedCondominiumName.value = localStorage.getItem('selectedCondominiumName');
    };

    // Função para construir URL com filtro por condomínio
    const buildUrlWithCondominiumFilter = (baseUrl) => {
      if (selectedCondominiumId.value) {
        const separator = baseUrl.includes('?') ? '&' : '?';
        return `${baseUrl}${separator}condominium_id=${selectedCondominiumId.value}`;
      }
      return baseUrl;
    };

    // Computed para calcular estatísticas
    const stats = computed(() => {
      const proprietarios = residents.value.filter(r => r.classification === 'proprietario').length;
      const moradores = residents.value.filter(r => r.classification === 'morador').length;
      const inquilinos = residents.value.filter(r => r.classification === 'inquilino').length;
      const ativos = residents.value.filter(r => r.active === true || r.active === 1).length;
      const inativos = residents.value.filter(r => r.active === false || r.active === 0).length;

      return {
        proprietarios,
        moradores,
        inquilinos,
        ativos,
        inativos
      };
    });

    // Watcher para debug do filtro
    watch([filter, search, classificationFilter], ([newFilter, newSearch, newClassificationFilter]) => {
      console.log('Filtro alterado:', { filter: newFilter, search: newSearch, classificationFilter: newClassificationFilter });
    });

    // Watcher para quando os dados dos moradores mudam
    watch(residents, (newResidents) => {
      console.log('Dados dos moradores atualizados:', newResidents.length, 'moradores');
      // Forçar recálculo do filtro
      if (filter.value !== 'all') {
        console.log('Forçando recálculo do filtro:', filter.value);
      }
    }, { deep: true });

    const clearFilters = () => {
      search.value = '';
      filter.value = 'all';
      classificationFilter.value = 'all';
    };

    const isFilterActive = computed(() => {
      return search.value || filter.value !== 'all' || classificationFilter.value !== 'all';
    });

    const getClassificationLabel = (classification) => {
      const labels = {
        'proprietario': 'Proprietário',
        'morador': 'Morador',
        'inquilino': 'Inquilino'
      };
      return labels[classification] || classification;
    };

    const getClassificationBadgeClass = (classification) => {
      const classes = {
        'proprietario': 'bg-purple-500/20 text-purple-300 border border-purple-500/30',
        'morador': 'bg-blue-500/20 text-blue-300 border border-blue-500/30',
        'inquilino': 'bg-orange-500/20 text-orange-300 border border-orange-500/30'
      };
      return classes[classification] || 'bg-gray-500/20 text-gray-300 border border-gray-500/30';
    };

    const loadData = async () => {
      try {
        const token = localStorage.getItem('token');
        
        // Usar o composable para construir a URL com filtro
        const url = buildUrlWithCondominiumFilter('/api/residents');
        
        const response = await fetch(url, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          console.log('Dados brutos da API:', data);
          
          // Verificar se os dados têm a estrutura esperada
          if (Array.isArray(data)) {
            residents.value = data;
            console.log('Moradores carregados (array):', residents.value);
            
            // Log detalhado de cada morador para debug
            residents.value.forEach((resident, index) => {
              console.log(`Morador ${index + 1}:`, {
                id: resident.id,
                name: resident.name,
                classification: resident.classification,
                active: resident.active,
                condominium_id: resident.condominium_id,
                allKeys: Object.keys(resident)
              });
            });
          } else if (data.data && Array.isArray(data.data)) {
            // Se a API retorna { data: [...] }
            residents.value = data.data;
            console.log('Moradores carregados (data.data):', residents.value);
          } else {
            console.error('Estrutura de dados inesperada:', data);
            residents.value = [];
          }
        } else {
          error.value = 'Falha ao carregar dados';
          console.error('Erro na API:', response.status, response.statusText);
          
          // Dados de exemplo para teste do filtro
          residents.value = [
            {
              id: 1,
              name: 'João Silva',
              email: 'joao@email.com',
              phone: '(11) 99999-9999',
              classification: 'proprietario',
              active: true,
              condominium_id: selectedCondominiumId.value || 1,
              condominium: { name: 'Condomínio A' }
            },
            {
              id: 2,
              name: 'Maria Santos',
              email: 'maria@email.com',
              phone: '(11) 88888-8888',
              classification: 'morador',
              active: true,
              condominium_id: selectedCondominiumId.value || 1,
              condominium: { name: 'Condomínio A' }
            },
            {
              id: 3,
              name: 'Pedro Costa',
              email: 'pedro@email.com',
              phone: '(11) 77777-7777',
              classification: 'inquilino',
              active: false,
              condominium_id: selectedCondominiumId.value || 1,
              condominium: { name: 'Condomínio A' }
            }
          ];
        }
      } catch (e) {
        error.value = 'Erro de conexão';
        console.error('Exceção ao carregar dados:', e);
        
        // Dados de exemplo para teste do filtro
        residents.value = [
          {
            id: 1,
            name: 'João Silva',
            email: 'joao@email.com',
            phone: '(11) 99999-9999',
            classification: 'proprietario',
            active: true,
            condominium_id: selectedCondominiumId.value || 1,
            condominium: { name: 'Condomínio A' }
          },
          {
            id: 2,
            name: 'Maria Santos',
            email: 'maria@email.com',
            phone: '(11) 88888-8888',
            classification: 'morador',
            active: true,
            condominium_id: selectedCondominiumId.value || 1,
            condominium: { name: 'Condomínio A' }
          },
          {
            id: 3,
            name: 'Pedro Costa',
            email: 'pedro@email.com',
            phone: '(11) 77777-7777',
            classification: 'inquilino',
            active: false,
            condominium_id: selectedCondominiumId.value || 1,
            condominium: { name: 'Condomínio A' }
          }
        ];
      } finally {
        loading.value = false;
      }
    };

    const loadCondominiums = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await fetch('/api/condominiums', {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          condominiums.value = Array.isArray(data) ? data : (data.data || []);
        }
      } catch (e) {
        console.error('Erro ao carregar condomínios:', e);
      }
    };

    const filteredResidents = computed(() => {
      console.log('=== INÍCIO DO FILTRO ===');
      console.log('Filtrando moradores:', {
        total: residents.value.length,
        filter: filter.value,
        search: search.value,
        classificationFilter: classificationFilter.value
      });
      
      // Log de todos os moradores antes do filtro
      console.log('Todos os moradores antes do filtro:');
      residents.value.forEach((resident, index) => {
        console.log(`  ${index + 1}. ${resident.name} - Ativo: ${resident.active} - Classificação: ${resident.classification}`);
      });
      
      const filtered = residents.value.filter(resident => {
        // Filtro por busca (nome ou email)
        const matchesSearch = !search.value || 
          resident.name.toLowerCase().includes(search.value.toLowerCase()) ||
          resident.email.toLowerCase().includes(search.value.toLowerCase());
        
        // Filtro por status ativo/inativo
        let matchesFilter = false;
        if (filter.value === 'all') {
          matchesFilter = true;
        } else if (filter.value === 'active') {
          matchesFilter = resident.active === true || resident.active === 1;
        } else if (filter.value === 'inactive') {
          matchesFilter = resident.active === false || resident.active === 0;
        }

        // Filtro por classificação
        let matchesClassification = false;
        if (classificationFilter.value === 'all') {
          matchesClassification = true;
        } else {
          matchesClassification = resident.classification === classificationFilter.value;
        }
        
        console.log(`🔍 Morador ${resident.name}:`, {
          active: resident.active,
          classification: resident.classification,
          matchesSearch,
          matchesFilter,
          matchesClassification,
          searchTerm: search.value,
          filterTerm: filter.value,
          classificationTerm: classificationFilter.value,
          willInclude: matchesSearch && matchesFilter && matchesClassification
        });
        
        return matchesSearch && matchesFilter && matchesClassification;
      });
      
      console.log('=== RESULTADO DO FILTRO ===');
      console.log('Resultado filtrado:', filtered.length, 'moradores');
      filtered.forEach((resident, index) => {
        console.log(`  ${index + 1}. ${resident.name} - Ativo: ${resident.active} - Classificação: ${resident.classification}`);
      });
      console.log('=== FIM DO FILTRO ===');
      
      return filtered;
    });

    const openNewResidentPage = () => {
      router.push('/residents/new');
    };

    const editResident = (resident) => {
      router.push(`/residents/${resident.id}/edit`);
    };

    const deleteResident = async (resident) => {
      if (!confirm(`Excluir ${resident.name}?`)) return;
      try {
        const token = localStorage.getItem('token');
        const response = await fetch(`/api/residents/${resident.id}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` }
        });
        if (response.ok) {
          residents.value = residents.value.filter(r => r.id !== resident.id);
        } else {
          console.error('Erro ao excluir morador');
        }
      } catch (e) {
        console.error('Erro ao excluir morador:', e);
      }
    };

    // Carregar dados ao montar o componente
    onMounted(() => {
      loadData();
      loadCondominiums();
      
      // Configurar listeners para mudanças de condomínio
      window.addEventListener('condominiumSelected', () => {
        console.log('Condomínio alterado via evento customizado');
        updateSelectedCondominium();
        loadData();
      });

      window.addEventListener('storage', (e) => {
        if (e.key === 'selectedCondominium') {
          console.log('Condomínio alterado via localStorage');
          updateSelectedCondominium();
          loadData();
        }
      });
    });

    return {
      residents,
      condominiums,
      loading,
      error,
      search,
      filter,
      classificationFilter,
      filteredResidents,
      openNewResidentPage,
      editResident,
      deleteResident,
      clearFilters,
      isFilterActive,
      getClassificationLabel,
      getClassificationBadgeClass,
      selectedCondominiumName,
      stats
    };
  }
};
</script> 