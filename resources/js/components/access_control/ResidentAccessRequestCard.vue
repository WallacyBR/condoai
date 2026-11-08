<template>
  <div
    class="rounded-2xl p-5 border-2 shadow-md transition-all duration-300 ease-in-out bg-gradient-to-br from-gray-800/80 via-gray-900/90 to-gray-800/80"
    :class="borderColorClass"
  >
    <div class="flex flex-col h-full">
      <!-- Header do Card: Ícone e Status (Sempre Pendente aqui, mas mantendo a estrutura) -->
      <div class="flex items-center mb-3">
        <div
          class="h-10 w-10 rounded-full flex items-center justify-center mr-3 flex-shrink-0"
          :class="statusInfo.bgColor"
        >
          <svg class="h-5 w-5" :class="statusInfo.textColor" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="statusInfo.icon"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold" :class="statusInfo.textColor">{{ formattedStatus }}</p>
          <p class="text-xs text-gray-400">Solicitado {{ timeSince(request.request_timestamp) }}</p>
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
            :class="[statusInfo.bgColor, borderColorClass, statusInfo.textColor]"
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
        <p><strong class="font-medium text-gray-400">Veículo:</strong> {{ request.visitor_vehicle.plate || request.visitor_vehicle.license_plate }} - {{ request.visitor_vehicle.model }} ({{ request.visitor_vehicle.color }})</p>
      </div>

      <!-- Observações da Portaria (se houver) -->
      <div v-if="request.gatehouse_observation" class="text-xs text-gray-400 mt-1 mb-3 border-t border-gray-700/50 pt-3">
        <p><strong class="text-gray-500">Obs. Portaria:</strong> {{ truncateText(request.gatehouse_observation, 70) }}</p>
      </div>

      <!-- Ações -->
      <div class="mt-auto pt-4 border-t border-gray-700/50">
        <div class="flex justify-end">
          <NeonButton
            @click="$emit('respond-to-request', request)"
            size="sm"
            variant="primary"
            class="w-full sm:w-auto"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
            Responder Solicitação
          </NeonButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import NeonButton from '../NeonButton.vue';
import { formatDistanceToNowStrict } from 'date-fns';
import { ptBR } from 'date-fns/locale';

const props = defineProps({
  request: {
    type: Object,
    required: true,
  },
});

defineEmits(['respond-to-request']);

// Para a interface do morador, o status será predominantemente 'pendente'
// mas mantemos a estrutura para consistência caso haja outros status no futuro.
const statusMap = {
  pending: { text: 'Pendente de Aprovação', color: 'orange', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', borderColor: 'border-orange-500/50', textColor: 'text-orange-300', bgColor: 'bg-orange-500/20' },
  // Outros status podem ser adicionados se a tela do morador for exibir mais do que pendentes
};

const statusInfo = computed(() => statusMap[props.request.status] || statusMap.pending);

const formattedStatus = computed(() => statusInfo.value.text);
const borderColorClass = computed(() => statusInfo.value.borderColor);

const timeSince = (timestamp) => {
  if (!timestamp) return '';
  try {
    return formatDistanceToNowStrict(new Date(timestamp), { addSuffix: true, locale: ptBR });
  } catch (e) {
    return 'data inválida';
  }
};

const truncateText = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

</script>

<style scoped>
/* Estilos específicos se necessário */
</style>
