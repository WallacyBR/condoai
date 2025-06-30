<template>
  <AuthenticatedLayout page-title="Minhas Autorizações de Acesso">
    <HeaderCard
      title="Autorizações de Acesso Pendentes"
      description="Visualize e responda às solicitações de acesso de visitantes para sua unidade."
      icon-path="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
      icon-color="blue"
    />

    <div class="mt-6 bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-xl p-6">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold text-white">Minhas Solicitações Pendentes</h3>
        <NeonButton @click="fetchMyRequests" :loading="loading" size="sm" variant="secondary">
          <svg class="h-4 w-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          <span v-if="!loading" class="ml-2">Atualizar</span>
        </NeonButton>
      </div>

      <div v-if="loading && myRequests.length === 0" class="text-center py-10">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-400 mx-auto"></div>
        <p class="text-gray-400 mt-3">Carregando suas solicitações...</p>
      </div>

      <div v-if="!loading && error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
          <p class="text-red-300">{{ error }}</p>
      </div>

      <div v-if="!loading && myRequests.length === 0 && !error" class="text-center py-10">
        <svg class="h-12 w-12 mx-auto text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-gray-400 mt-3">Nenhuma solicitação de acesso pendente para você no momento.</p>
      </div>

      <div v-if="myRequests.length > 0" class="space-y-4">
        <ResidentAccessRequestCard
          v-for="request in myRequests"
          :key="request.id"
          :request="request"
          @respond-to-request="openRespondModal"
        />
      </div>
    </div>

    <!-- Modal/Página para Responder Solicitação -->
    <RespondAccessRequestModal
      :show="showResponseModal"
      :request-details="selectedRequestForResponse"
      @close="closeRespondModal"
      @response-submitted="handleResponseSubmitted"
    />

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import HeaderCard from '../../components/layouts/HeaderCard.vue';
import NeonButton from '../../components/NeonButton.vue';
import ResidentAccessRequestCard from '../../components/access_control/ResidentAccessRequestCard.vue'; // Será criado
import RespondAccessRequestModal from '../../components/access_control/RespondAccessRequestModal.vue'; // Será criado
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';

const { successToast, errorToast } = useToast();

const myRequests = ref([]);
const loading = ref(false);
const error = ref('');

const showResponseModal = ref(false);
const selectedRequestForResponse = ref(null);

const fetchMyRequests = async () => {
  loading.value = true;
  error.value = '';
  try {
    // A API já filtra por status 'pendente' por padrão para esta rota
    const response = await api.get('/api/access-control/access-requests/my-requests');
    myRequests.value = response.data?.data || [];
  } catch (e) {
    console.error("Erro ao buscar minhas solicitações:", e);
    error.value = 'Falha ao carregar suas solicitações pendentes.';
    errorToast(error.value);
  } finally {
    loading.value = false;
  }
};

const openRespondModal = (request) => {
  selectedRequestForResponse.value = request;
  showResponseModal.value = true;
};

const closeRespondModal = () => {
  showResponseModal.value = false;
  selectedRequestForResponse.value = null;
};

const handleResponseSubmitted = () => {
  successToast('Sua resposta foi enviada com sucesso!');
  closeRespondModal();
  fetchMyRequests(); // Atualiza a lista de solicitações pendentes
};

onMounted(fetchMyRequests);
</script>
