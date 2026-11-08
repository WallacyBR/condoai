<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-2xl p-6 w-full max-w-4xl mx-4 border-2 border-cyan-500/30 shadow-2xl max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white flex items-center">
          <svg class="h-6 w-6 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Nova Solicitação de Acesso
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-white">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Dados do Visitante -->
        <div class="bg-gray-800/50 border border-gray-700/50 rounded-xl p-4">
          <h3 class="text-lg font-medium text-white mb-4 flex items-center">
            <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Dados do Visitante
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Nome Completo *</label>
              <input
                v-model="form.visitor_name"
                type="text"
                required
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                placeholder="Nome completo do visitante"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Documento *</label>
              <input
                v-model="form.visitor_document"
                type="text"
                required
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                placeholder="CPF ou RG"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Telefone</label>
              <input
                v-model="form.visitor_phone"
                type="tel"
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                placeholder="(11) 99999-9999"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Data/Hora da Visita *</label>
              <input
                v-model="form.visit_date"
                type="datetime-local"
                required
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
              />
            </div>
          </div>
        </div>

        <!-- Destino -->
        <div class="bg-gray-800/50 border border-gray-700/50 rounded-xl p-4">
          <h3 class="text-lg font-medium text-white mb-4 flex items-center">
            <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0H3"></path>
            </svg>
            Destino
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Torre/Bloco</label>
              <input
                v-model="form.tower_block"
                type="text"
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                placeholder="Ex: Torre A, Bloco 1"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Apartamento *</label>
              <input
                v-model="form.apartment"
                type="text"
                required
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                placeholder="Ex: 101, 205"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Nome do Morador</label>
              <input
                v-model="form.resident_name"
                type="text"
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                placeholder="Nome do morador a visitar"
              />
            </div>
          </div>
        </div>

        <!-- Dados do Veículo -->
        <div class="bg-gray-800/50 border border-gray-700/50 rounded-xl p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-white flex items-center">
              <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
              Veículo (Opcional)
            </h3>
            <label class="flex items-center cursor-pointer">
              <input v-model="hasVehicle" type="checkbox" class="sr-only" />
              <div class="relative">
                <div class="block bg-gray-600 w-14 h-8 rounded-full"></div>
                <div :class="['absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition', hasVehicle ? 'transform translate-x-6 bg-cyan-400' : '']"></div>
              </div>
              <span class="ml-3 text-sm text-gray-300">Possui veículo</span>
            </label>
          </div>

          <div v-if="hasVehicle" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Placa *</label>
              <input
                v-model="form.vehicle_plate"
                type="text"
                :required="hasVehicle"
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 uppercase"
                placeholder="ABC-1234"
                maxlength="8"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Modelo</label>
              <input
                v-model="form.vehicle_model"
                type="text"
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                placeholder="Ex: Honda Civic"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Cor</label>
              <input
                v-model="form.vehicle_color"
                type="text"
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                placeholder="Ex: Branco"
              />
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Observações da Portaria</label>
          <textarea
            v-model="form.gatehouse_observation"
            rows="3"
            class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
            placeholder="Observações adicionais sobre a solicitação..."
          ></textarea>
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
            class="flex-2 px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-500 transition-colors disabled:opacity-50"
            :disabled="loading"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Criando...
            </span>
            <span v-else class="flex items-center justify-center">
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Criar Solicitação
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';

const props = defineProps({
  show: { type: Boolean, required: true }
});

const emit = defineEmits(['close', 'solicitacao-criada']);
const { successToast, errorToast } = useToast();

const loading = ref(false);
const hasVehicle = ref(false);

const form = ref({
  visitor_name: '',
  visitor_document: '',
  visitor_phone: '',
  visit_date: '',
  tower_block: '',
  apartment: '',
  resident_name: '',
  vehicle_plate: '',
  vehicle_model: '',
  vehicle_color: '',
  gatehouse_observation: ''
});

// Resetar formulário quando modal abrir
watch(() => props.show, (newShow) => {
  if (newShow) {
    form.value = {
      visitor_name: '',
      visitor_document: '',
      visitor_phone: '',
      visit_date: new Date().toISOString().slice(0, 16),
      tower_block: '',
      apartment: '',
      resident_name: '',
      vehicle_plate: '',
      vehicle_model: '',
      vehicle_color: '',
      gatehouse_observation: ''
    };
    hasVehicle.value = false;
  }
});

// Limpar dados do veículo quando desmarcar
watch(hasVehicle, (newValue) => {
  if (!newValue) {
    form.value.vehicle_plate = '';
    form.value.vehicle_model = '';
    form.value.vehicle_color = '';
  }
});

const handleSubmit = async () => {
  if (loading.value) return;

  loading.value = true;
  try {
    const payload = {
      visitor_name: form.value.visitor_name,
      visitor_document: form.value.visitor_document,
      visitor_phone: form.value.visitor_phone || null,
      visit_date: form.value.visit_date,
      tower_block: form.value.tower_block || null,
      apartment: form.value.apartment,
      resident_name: form.value.resident_name || null,
      gatehouse_observation: form.value.gatehouse_observation || null
    };

    if (hasVehicle.value && form.value.vehicle_plate) {
      payload.vehicle = {
        plate: form.value.vehicle_plate.toUpperCase(),
        model: form.value.vehicle_model || null,
        color: form.value.vehicle_color || null
      };
    }

    await api.post('/access-control/access-requests', payload);
    
    successToast('Solicitação de acesso criada com sucesso!');
    emit('solicitacao-criada');
    emit('close');
  } catch (error) {
    console.error('Erro ao criar solicitação:', error);
    errorToast(error.response?.data?.message || 'Erro ao criar solicitação de acesso');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Toggle switch customização */
input:checked + div > div {
  @apply bg-cyan-400 transform translate-x-6;
}
</style> 