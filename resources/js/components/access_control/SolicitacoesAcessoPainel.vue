<template>
  <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-xl p-6">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-xl font-semibold text-white flex items-center">
        <svg class="h-6 w-6 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Solicitações de Acesso
      </h3>
      <div>
        <!-- Filtros e Ações (Ex: Refresh) -->
        <NeonButton @click="fetchAccessRequests" :loading="loading" size="sm" variant="secondary">
          <svg class="h-4 w-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          <span v-if="!loading" class="ml-2">Atualizar</span>
        </NeonButton>
      </div>
    </div>

    <div v-if="loading && accessRequests.length === 0" class="text-center py-10">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-400 mx-auto"></div>
      <p class="text-gray-400 mt-3">Carregando solicitações...</p>
    </div>

    <div v-if="!loading && error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
        <p class="text-red-300">{{ error }}</p>
    </div>

    <div v-if="!loading && accessRequests.length === 0 && !error" class="text-center py-10">
      <svg class="h-12 w-12 mx-auto text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
      <p class="text-gray-400 mt-3">Nenhuma solicitação de acesso encontrada.</p>
    </div>

    <div v-if="accessRequests.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <AccessRequestCard
        v-for="request in accessRequests"
        :key="request.id"
        :request="request"
        @cancel-request="handleCancelRequest"
        @record-entry="handleRecordEntry"
        @record-exit="handleRecordExit"
      />
    </div>

    <!-- Modais de confirmação -->
    <ConfirmActionModal
      :show="showConfirmCancelModal"
      title="Cancelar Solicitação"
      message="Tem certeza que deseja cancelar esta solicitação de acesso?"
      confirm-button-text="Sim, Cancelar"
      confirm-button-variant="danger"
      :is-loading="cancellingRequest"
      @confirm="confirmCancelRequest"
      @close="showConfirmCancelModal = false"
    />

    <RecordEntryModal
      :show="showRecordEntryModal"
      :request-id="selectedRequestForEntry?.id"
      :visitor-name="selectedRequestForEntry?.visitor?.name"
      @close="showRecordEntryModal = false"
      @entry-recorded="handleEntrySuccessfullyRecorded"
    />

    <ConfirmActionModal
      :show="showRecordExitModal"
      title="Registrar Saída"
      :message="`Confirmar a saída para ${selectedRequestForExit?.visitor?.name}?`"
      confirm-button-text="Sim, Registrar Saída"
      confirm-button-variant="success"
      :is-loading="recordingExit"
      @confirm="confirmRecordExit"
      @close="showRecordExitModal = false"
    />

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../utils/api'; // Supondo que api.js está configurado
import NeonButton from '../NeonButton.vue';
import AccessRequestCard from './AccessRequestCard.vue'; // Será criado
import ConfirmActionModal from '../Modal.vue'; // Usando o Modal genérico para confirmação
import RecordEntryModal from './RecordEntryModal.vue'; // Será criado
import { useToast } from '../../composables/useToast';

const { successToast, errorToast } = useToast();

const accessRequests = ref([]);
const loading = ref(false);
const error = ref('');
const autoRefreshInterval = ref(null);

const showConfirmCancelModal = ref(false);
const requestToCancel = ref(null);
const cancellingRequest = ref(false);

const showRecordEntryModal = ref(false);
const selectedRequestForEntry = ref(null);

const showRecordExitModal = ref(false);
const selectedRequestForExit = ref(null);
const recordingExit = ref(false);


const fetchAccessRequests = async () => {
  loading.value = true;
  error.value = '';
  try {
    // Vamos buscar apenas as "ativas" por padrão. Outros filtros podem ser adicionados.
    const response = await api.get('/api/access-control/access-requests/gatehouse?ativas=true&per_page=50');
    accessRequests.value = response.data?.data || [];
  } catch (e) {
    console.error("Erro ao buscar solicitações de acesso:", e);
    error.value = 'Falha ao carregar as solicitações. Tente novamente.';
    errorToast(error.value)
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchAccessRequests();
  // autoRefreshInterval.value = setInterval(fetchAccessRequests, 30000); // Atualiza a cada 30 segundos
});

// onBeforeUnmount(() => {
//   if (autoRefreshInterval.value) {
//     clearInterval(autoRefreshInterval.value);
//   }
// });

// Funções para manipular ações dos cards
const handleCancelRequest = (request) => {
  requestToCancel.value = request;
  showConfirmCancelModal.value = true;
};

const confirmCancelRequest = async () => {
  if (!requestToCancel.value) return;
  cancellingRequest.value = true;
  try {
    await api.post(`/api/access-control/access-requests/${requestToCancel.value.id}/cancel`, {
      // observacao_cancelamento: 'Cancelado pela portaria via painel.' // Opcional
    });
    successToast('Solicitação cancelada com sucesso!');
    fetchAccessRequests(); // Recarrega a lista
  } catch (e) {
    console.error('Erro ao cancelar solicitação:', e);
    errorToast(e.response?.data?.message || 'Falha ao cancelar solicitação.');
  } finally {
    cancellingRequest.value = false;
    showConfirmCancelModal.value = false;
    requestToCancel.value = null;
  }
};

const handleRecordEntry = (request) => {
  selectedRequestForEntry.value = request;
  showRecordEntryModal.value = true;
};

const handleEntrySuccessfullyRecorded = () => {
    successToast('Entrada registrada com sucesso!');
    fetchAccessRequests();
    showRecordEntryModal.value = false;
    selectedRequestForEntry.value = null;
}

const handleRecordExit = (request) => {
  selectedRequestForExit.value = request;
  showRecordExitModal.value = true;
};

const confirmRecordExit = async () => {
  if (!selectedRequestForExit.value) return;
  recordingExit.value = true;
  try {
    await api.post(`/api/access-control/access-requests/${selectedRequestForExit.value.id}/record-exit`, {
        // observacao_portaria_saida: 'Saída registrada via painel.' // Opcional
    });
    successToast('Saída registrada com sucesso!');
    fetchAccessRequests(); // Recarrega a lista
  } catch (e) {
    console.error('Erro ao registrar saída:', e);
    errorToast(e.response?.data?.message || 'Falha ao registrar saída.');
  } finally {
    recordingExit.value = false;
    showRecordExitModal.value = false;
    selectedRequestForExit.value = null;
  }
};

</script>

<style scoped>
/* Adicionar estilos específicos se necessário */
</style>
