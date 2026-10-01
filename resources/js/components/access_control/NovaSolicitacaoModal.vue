<template>
  <div v-if="show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl p-6 w-full max-w-4xl border border-slate-200 dark:border-slate-800 shadow-xl max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
        <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 flex items-center">
          <svg class="h-5 w-5 mr-2.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Nova Solicitação de Acesso
        </h2>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Dados do Visitante -->
        <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-5">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-4 flex items-center">
            <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Dados do Visitante
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nome Completo *</label>
              <input
                v-model="form.visitor_name"
                type="text"
                required
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
                placeholder="Nome completo do visitante"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Documento *</label>
              <input
                v-model="form.visitor_document"
                type="text"
                required
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
                placeholder="CPF ou RG"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Telefone</label>
              <input
                v-model="form.visitor_phone"
                type="tel"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
                placeholder="(11) 99999-9999"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Data/Hora da Visita *</label>
              <input
                v-model="form.visit_date"
                type="datetime-local"
                required
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              />
            </div>
          </div>
        </div>

        <!-- Destino -->
        <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-5">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-4 flex items-center">
            <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0H3"></path>
            </svg>
            Destino
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Unidade de Destino *</label>
              <select
                v-model="form.apartment_id"
                required
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              >
                <option disabled value="">Selecione a unidade</option>
                <option v-for="apartment in apartments" :key="apartment.id" :value="apartment.id">
                  {{ formatApartment(apartment) }}
                </option>
              </select>
              <p v-if="loadingApartments" class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">Carregando unidades...</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Responsável Vinculado</label>
              <input
                :value="selectedApartmentResident"
                type="text"
                disabled
                class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-600 dark:text-slate-400 text-sm cursor-not-allowed"
                placeholder="Preenchido pela unidade selecionada"
              />
            </div>
          </div>
        </div>

        <!-- Dados do Veículo -->
        <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center">
              <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
              Veículo (Opcional)
            </h3>
            <label class="flex items-center cursor-pointer">
              <input v-model="hasVehicle" type="checkbox" class="sr-only" />
              <div class="relative">
                <div class="block w-11 h-6 rounded-full transition-colors" :class="hasVehicle ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-700'"></div>
                <div :class="['absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition-transform', hasVehicle ? 'transform translate-x-5' : '']"></div>
              </div>
              <span class="ml-2.5 text-xs text-slate-700 dark:text-slate-300 font-medium">Possui veículo</span>
            </label>
          </div>

          <div v-if="hasVehicle" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Placa *</label>
              <input
                v-model="form.vehicle_plate"
                type="text"
                :required="hasVehicle"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm uppercase font-mono"
                placeholder="ABC-1234"
                maxlength="8"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Modelo</label>
              <input
                v-model="form.vehicle_model"
                type="text"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
                placeholder="Ex: Honda Civic"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Cor</label>
              <input
                v-model="form.vehicle_color"
                type="text"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
                placeholder="Ex: Branco"
              />
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
            Observações da Portaria
          </label>
          <textarea
            v-model="form.gatehouse_observation"
            rows="3"
            class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            placeholder="Observações adicionais sobre a solicitação..."
          ></textarea>
        </div>

        <!-- Botões -->
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors shadow-sm"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-50 shadow-sm flex items-center"
            :disabled="loading"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-2.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Criando...
            </span>
            <span v-else class="flex items-center justify-center">
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
import { computed, onMounted, ref, watch } from 'vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';

const props = defineProps({
  show: { type: Boolean, required: true }
});

const emit = defineEmits(['close', 'solicitacao-criada']);
const { successToast, errorToast } = useToast();

const loading = ref(false);
const loadingApartments = ref(false);
const hasVehicle = ref(false);
const apartments = ref([]);

const form = ref({
  visitor_name: '',
  visitor_document: '',
  visitor_phone: '',
  visit_date: '',
  apartment_id: '',
  vehicle_plate: '',
  vehicle_model: '',
  vehicle_color: '',
  gatehouse_observation: ''
});

const selectedApartment = computed(() => {
  return apartments.value.find((apartment) => String(apartment.id) === String(form.value.apartment_id));
});

const selectedApartmentResident = computed(() => {
  return selectedApartment.value?.resident?.name
    || selectedApartment.value?.owner?.name
    || selectedApartment.value?.tenant?.name
    || '';
});

const formatApartment = (apartment) => {
  const structure = [apartment.tower?.name, apartment.block?.name].filter(Boolean).join(' / ');
  const resident = apartment.resident?.name || apartment.owner?.name || apartment.tenant?.name;
  return [
    structure ? `${structure} - Unidade ${apartment.number}` : `Unidade ${apartment.number}`,
    resident ? `(${resident})` : null,
  ].filter(Boolean).join(' ');
};

const fetchApartments = async () => {
  loadingApartments.value = true;

  try {
    const response = await api.get('/apartments');
    apartments.value = response.data?.data || response.data || [];
  } catch (error) {
    console.error('Erro ao carregar unidades:', error);
    errorToast('Não foi possível carregar a lista de unidades.');
  } finally {
    loadingApartments.value = false;
  }
};

// Resetar formulário quando modal abrir
watch(() => props.show, (newShow) => {
  if (newShow) {
    form.value = {
      visitor_name: '',
      visitor_document: '',
      visitor_phone: '',
      visit_date: new Date().toISOString().slice(0, 16),
      apartment_id: '',
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
      apartment_id: form.value.apartment_id,
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

onMounted(fetchApartments);
</script>
