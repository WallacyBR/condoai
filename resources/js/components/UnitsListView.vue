<template>
  <AuthenticatedLayout page-title="Visualizar Unidades">
    <div class="space-y-6">
      <TopCard title="Unidades" :icon="BuildingIcon">
        <template #actions>
          <NeonButton @click="navigateToUnitsManagement" size="md">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Gerenciar Unidades
          </NeonButton>
        </template>
      </TopCard>
      <!-- Aviso quando nenhum condomínio está selecionado -->
      <div v-if="!selectedCondominiumName && !loading" class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <span class="text-yellow-300 font-medium">Nenhum condomínio selecionado</span>
        </div>
        <p class="text-yellow-200 text-sm mt-1">Selecione um condomínio para visualizar as unidades.</p>
      </div>

      <!-- Cards de Resumo -->
      <div v-if="selectedCondominiumName && !loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatsCard 
          label="Total de Unidades" 
          :value="unitStats.totalUnidades"
          variant="primary"
          :default-icon-path="'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'"
        />
        
        <StatsCard 
          label="Ocupadas" 
          :value="unitStats.ocupadas"
          variant="blue"
          :default-icon-path="'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'"
        />
        
        <StatsCard 
          label="Vazias" 
          :value="unitStats.vazias"
          variant="orange"
          :default-icon-path="'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z'"
        />
        
        <StatsCard 
          label="Taxa de Ocupação" 
          :value="unitStats.taxaOcupacao"
          variant="red"
        />
      </div>

      <!-- Filtros -->
      <div v-if="selectedCondominiumName">
        <ListFilters
          v-model="filters"
          search-placeholder="Buscar unidades..."
          :status-options="statusOptions"
          :tower-options="towers"
          :block-options="blocks"
          @clear-filters="handleClearFilters"
        >
          <template #summary>
            {{ filteredUnits.length }} de {{ units.length }} unidades
          </template>
        </ListFilters>
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
      <div v-else-if="filteredUnits.length === 0 && selectedCondominiumName" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhuma unidade encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ search ? 'Tente ajustar sua busca ou limpar os filtros aplicados' : 'Utilize o botão "Gerenciar Unidades" no cabeçalho para criar novas unidades' }}
        </p>
      </div>

      <!-- Units Cards Grid -->
      <div v-else-if="selectedCondominiumName" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="unit in filteredUnits" 
          :key="unit.id" 
          class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300 hover:shadow-2xl hover:scale-105"
        >
          <!-- Header com Status -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center border-2 border-indigo-500/30">
                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">Unidade {{ unit.number }}</h3>
                <p class="text-sm text-gray-400">{{ unit.block ? unit.block.name : '' }}{{ unit.tower ? ` - ${unit.tower.name}` : '' }}</p>
              </div>
            </div>
            <div class="flex flex-col items-end space-y-1">
              <span class="px-2 py-1 text-xs font-semibold rounded-full"
                    :class="unitStatsHelpers.getStatusClass(unit)">
                {{ unitStatsHelpers.getStatusLabel(unit) }}
              </span>
              <span class="text-xs text-gray-400">{{ unit.floor }}º andar</span>
            </div>
          </div>

          <!-- Informações da Unidade -->
          <div class="bg-gray-800/50 rounded-lg p-4 mb-4 border border-gray-700/50">
            <div class="space-y-2">
              <!-- Estrutura -->
              <div class="flex items-center space-x-2">
                <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-sm text-blue-300">Estrutura</span>
              </div>
              <div class="space-y-1">
                <div v-if="unit.tower">
                  <span class="text-gray-400">Torre:</span>
                  <span class="text-white ml-2">{{ unit.tower.name }}</span>
                </div>
                <div v-if="unit.block">
                  <span class="text-gray-400">Bloco:</span>
                  <span class="text-white ml-2">{{ unit.block.name }}</span>
                </div>
                <div>
                  <span class="text-gray-400">Andar:</span>
                  <span class="text-white ml-2">{{ unit.floor }}º</span>
                </div>
              </div>

              <!-- Proprietário -->
              <div class="flex items-center space-x-2 mt-3">
                <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-sm text-green-300">Proprietário</span>
              </div>
              <p class="text-white">{{ unit.owner ? unit.owner.name : 'Não informado' }}</p>

              <!-- Inquilino -->
              <div v-if="unit.tenant" class="mt-3">
                <div class="flex items-center space-x-2">
                  <svg class="h-4 w-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <span class="text-sm text-purple-300">Inquilino</span>
                </div>
                <p class="text-white">{{ unit.tenant.name }}</p>
              </div>
            </div>
          </div>

          <!-- Ações -->
          <div class="flex space-x-2">
            <NeonButton @click="viewResidents(unit)" size="sm" variant="success" class="flex-1">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0z" />
              </svg>
              Moradores
            </NeonButton>
            <NeonButton @click="viewVehicles(unit)" size="sm" variant="info" class="flex-1">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-3 3m0 0l-3-3m3 3V8a2 2 0 00-2-2H9m11 11H9m0 0V6a2 2 0 012-2h5a2 2 0 012 2v3" />
              </svg>
              Veículos
            </NeonButton>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed, watch, onMounted, h } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';
