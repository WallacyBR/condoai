<template>
  <div class="space-y-4">
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

    <!-- Tipo de Reserva -->
    <div>
      <label class="block text-sm font-medium text-gray-300 mb-2">
        Tipo de Reserva
      </label>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <!-- Períodos Padrão -->
        <div v-if="availablePeriods.length > 0" class="space-y-2">
          <h4 class="text-sm font-medium text-indigo-300">Períodos Padrão</h4>
          <div class="space-y-2">
            <div 
              v-for="period in availablePeriods" 
              :key="period.type"
              class="flex items-center justify-between p-3 bg-gray-800/50 border border-gray-700 rounded-lg hover:border-indigo-500 transition-colors cursor-pointer"
              :class="{ 'border-indigo-500 bg-indigo-500/10': form.booking_type === period.type }"
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

        <!-- Reserva por Hora -->
        <div v-if="availableHourlySlots.length > 0" class="space-y-2">
          <h4 class="text-sm font-medium text-indigo-300">Reserva por Hora</h4>
          <div class="space-y-2">
            <div 
              v-for="slot in availableHourlySlots" 
              :key="`${slot.start_time}-${slot.end_time}`"
              class="flex items-center justify-between p-3 bg-gray-800/50 border border-gray-700 rounded-lg hover:border-indigo-500 transition-colors cursor-pointer"
              :class="{ 'border-indigo-500 bg-indigo-500/10': isSlotSelected(slot) }"
              @click="selectHourlySlot(slot)"
            >
              <div>
                <div class="font-medium text-white">{{ slot.formatted_start }} - {{ slot.formatted_end }}</div>
                <div class="text-sm text-gray-400">{{ slot.name }}</div>
              </div>
              <div class="text-right">
                <div v-if="slot.price" class="font-medium text-green-400">
                  R$ {{ formatPrice(slot.price) }}
                </div>
                <div v-else class="text-sm text-gray-500">Gratuito</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Reserva Customizada (apenas se permitido) -->
        <div v-if="area.allow_custom_periods && !availablePeriods.length && !availableHourlySlots.length" class="space-y-2">
          <h4 class="text-sm font-medium text-indigo-300">Reserva Personalizada</h4>
          <div class="space-y-3">
            <div>
              <label class="block text-sm text-gray-400 mb-1">Horário de Início</label>
              <input
                v-model="form.start_time"
                type="time"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm text-gray-400 mb-1">Horário de Fim</label>
              <input
                v-model="form.end_time"
                type="time"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div v-if="customPrice" class="text-sm text-green-400">
              Preço: R$ {{ formatPrice(customPrice) }}
            </div>
          </div>
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
        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none"
        placeholder="Informações adicionais sobre a reserva..."
      ></textarea>
    </div>

    <!-- Botão de Reserva -->
    <div class="flex justify-end">
      <button 
        @click="submitBooking"
        :disabled="!canSubmit"
        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
      >
        {{ loading ? 'Reservando...' : 'Fazer Reserva' }}
      </button>
    </div>

    <!-- Mensagem de Erro -->
    <div v-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-red-300">
      {{ error }}
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue';

export default {
  name: 'CommonAreaBookingForm',
  props: {
    area: {
      type: Object,
      required: true
    }
  },
  emits: ['submit'],
  setup(props, { emit }) {
    const loading = ref(false);
    const error = ref('');
    const availableSlots = ref([]);
    const availablePeriods = ref([]);
    const availableHourlySlots = ref([]);

    const form = reactive({
      date: '',
      start_time: '',
      end_time: '',
      booking_type: 'custom',
      notes: ''
    });

    // Data de hoje para validação
    const today = computed(() => {
      return new Date().toISOString().split('T')[0];
    });

    // Calcular preço para reserva customizada
    const customPrice = computed(() => {
      if (!form.start_time || !form.end_time || !props.area.price_per_hour) {
        return null;
      }

      const start = new Date(`2000-01-01 ${form.start_time}`);
      const end = new Date(`2000-01-01 ${form.end_time}`);
      const hours = (end - start) / (1000 * 60 * 60);
      
      return hours > 0 ? props.area.price_per_hour * hours : null;
    });

    // Verificar se pode submeter
    const canSubmit = computed(() => {
      if (!form.date) return false;
      
      if (form.booking_type === 'custom') {
        return form.start_time && form.end_time && form.start_time < form.end_time;
      }
      
      return true;
    });

    // Carregar slots disponíveis
    const loadAvailableSlots = async () => {
      if (!form.date) return;

      try {
        loading.value = true;
        error.value = '';

        const token = localStorage.getItem('token');
        const response = await fetch(`/api/common-areas/${props.area.id}/available-slots?date=${form.date}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          const data = await response.json();
          availableSlots.value = data.available_slots;
          
          // Separar períodos padrão dos slots customizados
          availablePeriods.value = data.available_slots.filter(slot => slot.type === 'period');
          availableHourlySlots.value = data.available_slots.filter(slot => slot.type === 'hourly');
        } else {
          error.value = 'Erro ao carregar horários disponíveis';
        }
      } catch (err) {
        console.error('Erro ao carregar slots:', err);
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    // Selecionar período padrão
    const selectPeriod = (period) => {
      form.booking_type = period.period_type;
      form.start_time = period.formatted_start;
      form.end_time = period.formatted_end;
    };

    // Selecionar slot por hora
    const selectHourlySlot = (slot) => {
      form.booking_type = 'hourly';
      form.start_time = slot.formatted_start;
      form.end_time = slot.formatted_end;
    };

    // Formatar preço
    const formatPrice = (price) => {
      return price ? price.toFixed(2).replace('.', ',') : '0,00';
    };

    // Verificar se um slot está selecionado
    const isSlotSelected = (slot) => {
      return form.start_time === slot.formatted_start && form.end_time === slot.formatted_end;
    };

    // Submeter reserva
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
          booking_type: form.booking_type,
          notes: form.notes
        };

        const token = localStorage.getItem('token');
        const response = await fetch(`/api/common-areas/${props.area.id}/bookings`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(bookingData)
        });

        const data = await response.json();

        if (response.ok) {
          emit('submit', data);
          // Limpar formulário
          Object.assign(form, {
            date: '',
            start_time: '',
            end_time: '',
            booking_type: 'custom',
            notes: ''
          });
          availablePeriods.value = [];
          availableHourlySlots.value = [];
        } else {
          error.value = data.message || 'Erro ao fazer reserva';
        }
      } catch (err) {
        console.error('Erro ao fazer reserva:', err);
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      error,
      availablePeriods,
      availableHourlySlots,
      today,
      customPrice,
      canSubmit,
      loadAvailableSlots,
      selectPeriod,
      selectHourlySlot,
      formatPrice,
      isSlotSelected,
      submitBooking
    };
  }
};
</script>
