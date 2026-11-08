<template>
  <AuthenticatedLayout page-title="Minhas Manutenções Concluídas">
    <div class="p-4 sm:p-6 lg:p-8">
      <!-- Header com filtros -->
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30 mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
          <h2 class="text-2xl font-semibold text-white">Minhas Manutenções Concluídas</h2>
          <div class="flex items-end space-x-3">
            <button @click="refreshData" 
                    class="p-2 bg-cyan-600/20 hover:bg-cyan-600/30 rounded-lg transition-colors duration-200"
                    :disabled="isLoading">
              <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Filtros de Data -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="flex flex-col">
            <label for="start-date" class="text-sm font-medium text-gray-300 mb-1">Data Inicial</label>
            <input type="date" id="start-date" v-model="filters.startDate" @change="applyFilters"
                   class="bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 p-3">
          </div>
          <div class="flex flex-col">
            <label for="end-date" class="text-sm font-medium text-gray-300 mb-1">Data Final</label>
            <input type="date" id="end-date" v-model="filters.endDate" @change="applyFilters"
                   class="bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 p-3">
          </div>
          <div class="flex items-end">
            <button @click="clearFilters" 
                    class="w-full px-4 py-3 bg-gray-600/40 hover:bg-gray-600/60 text-white rounded-lg transition-colors duration-200">
              Limpar Filtros
            </button>
          </div>
        </div>

        <!-- Status dos Filtros -->
        <div v-if="hasActiveFilters" class="mt-4 bg-cyan-500/10 border border-cyan-500/30 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
                </svg>
                <span class="text-cyan-300 font-medium">Filtros Ativos:</span>
              </div>
              <div class="flex space-x-2">
                <span v-if="filters.startDate" class="px-2 py-1 bg-cyan-500/20 text-cyan-300 text-sm rounded-full border border-cyan-500/30">
                  Desde: {{ formatDate(filters.startDate) }}
                </span>
                <span v-if="filters.endDate" class="px-2 py-1 bg-cyan-500/20 text-cyan-300 text-sm rounded-full border border-cyan-500/30">
                  Até: {{ formatDate(filters.endDate) }}
                </span>
              </div>
            </div>
            <div class="text-sm text-cyan-400">
              {{ records.length }} {{ records.length === 1 ? 'registro encontrado' : 'registros encontrados' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center text-gray-400 py-16">
        <div class="relative">
          <svg class="animate-spin h-12 w-12 text-cyan-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="text-lg font-medium">Carregando manutenções...</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="records.length === 0" class="text-center text-gray-400 py-16">
        <div class="bg-gray-800/30 backdrop-blur-md rounded-2xl p-8 border border-gray-600/30">
          <div class="w-24 h-24 bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-white mb-2">Nenhuma manutenção encontrada</h3>
          <p class="text-gray-400 mb-6">
            {{ hasActiveFilters ? 'Não há manutenções concluídas no período selecionado.' : 'Você ainda não concluiu nenhuma manutenção.' }}
          </p>
        </div>
      </div>

      <!-- Records List -->
      <div v-else class="space-y-4">
        <div v-for="(record, index) in records" :key="record.id"
             class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50 hover:border-green-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-green-500/10"
             :style="{ animationDelay: `${index * 100}ms` }">
          
          <!-- Record Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-4 flex-1">
              <!-- Completed Icon -->
              <div class="h-10 w-10 rounded-full bg-green-500/20 flex items-center justify-center">
                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              
              <!-- Record Info -->
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-white mb-1">{{ record.schedule_title || 'Manutenção' }}</h3>
                <p class="text-sm text-gray-400">{{ record.equipment_name || 'Equipamento não especificado' }}</p>
              </div>
            </div>
            
            <!-- Status Badge -->
            <div class="flex items-center space-x-2 bg-green-500/20 px-3 py-1 rounded-full border border-green-500/30">
              <div class="w-2 h-2 bg-green-400 rounded-full"></div>
              <span class="text-xs font-medium text-green-400">Concluído</span>
            </div>
          </div>

          <!-- Record Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Date and Time -->
            <div class="bg-gray-700/30 rounded-xl p-4 border border-gray-600/30">
              <div class="flex items-center space-x-3">
                <div class="h-8 w-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                  <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-400">Data de Realização</p>
                  <p class="text-sm font-semibold text-white">{{ formatDateTime(record.performed_at) }}</p>
                </div>
              </div>
            </div>
            
            <!-- Photos Count -->
            <div class="bg-gray-700/30 rounded-xl p-4 border border-gray-600/30">
              <div class="flex items-center space-x-3">
                <div class="h-8 w-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                  <svg class="h-4 w-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-400">Fotos</p>
                  <p class="text-sm font-semibold text-white">{{ record.photos?.length || 0 }} foto(s)</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="record.notes" class="bg-gray-700/30 rounded-xl p-4 border border-gray-600/30 mb-4">
            <h5 class="text-sm font-medium text-gray-400 mb-2">Observações</h5>
            <p class="text-white text-sm leading-relaxed">{{ record.notes }}</p>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-600/30">
            <button @click="viewDetails(record)"
                    class="px-4 py-2 bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-400 rounded-lg transition-colors duration-200 flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
              <span>Ver Detalhes</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Paginação -->
      <div v-if="pagination.total > pagination.per_page" class="mt-8 flex justify-center">
        <div class="flex items-center space-x-2">
          <button @click="changePage(pagination.current_page - 1)"
                  :disabled="pagination.current_page <= 1"
                  class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-800 border border-gray-600 rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
            Anterior
          </button>
          
          <span class="px-4 py-2 text-sm text-gray-300">
            Página {{ pagination.current_page }} de {{ pagination.last_page }}
          </span>
          
          <button @click="changePage(pagination.current_page + 1)"
                  :disabled="pagination.current_page >= pagination.last_page"
                  class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-800 border border-gray-600 rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
            Próxima
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import api from '@/utils/api';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useRouter } from 'vue-router';

const router = useRouter();

const records = ref([]);
const isLoading = ref(false);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
});

