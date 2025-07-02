<template>
  <AuthenticatedLayout page-title="Gerenciar Unidades">
    <div class="space-y-6">
      <!-- Header com Botão Voltar -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">Gerenciar Unidades</h1>
          <p class="text-gray-400 mt-1">{{ selectedCondominiumName }}</p>
        </div>
        <NeonButton @click="goBack" variant="secondary" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Voltar
        </NeonButton>
      </div>

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
        <!-- Total de Unidades -->
        <div class="bg-gradient-to-br from-blue-900/50 via-blue-800/50 to-blue-900/50 rounded-2xl p-6 border-2 border-blue-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-blue-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-300 text-sm font-medium">Total</p>
              <p class="text-2xl font-bold text-blue-100">{{ stats.total }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v13h18V7H3zm5 10H6v-4h2v4zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM3 5h18V3H3v2z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Unidades Ocupadas -->
        <div class="bg-gradient-to-br from-green-900/50 via-green-800/50 to-green-900/50 rounded-2xl p-6 border-2 border-green-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-green-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-300 text-sm font-medium">Ocupadas</p>
              <p class="text-2xl font-bold text-green-100">{{ stats.ocupadas }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Unidades Vazias -->
        <div class="bg-gradient-to-br from-orange-900/50 via-orange-800/50 to-orange-900/50 rounded-2xl p-6 border-2 border-orange-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-orange-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-300 text-sm font-medium">Vazias</p>
              <p class="text-2xl font-bold text-orange-100">{{ stats.vazias }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-orange-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Unidades Inativas -->
        <div class="bg-gradient-to-br from-gray-900/50 via-gray-800/50 to-gray-900/50 rounded-2xl p-6 border-2 border-gray-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-gray-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-300 text-sm font-medium">Inativas</p>
              <p class="text-2xl font-bold text-gray-100">{{ stats.inativas }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-gray-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
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
              placeholder="Buscar unidades..."
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
            <option value="all">Todas</option>
            <option value="occupied">Ocupadas</option>
            <option value="vacant">Vazias</option>
            <option value="inactive">Inativas</option>
          </select>
          <select
            v-model="towerFilter"
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todas as Torres</option>
            <option v-for="tower in towers" :key="tower.id" :value="tower.id">{{ tower.name }}</option>
          </select>
          <select
            v-model="blockFilter"
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todos os Blocos</option>
            <option v-for="block in blocks" :key="block.id" :value="block.id">{{ block.name }}</option>
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
        <NeonButton @click="openNewUnitModal" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nova Unidade
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
                Status: {{ getFilterLabel(filter) }}
              </span>
              <span v-if="towerFilter !== 'all'" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
                Torre: {{ getTowerName(towerFilter) }}
              </span>
              <span v-if="blockFilter !== 'all'" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
                Bloco: {{ getBlockName(blockFilter) }}
              </span>
            </div>
          </div>
          <div class="text-sm text-indigo-400">
            {{ filteredUnits.length }} de {{ units.length }} unidades
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
      <div v-else-if="filteredUnits.length === 0 && selectedCondominiumName" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M3 7v13h18V7H3zm5 10H6v-4h2v4zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM3 5h18V3H3v2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhuma unidade encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ search ? 'Tente ajustar sua busca' : 'Comece adicionando uma nova unidade' }}
        </p>
        <div class="mt-6">
          <NeonButton @click="openNewUnitModal" size="md">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Unidade
          </NeonButton>
        </div>
      </div>

      <!-- Units Cards Grid -->
      <div v-else-if="selectedCondominiumName" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="unit in filteredUnits" 
          :key="unit.id" 
          class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300 hover:shadow-2xl hover:scale-105"
        >
          <!-- Header com Número e Status -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center border-2 border-indigo-500/30">
                <span class="text-indigo-600 font-bold text-lg">
                  {{ unit.number }}
                </span>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">Unidade {{ unit.number }}</h3>
                <p class="text-sm text-gray-400">Andar {{ unit.floor }}</p>
              </div>
            </div>
            <div class="flex flex-col items-end space-y-1">
              <span class="px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getStatusBadgeClass(unit)">
                {{ getStatusLabel(unit) }}
              </span>
            </div>
          </div>

          <!-- Informações da Estrutura -->
          <div class="bg-gray-800/50 rounded-lg p-4 mb-4 border border-gray-700/50">
            <div class="flex items-center space-x-2 mb-2">
              <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span class="text-sm font-medium text-blue-300">Estrutura</span>
            </div>
            <div class="space-y-1 text-sm text-gray-300">
              <div v-if="unit.tower">
                <span class="text-gray-400">Torre:</span>
                <span class="ml-2">{{ unit.tower.name }}</span>
              </div>
              <div v-if="unit.block">
                <span class="text-gray-400">Bloco:</span>
                <span class="ml-2">{{ unit.block.name }}</span>
              </div>
            </div>
          </div>

          <!-- Informações do Morador (se ocupada) -->
          <div v-if="unit.resident" class="bg-green-800/20 rounded-lg p-4 mb-4 border border-green-700/50">
            <div class="flex items-center space-x-2 mb-2">
              <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span class="text-sm font-medium text-green-300">Morador</span>
            </div>
                          <div class="space-y-1 text-sm">
                <p class="text-white font-medium">{{ unit.resident.name }}</p>
                <p class="text-gray-300" v-if="unit.resident.email">{{ unit.resident.email }}</p>
                <p class="text-gray-300" v-if="unit.resident.cell_phone || unit.resident.phone">{{ unit.resident.cell_phone || unit.resident.phone }}</p>
              </div>
          </div>

          <!-- Ações -->
          <div class="flex space-x-2">
            <NeonButton @click="editUnit(unit)" size="sm" variant="info" class="flex-1">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </NeonButton>
            <NeonButton @click="deleteUnit(unit)" size="sm" variant="danger" class="flex-1">
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
  name: 'UnitsManagementView',
  components: { AuthenticatedLayout, NeonButton },
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
        const token = localStorage.getItem('token');
        
        // Carregar unidades
        const unitsUrl = buildUrlWithCondominiumFilter('/apartments');
        const unitsResponse = await fetch(unitsUrl, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (unitsResponse.ok) {
          const unitsData = await unitsResponse.json();
          console.log('Dados das unidades retornados pela API:', unitsData);
          units.value = Array.isArray(unitsData) ? unitsData : (unitsData.data || []);
        } else {
          error.value = 'Falha ao carregar unidades';
          // Dados de exemplo para teste
          units.value = [
            {
              id: 1,
              number: '101',
              floor: 1,
              active: true,
              tower: { id: 1, name: 'Torre A' },
              block: { id: 1, name: 'Bloco 1' },
                          resident: {
              name: 'João Silva',
              email: 'joao@email.com',
              cell_phone: '(11) 99999-9999'
            }
            },
            {
              id: 2,
              number: '102',
              floor: 1,
              active: true,
              tower: { id: 1, name: 'Torre A' },
              block: { id: 1, name: 'Bloco 1' },
              resident: null
            },
            {
              id: 3,
              number: '201',
              floor: 2,
              active: false,
              tower: { id: 1, name: 'Torre A' },
              block: { id: 1, name: 'Bloco 1' },
              resident: null
            }
          ];
        }

        // Carregar torres
        const towersUrl = buildUrlWithCondominiumFilter('/towers');
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
        const blocksUrl = buildUrlWithCondominiumFilter('/blocks');
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

      } catch (e) {
        error.value = 'Erro de conexão';
        console.error('Exceção ao carregar dados:', e);
        
        // Dados de exemplo para teste
        units.value = [
          {
            id: 1,
            number: '101',
            floor: 1,
            active: true,
            tower: { id: 1, name: 'Torre A' },
            block: { id: 1, name: 'Bloco 1' },
            resident: {
              name: 'João Silva',
              email: 'joao@email.com',
              cell_phone: '(11) 99999-9999'
            }
          },
          {
            id: 2,
            number: '102',
            floor: 1,
            active: true,
            tower: { id: 1, name: 'Torre A' },
            block: { id: 1, name: 'Bloco 1' },
            resident: null
          }
        ];
        
        towers.value = [
          { id: 1, name: 'Torre A' },
          { id: 2, name: 'Torre B' }
        ];
        
        blocks.value = [
          { id: 1, name: 'Bloco 1' },
          { id: 2, name: 'Bloco 2' }
        ];
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
      console.log('Excluir unidade:', unit);
    };

    // Carregar dados ao montar o componente
    onMounted(() => {
      loadData();
      
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