<template>
  <div
    class="rounded-2xl p-5 border-2 shadow-md transition-all duration-300 ease-in-out"
    :class="cardClasses"
  >
    <div class="flex flex-col h-full">
      <!-- Header do Card: Ícone e Status (Ativa/Inativa) -->
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
          <p class="text-sm font-semibold" :class="statusInfo.textColor">{{ statusInfo.text }}</p>
          <p class="text-xs text-gray-400">Processo: {{ measure.process_number || 'N/A' }}</p>
        </div>
      </div>

      <!-- Detalhes da Medida -->
      <div class="space-y-2 text-sm text-gray-300 mb-3 border-t border-gray-700/50 pt-3">
        <p><strong class="text-gray-400">Morador Protegido:</strong> {{ measure.protected_resident?.name || 'N/A' }}</p>
        <p><strong class="text-gray-400">Visitante Agressor:</strong> {{ measure.aggressor_visitor?.name || 'N/A' }} (Doc: {{ measure.aggressor_visitor?.document || 'N/A' }})</p>
        <p><strong class="text-gray-400">Descrição:</strong> {{ truncateText(measure.measure_description, 100) }}</p>
        <p><strong class="text-gray-400">Validade:</strong> {{ formatDate(measure.validity_start_date) }} até {{ measure.validity_end_date ? formatDate(measure.validity_end_date) : 'Indeterminada' }}</p>
        <div v-if="measure.document_url" class="mt-2">
            <a
                :href="measure.document_url"
                target="_blank"
                class="inline-flex items-center text-xs text-cyan-400 hover:text-cyan-300 hover:underline"
            >
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                Ver Documento Anexado
            </a>
        </div>
      </div>

      <!-- Ações -->
      <div class="mt-auto pt-4 border-t border-gray-700/50">
        <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2 justify-end">
          <NeonButton
            @click="$emit('edit-measure', measure)"
            size="sm"
            variant="info"
            class="w-full sm:w-auto"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Editar
          </NeonButton>
          <NeonButton
            @click="$emit('toggle-measure-status', measure)"
            size="sm"
            :variant="measure.is_active ? 'warning' : 'success'"
            class="w-full sm:w-auto"
          >
            <svg v-if="measure.is_active" class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            <svg v-else class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ measure.is_active ? 'Desativar' : 'Ativar' }}
          </NeonButton>
           <NeonButton
            @click="$emit('delete-measure', measure)"
            size="sm"
            variant="danger"
            class="w-full sm:w-auto"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Excluir
          </NeonButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import NeonButton from '../NeonButton.vue';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';

const props = defineProps({
  measure: {
    type: Object,
    required: true,
  },
});

defineEmits(['edit-measure', 'toggle-measure-status', 'delete-measure']);

const statusMap = {
  active: { text: 'Ativa', color: 'green', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', borderColor: 'border-green-500/50', textColor: 'text-green-300', bgColor: 'bg-green-500/20' },
  inactive: { text: 'Inativa', color: 'gray', icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', borderColor: 'border-gray-600/50', textColor: 'text-gray-400', bgColor: 'bg-gray-700/20' },
};

const statusInfo = computed(() => props.measure.is_active ? statusMap.active : statusMap.inactive);

const cardClasses = computed(() => [
  `bg-gradient-to-br from-gray-800/80 via-gray-900/90 to-gray-800/80`,
  statusInfo.value.borderColor,
  `hover:border-${statusInfo.value.color}-500/70`
]);

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  try {
    return format(parseISO(dateString), 'dd/MM/yyyy', { locale: ptBR });
  } catch (e) {
    console.error("Error formatting date:", dateString, e);
    return dateString; // Retorna a string original se houver erro
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
