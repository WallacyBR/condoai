<template>
  <div class="space-y-6">
    <!-- Header com filtros -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h2 class="text-xl font-bold text-white">Reservas</h2>
        <p class="text-gray-400">{{ selectedCondominiumName }}</p>
      </div>
      
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
        <!-- Filtros -->
        <div class="flex gap-2">
          <select
            v-model="statusFilter"
            class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todos os Status</option>
            <option value="pending">Pendentes</option>
            <option value="confirmed">Confirmadas</option>
            <option value="cancelled">Canceladas</option>
            <option value="completed">Concluídas</option>
          </select>

          <select
            v-model="periodFilter"
            class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todos os Períodos</option>
            <option value="future">Futuras</option>
            <option value="today">Hoje</option>
            <option value="past">Passadas</option>
          </select>
          
          <div class="relative">
            <input
              type="text"
              v-model="search"
              placeholder="Buscar reservas..."
              class="w-48 pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div v-if="bookings.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Total de Reservas</p>
            <p class="text-2xl font-bold text-white">{{ bookings.length }}</p>
          </div>
          <div class="h-10 w-10 rounded-full bg-blue-500/20 flex items-center justify-center">
            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Confirmadas</p>
            <p class="text-2xl font-bold text-green-400">{{ confirmedBookings }}</p>
          </div>
          <div class="h-10 w-10 rounded-full bg-green-500/20 flex items-center justify-center">
            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Pendentes</p>
            <p class="text-2xl font-bold text-yellow-400">{{ pendingBookings }}</p>
          </div>
          <div class="h-10 w-10 rounded-full bg-yellow-500/20 flex items-center justify-center">
            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Este Mês</p>
            <p class="text-2xl font-bold text-purple-400">{{ monthlyBookings }}</p>
          </div>
          <div class="h-10 w-10 rounded-full bg-purple-500/20 flex items-center justify-center">
            <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="flex items-center space-x-3">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <span class="text-gray-400">Carregando reservas...</span>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
      <div class="flex items-center space-x-2">
        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ error }}</span>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredBookings.length === 0" class="text-center py-12">
      <div class="max-w-md mx-auto">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h3 class="text-xl font-medium text-gray-300 mb-2">
          {{ search ? 'Nenhuma reserva encontrada' : 'Nenhuma reserva cadastrada' }}
        </h3>
        <p class="text-gray-500">
          {{ search ? 'Tente ajustar sua busca' : 'Use o botão "Nova Reserva" no topo da página para começar' }}
        </p>
      </div>
    </div>

    <!-- Lista de Reservas -->
    <div v-else class="space-y-4">
      <div 
        v-for="booking in filteredBookings" 
        :key="booking.id"
        class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-xl shadow-xl border border-gray-700/50 hover:border-indigo-500/40 transition-all duration-300 overflow-hidden"
      >
        <div class="p-6">
          <!-- Header da reserva -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="h-12 w-12 rounded-full bg-indigo-500/20 flex items-center justify-center">
                <svg class="h-6 w-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">{{ booking.common_area?.name || 'Área não informada' }}</h3>
                <p class="text-gray-400 text-sm">Apt. {{ booking.apartment?.number || 'N/A' }}</p>
              </div>
            </div>

            <!-- Status da reserva -->
            <span :class="getStatusClass(booking.status)" class="px-3 py-1 text-xs font-medium rounded-full">
              {{ getStatusName(booking.status) }}
            </span>
          </div>

          <!-- Detalhes da reserva -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <!-- Data e horário -->
            <div class="flex items-center space-x-3">
              <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <div>
                <p class="text-white font-medium">{{ formatDate(booking.start_time) }}</p>
                <p class="text-gray-400 text-sm">{{ formatTime(booking.start_time) }} - {{ formatTime(booking.end_time) }}</p>
              </div>
            </div>

            <!-- Tipo de reserva -->
            <div class="flex items-center space-x-3">
              <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <p class="text-white font-medium">{{ getBookingTypeName(booking.booking_type) }}</p>
                <p class="text-gray-400 text-sm">{{ booking.duration_hours || 0 }}h de duração</p>
              </div>
            </div>

            <!-- Preço -->
            <div v-if="booking.total_price" class="flex items-center space-x-3">
              <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
              </svg>
              <div>
                <p class="text-white font-medium">R$ {{ formatPrice(booking.total_price) }}</p>
                <p class="text-gray-400 text-sm">{{ booking.payment_status || 'Pendente' }}</p>
              </div>
            </div>
          </div>

          <!-- Observações -->
          <div v-if="booking.notes" class="mb-4">
            <p class="text-gray-300 text-sm italic">
              "{{ booking.notes }}"
            </p>
          </div>

          <!-- Ações -->
          <div class="flex items-center justify-between pt-4 border-t border-gray-700">
            <div class="text-xs text-gray-500">
              Criada em {{ formatDate(booking.created_at) }}
            </div>
            
            <div class="flex space-x-2">
              <button 
                v-if="canCancelBooking(booking)"
                @click="cancelBooking(booking)"
                class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-lg border border-red-500/30 hover:border-red-500/50 transition-colors text-sm"
              >
                <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancelar
              </button>

              <NeonButton 
                @click="viewBookingDetails(booking)"
                variant="secondary"
                size="sm"
              >
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Ver Detalhes
              </NeonButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import NeonButton from './NeonButton.vue';

