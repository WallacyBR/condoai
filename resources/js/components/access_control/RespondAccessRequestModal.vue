<template>
  <Modal :show="show" @close="$emit('close')" max-width="2xl">
    <template #title>
      <div class="flex items-center">
        <svg class="h-6 w-6 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
        Responder Solicitação de Acesso
      </div>
    </template>

    <div v-if="loadingDetails" class="py-12 text-center">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-cyan-400 mx-auto"></div>
        <p class="text-gray-400 mt-3">Carregando detalhes da solicitação...</p>
    </div>

    <div v-if="detailsError && !loadingDetails" class="py-8 text-center">
        <p class="text-red-400 text-lg">{{ detailsError }}</p>
    </div>

    <div v-if="currentRequestDetails && !loadingDetails && !detailsError" class="space-y-6 p-2">
      <!-- Detalhes do Visitante e Veículo -->
      <div class="bg-gray-800/50 border border-gray-700/50 rounded-xl p-4">
        <div class="flex items-start space-x-4">
          <img
            v-if="currentRequestDetails.visitor?.photo_url"
            :src="currentRequestDetails.visitor.photo_url"
            alt="Foto do Visitante"
            class="h-24 w-24 rounded-lg object-cover border-2 border-gray-600"
          >
          <div
            v-else
            class="h-24 w-24 rounded-lg flex items-center justify-center border-2 border-gray-600 bg-gray-700 text-3xl font-semibold text-gray-400"
          >
            {{ currentRequestDetails.visitor?.name?.charAt(0).toUpperCase() || 'V' }}
          </div>
          <div class="flex-1">
            <h4 class="text-lg font-semibold text-white">{{ currentRequestDetails.visitor?.name }}</h4>
            <p class="text-sm text-gray-300">Documento: {{ currentRequestDetails.visitor?.document }}</p>
            <p v-if="currentRequestDetails.visitor?.phone" class="text-sm text-gray-300">Telefone: {{ currentRequestDetails.visitor?.phone }}</p>

            <div v-if="currentRequestDetails.visitor_vehicle" class="mt-2 pt-2 border-t border-gray-700/50">
              <p class="text-sm text-gray-300"><strong class="text-gray-400">Veículo:</strong> {{ currentRequestDetails.visitor_vehicle.plate || currentRequestDetails.visitor_vehicle.license_plate }} - {{ currentRequestDetails.visitor_vehicle.model }} ({{ currentRequestDetails.visitor_vehicle.color }})</p>
            </div>
          </div>
        </div>
        <p v-if="currentRequestDetails.gatehouse_observation" class="text-sm text-gray-400 mt-3 pt-3 border-t border-gray-700/50">
            <strong class="text-gray-500">Observação da Portaria:</strong> {{ currentRequestDetails.gatehouse_observation }}
        </p>
      </div>

      <!-- Ações do Morador -->
      <div>
        <label for="resident_observation" class="block text-sm font-medium text-gray-300 mb-1">Sua Observação (Opcional):</label>
        <textarea
          id="resident_observation"
          v-model="residentObservation"
          rows="3"
          placeholder="Ex: Pode subir, estou aguardando."
          class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
        ></textarea>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-2 gap-3">
        <NeonButton @click="submitResponse('authorized')" variant="success" class="w-full">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          Autorizar
        </NeonButton>
        <NeonButton @click="submitResponse('unauthorized')" variant="danger" class="w-full">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          Não Autorizar
        </NeonButton>
        <NeonButton @click="submitResponse('inform_not_home')" variant="info" class="w-full">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          Não Estou
        </NeonButton>
        <NeonButton @click="submitResponse('awaiting_at_gatehouse')" variant="warning" class="w-full">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          Aguardar Portaria
        </NeonButton>
      </div>
    </div>

    <template #footer v-if="currentRequestDetails && !loadingDetails && !detailsError">
      <div class="flex justify-end">
        <NeonButton @click="$emit('close')" variant="secondary">
          Fechar
        </NeonButton>
      </div>
    </template>
     <template #footer v-else-if="!loadingDetails">
        <NeonButton @click="$emit('close')" variant="secondary">
          Fechar
        </NeonButton>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch, toRefs } from 'vue';
import Modal from '../Modal.vue';
import NeonButton from '../NeonButton.vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';

const props = defineProps({
  show: Boolean,
  requestDetails: { // Este será o objeto da solicitação passado pelo card
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close', 'response-submitted']);
const { successToast, errorToast } = useToast();

const currentRequestDetails = ref(null); // Para armazenar os detalhes completos se precisarmos buscar mais
const loadingDetails = ref(false);
const detailsError = ref('');

const residentObservation = ref('');
const submittingResponse = ref(false);

watch(() => props.show, (newVal) => {
  if (newVal && props.requestDetails) {
    // Se já temos os detalhes, usamos. Se não, poderíamos buscar aqui.
    // No nosso caso, ResidentAccessRequestCard já deve ter os dados necessários.
    currentRequestDetails.value = { ...props.requestDetails };
    residentObservation.value = ''; // Limpa observação anterior
    detailsError.value = '';
    loadingDetails.value = false;

    // Exemplo: Se precisasse buscar mais detalhes:
    // fetchFullRequestDetails(props.requestDetails.id);
  }
});

// Função de exemplo se precisasse buscar mais detalhes (não usada no fluxo atual se o card já tem tudo)
/*
const fetchFullRequestDetails = async (requestId) => {
  loadingDetails.value = true;
  detailsError.value = '';
  try {
    const response = await api.get(`/access-control/access-requests/${requestId}`);
    currentRequestDetails.value = response.data;
  } catch (e) {
    console.error("Erro ao buscar detalhes completos da solicitação:", e);
    detailsError.value = "Não foi possível carregar todos os detalhes da solicitação.";
  } finally {
    loadingDetails.value = false;
  }
};
*/

const submitResponse = async (responseAction) => {
  if (!currentRequestDetails.value || submittingResponse.value) return;

  submittingResponse.value = true;
  try {
    await api.post(`/access-control/access-requests/${currentRequestDetails.value.id}/respond`, {
      response: responseAction,
      resident_observation: residentObservation.value,
    });
    emit('response-submitted');
  } catch (e) {
    console.error('Erro ao enviar resposta:', e);
    errorToast(e.response?.data?.message || 'Falha ao enviar resposta.');
  } finally {
    submittingResponse.value = false;
  }
};

</script>

<style scoped>
/* Adicione estilos se necessário */
</style>
