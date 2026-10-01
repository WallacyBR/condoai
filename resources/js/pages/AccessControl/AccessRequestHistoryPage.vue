<template>
  <AuthenticatedLayout page-title="Histórico de Autorizações">
    <HeaderCard
      title="Histórico de Autorizações de Acesso"
      description="Visualize todas as solicitações de acesso já processadas no seu condomínio."
      icon-path="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
      icon-color="blue"
    />

    <div class="mt-6 bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-xl p-6">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold text-white">Histórico Completo</h3>
        <div class="flex gap-3">
          <select v-model="filters.status" @change="fetchHistory" class="form-select bg-gray-800 border-gray-600 text-white">
            <option value="">Todos os Status</option>
            <option value="authorized">Autorizados</option>
            <option value="unauthorized">Não Autorizados</option>
            <option value="cancelled">Cancelados</option>
            <option value="entry_recorded">Entrada Registrada</option>
          </select>
          <NeonButton @click="fetchHistory" :loading="loading" size="sm" variant="secondary">
            <svg class="h-4 w-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span v-if="!loading" class="ml-2">Atualizar</span>
          </NeonButton>
        </div>
      </div>

      <div v-if="loading && historyRequests.length === 0" class="text-center py-10">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-400 mx-auto"></div>
        <p class="text-gray-400 mt-3">Carregando histórico...</p>
      </div>

      <div v-if="!loading && errorMessage" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
        <p class="text-red-300">{{ errorMessage }}</p>
      </div>

      <div v-if="!loading && historyRequests.length === 0 && !errorMessage" class="text-center py-10">
        <svg class="h-12 w-12 mx-auto text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="text-gray-400 mt-3">Nenhuma solicitação de acesso encontrada no histórico.</p>
      </div>

      <div v-if="historyRequests.length > 0" class="space-y-4">
        <AccessRequestCard
          v-for="request in historyRequests"
          :key="request.id"
          :request="request"
          :show-actions="false"
        />
      </div>

      <!-- Paginação -->
      <div v-if="pagination && pagination.last_page > 1" class="mt-6 flex justify-center">
        <nav class="flex space-x-2">
          <button
            v-for="page in getPaginationPages()"
            :key="page"
            @click="changePage(page)"
            :disabled="page === pagination.current_page"
            class="px-3 py-2 text-sm rounded-lg transition-colors"
            :class="page === pagination.current_page 
              ? 'bg-blue-600 text-white' 
              : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
          >
            {{ page }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Modal de Detalhes -->
    <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-900 rounded-2xl p-6 w-full max-w-2xl mx-4 border border-gray-700/50 shadow-2xl">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-white">Detalhes da Solicitação</h2>
          <button @click="closeDetailsModal" class="text-gray-400 hover:text-white">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div v-if="selectedRequest" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Visitante</label>
              <p class="text-white">{{ selectedRequest.visitor?.name || 'N/A' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Documento</label>
              <p class="text-white">{{ selectedRequest.visitor?.document || 'N/A' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Unidade de Destino</label>
              <p class="text-white">{{ selectedRequest.apartment?.number || 'N/A' }}</p>
              <p v-if="selectedRequest.resident?.name" class="text-xs text-gray-400 mt-1">
                Responsável: {{ selectedRequest.resident.name }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Status</label>
              <span :class="getStatusInfo(selectedRequest.status).textColor">
                {{ getStatusInfo(selectedRequest.status).text }}
              </span>
            </div>
            <div v-if="selectedRequest.visitor_vehicle">
              <label class="block text-sm font-medium text-gray-400 mb-1">Veículo</label>
              <p class="text-white">{{ selectedRequest.visitor_vehicle.plate }} - {{ selectedRequest.visitor_vehicle.model }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Data da Solicitação</label>
              <p class="text-white">{{ formatDateTime(selectedRequest.request_timestamp) }}</p>
            </div>
          </div>

          <div v-if="selectedRequest.resident_observation" class="mt-4">
            <label class="block text-sm font-medium text-gray-400 mb-1">Observação do Morador</label>
            <p class="text-gray-300 bg-gray-800/50 p-3 rounded-lg">{{ selectedRequest.resident_observation }}</p>
          </div>

          <div v-if="selectedRequest.gatehouse_observation" class="mt-4">
            <label class="block text-sm font-medium text-gray-400 mb-1">Observação da Portaria</label>
            <p class="text-gray-300 bg-gray-800/50 p-3 rounded-lg">{{ selectedRequest.gatehouse_observation }}</p>
          </div>
        </div>
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import HeaderCard from '../../components/layouts/HeaderCard.vue';
import NeonButton from '../../components/NeonButton.vue';
import AccessRequestCard from '../../components/access_control/AccessRequestCard.vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';
import { formatDateTime } from '../../utils/dateHelpers';

const { errorToast } = useToast();

const historyRequests = ref([]);
const loading = ref(false);
const errorMessage = ref('');
const pagination = ref(null);
const showDetailsModal = ref(false);
const selectedRequest = ref(null);

const filters = reactive({
  status: '',
  page: 1
});

const fetchHistory = async (page = 1) => {
  loading.value = true;
  errorMessage.value = '';
  filters.page = page;
  
  try {
    const params = {
      page: filters.page,
      per_page: 15,
      ...(filters.status && { status: filters.status })
    };
    
    const response = await api.get('/access-control/access-requests/history', { params });
    historyRequests.value = Array.isArray(response.data) ? response.data : response.data?.data || [];
    pagination.value = response.data?.meta || (response.data?.last_page ? response.data : null);
  } catch (e) {
    console.error("Erro ao buscar histórico:", e);
    errorMessage.value = 'Falha ao carregar o histórico de solicitações.';
    errorToast(errorMessage.value);
  } finally {
    loading.value = false;
  }
};

const viewDetails = (request) => {
  selectedRequest.value = request;
  showDetailsModal.value = true;
};

const closeDetailsModal = () => {
  showDetailsModal.value = false;
  selectedRequest.value = null;
};

const changePage = (page) => {
  if (page !== filters.page) {
    fetchHistory(page);
  }
};

const getPaginationPages = () => {
  if (!pagination.value) return [];
  
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  
  // Mostrar no máximo 5 páginas
  const start = Math.max(1, current - 2);
  const end = Math.min(last, start + 4);
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
};

const getStatusInfo = (status) => {
  const statusMap = {
    pending: { text: 'Pendente', textColor: 'text-orange-300' },
    authorized: { text: 'Autorizado', textColor: 'text-green-300' },
    unauthorized: { text: 'Não Autorizado', textColor: 'text-red-300' },
    cancelled: { text: 'Cancelado', textColor: 'text-gray-400' },
    entry_recorded: { text: 'Entrada Registrada', textColor: 'text-cyan-300' },
    awaiting_at_gatehouse: { text: 'Aguardando na Portaria', textColor: 'text-blue-300' },
    inform_not_home: { text: 'Morador Ausente', textColor: 'text-purple-300' }
  };
  
  return statusMap[status] || { text: status, textColor: 'text-gray-300' };
};

onMounted(() => fetchHistory());
</script>
