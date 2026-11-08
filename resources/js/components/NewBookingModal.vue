<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-900 opacity-75" @click="$emit('close')"></div>
      </div>

      <div class="inline-block align-bottom bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-gray-900 px-6 pt-6 pb-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-white">
              Nova Reserva
            </h3>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-white transition-colors"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <!-- Seleção da Área Comum -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">
                Área Comum *
              </label>
              <select
                v-model="form.common_area_id"
                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                @change="onAreaChange"
              >
                <option value="">Selecione uma área</option>
                <option v-for="area in areas" :key="area.id" :value="area.id">
                  {{ area.name }}
                </option>
              </select>
            </div>

            <!-- Seleção de Data -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">
                Data da Reserva *
              </label>
              <input 
                v-model="form.date" 
                type="date" 
                :min="today"
                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                @change="loadAvailableSlots"
              />
            </div>

            <!-- Períodos Disponíveis -->
            <div v-if="availablePeriods.length > 0">
              <label class="block text-sm font-medium text-gray-300 mb-2">
                Períodos Disponíveis
              </label>
              <div class="space-y-2">
                <div 
                  v-for="period in availablePeriods" 
                  :key="period.type"
                  class="flex items-center justify-between p-3 bg-gray-800/50 border border-gray-700 rounded-lg hover:border-indigo-500 transition-colors cursor-pointer"
                  :class="{ 'border-indigo-500 bg-indigo-500/10': selectedPeriod?.type === period.type }"
                  @click="selectPeriod(period)"
                >
                  <div>
                    <div class="font-medium text-white">{{ period.name }}</div>
                    <div class="text-sm text-gray-400">{{ period.formatted_time }}</div>
                  </div>
                  <div class="text-right">
                    <div v-if="period.price" class="font-medium text-green-400">
                      R$ {{ formatPrice(period.price) }}
                    </div>
                    <div v-else class="text-sm text-gray-500">Gratuito</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reserva Customizada -->
            <div v-if="selectedArea && selectedArea.allow_custom_periods">
              <label class="block text-sm font-medium text-gray-300 mb-2">
                Horário Personalizado
              </label>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Início</label>
                  <input
                    v-model="form.start_time"
                    type="time"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  />
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Fim</label>
                  <input
                    v-model="form.end_time"
                    type="time"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  />
                </div>
              </div>
            </div>

            <!-- Observações -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">
                Observações
              </label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                placeholder="Observações sobre a reserva..."
              ></textarea>
            </div>

            <!-- Preço Total -->
            <div v-if="totalPrice" class="bg-green-500/10 border border-green-500/30 rounded-lg p-3">
              <div class="flex items-center justify-between">
                <span class="text-green-300 font-medium">Valor Total:</span>
                <span class="text-green-300 font-bold text-lg">R$ {{ formatPrice(totalPrice) }}</span>
              </div>
            </div>

            <!-- Mensagem de Erro -->
            <div v-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-red-300">
              {{ error }}
            </div>
          </div>
        </div>

        <div class="bg-gray-800 px-6 py-4 flex justify-end space-x-3">
          <button
            @click="$emit('close')"
            class="px-4 py-2 text-gray-300 hover:text-white transition-colors"
          >
            Cancelar
          </button>
          <NeonButton 
            @click="submitBooking"
            :disabled="!canSubmit || loading"
          >
            {{ loading ? 'Criando...' : 'Criar Reserva' }}
          </NeonButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue';
import NeonButton from './NeonButton.vue';

