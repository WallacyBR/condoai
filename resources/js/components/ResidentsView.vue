<template>
  <AuthenticatedLayout page-title="Moradores">
    <div class="space-y-6">
      <TopCard title="Moradores" :icon="UserIcon">
        <template #actions>
          <NeonButton @click="openNewResidentPage" size="md">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Morador
          </NeonButton>
        </template>
      </TopCard>
      <!-- Aviso quando nenhum condomínio está selecionado -->
      <div v-if="!selectedCondominiumName && !loading" class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl p-4">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <span class="text-amber-800 dark:text-amber-300 font-semibold text-sm">Nenhum condomínio selecionado</span>
        </div>
        <p class="text-amber-700 dark:text-amber-400 text-xs mt-1">Selecione um condomínio no menu superior para visualizar os moradores.</p>
      </div>

      <!-- Aviso quando filtrando por unidade específica -->
      <div v-if="filteredByApartment && selectedCondominiumName" class="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/50 rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="text-blue-800 dark:text-blue-300 font-semibold text-sm">Filtrando por unidade específica</span>
          </div>
          <NeonButton @click="clearApartmentFilter" size="sm" variant="secondary">
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Ver Todos
          </NeonButton>
        </div>
        <p class="text-blue-700 dark:text-blue-400 text-xs mt-1">
          Mostrando apenas moradores da Unidade {{ filteredByApartment.number }}
          {{ filteredByApartment.block ? `- ${filteredByApartment.block}` : '' }}
          {{ filteredByApartment.tower ? `- ${filteredByApartment.tower}` : '' }}
        </p>
      </div>

      <!-- Cards de Resumo Sóbrios -->
      <div v-if="selectedCondominiumName && !loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <StatsCard label="Proprietários" :value="stats.proprietarios" variant="purple" />
        <StatsCard label="Moradores" :value="stats.moradores" variant="blue" />
        <StatsCard label="Inquilinos" :value="stats.inquilinos" variant="orange" />
        <StatsCard label="Ativos" :value="stats.ativos" variant="green" />
        <StatsCard label="Inativos" :value="stats.inativos" variant="primary" />
      </div>

      <!-- Header Actions / Filtros -->
      <div v-if="selectedCondominiumName" class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
          <div class="relative flex-1 sm:w-64">
            <input
              type="text"
              v-model="search"
              placeholder="Buscar moradores..."
              class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-sm shadow-sm"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          <select
            v-model="filter"
            class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-sm shadow-sm"
          >
            <option value="all">Todos os Status</option>
            <option value="active">Ativos</option>
            <option value="inactive">Inativos</option>
          </select>
          <select
            v-model="classificationFilter"
            class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-sm shadow-sm"
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
            variant="secondary"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Limpar
          </NeonButton>
        </div>
      </div>

      <!-- Filter Status -->
      <div v-if="isFilterActive && selectedCondominiumName" class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl p-3.5">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
          <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Filtros Ativos:</span>
            <span v-if="search" class="px-2 py-0.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 text-xs rounded-full font-medium border border-indigo-200 dark:border-indigo-800">
              Busca: "{{ search }}"
            </span>
            <span v-if="filter !== 'all'" class="px-2 py-0.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 text-xs rounded-full font-medium border border-indigo-200 dark:border-indigo-800">
              Status: {{ filter === 'active' ? 'Ativos' : 'Inativos' }}
            </span>
            <span v-if="classificationFilter !== 'all'" class="px-2 py-0.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 text-xs rounded-full font-medium border border-indigo-200 dark:border-indigo-800">
              Tipo: {{ getClassificationLabel(classificationFilter) }}
            </span>
          </div>
          <div class="text-xs font-medium text-slate-500 dark:text-slate-400">
            {{ filteredResidents.length }} de {{ residents.length }} moradores
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-indigo-600 border-t-transparent"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl p-4 text-red-700 dark:text-red-300 text-sm">
        {{ error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredResidents.length === 0 && selectedCondominiumName" class="text-center py-12 bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-800 rounded-xl p-8">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="mt-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Nenhum morador encontrado</h3>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          {{ search ? 'Tente ajustar sua busca ou limpar os filtros' : 'Comece adicionando um novo morador ao condomínio' }}
        </p>
      </div>

      <!-- Residents Cards Grid Sóbrio -->
      <div v-else-if="selectedCondominiumName" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div 
          v-for="resident in filteredResidents" 
          :key="resident.id" 
          class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors"
        >
          <!-- Header com Avatar e Status -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="h-11 w-11 rounded-full border border-slate-200 dark:border-slate-700 overflow-hidden flex-shrink-0">
                <ResidentPhoto 
                  :photo="resident.photo"
                  :name="resident.name"
                  :active="resident.active"
                  :show-status="true"
                />
              </div>
              <div class="min-w-0">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 truncate">{{ resident.name }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ resident.email }}</p>
              </div>
            </div>
            <div class="flex flex-col items-end space-y-1">
              <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full"
                    :class="resident.active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'">
                {{ resident.active ? 'Ativo' : 'Inativo' }}
              </span>
              <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                {{ getClassificationLabel(resident.classification) }}
              </span>
            </div>
          </div>

          <!-- Informações de Contato -->
          <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3 mb-4 border border-slate-100 dark:border-slate-800">
            <div class="flex items-center space-x-2 mb-1">
              <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Telefone:</span>
            </div>
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ resident.cell_phone || 'Não informado' }}</p>
          </div>

          <!-- Ações -->
          <div class="flex space-x-2">
            <NeonButton @click="editResident(resident)" size="sm" variant="secondary" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </NeonButton>
            <NeonButton @click="showDeleteConfirmation(resident)" size="sm" variant="danger" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Excluir
            </NeonButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação de Exclusão Sóbrio -->
    <div v-if="deleteConfirmation.show" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click="closeDeleteConfirmation">
      <div class="bg-white dark:bg-slate-900 rounded-xl shadow-2xl p-6 border border-slate-200 dark:border-slate-800 max-w-md w-full" @click.stop>
        <div class="flex items-center mb-4">
          <div class="w-10 h-10 bg-red-50 dark:bg-red-950/40 text-red-600 rounded-lg flex items-center justify-center mr-3.5 flex-shrink-0">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Confirmar Exclusão</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Esta ação não pode ser desfeita</p>
          </div>
        </div>

        <div class="mb-5">
          <p class="text-sm text-slate-600 dark:text-slate-300 mb-2">
            Tem certeza que deseja remover este morador do condomínio?
          </p>
          
          <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
            <p class="text-slate-900 dark:text-slate-100 text-sm font-semibold">
              {{ deleteConfirmation.itemName }}
            </p>
            <div v-if="deleteConfirmation.item" class="mt-2 pt-2 border-t border-slate-200 dark:border-slate-700">
              <div class="space-y-1 text-xs text-slate-500 dark:text-slate-400">
                <div>Email: <span class="text-slate-700 dark:text-slate-300">{{ deleteConfirmation.item.email || 'Não informado' }}</span></div>
                <div>Telefone: <span class="text-slate-700 dark:text-slate-300">{{ deleteConfirmation.item.cell_phone || 'Não informado' }}</span></div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-3 justify-end">
          <NeonButton 
            @click="closeDeleteConfirmation"
            size="md"
            variant="secondary"
          >
            Cancelar
          </NeonButton>
          <NeonButton 
            @click="confirmDelete"
            size="md"
            variant="danger"
            :loading="deleteConfirmation.loading"
          >
            Confirmar Exclusão
          </NeonButton>
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
import ResidentPhoto from './ResidentPhoto.vue';
import TopCard from './ui/TopCard.vue';
import StatsCard from './ui/StatsCard.vue';
import { apiRequest } from '../utils/api';

