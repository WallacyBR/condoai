<template>
  <AuthenticatedLayout page-title="Gerenciar Unidades">
    <div class="space-y-6">
      <!-- Header com Botão Voltar unificado -->
      <PageHeader
        title="Gerenciar Unidades"
        :subtitle="selectedCondominiumName"
        show-back
        @back="goBack"
      >
        <template #actions>
          <AppButton @click="openNewUnitModal" variant="primary">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Unidade
          </AppButton>
        </template>
      </PageHeader>

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

      <!-- Cards de Resumo Modernizados -->
      <div v-if="selectedCondominiumName && !loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatsCard
          label="Total de Unidades"
          :value="stats.total"
          variant="primary"
          :default-icon-path="'M3 7v13h18V7H3zm5 10H6v-4h2v4zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM3 5h18V3H3v2z'"
        />
        <StatsCard
          label="Ocupadas"
          :value="stats.ocupadas"
          variant="green"
          :default-icon-path="'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'"
        />
        <StatsCard
          label="Vazias"
          :value="stats.vazias"
          variant="orange"
          :default-icon-path="'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'"
        />
        <StatsCard
          label="Inativas"
          :value="stats.inativas"
          variant="default"
          :default-icon-path="'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728'"
        />
      </div>

      <!-- Barra de Filtros e Busca -->
      <div v-if="selectedCondominiumName" class="flex flex-wrap gap-4 items-center justify-between">
        <div class="flex flex-wrap gap-3 items-center">
          <div class="relative">
            <input
              type="text"
              v-model="search"
              placeholder="Buscar unidades..."
              class="w-64 pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-sm shadow-sm"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          <select
            v-model="filter"
            class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm"
          >
            <option value="all">Todas</option>
            <option value="occupied">Ocupadas</option>
            <option value="vacant">Vazias</option>
            <option value="inactive">Inativas</option>
          </select>
          <select
            v-model="towerFilter"
            class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm"
          >
            <option value="all">Todas as Torres</option>
            <option v-for="tower in towers" :key="tower.id" :value="tower.id">{{ tower.name }}</option>
          </select>
          <select
            v-model="blockFilter"
            class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm"
          >
            <option value="all">Todos os Blocos</option>
            <option v-for="block in blocks" :key="block.id" :value="block.id">{{ block.name }}</option>
          </select>
          <AppButton 
            v-if="isFilterActive" 
            @click="clearFilters" 
            size="sm" 
            variant="ghost"
          >
            <svg class="h-4 w-4 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Limpar
          </AppButton>
        </div>
      </div>

      <!-- Resumo dos Filtros Ativos -->
      <div v-if="isFilterActive && selectedCondominiumName" class="bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/50 rounded-xl p-3 flex items-center justify-between">
        <div class="flex items-center space-x-3 text-sm">
          <span class="text-indigo-900 dark:text-indigo-200 font-medium">Filtros:</span>
          <div class="flex flex-wrap gap-2">
            <span v-if="search" class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs rounded-md">
              Busca: "{{ search }}"
            </span>
            <span v-if="filter !== 'all'" class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs rounded-md">
              Status: {{ getFilterLabel(filter) }}
            </span>
            <span v-if="towerFilter !== 'all'" class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs rounded-md">
              Torre: {{ getTowerName(towerFilter) }}
            </span>
            <span v-if="blockFilter !== 'all'" class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs rounded-md">
              Bloco: {{ getBlockName(blockFilter) }}
            </span>
          </div>
        </div>
        <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
          {{ filteredUnits.length }} de {{ units.length }} unidades
        </span>
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
      <EmptyState
        v-else-if="filteredUnits.length === 0 && selectedCondominiumName"
        title="Nenhuma unidade encontrada"
        :description="search ? 'Tente ajustar sua busca ou limpar os filtros.' : 'Comece adicionando uma nova unidade ao condomínio.'"
      >
        <template #actions>
          <AppButton @click="openNewUnitModal" variant="primary">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Unidade
          </AppButton>
        </template>
      </EmptyState>

      <!-- Units Cards Grid -->
      <div v-else-if="selectedCondominiumName" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="unit in filteredUnits" 
          :key="unit.id" 
          class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors flex flex-col justify-between"
        >
          <div>
            <!-- Header com Número e Status -->
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 flex items-center justify-center">
                  <span class="text-indigo-600 dark:text-indigo-400 font-semibold text-base">
                    {{ unit.number }}
                  </span>
                </div>
                <div>
                  <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Unidade {{ unit.number }}</h3>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Andar {{ unit.floor }}</p>
                </div>
              </div>
              <span class="px-2.5 py-0.5 text-xs font-medium rounded-full"
                    :class="getStatusBadgeClass(unit)">
                {{ getStatusLabel(unit) }}
              </span>
            </div>

            <!-- Informações da Estrutura -->
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3 mb-3 border border-slate-100 dark:border-slate-800">
              <div class="flex items-center space-x-2 mb-1.5">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Estrutura</span>
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
              </div>
            </div>

            <!-- Informações do Morador (se ocupada) -->
            <div v-if="unit.resident" class="bg-emerald-50 dark:bg-emerald-950/30 rounded-lg p-3 mb-4 border border-emerald-100 dark:border-emerald-800/40">
              <div class="flex items-center space-x-2 mb-1.5">
                <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-300">Morador</span>
              </div>
              <div class="space-y-0.5 text-xs">
                <p class="text-emerald-950 dark:text-emerald-200 font-medium">{{ unit.resident.name }}</p>
                <p class="text-emerald-700 dark:text-emerald-400" v-if="unit.resident.email">{{ unit.resident.email }}</p>
                <p class="text-emerald-700 dark:text-emerald-400" v-if="unit.resident.cell_phone || unit.resident.phone">{{ unit.resident.cell_phone || unit.resident.phone }}</p>
              </div>
            </div>
          </div>

          <!-- Ações -->
          <div class="flex space-x-2 mt-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <AppButton @click="editUnit(unit)" size="sm" variant="secondary" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </AppButton>
            <AppButton @click="deleteUnit(unit)" size="sm" variant="danger" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Excluir
            </AppButton>
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
import AppButton from './ui/AppButton.vue';
import PageHeader from './ui/PageHeader.vue';
import StatsCard from './ui/StatsCard.vue';
import EmptyState from './ui/EmptyState.vue';
import { apiRequest } from '../utils/api';

