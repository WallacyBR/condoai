<template>
  <AuthenticatedLayout page-title="Correspondências">
    <div class="space-y-6">
      <!-- Aviso quando nenhum condomínio está selecionado -->
      <div v-if="!selectedCondominiumName && !loading" class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <span class="text-yellow-300 font-medium">Nenhum condomínio selecionado</span>
        </div>
        <p class="text-yellow-200 text-sm mt-1">Selecione um condomínio para visualizar as correspondências.</p>
      </div>

      <!-- Cards de Resumo -->
      <div v-if="selectedCondominiumName && !loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total de Correspondências -->
        <div class="bg-gradient-to-br from-purple-900/50 via-purple-800/50 to-purple-900/50 rounded-2xl p-6 border-2 border-purple-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-purple-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-300 text-sm font-medium">Total de Correspondências</p>
              <p class="text-2xl font-bold text-purple-100">{{ stats.total }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-purple-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Pendentes -->
        <div class="bg-gradient-to-br from-orange-900/50 via-orange-800/50 to-orange-900/50 rounded-2xl p-6 border-2 border-orange-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-orange-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-300 text-sm font-medium">Pendentes</p>
              <p class="text-2xl font-bold text-orange-100">{{ stats.pendentes }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-orange-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Retiradas -->
        <div class="bg-gradient-to-br from-green-900/50 via-green-800/50 to-green-900/50 rounded-2xl p-6 border-2 border-green-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-green-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-300 text-sm font-medium">Retiradas</p>
              <p class="text-2xl font-bold text-green-100">{{ stats.retiradas }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Taxa de Retirada -->
        <div class="bg-gradient-to-br from-blue-900/50 via-blue-800/50 to-blue-900/50 rounded-2xl p-6 border-2 border-blue-500/30 shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-blue-500/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-300 text-sm font-medium">Taxa de Retirada</p>
              <p class="text-2xl font-bold text-blue-100">{{ stats.taxaRetirada }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Header Actions -->
      <div v-if="selectedCondominiumName" class="flex flex-wrap justify-between items-center gap-4 mb-2">
        <div class="flex flex-wrap gap-2 items-center">
          <div class="relative">
            <input
              type="text"
              v-model="search"
              placeholder="Buscar por unidade, torre ou bloco..."
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
          <select
            v-model="statusFilter"
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todos os Status</option>
            <option value="pending">Pendentes</option>
            <option value="retrieved">Retiradas</option>
          </select>
          <input
            type="date"
            v-model="dateFilter"
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          />
          <input
            type="text"
            v-model="locationFilter"
            placeholder="Local de retirada..."
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          />
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
        <div class="flex space-x-2">
          <NeonButton @click="navigateToDelivery" size="md" class="bg-cyan-600/40 hover:bg-cyan-600/60">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2M8 7h8M9 12h6" />
            </svg>
            Entrega
          </NeonButton>
          <NeonButton @click="navigateToNew" size="md">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Correspondência
          </NeonButton>
        </div>
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
                Status: {{ getStatusFilterLabel(statusFilter) }}
              </span>
              <span v-if="dateFilter" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
                Data: {{ formatDate(dateFilter) }}
              </span>
              <span v-if="locationFilter" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
                Local: "{{ locationFilter }}"
              </span>
            </div>
          </div>
          <div class="text-sm text-indigo-400">
            {{ filteredPackages.length }} de {{ packages.length }} correspondências
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
      <div v-else-if="filteredPackages.length === 0 && selectedCondominiumName" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhuma correspondência encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ search || statusFilter !== 'all' || dateFilter || locationFilter ? 'Tente ajustar os filtros de busca' : 'Use o botão "Nova Correspondência" acima para registrar uma nova correspondência' }}
        </p>
      </div>

      <!-- Packages Cards Grid -->
      <div v-else-if="selectedCondominiumName" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="pkg in filteredPackages" 
          :key="pkg.id" 
          class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300 hover:shadow-2xl hover:scale-105"
        >
          <!-- Header com Status -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center border-2 border-indigo-500/30">
                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">Unidade {{ pkg.apartment.number }}</h3>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                  <span v-if="pkg.apartment && pkg.apartment.tower" class="text-xs px-2 py-1 rounded-full bg-blue-900/40 text-blue-200 border border-blue-700/30">{{ pkg.apartment.tower.name }}</span>
                  <span v-if="pkg.apartment && pkg.apartment.block" class="text-xs px-2 py-1 rounded-full bg-green-900/40 text-green-200 border border-green-700/30">{{ pkg.apartment.block.name }}</span>
                </div>
                <p class="text-sm text-gray-400 mt-2">{{ formatDate(pkg.created_at) }}</p>
              </div>
            </div>
            <div class="flex flex-col items-end space-y-1">
              <span class="px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getStatusBadgeClass(pkg)">
                {{ getStatusLabel(pkg) }}
              </span>
            </div>
          </div>

          <!-- Informações da Correspondência -->
          <div class="bg-gray-800/50 rounded-lg p-4 mb-4 border border-gray-700/50">
            <div class="space-y-2">
              <!-- Local de Retirada -->
              <div class="flex items-center space-x-2">
                <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-sm text-blue-300">{{ pkg.pickup_location }}</span>
              </div>

              <!-- Observação -->
              <div v-if="pkg.observation" class="mt-3">
                <div class="flex items-center space-x-2">
                  <svg class="h-4 w-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4M7 16h10M3 4h18a2 2 0 012 2v12a2 2 0 01-2 2H3a2 2 0 01-2-2V6a2 2 0 012-2z" />
                  </svg>
                  <span class="text-sm text-yellow-300">Observação</span>
                </div>
                <p class="text-white text-sm mt-1">{{ pkg.observation }}</p>
              </div>

              <!-- Data de Retirada -->
              <div v-if="pkg.retrieved_at" class="mt-3">
                <div class="flex items-center space-x-2">
                  <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span class="text-sm text-green-300">Retirada em</span>
                </div>
                <p class="text-white text-sm mt-1">{{ formatDate(pkg.retrieved_at) }}</p>
              </div>

              <!-- Responsável -->
              <div v-if="pkg.user" class="mt-3">
                <div class="flex items-center space-x-2">
                  <svg class="h-4 w-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span class="text-sm text-purple-300">Registrado por</span>
                </div>
                <p class="text-white text-sm mt-1">{{ pkg.user.name }}</p>
              </div>
            </div>
          </div>

          <!-- Ações -->
          <div class="flex space-x-2">
            <NeonButton @click="openInfo(pkg)" size="sm" class="flex-1 bg-blue-600/40 hover:bg-blue-600/60">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Info
            </NeonButton>
            <NeonButton @click="editPackage(pkg)" size="sm" class="flex-1 bg-indigo-600/40 hover:bg-indigo-600/60">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </NeonButton>
            <NeonButton @click="showDeleteConfirmation(pkg)" size="sm" class="flex-1 bg-red-600/40 hover:bg-red-600/60">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Excluir
            </NeonButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Informações -->
    <PackageInfoModal v-if="showInfo" :pkg="selected" @close="showInfo = false" />

    <!-- Modal de Confirmação de Exclusão -->
    <div v-if="deleteConfirmation.show" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" @click="closeDeleteConfirmation">
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-2xl p-6 border-2 border-red-500/30 max-w-md w-full mx-4" @click.stop>
        <div class="flex items-center mb-4">
          <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center mr-4">
            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-white">Confirmar Exclusão</h3>
            <p class="text-sm text-gray-400">Esta ação não pode ser desfeita</p>
          </div>
        </div>

        <div class="mb-6">
          <p class="text-gray-300 mb-2">
            Tem certeza que deseja excluir esta correspondência?
          </p>
          
          <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-3">
            <p class="text-red-300 text-sm font-medium">
              {{ deleteConfirmation.itemName }}
            </p>
            <!-- Informações adicionais da correspondência -->
            <div v-if="deleteConfirmation.item" class="mt-2 pt-2 border-t border-red-500/20">
              <div class="space-y-1 text-xs text-gray-400">
                <div>
                  <span class="text-gray-500">Unidade:</span>
                  <span class="text-gray-300 ml-1">{{ formatApartmentDescription(deleteConfirmation.item.apartment) }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Local:</span>
                  <span class="text-gray-300 ml-1">{{ deleteConfirmation.item.pickup_location }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Data:</span>
                  <span class="text-gray-300 ml-1">{{ formatDate(deleteConfirmation.item.created_at) }}</span>
                </div>
                <div v-if="deleteConfirmation.item.observation">
                  <span class="text-gray-500">Observação:</span>
                  <span class="text-gray-300 ml-1">{{ deleteConfirmation.item.observation }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-3">
          <button 
            @click="closeDeleteConfirmation"
            class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors"
          >
            Cancelar
          </button>
          <button 
            @click="confirmDelete"
            :disabled="deleteConfirmation.loading"
            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-800 disabled:cursor-not-allowed text-white rounded-lg transition-colors flex items-center justify-center"
          >
            <span v-if="!deleteConfirmation.loading">Excluir</span>
            <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';
import { apiRequest } from '../utils/api';
import { formatApartmentDescription } from '../utils/formatters';
import PackageInfoModal from './PackageInfoModal.vue';

export default {
  name: 'PackagesView',
  components: { AuthenticatedLayout, NeonButton, PackageInfoModal },
  setup() {
    const router = useRouter();
    const packages = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const statusFilter = ref('all');
    const dateFilter = ref('');
    const locationFilter = ref('');
    const towerFilter = ref('all');
    const blockFilter = ref('all');
    const towers = ref([]);
    const blocks = ref([]);
    const selected = ref(null);
    const showInfo = ref(false);

    // Estado para modal de confirmação de exclusão
    const deleteConfirmation = ref({
      show: false,
      item: null,
      itemName: '',
      loading: false
    });

    // Variáveis para gerenciar filtro por condomínio
    const selectedCondominiumId = ref(localStorage.getItem('selectedCondominium'));
    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName'));

    // Função para atualizar as informações do condomínio selecionado
    const updateSelectedCondominium = () => {
      selectedCondominiumId.value = localStorage.getItem('selectedCondominium');
      selectedCondominiumName.value = localStorage.getItem('selectedCondominiumName');
    };

    // Computed para calcular estatísticas
    const stats = computed(() => {
      const total = packages.value.length;
      const retiradas = packages.value.filter(p => p.retrieved_at).length;
      const pendentes = packages.value.filter(p => !p.retrieved_at).length;
      const taxaRetirada = total > 0 ? Math.round((retiradas / total) * 100) : 0;

      return {
        total,
        retiradas,
        pendentes,
        taxaRetirada: `${taxaRetirada}%`
      };
    });

    const clearFilters = () => {
      search.value = '';
      statusFilter.value = 'all';
      dateFilter.value = '';
      locationFilter.value = '';
    };

    const isFilterActive = computed(() => {
      return search.value || statusFilter.value !== 'all' || dateFilter.value || locationFilter.value;
    });

    const getStatusLabel = (pkg) => {
      return pkg.retrieved_at ? 'Retirada' : 'Pendente';
    };

    const getStatusBadgeClass = (pkg) => {
      if (pkg.retrieved_at) {
        return 'bg-green-500/20 text-green-300 border border-green-500/30';
      }
      return 'bg-orange-500/20 text-orange-300 border border-orange-500/30';
    };

    const getStatusFilterLabel = (status) => {
      const labels = {
        'pending': 'Pendentes',
        'retrieved': 'Retiradas',
        'all': 'Todos'
      };
      return labels[status] || status;
    };

    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('pt-BR');
    };

    const buildUrl = () => {
      let url = '/api/packages';
      const params = [];
      const condo = localStorage.getItem('selectedCondominium');
      if (condo) params.push('condominium_id=' + condo);
      if (statusFilter.value !== 'all') params.push('status=' + statusFilter.value);
      if (dateFilter.value) params.push('date=' + dateFilter.value);
      if (locationFilter.value) params.push('pickup_location=' + locationFilter.value);
      if (search.value) params.push('unit=' + search.value);
      if (params.length) url += '?' + params.join('&');
      return url;
    };

    const loadPackages = async () => {
      try {
        loading.value = true;
        error.value = '';
        const data = await apiRequest(buildUrl());
        if (data) {
          packages.value = Array.isArray(data) ? data : (data.data || []);
          // Atualizar listas de torres e blocos para os filtros
          const towerMap = new Map();
          const blockMap = new Map();
          packages.value.forEach(pkg => {
            if (pkg.apartment && pkg.apartment.tower) {
              towerMap.set(pkg.apartment.tower.id, pkg.apartment.tower);
            }
            if (pkg.apartment && pkg.apartment.block) {
              blockMap.set(pkg.apartment.block.id, pkg.apartment.block);
            }
          });
          towers.value = Array.from(towerMap.values());
          blocks.value = Array.from(blockMap.values());
        } else {
          packages.value = [];
          towers.value = [];
          blocks.value = [];
        }
      } catch (e) {
        error.value = 'Erro ao carregar correspondências';
        packages.value = [];
        towers.value = [];
        blocks.value = [];
      } finally {
        loading.value = false;
      }
    };

    const filteredPackages = computed(() => {
      return packages.value.filter(pkg => {
        // Filtro de busca por unidade, torre ou bloco
        const searchTerm = search.value.toLowerCase();
        const apt = pkg.apartment;
        const matchesSearch = !search.value || (
          (apt && apt.number && apt.number.toLowerCase().includes(searchTerm)) ||
          (apt && apt.tower && apt.tower.name && apt.tower.name.toLowerCase().includes(searchTerm)) ||
          (apt && apt.block && apt.block.name && apt.block.name.toLowerCase().includes(searchTerm)) ||
          (pkg.observation && pkg.observation.toLowerCase().includes(searchTerm))
        );
        // Filtro por torre
        let matchesTower = true;
        if (towerFilter.value !== 'all') {
          matchesTower = apt && apt.tower && String(apt.tower.id) === String(towerFilter.value);
        }
        // Filtro por bloco
        let matchesBlock = true;
        if (blockFilter.value !== 'all') {
          matchesBlock = apt && apt.block && String(apt.block.id) === String(blockFilter.value);
        }
        // Filtro por status
        let matchesStatus = true;
        if (statusFilter.value === 'pending') {
          matchesStatus = !pkg.retrieved_at;
        } else if (statusFilter.value === 'retrieved') {
          matchesStatus = !!pkg.retrieved_at;
        }
        // Filtro por data
        const matchesDate = !dateFilter.value || 
          pkg.created_at?.startsWith(dateFilter.value);
        // Filtro por local
        const matchesLocation = !locationFilter.value ||
          pkg.pickup_location.toLowerCase().includes(locationFilter.value.toLowerCase());
        return matchesSearch && matchesTower && matchesBlock && matchesStatus && matchesDate && matchesLocation;
      });
    });

    const remove = async (pkg) => {
      // Configurar modal de confirmação
      deleteConfirmation.value = {
        show: true,
        item: pkg,
        itemName: `Correspondência para ${formatApartmentDescription(pkg.apartment)}`,
        loading: false
      };
    };

    // Funções do modal de confirmação
    const showDeleteConfirmation = (pkg) => {
      deleteConfirmation.value = {
        show: true,
        item: pkg,
        itemName: `Correspondência para ${formatApartmentDescription(pkg.apartment)}`,
        loading: false
      };
    };

    const closeDeleteConfirmation = () => {
      deleteConfirmation.value.show = false;
      deleteConfirmation.value.loading = false;
    };

    const confirmDelete = async () => {
      const { item } = deleteConfirmation.value;
      deleteConfirmation.value.loading = true;

      try {
        const resp = await apiRequest(`/api/packages/${item.id}`, { method: 'DELETE' });
        if (resp !== null) {
          packages.value = packages.value.filter(p => p.id !== item.id);
          closeDeleteConfirmation();
        }
      } catch (e) {
        error.value = 'Erro ao excluir correspondência';
        deleteConfirmation.value.loading = false;
      }
    };

    const openInfo = (pkg) => {
      selected.value = pkg;
      showInfo.value = true;
    };

    const editPackage = (pkg) => {
      router.push(`/packages/${pkg.id}/edit`);
    };

    const navigateToNew = () => {
      router.push('/packages/new');
    };

    const navigateToDelivery = () => {
      router.push('/packages/deliver');
    };

    // Carregar dados ao montar o componente
    onMounted(() => {
      loadPackages();
      
      // Configurar listeners para mudanças de condomínio
      window.addEventListener('condominiumSelected', () => {
        updateSelectedCondominium();
        loadPackages();
      });

      window.addEventListener('storage', (e) => {
        if (e.key === 'selectedCondominium') {
          updateSelectedCondominium();
          loadPackages();
        }
      });
    });

    // Recarregar quando os filtros mudarem
    watch([statusFilter, dateFilter, locationFilter], () => {
      loadPackages();
    });

    return {
      packages,
      loading,
      error,
      search,
      statusFilter,
      dateFilter,
      locationFilter,
      filteredPackages,
      selected,
      showInfo,
      selectedCondominiumName,
      stats,
      clearFilters,
      isFilterActive,
      getStatusLabel,
      getStatusBadgeClass,
      getStatusFilterLabel,
      formatDate,
      loadPackages,
      remove,
      openInfo,
      editPackage,
      navigateToNew,
      navigateToDelivery,
      formatApartmentDescription,
      deleteConfirmation,
      showDeleteConfirmation,
      closeDeleteConfirmation,
      confirmDelete,
      towerFilter,
      blockFilter,
      towers,
      blocks
    };
  }
};
</script>
