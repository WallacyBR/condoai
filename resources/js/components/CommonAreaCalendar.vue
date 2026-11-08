<template>
  <div class="space-y-4">
    <!-- Formulário de Reserva -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
      <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
        Nova Reserva
      </h3>
      <CommonAreaBookingForm :area="area" @submit="onBookingSubmitted" />
    </div>

    <!-- Lista de Reservas -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
      <h3 class="text-lg font-semibold text-indigo-300 mb-4 border-b border-indigo-500/30 pb-2">
        Reservas Existentes
      </h3>
      
      <div v-if="loading" class="flex justify-center items-center py-8">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
          <span class="text-gray-400">Carregando reservas...</span>
        </div>
      </div>

      <div v-else-if="bookings.length === 0" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="mt-2 text-gray-400">Nenhuma reserva encontrada</p>
      </div>

      <div v-else class="space-y-3">
        <div 
          v-for="booking in sortedBookings" 
          :key="booking.id"
          class="flex items-center justify-between p-4 bg-gray-800/30 border border-gray-700 rounded-lg hover:border-gray-600 transition-colors"
        >
          <div class="flex-1">
            <div class="flex items-center space-x-3 mb-2">
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-white">
                  {{ formatDate(booking.start_time) }}
                </span>
                <span class="text-gray-400">•</span>
                <span class="text-sm text-gray-300">
                  {{ formatTime(booking.start_time) }} - {{ formatTime(booking.end_time) }}
                </span>
              </div>
              
              <!-- Tipo de Reserva -->
              <span class="px-2 py-1 text-xs font-medium bg-indigo-500/20 text-indigo-300 rounded">
                {{ getBookingTypeName(booking.booking_type) }}
              </span>
              
              <!-- Status -->
              <span :class="getStatusClass(booking.status)" class="px-2 py-1 text-xs font-medium rounded">
                {{ getStatusName(booking.status) }}
              </span>
            </div>
            
            <div class="flex items-center space-x-4 text-sm text-gray-400">
              <span>Apto: {{ booking.apartment?.number || 'N/A' }}</span>
              <span v-if="booking.total_price" class="text-green-400">
                R$ {{ formatPrice(booking.total_price) }}
              </span>
              <span v-if="booking.notes" class="italic">
                "{{ booking.notes }}"
              </span>
            </div>
          </div>
          
          <!-- Ações -->
          <div class="flex items-center space-x-2">
            <button 
              v-if="booking.canBeCancelled"
              @click="cancelBooking(booking.id)"
              class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors"
              title="Cancelar reserva"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            
            <button 
              v-if="booking.canBeEdited"
              @click="editBooking(booking)"
              class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/10 rounded-lg transition-colors"
              title="Editar reserva"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import CommonAreaBookingForm from './CommonAreaBookingForm.vue';

export default {
  name: 'CommonAreaCalendar',
  props: { 
    area: { 
      type: Object, 
      required: true 
    } 
  },
  components: { CommonAreaBookingForm },
  setup(props) {
    const token = localStorage.getItem('token');
    const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

    const bookings = ref([]);
    const loading = ref(false);

    // Ordenar reservas por data
    const sortedBookings = computed(() => {
      return [...bookings.value].sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
    });

    const loadBookings = async () => {
      try {
        loading.value = true;
        const resp = await fetch(`/api/common-areas/${props.area.id}/bookings`, { headers });
        
        if (resp.ok) {
          const data = await resp.json();
          // Adicionar métodos auxiliares aos objetos de reserva
          bookings.value = data.map(booking => ({
            ...booking,
            canBeCancelled: canBeCancelled(booking),
            canBeEdited: canBeEdited(booking)
          }));
        } else {
          console.error('Erro ao carregar reservas');
        }
      } catch (error) {
        console.error('Erro ao carregar reservas:', error);
      } finally {
        loading.value = false;
      }
    };

    const onBookingSubmitted = (newBooking) => {
      // Adicionar métodos auxiliares ao novo booking
      const bookingWithHelpers = {
        ...newBooking,
        canBeCancelled: canBeCancelled(newBooking),
        canBeEdited: canBeEdited(newBooking)
      };
      
      bookings.value.push(bookingWithHelpers);
    };

    const cancelBooking = async (bookingId) => {
      if (!confirm('Tem certeza que deseja cancelar esta reserva?')) return;

      try {
        const response = await fetch(`/api/common-areas/${props.area.id}/bookings/${bookingId}/cancel`, {
          method: 'PATCH',
          headers
        });

        if (response.ok) {
          // Atualizar status da reserva localmente
          const booking = bookings.value.find(b => b.id === bookingId);
          if (booking) {
            booking.status = 'cancelled';
            booking.canBeCancelled = false;
            booking.canBeEdited = false;
          }
        } else {
          alert('Erro ao cancelar reserva');
        }
      } catch (error) {
        console.error('Erro ao cancelar reserva:', error);
        alert('Erro de conexão');
      }
    };

    const editBooking = (booking) => {
      // Implementar edição de reserva se necessário
      console.log('Editar reserva:', booking);
    };

    // Métodos auxiliares
    const canBeCancelled = (booking) => {
      return ['pending', 'confirmed'].includes(booking.status) && 
             new Date(booking.start_time) > new Date();
    };

    const canBeEdited = (booking) => {
      return ['pending', 'confirmed'].includes(booking.status) && 
             new Date(booking.start_time) > new Date();
    };

    const getBookingTypeName = (type) => {
      const types = {
        'custom': 'Personalizada',
        'morning': 'Manhã',
        'afternoon': 'Tarde',
        'evening': 'Noite',
        'full_day': 'Dia Todo'
      };
      return types[type] || 'Desconhecido';
    };

    const getStatusName = (status) => {
      const statuses = {
        'pending': 'Pendente',
        'confirmed': 'Confirmada',
        'cancelled': 'Cancelada',
        'completed': 'Concluída'
      };
      return statuses[status] || 'Desconhecido';
    };

    const getStatusClass = (status) => {
      const classes = {
        'pending': 'bg-yellow-500/20 text-yellow-300',
        'confirmed': 'bg-green-500/20 text-green-300',
        'cancelled': 'bg-red-500/20 text-red-300',
        'completed': 'bg-blue-500/20 text-blue-300'
      };
      return classes[status] || 'bg-gray-500/20 text-gray-300';
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('pt-BR');
    };

    const formatTime = (dateString) => {
      return new Date(dateString).toLocaleTimeString('pt-BR', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
    };

    const formatPrice = (price) => {
      return price ? price.toFixed(2).replace('.', ',') : '0,00';
    };

    onMounted(loadBookings);

    return { 
      bookings, 
      loading,
      sortedBookings,
      onBookingSubmitted,
      cancelBooking,
      editBooking,
      getBookingTypeName,
      getStatusName,
      getStatusClass,
      formatDate,
      formatTime,
      formatPrice
    };
  }
};
</script>