export default {
  name: 'BookingsSection',
  components: {
    NeonButton
  },
  setup() {
    const bookings = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const statusFilter = ref('all');
    const periodFilter = ref('all');

    // Estado do condomínio selecionado
    const selectedCondominiumId = ref(localStorage.getItem('selectedCondominium'));
    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName'));

    // Computed properties
    const filteredBookings = computed(() => {
      return bookings.value.filter(booking => {
        const matchesSearch = booking.common_area?.name?.toLowerCase().includes(search.value.toLowerCase()) ||
                            booking.apartment?.number?.toString().includes(search.value) ||
                            (booking.notes && booking.notes.toLowerCase().includes(search.value.toLowerCase()));
        
        const matchesStatus = statusFilter.value === 'all' || booking.status === statusFilter.value;
        
        let matchesPeriod = true;
        if (periodFilter.value !== 'all') {
          const bookingDate = new Date(booking.start_time);
          const today = new Date();
          const todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
          const bookingDateOnly = new Date(bookingDate.getFullYear(), bookingDate.getMonth(), bookingDate.getDate());
          
          if (periodFilter.value === 'future') {
            matchesPeriod = bookingDate > today;
          } else if (periodFilter.value === 'today') {
            matchesPeriod = bookingDateOnly.getTime() === todayDate.getTime();
          } else if (periodFilter.value === 'past') {
            matchesPeriod = bookingDate < today;
          }
        }
        
        return matchesSearch && matchesStatus && matchesPeriod;
      });
    });

    const confirmedBookings = computed(() => {
      return bookings.value.filter(booking => booking.status === 'confirmed').length;
    });

    const pendingBookings = computed(() => {
      return bookings.value.filter(booking => booking.status === 'pending').length;
    });

    const monthlyBookings = computed(() => {
      const currentMonth = new Date().getMonth();
      const currentYear = new Date().getFullYear();
      return bookings.value.filter(booking => {
        const bookingDate = new Date(booking.start_time);
        return bookingDate.getMonth() === currentMonth && bookingDate.getFullYear() === currentYear;
      }).length;
    });

    // Funções
    const loadBookings = async () => {
      try {
        loading.value = true;
        error.value = '';
        
        const token = localStorage.getItem('token');
        const condo = localStorage.getItem('selectedCondominium');
        
        if (!condo) {
          bookings.value = [];
          return;
        }

        const response = await fetch(`/api/common-area-bookings?condominium_id=${condo}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          bookings.value = await response.json();
        } else {
          error.value = 'Erro ao carregar reservas';
        }
      } catch (e) {
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    const canCancelBooking = (booking) => {
      return ['pending', 'confirmed'].includes(booking.status) && 
             new Date(booking.start_time) > new Date();
    };

    const cancelBooking = async (booking) => {
      if (!confirm('Tem certeza que deseja cancelar esta reserva?')) return;

      try {
        const token = localStorage.getItem('token');
        const response = await fetch(`/api/common-areas/${booking.common_area_id}/bookings/${booking.id}/cancel`, {
          method: 'PATCH',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          // Atualizar status da reserva localmente
          booking.status = 'cancelled';
        } else {
          alert('Erro ao cancelar reserva');
        }
      } catch (error) {
        console.error('Erro ao cancelar reserva:', error);
        alert('Erro de conexão');
      }
    };

    const viewBookingDetails = (booking) => {
      // Implementar visualização de detalhes se necessário
      console.log('Ver detalhes da reserva:', booking);
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
        'pending': 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30',
        'confirmed': 'bg-green-500/20 text-green-300 border border-green-500/30',
        'cancelled': 'bg-red-500/20 text-red-300 border border-red-500/30',
        'completed': 'bg-blue-500/20 text-blue-300 border border-blue-500/30'
      };
      return classes[status] || 'bg-gray-500/20 text-gray-300 border border-gray-500/30';
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

    // Carregar dados ao montar o componente
    onMounted(() => {
      loadBookings();
    });

    return {
      bookings,
      loading,
      error,
      search,
      statusFilter,
      periodFilter,
      selectedCondominiumName,
      filteredBookings,
      confirmedBookings,
      pendingBookings,
      monthlyBookings,
      canCancelBooking,
      cancelBooking,
      viewBookingDetails,
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