export default {
  name: 'ResidentsView',
  components: { AuthenticatedLayout, NeonButton, ResidentPhoto, TopCard, StatsCard },
  setup() {
    const router = useRouter();
    const UserIcon = {
      render() {
        return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })
        ]);
      }
    };
    const residents = ref([]);
    const condominiums = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const filter = ref('all');
    const classificationFilter = ref('all');

    // Estado para modal de confirmação de exclusão
    const deleteConfirmation = ref({
      show: false,
      item: null,
      itemName: '',
      loading: false
    });

    // Variáveis para gerenciar filtro por condomínio
    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName'));
    
    // Variável para rastrear filtro por unidade
    const filteredByApartment = ref(null);

    // Função para atualizar as informações do condomínio selecionado
    const updateSelectedCondominium = () => {
      selectedCondominiumName.value = localStorage.getItem('selectedCondominiumName');
    };

    // Função para construir URL com filtro por condomínio
    const buildUrlWithCondominiumFilter = (baseUrl) => {
      const urlParams = new URLSearchParams();
      
      const currentUrlParams = new URLSearchParams(window.location.search);
      const apartmentId = currentUrlParams.get('apartment_id');
      if (apartmentId) {
        urlParams.append('apartment_id', apartmentId);
      }
      
      const queryString = urlParams.toString();
      return queryString ? `${baseUrl}?${queryString}` : baseUrl;
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

    const clearFilters = () => {
      search.value = '';
      filter.value = 'all';
      classificationFilter.value = 'all';
    };

    const clearApartmentFilter = () => {
      router.push('/residents');
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
        const currentUrlParams = new URLSearchParams(window.location.search);
        const apartmentId = currentUrlParams.get('apartment_id');
        
        if (apartmentId) {
          try {
            const apartmentData = await apiRequest(`/apartments/${apartmentId}`);
            filteredByApartment.value = {
              id: apartmentData.id,
              number: apartmentData.number,
              block: apartmentData.block?.name || '',
              tower: apartmentData.tower?.name || ''
            };
          } catch {
            filteredByApartment.value = null;
          }
        } else {
          filteredByApartment.value = null;
        }
        
        const url = buildUrlWithCondominiumFilter('/residents');
        const data = await apiRequest(url);
        residents.value = Array.isArray(data) ? data : (data?.data || []);
      } catch (e) {
        error.value = e.message || 'Erro ao carregar moradores';
        residents.value = [];
      } finally {
        loading.value = false;
      }
    };

    const loadCondominiums = async () => {
      try {
        const data = await apiRequest('/condominiums');
        condominiums.value = Array.isArray(data) ? data : (data?.data || []);
      } catch {
        condominiums.value = [];
      }
    };

    const filteredResidents = computed(() => {
      return residents.value.filter(resident => {
        // Filtro por busca (nome ou email)
        const matchesSearch = !search.value || 
          (resident.name || '').toLowerCase().includes(search.value.toLowerCase()) ||
          (resident.email || '').toLowerCase().includes(search.value.toLowerCase());
        
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
        return matchesSearch && matchesFilter && matchesClassification;
      });
    });

    const openNewResidentPage = () => {
      router.push('/residents/new');
    };

    const editResident = (resident) => {
      router.push(`/residents/${resident.id}/edit`);
    };

    const deleteResident = async (resident) => {
      // Configurar modal de confirmação
      deleteConfirmation.value = {
        show: true,
        item: resident,
        itemName: `Morador: ${resident.name}`,
        loading: false
      };
    };

    // Funções do modal de confirmação
    const showDeleteConfirmation = (resident) => {
      deleteConfirmation.value = {
        show: true,
        item: resident,
        itemName: `Morador: ${resident.name}`,
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
        await apiRequest(`/residents/${item.id}`, { method: 'DELETE' });
        residents.value = residents.value.filter(r => r.id !== item.id);
        closeDeleteConfirmation();
      } catch {
        deleteConfirmation.value.loading = false;
      }
    };

    // Carregar dados ao montar o componente
    onMounted(() => {
      loadData();
      loadCondominiums();
      
      // Configurar listeners para mudanças de condomínio
      window.addEventListener('condominiumSelected', () => {
        updateSelectedCondominium();
        loadData();
      });

      window.addEventListener('storage', (e) => {
        if (e.key === 'selectedCondominium') {
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
      clearApartmentFilter,
      isFilterActive,
      getClassificationLabel,
      getClassificationBadgeClass,
      selectedCondominiumName,
      filteredByApartment,
      stats,
      deleteConfirmation,
      showDeleteConfirmation,
      closeDeleteConfirmation,
      confirmDelete,
      UserIcon
    };
  }
};
</script>
