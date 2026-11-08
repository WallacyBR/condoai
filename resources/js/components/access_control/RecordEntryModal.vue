<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-2xl p-6 w-full max-w-2xl mx-4 border-2 border-green-500/30 shadow-2xl">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white flex items-center">
          <svg class="h-6 w-6 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
          </svg>
          Registrar Entrada
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-white">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="space-y-6">
        <!-- Informações do Visitante -->
        <div class="bg-gray-800/50 border border-gray-700/50 rounded-xl p-4">
          <h3 class="text-lg font-medium text-white mb-3 flex items-center">
            <svg class="h-5 w-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Visitante
          </h3>
          <p class="text-white text-lg font-medium">{{ visitorName || 'Nome não disponível' }}</p>
          <p class="text-gray-400 text-sm mt-1">ID da Solicitação: #{{ requestId }}</p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Data e Hora da Entrada -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Data e Hora da Entrada *
            </label>
            <input
              v-model="form.entry_datetime"
              type="datetime-local"
              required
              class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-green-500 focus:border-green-500"
            />
          </div>

          <!-- Observações da Portaria -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Observações da Portaria
            </label>
            <textarea
              v-model="form.gatehouse_entry_observation"
              rows="3"
              class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="Observações sobre a entrada do visitante..."
            ></textarea>
          </div>

          <!-- Checklist de Verificação -->
          <div class="bg-gray-800/50 border border-gray-700/50 rounded-xl p-4">
            <h4 class="text-md font-medium text-white mb-3 flex items-center">
              <svg class="h-5 w-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Verificações de Segurança
            </h4>
            
            <div class="space-y-3">
              <label class="flex items-center">
                <input
                  v-model="form.document_verified"
                  type="checkbox"
                  class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-600 rounded bg-gray-700"
                />
                <span class="ml-3 text-sm text-gray-300">Documento de identidade verificado</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="form.photo_taken"
                  type="checkbox"
                  class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-600 rounded bg-gray-700"
                />
                <span class="ml-3 text-sm text-gray-300">Foto do visitante registrada</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="form.resident_contacted"
                  type="checkbox"
                  class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-600 rounded bg-gray-700"
                />
                <span class="ml-3 text-sm text-gray-300">Morador contatado e confirmou a visita</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="form.vehicle_registered"
                  type="checkbox"
                  class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-600 rounded bg-gray-700"
                />
                <span class="ml-3 text-sm text-gray-300">Veículo registrado (se aplicável)</span>
              </label>
            </div>
          </div>

          <!-- Botões -->
          <div class="flex space-x-3 pt-6 border-t border-gray-700/50">
            <button
              type="button"
              @click="$emit('close')"
              class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="flex-2 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition-colors disabled:opacity-50"
              :disabled="loading"
            >
              <span v-if="loading" class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Registrando...
              </span>
              <span v-else class="flex items-center justify-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Registrar Entrada
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';

const props = defineProps({
  show: { type: Boolean, required: true },
  requestId: { type: [String, Number], default: null },
  visitorName: { type: String, default: '' }
});

const emit = defineEmits(['close', 'entry-recorded']);
const { successToast, errorToast } = useToast();

const loading = ref(false);

const form = ref({
  entry_datetime: '',
  gatehouse_entry_observation: '',
  document_verified: false,
  photo_taken: false,
  resident_contacted: false,
  vehicle_registered: false
});

// Resetar formulário quando modal abrir
watch(() => props.show, (newShow) => {
  if (newShow) {
    // Definir data/hora atual como padrão
    const now = new Date();
    form.value = {
      entry_datetime: now.toISOString().slice(0, 16),
      gatehouse_entry_observation: '',
      document_verified: false,
      photo_taken: false,
      resident_contacted: false,
      vehicle_registered: false
    };
  }
});

const handleSubmit = async () => {
  if (loading.value || !props.requestId) return;

  loading.value = true;
  try {
    const payload = {
      entry_datetime: form.value.entry_datetime,
      gatehouse_entry_observation: form.value.gatehouse_entry_observation || null,
      security_checks: {
        document_verified: form.value.document_verified,
        photo_taken: form.value.photo_taken,
        resident_contacted: form.value.resident_contacted,
        vehicle_registered: form.value.vehicle_registered
      }
    };

    await api.post(`/api/access-control/access-requests/${props.requestId}/record-entry`, payload);
    
    successToast('Entrada registrada com sucesso!');
    emit('entry-recorded');
    emit('close');
  } catch (error) {
    console.error('Erro ao registrar entrada:', error);
    errorToast(error.response?.data?.message || 'Erro ao registrar entrada do visitante');
  } finally {
    loading.value = false;
  }
};
</script> 