export default {
  name: 'NewBookingModal',
  props: {
    show: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'created'],
  components: {
    NeonButton
  },
  setup(props, { emit }) {
    const areas = ref([]);
    const selectedArea = ref(null);
    const availablePeriods = ref([]);
    const selectedPeriod = ref(null);
    const loading = ref(false);
    const error = ref('');

    const form = reactive({
      common_area_id: '',
      date: '',
      start_time: '',
      end_time: '',
      notes: ''
    });

    // Data de hoje para validação
    const today = computed(() => {
      return new Date().toISOString().split('T')[0];
    });

    // Preço total calculado
    const totalPrice = computed(() => {
      if (selectedPeriod.value?.price) {
        return selectedPeriod.value.price;
      }
      
      if (selectedArea.value?.price_per_hour && form.start_time && form.end_time) {
        const start = new Date(`2000-01-01 ${form.start_time}`);
        const end = new Date(`2000-01-01 ${form.end_time}`);
        const hours = (end - start) / (1000 * 60 * 60);
        return hours > 0 ? selectedArea.value.price_per_hour * hours : 0;
      }
      
      return 0;
    });

    // Verificar se pode submeter
    const canSubmit = computed(() => {
      if (!form.common_area_id || !form.date) return false;
      
      if (selectedPeriod.value) return true;
      
      return form.start_time && form.end_time && form.start_time < form.end_time;
    });

    // Carregar áreas quando o modal for aberto
    watch(() => props.show, (show) => {
      if (show) {
        loadAreas();
      } else {
        resetForm();
      }
    });

    const loadAreas = async () => {
      try {
        const token = localStorage.getItem('token');
        const condo = localStorage.getItem('selectedCondominium');
        
        if (!condo) return;

        const response = await fetch(`/api/common-areas?condominium_id=${condo}&active=1`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          areas.value = await response.json();
        }
      } catch (e) {
        console.error('Erro ao carregar áreas:', e);
      }
    };

    const onAreaChange = () => {
      selectedArea.value = areas.value.find(area => area.id == form.common_area_id);
      selectedPeriod.value = null;
      availablePeriods.value = [];
      form.start_time = '';
      form.end_time = '';
      
      if (form.date && selectedArea.value) {
        loadAvailableSlots();
      }
    };

    const loadAvailableSlots = async () => {
      if (!form.date || !selectedArea.value) return;

      try {
        const token = localStorage.getItem('token');
        const response = await fetch(`/api/common-areas/${selectedArea.value.id}/available-slots?date=${form.date}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          const data = await response.json();
          availablePeriods.value = data.available_slots.filter(slot => slot.type === 'period');
        }
      } catch (e) {
        console.error('Erro ao carregar slots:', e);
      }
    };

    const selectPeriod = (period) => {
      selectedPeriod.value = period;
      form.start_time = period.formatted_start;
      form.end_time = period.formatted_end;
    };

    const submitBooking = async () => {
      if (!canSubmit.value) return;

      try {
        loading.value = true;
        error.value = '';

        const apartment = localStorage.getItem('selectedApartment');
        if (!apartment) {
          error.value = 'Nenhum apartamento selecionado';
          return;
        }

        const bookingData = {
          apartment_id: parseInt(apartment),
          start_time: form.date + ' ' + form.start_time,
          end_time: form.date + ' ' + form.end_time,
          booking_type: selectedPeriod.value?.period_type || 'custom',
          notes: form.notes
        };

        const token = localStorage.getItem('token');
        const response = await fetch(`/api/common-areas/${form.common_area_id}/bookings`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(bookingData)
        });

        const data = await response.json();

        if (response.ok) {
          emit('created', data);
          resetForm();
        } else {
          error.value = data.message || 'Erro ao criar reserva';
        }
      } catch (err) {
        console.error('Erro ao criar reserva:', err);
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    const resetForm = () => {
      Object.assign(form, {
        common_area_id: '',
        date: '',
        start_time: '',
        end_time: '',
        notes: ''
      });
      selectedArea.value = null;
      selectedPeriod.value = null;
      availablePeriods.value = [];
      error.value = '';
    };

    const formatPrice = (price) => {
      return price ? price.toFixed(2).replace('.', ',') : '0,00';
    };

    return {
      form,
      areas,
      selectedArea,
      availablePeriods,
      selectedPeriod,
      loading,
      error,
      today,
      totalPrice,
      canSubmit,
      onAreaChange,
      loadAvailableSlots,
      selectPeriod,
      submitBooking,
      formatPrice
    };
  }
};
</script> 