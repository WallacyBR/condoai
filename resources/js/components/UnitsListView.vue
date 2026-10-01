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
      <div v-if="!selectedCondominiumName && !loading" class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl p-4">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <span class="text-amber-900 dark:text-amber-200 font-medium">Nenhum condomínio selecionado</span>
        </div>
        <p class="text-amber-700 dark:text-amber-300 text-sm mt-1">Selecione um condomínio para visualizar as unidades.</p>
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
          variant="green"
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
          variant="primary"
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
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-4 text-rose-700 dark:text-rose-300 text-sm">
        {{ error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredUnits.length === 0 && selectedCondominiumName" class="text-center py-12 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <h3 class="mt-2 text-base font-medium text-slate-900 dark:text-slate-100">Nenhuma unidade encontrada</h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
          {{ search ? 'Tente ajustar sua busca ou limpar os filtros aplicados' : 'Utilize o botão "Gerenciar Unidades" no cabeçalho para criar novas unidades' }}
        </p>
      </div>

      <!-- Units Cards Grid -->
      <div v-else-if="selectedCondominiumName" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="unit in filteredUnits" 
          :key="unit.id" 
          class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors flex flex-col justify-between"
        >
          <div>
            <!-- Header com Status -->
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 flex items-center justify-center">
                  <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Unidade {{ unit.number }}</h3>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ unit.block ? unit.block.name : '' }}{{ unit.tower ? ` - ${unit.tower.name}` : '' }}</p>
                </div>
              </div>
              <div class="flex flex-col items-end space-y-1">
                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full"
                      :class="unitStatsHelpers.getStatusClass(unit)">
                  {{ unitStatsHelpers.getStatusLabel(unit) }}
                </span>
                <span class="text-xs text-slate-400">{{ unit.floor }}º andar</span>
              </div>
            </div>

            <!-- Informações da Unidade -->
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3.5 mb-4 border border-slate-100 dark:border-slate-800">
              <div class="space-y-3">
                <!-- Estrutura -->
                <div>
                  <div class="flex items-center space-x-1.5 mb-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Estrutura</span>
                  </div>
                  <div class="space-y-0.5 text-xs text-slate-600 dark:text-slate-400">
                    <div v-if="unit.tower" class="flex justify-between">
                      <span class="text-slate-500">Torre:</span>
                      <span class="font-medium text-slate-800 dark:text-slate-200">{{ unit.tower.name }}</span>
                    </div>
                    <div v-if="unit.block" class="flex justify-between">
                      <span class="text-slate-500">Bloco:</span>
                      <span class="font-medium text-slate-800 dark:text-slate-200">{{ unit.block.name }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-slate-500">Andar:</span>
                      <span class="font-medium text-slate-800 dark:text-slate-200">{{ unit.floor }}º</span>
                    </div>
                  </div>
                </div>

                <!-- Proprietário -->
                <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                  <div class="flex items-center space-x-1.5 mb-1 text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Proprietário</span>
                  </div>
                  <p class="text-xs text-slate-800 dark:text-slate-200 font-medium">{{ unit.owner ? unit.owner.name : 'Não informado' }}</p>
                </div>

                <!-- Inquilino -->
                <div v-if="unit.tenant" class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                  <div class="flex items-center space-x-1.5 mb-1 text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Inquilino</span>
                  </div>
                  <p class="text-xs text-slate-800 dark:text-slate-200 font-medium">{{ unit.tenant.name }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Ações -->
          <div class="flex space-x-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <AppButton @click="viewResidents(unit)" size="sm" variant="secondary" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0z" />
              </svg>
              Moradores
            </AppButton>
            <AppButton @click="viewVehicles(unit)" size="sm" variant="secondary" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-3 3m0 0l-3-3m3 3V8a2 2 0 00-2-2H9m11 11H9m0 0V6a2 2 0 012-2h5a2 2 0 012 2v3" />
              </svg>
              Veículos
            </AppButton>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed, onMounted, h } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';
import AppButton from './ui/AppButton.vue';
import TopCard from './ui/TopCard.vue';
import StatsCard from './ui/StatsCard.vue';
import ListFilters from './ui/ListFilters.vue';
import { useListFilters } from '../composables/useListFilters.js';
import { useUnitStats } from '../composables/useUnitStats.js';
import { apiRequest } from '../utils/api';

export default {
  name: 'UnitsListView',
  components: { AuthenticatedLayout, NeonButton, AppButton, TopCard, StatsCard, ListFilters },
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

    // Nome exibido apenas como contexto visual; o tenant é resolvido no backend.
    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName') || 'Condomínio atual');

    // Função para atualizar as informações do condomínio selecionado
    const updateSelectedCondominium = () => {
      selectedCondominiumName.value = localStorage.getItem('selectedCondominiumName') || 'Condomínio atual';
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
        const [unitsData, towersData, blocksData] = await Promise.all([
          apiRequest('/apartments'),
          apiRequest('/towers'),
          apiRequest('/blocks')
        ]);

        units.value = Array.isArray(unitsData) ? unitsData : (unitsData?.data || []);
        towers.value = Array.isArray(towersData) ? towersData : (towersData?.data || []);
        blocks.value = Array.isArray(blocksData) ? blocksData : (blocksData?.data || []);
      } catch (e) {
        error.value = e.message || 'Erro ao carregar unidades.';
        units.value = [];
        towers.value = [];
        blocks.value = [];
      } finally {
        loading.value = false;
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
      
      // Configurar listeners para mudanças de condomínio
      window.addEventListener('condominiumSelected', () => {
        updateSelectedCondominium();
        loadData();
      });

      window.addEventListener('storage', (e) => {
        if (e.key === 'selectedCondominiumName' || e.key === 'selectedCondominium') {
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
