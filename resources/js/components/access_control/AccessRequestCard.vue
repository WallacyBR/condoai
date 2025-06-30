<template>
  <div
    class="rounded-2xl p-5 border-2 shadow-lg transition-all duration-300 ease-in-out hover:shadow-2xl"
    :class="cardClasses"
  >
    <div class="flex flex-col h-full">
      <!-- Header do Card: Ícone e Status -->
      <div class="flex items-center mb-3">
        <div
          class="h-10 w-10 rounded-full flex items-center justify-center mr-3 flex-shrink-0"
          :class="iconBgClass"
        >
          <svg class="h-5 w-5" :class="iconColorClass" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="statusIconPath"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold" :class="statusTextColorClass">{{ formattedStatus }}</p>
          <p class="text-xs text-gray-400">{{ timeSince(request.request_timestamp) }}</p>
        </div>
      </div>

      <!-- Detalhes do Visitante -->
      <div class="mb-3 border-t border-gray-700/50 pt-3">
        <div class="flex items-center">
          <img
            v-if="request.visitor?.photo_url"
            :src="request.visitor.photo_url"
            alt="Foto do Visitante"
            class="h-12 w-12 rounded-full object-cover mr-3 border-2"
            :class="borderColorClass"
          >
          <div
            v-else
            class="h-12 w-12 rounded-full flex items-center justify-center mr-3 border-2 text-xl font-semibold"
            :class="[iconBgClass, borderColorClass, statusTextColorClass]"
          >
            {{ request.visitor?.name?.charAt(0).toUpperCase() || 'V' }}
          </div>
          <div>
            <p class="font-semibold text-gray-100 truncate" :title="request.visitor?.name">{{ request.visitor?.name || 'Visitante Desconhecido' }}</p>
            <p class="text-xs text-gray-400">{{ request.visitor?.document || 'Documento não informado' }}</p>
          </div>
        </div>
      </div>

      <!-- Detalhes do Veículo (se houver) -->
      <div v-if="request.visitor_vehicle" class="mb-3 text-xs text-gray-300 border-t border-gray-700/50 pt-3">
        <p><strong class="font-medium text-gray-400">Veículo:</strong> {{ request.visitor_vehicle.plate }} - {{ request.visitor_vehicle.model }} ({{ request.visitor_vehicle.cor }})</p>
      </div>

      <!-- Morador Destino -->
      <div class="mb-1 text-xs text-gray-300 border-t border-gray-700/50 pt-3">
        <p><strong class="font-medium text-gray-400">Destino:</strong> {{ request.resident?.name || 'Morador não informado' }}</p>
      </div>

      <!-- Observações (se houver) -->
      <div v-if="request.gatehouse_observation || request.resident_observation" class="text-xs text-gray-400 mt-1 mb-3 border-t border-gray-700/50 pt-3 space-y-1">
        <p v-if="request.gatehouse_observation"><strong class="text-gray-500">Obs. Portaria:</strong> {{ truncateText(request.gatehouse_observation, 50) }}</p>
        <p v-if="request.resident_observation"><strong class="text-gray-500">Obs. Morador:</strong> {{ truncateText(request.resident_observation, 50) }}</p>
      </div>

      <!-- Vaga Utilizada (se houver) -->
      <div v-if="request.parking_spot && request.status === 'entry_recorded'" class="text-xs text-cyan-300 mt-1 mb-3 border-t border-gray-700/50 pt-3">
          <p><strong class="font-medium text-cyan-400">Vaga:</strong> {{ request.parking_spot.identifier }}</p>
      </div>


      <!-- Ações -->
      <div class="mt-auto pt-4 border-t border-gray-700/50">
        <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2 justify-end">
          <NeonButton
            v-if="canRecordEntry"
            @click="$emit('record-entry', request)"
            size="sm"
            variant="success"
            class="w-full sm:w-auto"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
            Registrar Entrada
          </NeonButton>
          <NeonButton
            v-if="canRecordExit"
            @click="$emit('record-exit', request)"
            size="sm"
            variant="info"
            class="w-full sm:w-auto"
          >
             <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 003 3h4a3 3 0 003-3V7a3 3 0 00-3-3h-4a3 3 0 00-3 3v1"></path></svg>
            Registrar Saída
          </NeonButton>
          <NeonButton
            v-if="canCancel"
            @click="$emit('cancel-request', request)"
            size="sm"
            variant="danger"
            class="w-full sm:w-auto"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Cancelar
          </NeonButton>
           <NeonButton
            @click="viewDetails"
            v-if="!canRecordEntry && !canRecordExit && !canCancel"
            size="sm"
            variant="secondary"
            class="w-full sm:w-auto"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            Detalhes
          </NeonButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import NeonButton from '../NeonButton.vue';
