<template>
  <AuthenticatedLayout page-title="Visualizar Unidades">
    <div class="space-y-6">
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
        <div class="bg-gradient-to-br from-purple-900/50 via-purple-800/50 to-purple-900/50 rounded-2xl p-6 border-2 border-purple-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-purple-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-300 text-sm font-medium">Total de Unidades</p>
              <p class="text-2xl font-bold text-purple-100">{{ stats.totalUnidades }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-purple-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Unidades Ocupadas -->
        <div class="bg-gradient-to-br from-blue-900/50 via-blue-800/50 to-blue-900/50 rounded-2xl p-6 border-2 border-blue-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-blue-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-300 text-sm font-medium">Ocupadas</p>
              <p class="text-2xl font-bold text-blue-100">{{ stats.ocupadas }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Taxa de Ocupação -->
        <div class="bg-gradient-to-br from-red-900/50 via-red-800/50 to-red-900/50 rounded-2xl p-6 border-2 border-red-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-red-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-red-300 text-sm font-medium">Taxa de Ocupação</p>
              <p class="text-2xl font-bold text-red-100">{{ stats.taxaOcupacao }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-red-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
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
            v-model="statusFilter"
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todos os Status</option>
            <option value="occupied">Ocupadas</option>
            <option value="vacant">Vazias</option>
          </select>
          <select
            v-model="towerFilter"
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todas as Torres</option>
            <option v-for="tower in towers" :key="tower.id" :value="tower.id">
              {{ tower.name }}
            </option>
          </select>
          <select
            v-model="blockFilter"
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todos os Blocos</option>
            <option v-for="block in blocks" :key="block.id" :value="block.id">
              {{ block.name }}
            </option>
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
        <NeonButton @click="navigateToUnitsManagement" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Gerenciar Unidades
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
              <span v-if="statusFilter !== 'all'" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
                Status: {{ getStatusLabel(statusFilter) }}
              </span>
              <span v-if="towerFilter !== 'all'" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
                Torre: {{ getTowerLabel(towerFilter) }}
              </span>
              <span v-if="blockFilter !== 'all'" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
                Bloco: {{ getBlockLabel(blockFilter) }}
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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhuma unidade encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ search ? 'Tente ajustar sua busca' : 'Acesse o gerenciamento de unidades para criar novas unidades' }}
        </p>
        <div class="mt-6">
          <NeonButton @click="navigateToUnitsManagement" size="md">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Gerenciar Unidades
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
                    :class="getStatusBadgeClass(unit)">
                {{ getStatusLabel(unit) }}
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
            <NeonButton @click="editUnit(unit)" size="sm" class="flex-1 bg-blue-600/40 hover:bg-blue-600/60">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </NeonButton>
            <NeonButton @click="deleteUnit(unit)" size="sm" class="flex-1 bg-red-600/40 hover:bg-red-600/60">
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
  name: 'UnitsListView',
  components: { AuthenticatedLayout, NeonButton },
  setup() {
    const router = useRouter();
    const units = ref([]);
    const towers = ref([]);
    const blocks = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const statusFilter = ref('all');
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
      const totalUnidades = units.value.length;
      const ocupadas = units.value.filter(u => u.owner || u.tenant).length;
      const vazias = units.value.filter(u => !u.owner && !u.tenant).length;
      const taxaOcupacao = totalUnidades > 0 ? Math.round((ocupadas / totalUnidades) * 100) : 0;

      return {
        totalUnidades,
        ocupadas,
        vazias,
        taxaOcupacao: `${taxaOcupacao}%`
      };
    });

    const clearFilters = () => {
      search.value = '';
      statusFilter.value = 'all';
      towerFilter.value = 'all';
      blockFilter.value = 'all';
    };

    const isFilterActive = computed(() => {
      return search.value || statusFilter.value !== 'all' || towerFilter.value !== 'all' || blockFilter.value !== 'all';
    });

    const getStatusLabel = (unit) => {
      if (unit.owner || unit.tenant) {
        return 'Ocupada';
      }
      return 'Vazia';
    };

    const getStatusBadgeClass = (unit) => {
      if (unit.owner || unit.tenant) {
        return 'bg-blue-500/20 text-blue-300 border border-blue-500/30';
      }
      return 'bg-orange-500/20 text-orange-300 border border-orange-500/30';
    };

    const getTypeLabel = (type) => {
      const labels = {
        'apartment': 'Apartamento',
        'house': 'Casa',
        'commercial': 'Comercial'
      };
      return labels[type] || type;
    };

    const getTowerLabel = (towerId) => {
      const tower = towers.value.find(t => t.id === towerId);
      return tower ? tower.name : 'Todas as Torres';
    };

    const getBlockLabel = (blockId) => {
      const block = blocks.value.find(b => b.id === blockId);
      return block ? block.name : 'Todos os Blocos';
    };

    const loadData = async () => {
      try {
        const token = localStorage.getItem('token');
        
        // Carregar unidades
        const url = buildUrlWithCondominiumFilter('/api/apartments');
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
          error.value = 'Falha ao carregar dados';
          console.error('Erro na API:', response.status, response.statusText);
          units.value = [];
        }
      } catch (e) {
        error.value = 'Erro de conexão';
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
        const matchesSearch = !search.value || 
          unit.number.toLowerCase().includes(search.value.toLowerCase());
        
        // Filtro por status
        let matchesFilter = false;
        if (statusFilter.value === 'all') {
          matchesFilter = true;
        } else if (statusFilter.value === 'occupied') {
          matchesFilter = unit.owner || unit.tenant;
        } else if (statusFilter.value === 'vacant') {
          matchesFilter = !unit.owner && !unit.tenant;
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

    const navigateToUnitsManagement = () => {
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
      search,
      statusFilter,
      towerFilter,
      blockFilter,
      filteredUnits,
      navigateToUnitsManagement,
      editUnit,
      deleteUnit,
      clearFilters,
      isFilterActive,
      getStatusLabel,
      getStatusBadgeClass,
      getTypeLabel,
      getTowerLabel,
      getBlockLabel,
      selectedCondominiumName,
      stats
    };
  }
};
</script> 