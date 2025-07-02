<template>
  <AuthenticatedLayout page-title="Registrar Entrada de Acesso">
    <HeaderCard
      v-if="accessRequest"
      :title="`Registrar Entrada: ${accessRequest.visitor?.name}`"
      :description="`Solicitação de acesso para ${accessRequest.resident?.name}`"
      icon-path="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
      icon-color="cyan"
    />
    <HeaderCard
      v-else-if="loadingRequest"
      title="Carregando Solicitação..."
      description="Aguarde enquanto os dados da solicitação são carregados."
      icon-path="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
      icon-color="gray"
    />
     <HeaderCard
      v-else
      title="Erro ao Carregar Solicitação"
      description="Não foi possível carregar os detalhes da solicitação. Verifique o ID ou tente novamente."
      icon-path="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
      icon-color="red"
    />

    <div v-if="loadingRequest" class="text-center py-12">
      <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-cyan-400 mx-auto"></div>
      <p class="text-gray-300 mt-4 text-lg">Carregando dados da solicitação...</p>
    </div>

    <div v-if="requestError && !loadingRequest" class="bg-red-700/30 border border-red-500/50 rounded-2xl p-8 text-center mt-6 shadow-xl">
        <h3 class="text-2xl font-semibold text-red-300 mb-3">Erro ao Carregar</h3>
        <p class="text-red-200">{{ requestError }}</p>
        <NeonButton @click="goBack" variant="danger" class="mt-6">
            Voltar ao Painel
        </NeonButton>
    </div>

    <form @submit.prevent="submitRecordEntry" v-if="accessRequest && !loadingRequest && !requestError" class="mt-6 space-y-8">
      <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-6 shadow-lg">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
          <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM21 17a2 2 0 11-4 0 2 2 0 014 0zM13 6h5l3 4v6a1 1 0 01-1 1h-2m-10 0H3a1 1 0 01-1-1v-6l3-4h5m0 0V3a1 1 0 011-1h2a1 1 0 011 1v3"></path></svg>
          Detalhes da Entrada
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <label for="parking_spot_id" class="block text-sm font-medium text-gray-300 mb-1">Vaga de Estacionamento (Opcional)</label>
            <select id="parking_spot_id" v-model="form.parking_spot_id" class="form-input">
              <option :value="null">Nenhuma vaga específica</option>
              <option v-for="spot in availableParkingSpots" :key="spot.id" :value="spot.id">
                {{ spot.identifier }}
              </option>
            </select>
            <p v-if="loadingParkingSpots" class="text-xs text-cyan-400 mt-1">Carregando vagas disponíveis...</p>
          </div>
          <div class="md:col-span-2">
            <label for="gatehouse_entry_observation" class="block text-sm font-medium text-gray-300 mb-1">Observações da Portaria (Entrada)</label>
            <textarea id="gatehouse_entry_observation" v-model="form.gatehouse_entry_observation" rows="3" class="form-input"></textarea>
          </div>
        </div>
      </div>

      <!-- Resumo da Solicitação -->
       <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-6 shadow-lg">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          Resumo da Solicitação
        </h3>
        <div class="space-y-2 text-sm text-gray-300">
            <p><strong class="text-gray-400">Visitante:</strong> {{ accessRequest.visitor?.name }} ({{ accessRequest.visitor?.document }})</p>
            <p v-if="accessRequest.visitor_vehicle"><strong class="text-gray-400">Veículo:</strong> {{ accessRequest.visitor_vehicle?.plate }} - {{ accessRequest.visitor_vehicle?.model }}</p>
            <p><strong class="text-gray-400">Morador Destino:</strong> {{ accessRequest.resident?.name }}</p>
            <p><strong class="text-gray-400">Status Atual:</strong> <span :class="statusInfo.textColor">{{ statusInfo.text }}</span></p>
            <p v-if="accessRequest.gatehouse_observation"><strong class="text-gray-400">Obs. Portaria (Original):</strong> {{ accessRequest.gatehouse_observation }}</p>
            <p v-if="accessRequest.resident_observation"><strong class="text-gray-400">Obs. Morador:</strong> {{ accessRequest.resident_observation }}</p>
        </div>
      </div>

      <div class="flex justify-end space-x-4 pt-4 border-t border-gray-700/50">
        <NeonButton type="button" @click="goBack" variant="secondary">
          Cancelar
        </NeonButton>
        <NeonButton type="submit" :loading="submitting">
          Confirmar Entrada
        </NeonButton>
      </div>
    </form>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import HeaderCard from '../../components/layouts/HeaderCard.vue';