export default {
  name: 'UnitsManagementView',
  components: {
    AuthenticatedLayout,
    NeonButton,
    AppButton,
    PageHeader,
    StatsCard,
    EmptyState,
  },
  setup() {
    const router = useRouter();
    const units = ref([]);
    const towers = ref([]);
    const blocks = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const filter = ref('all');
    const towerFilter = ref('all');
    const blockFilter = ref('all');

    // Nome exibido apenas como contexto visual; o tenant é resolvido no backend.
    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName') || 'Condomínio atual');

    // Função para atualizar as informações do condomínio selecionado
    const updateSelectedCondominium = () => {
      selectedCondominiumName.value = localStorage.getItem('selectedCondominiumName') || 'Condomínio atual';
    };

    // Computed para calcular estatísticas
    const stats = computed(() => {
      const total = units.value.length;
      const ocupadas = units.value.filter(u => u.resident && u.active).length;
      const vazias = units.value.filter(u => !u.resident && u.active).length;
      const inativas = units.value.filter(u => !u.active).length;

      return {
        total,
        ocupadas,
        vazias,
        inativas
      };
    });

    const clearFilters = () => {
      search.value = '';
      filter.value = 'all';
      towerFilter.value = 'all';
      blockFilter.value = 'all';
    };

    const isFilterActive = computed(() => {
      return search.value || filter.value !== 'all' || towerFilter.value !== 'all' || blockFilter.value !== 'all';
    });

    const getFilterLabel = (filterValue) => {
      const labels = {
        'occupied': 'Ocupadas',
        'vacant': 'Vazias',
        'inactive': 'Inativas'
      };
      return labels[filterValue] || filterValue;
    };

    const getTowerName = (towerId) => {
      const tower = towers.value.find(t => t.id == towerId);
      return tower ? tower.name : 'Torre não encontrada';
    };

    const getBlockName = (blockId) => {
      const block = blocks.value.find(b => b.id == blockId);
      return block ? block.name : 'Bloco não encontrado';
    };

    const getStatusLabel = (unit) => {
      if (!unit.active) return 'Inativa';
      return unit.resident ? 'Ocupada' : 'Vazia';
    };

    const getStatusBadgeClass = (unit) => {
      if (!unit.active) {
        return 'bg-gray-500/20 text-gray-300 border border-gray-500/30';
      }
      if (unit.resident) {
        return 'bg-green-500/20 text-green-300 border border-green-500/30';
      }
      return 'bg-orange-500/20 text-orange-300 border border-orange-500/30';
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
        const matchesSearch = !search.value || 
          unit.number.toLowerCase().includes(search.value.toLowerCase());
        
        // Filtro por status
        let matchesFilter = false;
        if (filter.value === 'all') {
          matchesFilter = true;
        } else if (filter.value === 'occupied') {
          matchesFilter = unit.resident && unit.active;
        } else if (filter.value === 'vacant') {
          matchesFilter = !unit.resident && unit.active;
        } else if (filter.value === 'inactive') {
          matchesFilter = !unit.active;
        }

        // Filtro por torre
        let matchesTower = false;
        if (towerFilter.value === 'all') {
          matchesTower = true;
        } else {
          matchesTower = unit.tower && unit.tower.id == towerFilter.value;
        }
        
        // Filtro por bloco
        let matchesBlock = false;
        if (blockFilter.value === 'all') {
          matchesBlock = true;
        } else {
          matchesBlock = unit.block && unit.block.id == blockFilter.value;
        }
        
        return matchesSearch && matchesFilter && matchesTower && matchesBlock;
      });
    });

    const goBack = () => {
      router.push('/units-list');
    };

    const openNewUnitModal = () => {
      router.push('/units');
    };

    const editUnit = (unit) => {
      router.push(`/units/${unit.id}/edit`);
    };

    const deleteUnit = (unit) => {
      // Implementar lógica para excluir unidade
      error.value = `Exclusão da unidade ${unit.number} ainda não implementada.`;
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
      search,
      filter,
      towerFilter,
      blockFilter,
      filteredUnits,
      goBack,
      openNewUnitModal,
      editUnit,
      deleteUnit,
      clearFilters,
      isFilterActive,
      getFilterLabel,
      getTowerName,
      getBlockName,
      getStatusLabel,
      getStatusBadgeClass,
      selectedCondominiumName,
      stats
    };
  }
};
</script>
