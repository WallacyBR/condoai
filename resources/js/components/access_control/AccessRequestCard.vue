<template>
  <div
    class="relative rounded-xl p-5 border shadow-sm transition-colors overflow-hidden flex flex-col justify-between"
    :class="cardClasses"
  >
    <!-- ALERTA DE MEDIDA PROTETIVA -->
    <div
      v-if="hasProtectiveMeasure"
      class="bg-rose-500 text-white p-3 -mx-5 -mt-5 mb-4 text-center"
    >
      <div class="flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 1.944c-1.66 0-3.202.666-4.323 1.788S3.89 6.223 3.89 7.882v3.655a.5.5 0 00.13.325l1.196 1.367a.5.5 0 00.37.171h8.828a.5.5 0 00.37-.17l1.196-1.368a.5.5 0 00.13-.325V7.882c0-1.66-.666-3.202-1.788-4.323S11.66 1.944 10 1.944zM9 13a1 1 0 112 0v-1a1 1 0 11-2 0v1zm1-9a1 1 0 00-1 1v4a1 1 0 102 0V5a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <span class="font-bold text-xs uppercase tracking-wider">Medida Protetiva Ativa</span>
      </div>
      <p class="text-[11px] mt-0.5 opacity-90">
        Protegendo: <strong class="font-semibold">{{ request.protective_measure.protected_resident.name }}</strong>
      </p>
    </div>

    <div>
      <!-- Header do Card: Ícone e Status -->
      <div class="flex items-center justify-between mb-3.5">
        <div class="flex items-center">
          <div
            class="h-9 w-9 rounded-lg flex items-center justify-center mr-2.5 shrink-0"
            :class="iconBgClass"
          >
            <svg class="h-4 w-4" :class="iconColorClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="statusIconPath"></path>
            </svg>
          </div>
          <div>
            <p class="text-xs font-semibold" :class="statusTextColorClass">{{ formattedStatus }}</p>
            <p class="text-[11px] text-slate-400">{{ timeSince(request.request_timestamp) }}</p>
          </div>
        </div>
      </div>

      <!-- Detalhes do Visitante -->
      <div class="mb-3 border-t border-slate-100 dark:border-slate-800 pt-3">
        <div class="flex items-center">
          <img
            v-if="request.visitor?.photo_url"
            :src="request.visitor.photo_url"
            alt="Foto do Visitante"
            class="h-10 w-10 rounded-full object-cover mr-3 border border-slate-200 dark:border-slate-700"
          />
          <div
            v-else
            class="h-10 w-10 rounded-full flex items-center justify-center mr-3 border border-slate-200 dark:border-slate-700 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300"
          >
            {{ request.visitor?.name?.charAt(0).toUpperCase() || 'V' }}
          </div>
          <div class="min-w-0">
            <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm truncate" :title="request.visitor?.name">
              {{ request.visitor?.name || 'Visitante Desconhecido' }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
              {{ request.visitor?.document || 'Documento não informado' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Detalhes do Veículo -->
      <div v-if="request.visitor_vehicle" class="mb-2 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 pt-2.5">
        <p><strong class="font-medium text-slate-400">Veículo:</strong> {{ request.visitor_vehicle.plate || request.visitor_vehicle.license_plate }} - {{ request.visitor_vehicle.model }} ({{ request.visitor_vehicle.color }})</p>
      </div>

      <!-- Unidade Destino -->
      <div class="mb-2 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 pt-2.5">
        <p><strong class="font-medium text-slate-400">Unidade:</strong> {{ request.apartment?.number || 'Unidade não informada' }}</p>
        <p v-if="request.resident?.name" class="mt-0.5"><strong class="font-medium text-slate-400">Responsável:</strong> {{ request.resident.name }}</p>
      </div>

      <!-- Observações -->
      <div v-if="request.gatehouse_observation || request.resident_observation" class="text-xs text-slate-500 dark:text-slate-400 mb-2 border-t border-slate-100 dark:border-slate-800 pt-2.5 space-y-0.5">
        <p v-if="request.gatehouse_observation"><strong class="text-slate-400">Portaria:</strong> {{ truncateText(request.gatehouse_observation, 50) }}</p>
        <p v-if="request.resident_observation"><strong class="text-slate-400">Morador:</strong> {{ truncateText(request.resident_observation, 50) }}</p>
      </div>

      <!-- Vaga Utilizada -->
      <div v-if="request.parking_spot && request.status === 'entry_recorded'" class="text-xs text-indigo-600 dark:text-indigo-400 mb-2 border-t border-slate-100 dark:border-slate-800 pt-2.5">
        <p><strong class="font-medium">Vaga:</strong> {{ request.parking_spot.identifier }}</p>
      </div>
    </div>

    <!-- Ações -->
    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 mt-2">
      <div class="flex flex-col sm:flex-row gap-2 justify-end">
        <NeonButton
          v-if="canRecordEntry"
          @click="$emit('record-entry', request)"
          size="sm"
          variant="primary"
          class="w-full sm:w-auto"
          :disabled="hasProtectiveMeasure"
          :title="hasProtectiveMeasure ? 'Entrada bloqueada devido a medida protetiva' : 'Registrar Entrada'"
        >
          <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
          Registrar Entrada
        </NeonButton>

        <NeonButton
          v-if="canRecordExit"
          @click="$emit('record-exit', request)"
          size="sm"
          variant="secondary"
          class="w-full sm:w-auto"
        >
          <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 003 3h4a3 3 0 003-3V7a3 3 0 00-3-3h-4a3 3 0 00-3 3v1"></path></svg>
          Registrar Saída
        </NeonButton>

        <NeonButton
          v-if="canCancel"
          @click="$emit('cancel-request', request)"
          size="sm"
          variant="secondary"
          class="w-full sm:w-auto text-rose-600 hover:text-rose-700 dark:text-rose-400"
        >
          Cancelar
        </NeonButton>
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

defineEmits(['cancel-request', 'record-entry', 'record-exit']);

const hasProtectiveMeasure = computed(() => !!props.request.protective_measure);

const statusMap = {
  pending: { 
    text: 'Pendente', 
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 
    textColor: 'text-amber-700 dark:text-amber-300', 
    bgColor: 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400' 
  },
  authorized: { 
    text: 'Autorizado', 
    icon: 'M5 13l4 4L19 7', 
    textColor: 'text-emerald-700 dark:text-emerald-300', 
    bgColor: 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' 
  },
  unauthorized: { 
    text: 'Não Autorizado', 
    icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', 
    textColor: 'text-rose-700 dark:text-rose-300', 
    bgColor: 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400' 
  },
  awaiting_at_gatehouse: { 
    text: 'Aguard. Portaria', 
    icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 
    textColor: 'text-blue-700 dark:text-blue-300', 
    bgColor: 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' 
  },
  inform_not_home: { 
    text: 'Morador Ausente', 
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 
    textColor: 'text-purple-700 dark:text-purple-300', 
    bgColor: 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400' 
  },
  cancelled: { 
    text: 'Cancelado', 
    icon: 'M6 18L18 6M6 6l12 12', 
    textColor: 'text-slate-600 dark:text-slate-400', 
    bgColor: 'bg-slate-100 dark:bg-slate-800 text-slate-500' 
  },
  entry_recorded: { 
    text: 'Entrada Registrada', 
    icon: 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1', 
    textColor: 'text-indigo-700 dark:text-indigo-300', 
    bgColor: 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400' 
  },
  exit_recorded: { 
    text: 'Saída Registrada', 
    icon: 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 003 3h4a3 3 0 003-3V7a3 3 0 00-3-3h-4a3 3 0 00-3 3v1', 
    textColor: 'text-teal-700 dark:text-teal-300', 
    bgColor: 'bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400' 
  },
};

const statusInfo = computed(() => statusMap[props.request.status] || statusMap.pending);
const formattedStatus = computed(() => statusInfo.value.text);
const statusIconPath = computed(() => statusInfo.value.icon);
const statusTextColorClass = computed(() => statusInfo.value.textColor);
const iconBgClass = computed(() => statusInfo.value.bgColor);
const iconColorClass = computed(() => statusInfo.value.textColor);

const cardClasses = computed(() => {
  if (hasProtectiveMeasure.value) {
    return 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-300 dark:border-rose-800/80';
  }
  return 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700';
});

const canRecordEntry = computed(() => {
  if (hasProtectiveMeasure.value) return false;
  return ['authorized', 'awaiting_at_gatehouse', 'inform_not_home'].includes(props.request.status) && !props.request.actual_entry_timestamp;
});

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
</script>
