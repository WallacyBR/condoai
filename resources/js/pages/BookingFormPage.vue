<template>
  <AuthenticatedLayout :page-title="isEditing ? 'Editar Reserva' : 'Nova Reserva'">
    <div class="space-y-6">
      <!-- Cabeçalho -->
      <div class="flex items-center gap-3">
        <button
          @click="goBack"
          class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white transition-colors"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <h1 class="text-2xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-pink-500">
          {{ isEditing ? 'Editar Reserva' : 'Nova Reserva' }}
        </h1>
      </div>

      <!-- Formulário -->
      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Formulário Principal -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Seleção da Área Comum -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
              <h2 class="text-xl font-semibold text-white mb-4">Área Comum</h2>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    Selecione a área comum *
                  </label>
                  <select
                    v-model="form.common_area_id"
                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    @change="onAreaChange"
                    required
                  >
                    <option value="">Selecione uma área</option>
                    <option v-for="area in areas" :key="area.id" :value="area.id">
                      {{ area.name }}
                    </option>
                  </select>
                </div>
                
                <!-- Informações da Área Selecionada -->
                <div v-if="selectedArea" class="bg-gray-800/50 rounded-lg p-4 border border-gray-700/50">
                  <h3 class="font-medium text-white mb-2">{{ selectedArea.name }}</h3>
                  <div class="grid grid-cols-2 gap-4 text-sm">
                    <div v-if="selectedArea.capacity">
                      <span class="text-gray-400">Capacidade:</span>
                      <span class="text-white ml-2">{{ selectedArea.capacity }} pessoas</span>
                    </div>
                    <div v-if="selectedArea.price_per_hour">
                      <span class="text-gray-400">Preço por hora:</span>
                      <span class="text-green-400 ml-2 font-medium">R$ {{ formatPrice(selectedArea.price_per_hour) }}</span>
                    </div>
                    <div>
                      <span class="text-gray-400">Requer aprovação:</span>
                      <span class="text-white ml-2">{{ selectedArea.requires_approval ? 'Sim' : 'Não' }}</span>
                    </div>
                  </div>
                  <p v-if="selectedArea.description" class="text-gray-300 text-sm mt-2">
                    {{ selectedArea.description }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Seleção do Apartamento -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
              <h2 class="text-xl font-semibold text-white mb-4">Apartamento</h2>
              
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Selecione o apartamento *
                </label>
                <select
                  v-model="form.apartment_id"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  required
                >
                  <option value="">Selecione um apartamento</option>
                  <option v-for="apartment in apartments" :key="apartment.id" :value="apartment.id">
                    {{ formatApartment(apartment) }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Data e Horário -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
              <h2 class="text-xl font-semibold text-white mb-4">Data e Horário</h2>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    Data da reserva *
                  </label>
                  <input
                    v-model="form.date"
                    type="date"
                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    :min="today"
                    required
                    @change="onDateChange"
                  />
                </div>
                
                <!-- Tipo de Reserva -->
                <div v-if="selectedArea && bookingTypeForSelectedDate">
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    Tipo de Reserva
                  </label>
                  <div class="bg-gray-800/50 rounded-lg p-3">
                    <p class="text-white">{{ getBookingTypeText(bookingTypeForSelectedDate) }}</p>
                    <p class="text-gray-400 text-sm mt-1">{{ getBookingTypeDescription(bookingTypeForSelectedDate) }}</p>
                  </div>
                </div>
                
                <!-- Períodos Disponíveis (se for reserva por período) -->
                <div v-if="bookingTypeForSelectedDate === 'period' && availablePeriods.length > 0">
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    Período *
                  </label>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div 
                      v-for="period in availablePeriods" 
                      :key="period.period_type"
                      class="relative"
                    >
                      <input
                        :id="`period-${period.period_type}`"
                        v-model="form.booking_type"
                        :value="period.period_type"
                        type="radio"
                        name="period"
                        class="peer sr-only"
                        required
                      />
                      <label
                        :for="`period-${period.period_type}`"
                        class="flex items-center justify-between w-full p-4 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer hover:bg-gray-700 peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 transition-colors"
                      >
                        <div>
                          <div class="text-white font-medium">{{ period.name }}</div>
                          <div class="text-gray-400 text-sm">{{ period.formatted_start }} - {{ period.formatted_end }}</div>
                        </div>
                        <div v-if="period.price" class="text-green-400 font-medium">
                          R$ {{ formatPrice(period.price) }}
                        </div>
                      </label>
                    </div>
                  </div>
                </div>
                
                <!-- Horários Personalizados (se for reserva por hora) -->
                <div v-if="bookingTypeForSelectedDate === 'hourly'" class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                      Horário de início *
                    </label>
                    <input
                      v-model="form.start_time"
                      type="time"
                      class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                      Horário de término *
                    </label>
                    <input
                      v-model="form.end_time"
                      type="time"
                      class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                      required
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Observações -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
              <h2 class="text-xl font-semibold text-white mb-4">Observações</h2>
              
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Observações adicionais
                </label>
                <textarea
                  v-model="form.notes"
                  rows="4"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none"
                  placeholder="Descreva detalhes sobre a reserva, número de pessoas, etc."
                ></textarea>
              </div>
            </div>
          </div>
          
          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Resumo da Reserva -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
              <h3 class="text-lg font-semibold text-white mb-4">Resumo</h3>
              
              <div class="space-y-3">
                <div v-if="selectedArea">
                  <label class="block text-sm font-medium text-gray-400">Área</label>
                  <p class="text-white">{{ selectedArea.name }}</p>
                </div>
                
                <div v-if="form.date">
                  <label class="block text-sm font-medium text-gray-400">Data</label>
                  <p class="text-white">{{ formatDate(form.date) }}</p>
                </div>
                
                <div v-if="getSelectedTimeRange()">
                  <label class="block text-sm font-medium text-gray-400">Horário</label>
                  <p class="text-white">{{ getSelectedTimeRange() }}</p>
                </div>
                
                <div v-if="calculatedPrice">
                  <label class="block text-sm font-medium text-gray-400">Valor Total</label>
                  <p class="text-green-400 font-semibold text-lg">R$ {{ formatPrice(calculatedPrice) }}</p>
                </div>
              </div>
            </div>

            <!-- Regras da Área -->
            <div v-if="selectedArea?.rules" class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
              <h3 class="text-lg font-semibold text-white mb-4">Regras</h3>
              <div class="text-gray-300 text-sm whitespace-pre-wrap">{{ selectedArea.rules }}</div>
            </div>

            <!-- Botões de Ação -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
              <div class="space-y-3">
                <NeonButton
                  type="submit"
                  :disabled="!canSubmit || submitting"
                  class="w-full"
                >
                  <svg v-if="submitting" class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  {{ submitting ? 'Salvando...' : (isEditing ? 'Atualizar Reserva' : 'Criar Reserva') }}
                </NeonButton>
                
                <button
                  type="button"
                  @click="goBack"
                  class="w-full px-4 py-2 bg-gray-500/20 hover:bg-gray-500/30 text-gray-300 rounded-lg border border-gray-500/30 hover:border-gray-500/50 transition-colors"
                >
                  Cancelar
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import { apiRequest } from '../utils/api';

export default {
  name: 'BookingFormPage',
  components: {
    AuthenticatedLayout,
    NeonButton
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const areas = ref([]);
    const apartments = ref([]);
    const availablePeriods = ref([]);
    const loading = ref(true);
    const submitting = ref(false);
    const error = ref('');

    const form = ref({
      common_area_id: '',
      apartment_id: '',
      date: '',
      start_time: '',
      end_time: '',
      booking_type: '',
      notes: ''
    });

    const isEditing = computed(() => route.params.id !== undefined);
    const today = computed(() => new Date().toISOString().split('T')[0]);

    const selectedArea = computed(() => {
      return areas.value.find(area => area.id == form.value.common_area_id);
    });

    const bookingTypeForSelectedDate = ref(null);

    const calculatedPrice = computed(() => {
      if (!selectedArea.value || !form.value.date) return 0;
      
      const selectedPeriod = availablePeriods.value.find(p => p.period_type === form.value.booking_type);
      if (selectedPeriod) {
        return selectedPeriod.price || 0;
      }
      
      // Calcular preço por hora se necessário
      if (bookingTypeForSelectedDate.value === 'hourly' && form.value.start_time && form.value.end_time && selectedArea.value.price_per_hour) {
        const startTime = new Date(`2000-01-01T${form.value.start_time}`);
        const endTime = new Date(`2000-01-01T${form.value.end_time}`);
        const hours = (endTime - startTime) / (1000 * 60 * 60);
        return hours * selectedArea.value.price_per_hour;
      }
      
      return 0;
    });

    const canSubmit = computed(() => {
      return form.value.common_area_id && 
             form.value.apartment_id && 
             form.value.date && 
             (bookingTypeForSelectedDate.value === 'period' ? form.value.booking_type : (form.value.start_time && form.value.end_time));
    });

    // Funções auxiliares
    const formatPrice = (price) => {
      return parseFloat(price).toFixed(2).replace('.', ',');
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('pt-BR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    const formatApartment = (apartment) => {
      const block = apartment.block ? `Bloco ${apartment.block.name} - ` : '';
      return `${block}Apto ${apartment.number}`;
    };

    const getBookingTypeText = (type) => {
      return type === 'period' ? 'Reserva por Período' : 'Reserva por Hora';
    };

    const getBookingTypeDescription = (type) => {
      return type === 'period' 
        ? 'Escolha um dos períodos disponíveis para este dia'
        : 'Defina o horário de início e fim da reserva';
    };

    const getSelectedTimeRange = () => {
      if (bookingTypeForSelectedDate.value === 'period' && form.value.booking_type) {
        const selectedPeriod = availablePeriods.value.find(p => p.period_type === form.value.booking_type);
        if (selectedPeriod) {
          return `${selectedPeriod.formatted_start} - ${selectedPeriod.formatted_end}`;
        }
      } else if (bookingTypeForSelectedDate.value === 'hourly' && form.value.start_time && form.value.end_time) {
        return `${form.value.start_time} - ${form.value.end_time}`;
      }
      return '';
    };

    // Funções principais
    const loadAreas = async () => {
      try {
        const data = await apiRequest('/common-areas');
        areas.value = Array.isArray(data) ? data : (data?.data || []);
      } catch (e) {
        error.value = e.message || 'Erro ao carregar áreas';
      }
    };

    const loadApartments = async () => {
      try {
        const data = await apiRequest('/apartments');
        apartments.value = Array.isArray(data) ? data : (data?.data || []);
      } catch (e) {
        error.value = e.message || 'Erro ao carregar unidades';
      }
    };

    const loadBooking = async () => {
      if (!isEditing.value) return;

      try {
        const booking = await apiRequest(`/common-area-bookings/${route.params.id}`);
        const startTime = new Date(booking.start_time);
        const endTime = new Date(booking.end_time);

        form.value = {
          common_area_id: booking.common_area_id,
          apartment_id: booking.apartment_id,
          date: startTime.toISOString().split('T')[0],
          start_time: startTime.toTimeString().slice(0, 5),
          end_time: endTime.toTimeString().slice(0, 5),
          booking_type: booking.booking_type || '',
          notes: booking.notes || ''
        };
      } catch (e) {
        error.value = e.message || 'Erro ao carregar reserva';
      }
    };

    const onAreaChange = async () => {
      if (form.value.date) {
        await loadAvailableSlots();
      }
    };

    const onDateChange = async () => {
      if (form.value.common_area_id) {
        await loadAvailableSlots();
      }
    };

    const loadAvailableSlots = async () => {
      if (!form.value.common_area_id || !form.value.date) return;
      
      try {
        const data = await apiRequest(`/common-areas/${form.value.common_area_id}/available-slots?date=${form.value.date}`);
        bookingTypeForSelectedDate.value = data.booking_type;
        availablePeriods.value = data.available_slots || [];
      } catch (e) {
        error.value = e.message || 'Erro ao carregar horários disponíveis';
      }
    };

    const submitForm = async () => {
      if (!canSubmit.value) return;
      
      submitting.value = true;
      error.value = '';
      
      try {
        // Preparar dados do formulário
        const formData = {
          common_area_id: form.value.common_area_id,
          apartment_id: form.value.apartment_id,
          notes: form.value.notes || null
        };

        if (bookingTypeForSelectedDate.value === 'period') {
          // Para reservas por período, usar o período selecionado
          const selectedPeriod = availablePeriods.value.find(p => p.period_type === form.value.booking_type);
          if (selectedPeriod) {
            formData.start_time = selectedPeriod.start_time;
            formData.end_time = selectedPeriod.end_time;
            formData.booking_type = form.value.booking_type;
          }
        } else {
          // Para reservas por hora, usar horários personalizados
          formData.start_time = `${form.value.date}T${form.value.start_time}:00`;
          formData.end_time = `${form.value.date}T${form.value.end_time}:00`;
          formData.booking_type = 'hourly';
        }

        const url = isEditing.value 
          ? `/common-area-bookings/${route.params.id}`
          : `/common-area-bookings`;

        const method = isEditing.value ? 'PUT' : 'POST';

        const booking = await apiRequest(url, {
          method,
          body: JSON.stringify(formData)
        });

        router.push({ name: 'bookings.show', params: { id: booking.id } });
      } catch (e) {
        error.value = e.message || 'Erro ao salvar reserva';
      } finally {
        submitting.value = false;
      }
    };

    const goBack = () => {
      router.go(-1);
    };

    onMounted(async () => {
      await Promise.all([loadAreas(), loadApartments()]);
      if (isEditing.value) {
        await loadBooking();
      }
      loading.value = false;
    });

    return {
      form,
      areas,
      apartments,
      availablePeriods,
      loading,
      submitting,
      error,
      isEditing,
      today,
      selectedArea,
      bookingTypeForSelectedDate,
      calculatedPrice,
      canSubmit,
      formatPrice,
      formatDate,
      formatApartment,
      getBookingTypeText,
      getBookingTypeDescription,
      getSelectedTimeRange,
      onAreaChange,
      onDateChange,
      submitForm,
      goBack
    };
  }
};
</script>
