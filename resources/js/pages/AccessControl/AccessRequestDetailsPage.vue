<template>
  <AuthenticatedLayout page-title="Detalhes da Solicitação de Acesso">
    <div class="max-w-4xl mx-auto space-y-6">
      
      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="relative">
          <div class="w-16 h-16 border-4 border-blue-500/30 border-t-blue-500 rounded-full animate-spin"></div>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-8 h-8 bg-blue-500 rounded-full animate-pulse"></div>
          </div>
        </div>
        <p class="text-gray-400 ml-4">Carregando detalhes da solicitação...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-gradient-to-r from-red-900/50 to-red-800/50 border border-red-500/50 rounded-2xl p-6">
        <div class="flex items-center">
          <svg class="h-6 w-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-red-300">{{ error }}</p>
        </div>
      </div>

      <!-- Content -->
      <div v-else-if="request" class="space-y-6">
        
        <!-- Header Card com Status -->
        <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2" :class="statusInfo.borderColor">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div class="flex items-center mb-4 sm:mb-0">
              <div class="h-12 w-12 rounded-full flex items-center justify-center mr-4" :class="statusInfo.bgColor">
                <svg class="h-6 w-6" :class="statusInfo.textColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="statusInfo.icon"></path>
                </svg>
              </div>
              <div>
                <h1 class="text-2xl font-bold text-white">Solicitação #{{ request.id }}</h1>
                <p class="text-lg font-semibold" :class="statusInfo.textColor">{{ statusInfo.text }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-400">Solicitado em</p>
              <p class="text-white font-medium">{{ formatDateTime(request.request_timestamp) }}</p>
            </div>
          </div>

          <!-- Timeline -->
          <div class="bg-gray-800/50 rounded-xl p-4">
            <h3 class="text-lg font-semibold text-white mb-4">Timeline da Solicitação</h3>
            <div class="space-y-3">
              <div class="flex items-center">
                <div class="h-3 w-3 bg-blue-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-white font-medium">Solicitação criada</p>
                  <p class="text-sm text-gray-400">{{ formatDateTime(request.request_timestamp) }}</p>
                </div>
              </div>
              
              <div v-if="request.authorized_timestamp" class="flex items-center">
                <div class="h-3 w-3 bg-green-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-white font-medium">Autorizada pelo morador</p>
                  <p class="text-sm text-gray-400">{{ formatDateTime(request.authorized_timestamp) }}</p>
                </div>
              </div>
              
              <div v-if="request.actual_entry_timestamp" class="flex items-center">
                <div class="h-3 w-3 bg-cyan-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-white font-medium">Entrada registrada</p>
                  <p class="text-sm text-gray-400">{{ formatDateTime(request.actual_entry_timestamp) }}</p>
                </div>
              </div>
              
              <div v-if="request.actual_exit_timestamp" class="flex items-center">
                <div class="h-3 w-3 bg-teal-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-white font-medium">Saída registrada</p>
                  <p class="text-sm text-gray-400">{{ formatDateTime(request.actual_exit_timestamp) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações do Visitante -->
        <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border border-gray-700/50">
          <h2 class="text-xl font-semibold text-white mb-4 flex items-center">
            <svg class="h-6 w-6 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Informações do Visitante
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-center">
              <img
                v-if="request.visitor?.photo_url"
                :src="request.visitor.photo_url"
                alt="Foto do Visitante"
                class="h-20 w-20 rounded-full object-cover mr-4 border-2 border-blue-500/50"
              >
              <div
                v-else
                class="h-20 w-20 rounded-full flex items-center justify-center mr-4 border-2 border-blue-500/50 bg-blue-500/20 text-2xl font-semibold text-blue-300"
              >
                {{ request.visitor?.name?.charAt(0).toUpperCase() || 'V' }}
              </div>
              <div>
                <p class="text-xl font-semibold text-white">{{ request.visitor?.name || 'Nome não informado' }}</p>
                <p class="text-gray-400">{{ request.visitor?.document || 'Documento não informado' }}</p>
                <p v-if="request.visitor?.phone" class="text-gray-400">{{ request.visitor.phone }}</p>
              </div>
            </div>
            
            <div class="space-y-3 text-sm">
              <div v-if="request.visitor?.company">
                <span class="font-medium text-gray-400">Empresa:</span>
                <span class="text-white ml-2">{{ request.visitor.company }}</span>
              </div>
              <div v-if="request.visitor?.email">
                <span class="font-medium text-gray-400">Email:</span>
                <span class="text-white ml-2">{{ request.visitor.email }}</span>
              </div>
              <div v-if="request.visit_purpose">
                <span class="font-medium text-gray-400">Propósito da visita:</span>
                <span class="text-white ml-2">{{ request.visit_purpose }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações do Veículo -->
        <div v-if="request.visitor_vehicle" class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border border-gray-700/50">
          <h2 class="text-xl font-semibold text-white mb-4 flex items-center">
            <svg class="h-6 w-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
            </svg>
            Informações do Veículo
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
              <span class="font-medium text-gray-400">Placa:</span>
              <span class="text-white ml-2 font-mono">{{ request.visitor_vehicle.plate }}</span>
            </div>
            <div>
              <span class="font-medium text-gray-400">Modelo:</span>
              <span class="text-white ml-2">{{ request.visitor_vehicle.model }}</span>
            </div>
            <div>
              <span class="font-medium text-gray-400">Cor:</span>
              <span class="text-white ml-2">{{ request.visitor_vehicle.color }}</span>
            </div>
          </div>
        </div>

        <!-- Destino e Estacionamento -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Destino -->
          <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border border-gray-700/50">
            <h2 class="text-xl font-semibold text-white mb-4 flex items-center">
              <svg class="h-6 w-6 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Destino
            </h2>
            
            <div class="space-y-3 text-sm">
              <div>
                <span class="font-medium text-gray-400">Morador:</span>
                <span class="text-white ml-2">{{ request.resident?.name || 'Não informado' }}</span>
              </div>
              <div v-if="request.apartment">
                <span class="font-medium text-gray-400">Apartamento:</span>
                <span class="text-white ml-2">{{ request.apartment.number }}</span>
              </div>
              <div v-if="request.apartment?.block">
                <span class="font-medium text-gray-400">Bloco/Torre:</span>
                <span class="text-white ml-2">{{ request.apartment.block.name }}</span>
              </div>
            </div>
          </div>

          <!-- Estacionamento -->
          <div v-if="request.parking_spot" class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border border-gray-700/50">
            <h2 class="text-xl font-semibold text-white mb-4 flex items-center">
              <svg class="h-6 w-6 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 011 1v4a1 1 0 01-1 1h-1v10a2 2 0 01-2 2H6a2 2 0 01-2-2V10H3a1 1 0 01-1-1V5a1 1 0 011-1h4z" />
              </svg>
              Vaga de Estacionamento
            </h2>
            
            <div class="text-sm">
              <span class="font-medium text-gray-400">Vaga:</span>
              <span class="text-white ml-2 text-lg font-semibold">{{ request.parking_spot.identifier }}</span>
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div v-if="request.gatehouse_observation || request.resident_observation" class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border border-gray-700/50">
          <h2 class="text-xl font-semibold text-white mb-4 flex items-center">
            <svg class="h-6 w-6 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Observações
          </h2>
          
          <div class="space-y-4">
            <div v-if="request.gatehouse_observation" class="bg-gray-800/50 rounded-lg p-4">
              <h3 class="font-medium text-gray-300 mb-2">Observações da Portaria:</h3>
              <p class="text-white">{{ request.gatehouse_observation }}</p>
            </div>
            
            <div v-if="request.resident_observation" class="bg-gray-800/50 rounded-lg p-4">
              <h3 class="font-medium text-gray-300 mb-2">Observações do Morador:</h3>
              <p class="text-white">{{ request.resident_observation }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border border-gray-700/50">
          <div class="flex flex-wrap gap-4 justify-center sm:justify-start">
            <NeonButton
              v-if="canRecordEntry"
              @click="recordEntry"
              variant="success"
              size="md"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              Registrar Entrada
            </NeonButton>
            
            <NeonButton
              v-if="canRecordExit"
              @click="recordExit"
              variant="info"
              size="md"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 003 3h4a3 3 0 003-3V7a3 3 0 00-3-3h-4a3 3 0 00-3 3v1" />
              </svg>
              Registrar Saída
            </NeonButton>
            
            <NeonButton
              v-if="canCancel"
              @click="cancelRequest"
              variant="danger"
              size="md"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Cancelar Solicitação
            </NeonButton>
            
            <NeonButton
              @click="goBack"
              variant="secondary"
              size="md"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Voltar
            </NeonButton>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../../components/NeonButton.vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';

const route = useRoute();
const router = useRouter();
const { successToast, errorToast } = useToast();

const request = ref(null);
const isLoading = ref(true);
const error = ref('');

const statusMap = {
  pending: { 
    text: 'Pendente', 
    color: 'orange', 
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 
    borderColor: 'border-orange-500/50', 
    textColor: 'text-orange-300', 
    bgColor: 'bg-orange-500/20' 
  },
  authorized: { 
    text: 'Autorizado', 
    color: 'green', 
    icon: 'M5 13l4 4L19 7', 
    borderColor: 'border-green-500/50', 
    textColor: 'text-green-300', 
    bgColor: 'bg-green-500/20' 
  },
  unauthorized: { 
    text: 'Não Autorizado', 
    color: 'red', 
    icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', 
    borderColor: 'border-red-500/50', 
    textColor: 'text-red-300', 
    bgColor: 'bg-red-500/20' 
  },
  awaiting_at_gatehouse: { 
    text: 'Aguardando na Portaria', 
    color: 'blue', 
    icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 
    borderColor: 'border-blue-500/50', 
    textColor: 'text-blue-300', 
    bgColor: 'bg-blue-500/20' 
  },
  inform_not_home: { 
    text: 'Morador Ausente', 
    color: 'purple', 
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 
    borderColor: 'border-purple-500/50', 
    textColor: 'text-purple-300', 
    bgColor: 'bg-purple-500/20' 
  },
  cancelled: { 
    text: 'Cancelado', 
    color: 'gray', 
    icon: 'M6 18L18 6M6 6l12 12', 
    borderColor: 'border-gray-600/50', 
    textColor: 'text-gray-400', 
    bgColor: 'bg-gray-700/20' 
  },
  entry_recorded: { 
    text: 'Entrada Registrada', 
    color: 'cyan', 
    icon: 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1', 
    borderColor: 'border-cyan-500/50', 
    textColor: 'text-cyan-300', 
    bgColor: 'bg-cyan-500/20' 
  },
  exit_recorded: { 
    text: 'Saída Registrada', 
    color: 'teal', 
    icon: 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 003 3h4a3 3 0 003-3V7a3 3 0 00-3-3h-4a3 3 0 00-3 3v1', 
    borderColor: 'border-teal-500/50', 
    textColor: 'text-teal-300', 
    bgColor: 'bg-teal-500/20' 
  },
};

const statusInfo = computed(() => statusMap[request.value?.status] || statusMap.pending);

const canRecordEntry = computed(() => 
  request.value && 
  ['authorized', 'awaiting_at_gatehouse', 'inform_not_home'].includes(request.value.status) && 
  !request.value.actual_entry_timestamp
);

const canRecordExit = computed(() => 
  request.value && 
  request.value.status === 'entry_recorded' && 
  !request.value.actual_exit_timestamp
);

const canCancel = computed(() => 
  request.value && 
  ['pending', 'authorized', 'awaiting_at_gatehouse', 'inform_not_home'].includes(request.value.status) && 
  !request.value.actual_entry_timestamp
);

const fetchRequestDetails = async () => {
  try {
    isLoading.value = true;
    error.value = '';
    
    const response = await api.get(`/api/access-control/access-requests/${route.params.id}`);
    request.value = response.data.data;
  } catch (e) {
    console.error('Erro ao carregar detalhes da solicitação:', e);
    error.value = e.response?.data?.message || 'Erro ao carregar os detalhes da solicitação.';
    errorToast(error.value);
  } finally {
    isLoading.value = false;
  }
};

const formatDateTime = (timestamp) => {
  if (!timestamp) return 'Não informado';
  try {
    return format(new Date(timestamp), 'dd/MM/yyyy \'às\' HH:mm', { locale: ptBR });
  } catch (e) {
    console.error('Erro ao formatar data:', e);
    return 'Data inválida';
  }
};

const recordEntry = () => {
  router.push({ 
    name: 'RecordAccessEntry', 
    params: { id: request.value.id } 
  });
};

const recordExit = async () => {
  try {
    await api.post(`/api/access-control/access-requests/${request.value.id}/record-exit`);
    successToast('Saída registrada com sucesso!');
    fetchRequestDetails(); // Refresh data
  } catch (e) {
    console.error('Erro ao registrar saída:', e);
    errorToast(e.response?.data?.message || 'Erro ao registrar a saída.');
  }
};

const cancelRequest = async () => {
  if (!confirm('Tem certeza que deseja cancelar esta solicitação?')) {
    return;
  }
  
  try {
    await api.post(`/api/access-control/access-requests/${request.value.id}/cancel`);
    successToast('Solicitação cancelada com sucesso!');
    fetchRequestDetails(); // Refresh data
  } catch (e) {
    console.error('Erro ao cancelar solicitação:', e);
    errorToast(e.response?.data?.message || 'Erro ao cancelar a solicitação.');
  }
};

const goBack = () => {
  router.go(-1);
};

onMounted(() => {
  fetchRequestDetails();
});
</script> 