const filters = ref({
  startDate: '',
  endDate: ''
});

const hasActiveFilters = computed(() => {
  return filters.value.startDate || filters.value.endDate;
});

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return 'N/A';
  try {
    return format(parseISO(dateTimeString), "dd/MM/yyyy 'às' HH:mm", { locale: ptBR });
  } catch (e) {
    return 'Data inválida';
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  try {
    return format(new Date(dateString), 'dd/MM/yyyy', { locale: ptBR });
  } catch (e) {
    return dateString;
  }
};

const fetchCompletedRecords = async (page = 1) => {
  isLoading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page
    };

    if (filters.value.startDate) {
      params.start_date = filters.value.startDate;
    }
    if (filters.value.endDate) {
      params.end_date = filters.value.endDate;
    }

    const response = await api.get('/maintenance/schedules/my-completed', { params });
    
    records.value = response.data.data || [];
    
    // Atualizar informações de paginação
    const { current_page, last_page, per_page, total, from, to } = response.data;
    pagination.value = { current_page, last_page, per_page, total, from, to };
    
  } catch (error) {
    console.error('Erro ao buscar manutenções concluídas:', error);
    records.value = [];
  } finally {
    isLoading.value = false;
  }
};

const refreshData = () => {
  fetchCompletedRecords(pagination.value.current_page);
};

const applyFilters = () => {
  fetchCompletedRecords(1); // Resetar para primeira página quando aplicar filtros
};

const clearFilters = () => {
  filters.value.startDate = '';
  filters.value.endDate = '';
  fetchCompletedRecords(1);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchCompletedRecords(page);
  }
};

const viewDetails = (record) => {
  // Navegar para página de detalhes do registro
  router.push(`/maintenance/records/${record.id}`);
};

onMounted(() => {
  fetchCompletedRecords();
});
</script>

<style scoped>
/* Animação fade-in para os cards */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.space-y-4 > div {
  animation: fadeInUp 0.5s ease-out forwards;
}
</style> 