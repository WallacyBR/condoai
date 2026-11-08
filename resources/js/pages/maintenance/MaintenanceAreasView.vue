<template>
  <AuthenticatedLayout page-title="Áreas de Manutenção">
    <TopCard 
      title="Gerenciar Áreas de Manutenção" 
      subtitle="Controle todas as áreas de manutenção do condomínio"
      color="cyan"
    >
      <template #actions>
        <NeonButton @click="redirectToCreate()" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Nova Área
        </NeonButton>
      </template>
    </TopCard>

    <div class="space-y-6">
      <!-- Cards de Estatísticas -->
      <div v-if="!isLoadingAreas && !error" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <StatsCard
          label="Total de Áreas"
          :value="areas.length"
          variant="blue"
          default-icon-path="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
        />
        <StatsCard
          label="Áreas com Equipamentos"
          :value="areasWithEquipment"
          variant="green"
          default-icon-path="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
        />
        <StatsCard
          label="Áreas Ativas"
          :value="activeAreas"
          variant="purple"
          default-icon-path="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </div>

      <!-- Filtros -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" v-model="filters.name" placeholder="Filtrar por nome da área..."
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
            <input type="text" v-model="filters.description" placeholder="Filtrar por descrição..."
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoadingAreas" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-500"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
        {{ error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredAreas.length === 0" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhuma área encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ filters.name || filters.description ? 'Tente ajustar os filtros de busca' : 'Use o botão "Nova Área" no cabeçalho para criar uma nova área de manutenção' }}
        </p>
      </div>

      <!-- Areas Cards Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="area in filteredAreas" 
          :key="area.id" 
          class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all"
        >
          <!-- Header com Nome e Status -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="h-12 w-12 rounded-full bg-cyan-100 flex items-center justify-center border-2 border-cyan-500/30">
                <svg class="h-6 w-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">{{ area.name }}</h3>
                <p class="text-sm text-gray-400">Área de Manutenção</p>
              </div>
            </div>
            <div class="flex flex-col items-end space-y-1">
              <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-300 border border-green-500/30">
                Ativa
              </span>
            </div>
          </div>

          <!-- Descrição -->
          <div class="mb-4">
            <p class="text-sm text-gray-300">
              {{ area.description || 'Nenhuma descrição disponível' }}
            </p>
          </div>

          <!-- Informações Adicionais -->
          <div class="mb-6 space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-400">Equipamentos:</span>
              <span class="text-cyan-300 font-medium">{{ area.equipments_count || 0 }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-400">Agendamentos:</span>
              <span class="text-cyan-300 font-medium">{{ area.schedules_count || 0 }}</span>
            </div>
          </div>

          <!-- Ações -->
          <div class="flex space-x-2">
            <NeonButton @click="redirectToEdit(area.id)" size="sm" variant="secondary" class="flex-1">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </NeonButton>
            <NeonButton @click="confirmDeleteArea(area.id)" size="sm" variant="danger">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </NeonButton>
          </div>
        </div>
      </div>

      <!-- Paginação -->
      <div v-if="!isLoadingAreas && pagination.total > pagination.per_page" class="flex justify-between items-center">
        <p class="text-sm text-gray-700 dark:text-gray-400">
          Mostrando {{ pagination.from || 0 }} a {{ pagination.to || 0 }} de {{ pagination.total || 0 }} resultados
        </p>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <NeonButton @click="fetchAreas(1)" :disabled="pagination.current_page === 1" size="sm" variant="secondary">
            Primeira
          </NeonButton>
          <NeonButton @click="fetchAreas(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" size="sm" variant="secondary">
            Anterior
          </NeonButton>
          <span class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
          <NeonButton @click="fetchAreas(pagination.current_page + 1)" :disabled="!pagination.next_page_url" size="sm" variant="secondary">
            Próxima
          </NeonButton>
          <NeonButton @click="fetchAreas(pagination.last_page)" :disabled="pagination.current_page === pagination.last_page" size="sm" variant="secondary">
            Última
          </NeonButton>
        </nav>
      </div>
    </div>

    <Modal :show="isDeleteConfirmOpen" @close="isDeleteConfirmOpen = false" max-width="md">
      <template #title>Confirmar Exclusão</template>
      <div class="p-6">
        <p class="text-gray-300">Tem certeza que deseja excluir a área "{{ areaToDelete?.name || 'selecionada' }}"? Esta ação não pode ser desfeita.</p>
      </div>
      <template #footer>
        <NeonButton @click="isDeleteConfirmOpen = false" size="md" variant="secondary">
          Cancelar
        </NeonButton>
        <NeonButton @click="deleteArea" :disabled="isDeleting" size="md" variant="danger" :loading="isDeleting">
          {{ isDeleting ? 'Excluindo...' : 'Excluir' }}
        </NeonButton>
      </template>
    </Modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import TopCard from '@/components/ui/TopCard.vue';
import StatsCard from '@/components/ui/StatsCard.vue';
import Modal from '@/components/Modal.vue';
import NeonButton from '@/components/NeonButton.vue';
import api from '@/utils/api';

const router = useRouter();

const areas = ref([]);
const isLoadingAreas = ref(false);
const error = ref(null);

const filters = ref({
  name: '',
  description: ''
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
    per_page: 15,
    next_page_url: null,
    prev_page_url: null,
});

const isDeleteConfirmOpen = ref(false);
const areaToDeleteId = ref(null);
const isDeleting = ref(false);

const areaToDelete = computed(() => {
    return areas.value.find(a => a.id === areaToDeleteId.value);
});

// Computed properties para estatísticas
const areasWithEquipment = computed(() => {
    return areas.value.filter(area => (area.equipments_count || 0) > 0).length;
});

const activeAreas = computed(() => {
    return areas.value.length; // Assumindo que todas as áreas estão ativas
});

const filteredAreas = computed(() => {
    let filtered = areas.value;
    
    if (filters.value.name) {
        filtered = filtered.filter(area => 
            area.name.toLowerCase().includes(filters.value.name.toLowerCase())
        );
    }
    
    if (filters.value.description) {
        filtered = filtered.filter(area => 
            area.description && area.description.toLowerCase().includes(filters.value.description.toLowerCase())
        );
    }
    
    return filtered;
});

// Debounce para filtros
let debounceTimer = null;

async function fetchAreas(page = 1) {
  isLoadingAreas.value = true;
  error.value = null;
  try {
    const params = {
        page,
        per_page: pagination.value.per_page,
        ...filters.value
    };
    
    // Remover filtros vazios
    for (const key in params) {
        if (params[key] === '' || params[key] === null) {
            delete params[key];
        }
    }

    const response = await api.get('/maintenance/areas', { params });
    areas.value = response.data.data || [];

    const { current_page, last_page, from, to, total, per_page: respPerPage, next_page_url, prev_page_url } = response.data;
    pagination.value = { current_page, last_page, from, to, total, per_page: respPerPage || pagination.value.per_page, next_page_url, prev_page_url };

  } catch (err) {
    console.error('Erro ao buscar áreas:', err);
    error.value = 'Não foi possível carregar as áreas. Tente novamente mais tarde.';
    areas.value = [];
  } finally {
    isLoadingAreas.value = false;
  }
}

function redirectToCreate() {
  router.push('/maintenance/areas/create');
}

function redirectToEdit(id) {
  router.push(`/maintenance/areas/${id}/edit`);
}

function confirmDeleteArea(id) {
    areaToDeleteId.value = id;
    isDeleteConfirmOpen.value = true;
}

async function deleteArea() {
    if (!areaToDeleteId.value) return;
    isDeleting.value = true;
    try {
        await api.delete(`/maintenance/areas/${areaToDeleteId.value}`);
        let currentPage = pagination.value.current_page;
        if (areas.value.length === 1 && currentPage > 1) {
            currentPage--;
        }
        fetchAreas(currentPage);
    } catch (error) {
        console.error('Erro ao excluir área:', error);
        if (error.response && error.response.data.message) {
            alert(`Erro ao excluir: ${error.response.data.message}`);
        } else {
            alert('Ocorreu um erro ao excluir a área.');
        }
    } finally {
        isDeleteConfirmOpen.value = false;
        areaToDeleteId.value = null;
        isDeleting.value = false;
    }
}

// Watch para filtros com debounce
watch(filters, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchAreas(1); // Resetar para a primeira página ao filtrar
    }, 500); // 500ms debounce
}, { deep: true });

onMounted(() => {
  fetchAreas();
});
</script>