import { useRouter } from 'vue-router'; // Para o botão Detalhes
import { formatDistanceToNowStrict } from 'date-fns';
import { ptBR } from 'date-fns/locale';

const props = defineProps({
  request: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['cancel-request', 'record-entry', 'record-exit']);
const router = useRouter();

const statusMap = {
  pending: { text: 'Pendente', color: 'orange', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', borderColor: 'border-orange-500/50', textColor: 'text-orange-300', bgColor: 'bg-orange-500/20' },
  authorized: { text: 'Autorizado', color: 'green', icon: 'M5 13l4 4L19 7', borderColor: 'border-green-500/50', textColor: 'text-green-300', bgColor: 'bg-green-500/20' },
  unauthorized: { text: 'Não Autorizado', color: 'red', icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', borderColor: 'border-red-500/50', textColor: 'text-red-300', bgColor: 'bg-red-500/20' },
  awaiting_at_gatehouse: { text: 'Aguard. Portaria', color: 'blue', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', borderColor: 'border-blue-500/50', textColor: 'text-blue-300', bgColor: 'bg-blue-500/20' },
  inform_not_home: { text: 'Morador Ausente', color: 'purple', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', borderColor: 'border-purple-500/50', textColor: 'text-purple-300', bgColor: 'bg-purple-500/20' },
  cancelled: { text: 'Cancelado', color: 'gray', icon: 'M6 18L18 6M6 6l12 12', borderColor: 'border-gray-600/50', textColor: 'text-gray-400', bgColor: 'bg-gray-700/20' },
  entry_recorded: { text: 'Entrada Registrada', color: 'cyan', icon: 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1', borderColor: 'border-cyan-500/50', textColor: 'text-cyan-300', bgColor: 'bg-cyan-500/20' },
  exit_recorded: { text: 'Saída Registrada', color: 'teal', icon: 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 003 3h4a3 3 0 003-3V7a3 3 0 00-3-3h-4a3 3 0 00-3 3v1', borderColor: 'border-teal-500/50', textColor: 'text-teal-300', bgColor: 'bg-teal-500/20' },
};

const statusInfo = computed(() => statusMap[props.request.status] || statusMap.pending);

const formattedStatus = computed(() => statusInfo.value.text);
const statusIconPath = computed(() => statusInfo.value.icon);
const statusTextColorClass = computed(() => statusInfo.value.textColor);
const iconBgClass = computed(() => statusInfo.value.bgColor);
const iconColorClass = computed(() => statusInfo.value.textColor); // Ícone usa a mesma cor do texto do status
const borderColorClass = computed(() => statusInfo.value.borderColor);


const cardClasses = computed(() => [
  `bg-gradient-to-br from-gray-800/80 via-gray-900/90 to-gray-800/80`,
  statusInfo.value.borderColor,
  `hover:border-${statusInfo.value.color}-500/70` // Realce no hover com a cor do status
]);

const canRecordEntry = computed(() => ['authorized', 'awaiting_at_gatehouse', 'inform_not_home'].includes(props.request.status) && !props.request.actual_entry_timestamp);
const canRecordExit = computed(() => props.request.status === 'entry_recorded' && !props.request.actual_exit_timestamp);
const canCancel = computed(() => ['pending', 'authorized', 'awaiting_at_gatehouse', 'inform_not_home'].includes(props.request.status) && !props.request.actual_entry_timestamp);

const timeSince = (timestamp) => {
  if (!timestamp) return '';
  try {
    return formatDistanceToNowStrict(new Date(timestamp), { addSuffix: true, locale: ptBR });
  } catch (e) {
    console.error("Error formatting date:", e, timestamp);
    return 'data inválida';
  }
};

const truncateText = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

const viewDetails = () => {
  // TODO: Implementar navegação para página de detalhes da solicitação se necessário,
  // ou abrir um modal com mais informações. Por enquanto, apenas loga.
  console.log("Ver detalhes da solicitação:", props.request.id);
   router.push({ name: 'AccessRequestDetails', params: { id: props.request.id } }); // Supondo que a rota se chame assim
};

</script>

<style scoped>
/* Efeito sutil de pulsação para status pendente */
.border-orange-500\/50 {
  /* Pode-se adicionar uma animação de pulsação aqui se desejado para 'pendente' */
}
</style>