import TopCard from './ui/TopCard.vue';
import StatsCard from './ui/StatsCard.vue';
import ListFilters from './ui/ListFilters.vue';
import { useListFilters } from '../composables/useListFilters.js';
import { useUnitStats } from '../composables/useUnitStats.js';

export default {
  name: 'UnitsListView',
  components: { AuthenticatedLayout, NeonButton, TopCard, StatsCard, ListFilters },
  setup() {
    const router = useRouter();
    const units = ref([]);
    const towers = ref([]);
    const blocks = ref([]);
    const loading = ref(true);
    const error = ref('');
    
    // Usar composables
    const { search, statusFilter, towerFilter, blockFilter, clearFilters, isFilterActive } = useListFilters();
    const { stats: unitStats, getStatusClass, getStatusLabel } = useUnitStats(units);

    // Opções para o filtro de status
    const statusOptions = [
      { value: 'occupied', label: 'Ocupadas' },
      { value: 'vacant', label: 'Vazias' }
    ];

    // Filtros como objeto reativo
    const filters = computed({
      get: () => ({
        search: search.value,
        statusFilter: statusFilter.value,
        towerFilter: towerFilter.value,
        blockFilter: blockFilter.value
      }),
      set: (value) => {
        search.value = value.search;
        statusFilter.value = value.statusFilter;
        towerFilter.value = value.towerFilter;
        blockFilter.value = value.blockFilter;
      }
    });

    // Helpers para o status das unidades
    const unitStatsHelpers = { getStatusClass, getStatusLabel };

    const BuildingIcon = {
      render() {
        return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' })
        ]);
      }
    };

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

    // Função para lidar com limpeza de filtros
    const handleClearFilters = () => {
      clearFilters();
    };

    const getTypeLabel = (type) => {
      const labels = {
        'apartment': 'Apartamento',
        'house': 'Casa',
        'commercial': 'Comercial'
      };
      return labels[type] || type;
    };

    const loadData = async () => {
      try {
        const token = localStorage.getItem('token');
        
        // Carregar unidades
        const url = buildUrlWithCondominiumFilter('/api/apartments');
        console.log('Fetching units with token:', token ? 'Present' : 'Missing');
        console.log('Fetching units from URL:', url);
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
            units.value = data;
          } else if (data.data && Array.isArray(data.data)) {
            units.value = data.data;
          } else {
            console.error('Estrutura de dados inesperada:', data);
            units.value = [];
          }

          // Carregar torres
          const towersUrl = buildUrlWithCondominiumFilter('/api/towers');
          console.log('Fetching towers with token:', token ? 'Present' : 'Missing');
          console.log('Fetching towers from URL:', towersUrl);
          const towersResponse = await fetch(towersUrl, {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          });
          
          if (towersResponse.ok) {
            const towersData = await towersResponse.json();
            towers.value = Array.isArray(towersData) ? towersData : (towersData.data || []);
          }

          // Carregar blocos
          const blocksUrl = buildUrlWithCondominiumFilter('/api/blocks');
          console.log('Fetching blocks with token:', token ? 'Present' : 'Missing');
          console.log('Fetching blocks from URL:', blocksUrl);
          const blocksResponse = await fetch(blocksUrl, {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          });
          
          if (blocksResponse.ok) {
            const blocksData = await blocksResponse.json();
            blocks.value = Array.isArray(blocksData) ? blocksData : (blocksData.data || []);
          }
        } else {
          // Tenta ler a resposta de erro como JSON, que é o esperado do Handler.php
          const errorBody = await response.json().catch(() => null);
          const errorMessage = errorBody?.message || `Falha ao carregar dados. Status: ${response.status}`;
          error.value = errorMessage;
          console.error('Erro na API:', response.status, response.statusText, errorBody);
          units.value = [];
        }
      } catch (e) {
        if (e instanceof SyntaxError) {
          error.value = 'Erro ao processar a resposta do servidor. A resposta não é um JSON válido.';
        } else {
          error.value = e.message || 'Erro de conexão.';
        }
        console.error('Exceção ao carregar dados:', e);
        units.value = [];
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
          console.log('Dados brutos dos condomínios:', data);
          
          // Verificar se os dados têm a estrutura esperada
          if (Array.isArray(data)) {
            console.log('Condomínios carregados diretamente:', data);
          } else if (data.data && Array.isArray(data.data)) {
            console.log('Condomínios carregados a partir de data.data:', data.data);
          } else {
            console.error('Estrutura de dados inesperada:', data);
          }
        } else {
          error.value = 'Falha ao carregar dados dos condomínios';
          console.error('Erro ao carregar condomínios:', response.status, response.statusText);
        }
      } catch (e) {
        error.value = 'Erro ao carregar condomínios';
        console.error('Exceção ao carregar condomínios:', e);
      }
    };

    const filteredUnits = computed(() => {
      return units.value.filter(unit => {
        // Filtro por busca (número)
        const matchesSearch = !filters.value.search || 
          unit.number.toLowerCase().includes(filters.value.search.toLowerCase());
        
        // Filtro por status
        let matchesFilter = false;
        if (filters.value.statusFilter === 'all') {
          matchesFilter = true;
        } else if (filters.value.statusFilter === 'occupied') {
          matchesFilter = unit.owner || unit.tenant;
        } else if (filters.value.statusFilter === 'vacant') {
          matchesFilter = !unit.owner && !unit.tenant;
        }

        // Filtro por torre
        let matchesTower = false;
        if (filters.value.towerFilter === 'all') {
          matchesTower = true;
        } else {
          matchesTower = unit.tower && unit.tower.id == filters.value.towerFilter;
        }
        
        // Filtro por bloco
        let matchesBlock = false;
        if (filters.value.blockFilter === 'all') {
          matchesBlock = true;
        } else {
          matchesBlock = unit.block && unit.block.id == filters.value.blockFilter;
        }
        
        return matchesSearch && matchesFilter && matchesTower && matchesBlock;
      });
    });

    const navigateToUnitsManagement = () => {
      router.push('/units');
    };

    const viewResidents = (unit) => {
      router.push(`/residents?apartment_id=${unit.id}`);
    };

    const viewVehicles = (unit) => {
      router.push(`/vehicles?apartment_id=${unit.id}`);
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
      units,
      towers,
      blocks,
      loading,
      error,
      filteredUnits,
      navigateToUnitsManagement,
      viewResidents,
      viewVehicles,
      getTypeLabel,
      selectedCondominiumName,
      unitStats,
      unitStatsHelpers,
      filters,
      statusOptions,
      handleClearFilters,
      BuildingIcon
    };
  }
};
</script> 