import NeonButton from '../../components/NeonButton.vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';

const route = useRoute();
const router = useRouter();
const { successToast, errorToast } = useToast();

const accessRequestId = route.params.id;
const accessRequest = ref(null);
const loadingRequest = ref(true);
const requestError = ref('');

const form = reactive({
  parking_spot_id: null,
  gatehouse_entry_observation: '',
});

const availableParkingSpots = ref([]);
const loadingParkingSpots = ref(false);
const submitting = ref(false);

const statusMap = {
  pending: { text: 'Pendente', textColor: 'text-orange-300' },
  authorized: { text: 'Autorizado', textColor: 'text-green-300' },
  unauthorized: { text: 'Não Autorizado', textColor: 'text-red-300' },
  awaiting_at_gatehouse: { text: 'Aguardando na Portaria', textColor: 'text-blue-300' },
  inform_not_home: { text: 'Morador Ausente', textColor: 'text-purple-300'},
  cancelled: { text: 'Cancelado', textColor: 'text-gray-400' },
  entry_recorded: { text: 'Entrada Registrada', textColor: 'text-cyan-300' },
  exit_recorded: { text: 'Saída Registrada', textColor: 'text-teal-300' },
};
const statusInfo = computed(() => statusMap[accessRequest.value?.status] || {text: 'Desconhecido', textColor: 'text-gray-400'});


const fetchAccessRequestDetails = async () => {
  loadingRequest.value = true;
  requestError.value = '';
  try {
    const response = await api.get(`/api/access-control/access-requests/${accessRequestId}`);
    accessRequest.value = response.data;
    if (!accessRequest.value) {
        throw new Error("Solicitação não encontrada.");
    }
    if (!['authorized', 'awaiting_at_gatehouse', 'inform_not_home'].includes(accessRequest.value.status) || accessRequest.value.actual_entry_timestamp) {
        requestError.value = "Esta solicitação não pode ter a entrada registrada ou a entrada já foi registrada.";
        errorToast(requestError.value);
    }

  } catch (e) {
    console.error("Erro ao buscar detalhes da solicitação:", e);
    requestError.value = e.response?.data?.message || 'Falha ao carregar dados da solicitação.';
    accessRequest.value = null; // Garante que o form não seja mostrado
  } finally {
    loadingRequest.value = false;
  }
};

const fetchAvailableParkingSpots = async () => {
  loadingParkingSpots.value = true;
  try {
    // TODO: Ajustar endpoint para buscar apenas vagas DISPONÍVEIS
    const response = await api.get('/parking-spots?per_page=1000'); // Assumindo que lista todas
    availableParkingSpots.value = response.data.data || response.data;
  } catch (e) {
    console.error("Erro ao buscar vagas:", e);
    // Não é crítico, pode continuar sem vagas
  } finally {
    loadingParkingSpots.value = false;
  }
};

const submitRecordEntry = async () => {
  if (!accessRequest.value || requestError.value) {
    errorToast("Não é possível registrar entrada devido a um erro anterior ou solicitação inválida.");
    return;
  }
  submitting.value = true;
  try {
    await api.post(`/api/access-control/access-requests/${accessRequestId}/record-entry`, {
      parking_spot_id: form.parking_spot_id,
      gatehouse_entry_observation: form.gatehouse_entry_observation,
    });
    successToast('Entrada registrada com sucesso!');
    router.push({ name: 'AccessControlDashboard' });
  } catch (e) {
    console.error("Erro ao registrar entrada:", e);
    errorToast(e.response?.data?.message || 'Falha ao registrar entrada.');
  } finally {
    submitting.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'AccessControlDashboard' });
};

onMounted(async () => {
  await fetchAccessRequestDetails();
  if (accessRequest.value && !requestError.value) { // Só busca vagas se a solicitação for válida para entrada
    fetchAvailableParkingSpots();
  }
});

</script>

<style scoped>
.form-input {
  @apply w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors;
}
</style>
