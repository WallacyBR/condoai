<template>
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
      <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 flex items-center">
        <svg class="h-5 w-5 mr-2.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Solicitações de Acesso
      </h3>
      <div>
        <NeonButton @click="fetchAccessRequests" :loading="loading" size="sm" variant="secondary">
          <svg class="h-4 w-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          <span v-if="!loading" class="ml-1.5">Atualizar</span>
        </NeonButton>
      </div>
    </div>

    <div v-if="loading && accessRequests.length === 0" class="text-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-300 border-t-indigo-600 dark:border-slate-700 dark:border-t-indigo-400 mx-auto"></div>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">Carregando solicitações...</p>
    </div>

    <div v-if="!loading && error" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-4 text-center">
      <p class="text-xs text-rose-700 dark:text-rose-300">{{ error }}</p>
    </div>

    <div v-if="!loading && accessRequests.length === 0 && !error" class="text-center py-12">
      <svg class="h-10 w-10 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
      </svg>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Nenhuma solicitação de acesso ativa encontrada.</p>
    </div>

    <div v-if="accessRequests.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
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
import { ref, onMounted } from 'vue';
import api from '../../utils/api';
import NeonButton from '../NeonButton.vue';
import AccessRequestCard from './AccessRequestCard.vue';
import ConfirmActionModal from '../ConfirmActionModal.vue';
import RecordEntryModal from './RecordEntryModal.vue';
import { useToast } from '../../composables/useToast';

const { successToast, errorToast } = useToast();

const accessRequests = ref([]);
const loading = ref(false);
const error = ref('');

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
    const response = await api.get('/access-control/access-requests/gatehouse?ativas=true&per_page=50');
    accessRequests.value = response.data?.data || [];
  } catch (e) {
    console.error("Erro ao buscar solicitações de acesso:", e);
    error.value = 'Falha ao carregar as solicitações. Tente novamente.';
    errorToast(error.value);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchAccessRequests();
});

const handleCancelRequest = (request) => {
  requestToCancel.value = request;
  showConfirmCancelModal.value = true;
};

const confirmCancelRequest = async () => {
  if (!requestToCancel.value) return;
  cancellingRequest.value = true;
  try {
    await api.post(`/access-control/access-requests/${requestToCancel.value.id}/cancel`);
    successToast('Solicitação cancelada com sucesso!');
    fetchAccessRequests();
  } catch (e) {
    console.error("Erro ao cancelar solicitação:", e);
    errorToast('Falha ao cancelar a solicitação.');
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
  showRecordEntryModal.value = false;
  selectedRequestForEntry.value = null;
  fetchAccessRequests();
};

const handleRecordExit = (request) => {
  selectedRequestForExit.value = request;
  showRecordExitModal.value = true;
};

const confirmRecordExit = async () => {
  if (!selectedRequestForExit.value) return;
  recordingExit.value = true;
  try {
    await api.post(`/access-control/access-requests/${selectedRequestForExit.value.id}/record-exit`);
    successToast('Saída registrada com sucesso!');
    fetchAccessRequests();
  } catch (e) {
    console.error("Erro ao registrar saída:", e);
    errorToast('Falha ao registrar saída.');
  } finally {
    recordingExit.value = false;
    showRecordExitModal.value = false;
    selectedRequestForExit.value = null;
  }
};
</script>
