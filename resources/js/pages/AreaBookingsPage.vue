<template>
  <AuthenticatedLayout :page-title="`Reservas - ${area?.name || 'Área'}`">
    <div class="space-y-6">
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

      <!-- Conteúdo -->
      <div v-else-if="area" class="space-y-6">
        <!-- Cabeçalho -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <button
                @click="goBack"
                class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white transition-colors"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <h1 class="text-2xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-pink-500">
                Reservas - {{ area.name }}
              </h1>
            </div>
            <p v-if="area.description" class="text-gray-400">{{ area.description }}</p>
          </div>
          
          <!-- Ações -->
          <div class="flex items-center gap-2">
            <NeonButton 
              @click="createNewBooking"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nova Reserva
            </NeonButton>
          </div>
        </div>

        <!-- Informações da Área -->
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Capacidade</label>
              <p class="text-white text-xl font-semibold">{{ area.capacity || 'N/A' }} pessoas</p>
            </div>
            
            <div v-if="area.price_per_hour">
              <label class="block text-sm font-medium text-gray-400 mb-1">Preço por Hora</label>
              <p class="text-green-400 text-xl font-semibold">R$ {{ formatPrice(area.price_per_hour) }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Requer Aprovação</label>
              <p class="text-white text-xl font-semibold">{{ area.requires_approval ? 'Sim' : 'Não' }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Status</label>
              <p class="text-xl font-semibold" :class="area.active ? 'text-green-400' : 'text-red-400'">
                {{ area.active ? 'Ativa' : 'Inativa' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Estatísticas das Reservas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatsCard
            title="Total de Reservas"
            :value="totalBookings"
            icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
            color="bg-blue-500"
          />
          <StatsCard
            title="Reservas Ativas"
            :value="activeBookings"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            color="bg-green-500"
          />
          <StatsCard
            title="Pendentes"
            :value="pendingBookings"
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            color="bg-yellow-500"
          />
          <StatsCard
            title="Este Mês"
            :value="monthlyBookings"
            icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
            color="bg-purple-500"
          />
        </div>

        <!-- Filtros -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
            <select
              v-model="statusFilter"
              class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            >
              <option value="">Todos os status</option>
              <option value="pending">Pendente</option>
              <option value="approved">Aprovada</option>
              <option value="rejected">Rejeitada</option>
              <option value="cancelled">Cancelada</option>
              <option value="completed">Concluída</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Período</label>
            <select
              v-model="periodFilter"
              class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            >
              <option value="">Todos os períodos</option>
              <option value="today">Hoje</option>
              <option value="week">Esta semana</option>
              <option value="month">Este mês</option>
              <option value="upcoming">Próximas</option>
              <option value="past">Passadas</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Buscar por apartamento</label>
            <input
              v-model="apartmentSearch"
              type="text"
              placeholder="Ex: Apto 101, Bloco A..."
              class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            />
          </div>
        </div>

        <!-- Lista de Reservas -->
        <div v-if="filteredBookings.length === 0" class="text-center py-12">
          <div class="max-w-md mx-auto">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h3 class="text-xl font-medium text-gray-300 mb-2">
              {{ statusFilter || periodFilter || apartmentSearch ? 'Nenhuma reserva encontrada' : 'Nenhuma reserva para esta área' }}
            </h3>
            <p class="text-gray-500">
              {{ statusFilter || periodFilter || apartmentSearch 
                ? 'Tente ajustar os filtros de busca' 
                : 'Esta área ainda não possui reservas cadastradas' 
              }}
            </p>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 gap-6">
          <div 
            v-for="booking in filteredBookings" 
            :key="booking.id"
            class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-6 border border-gray-700/50 hover:border-gray-600/50 transition-all duration-200"
          >
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
              <!-- Informações principais -->
              <div class="flex-1">
                <div class="flex items-start gap-4">
                  <!-- Status Badge -->
                  <span 
                    class="px-3 py-1 text-xs font-semibold rounded-full shrink-0"
                    :class="getStatusBadgeClass(booking.status)"
                  >
                    {{ getStatusText(booking.status) }}
                  </span>
                  
                  <!-- Detalhes da reserva -->
                  <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-300 mb-2">
                      <!-- Apartamento -->
                      <div class="flex items-center gap-1">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="font-medium text-white">{{ formatApartment(booking.apartment) }}</span>
                      </div>
                      
                      <!-- Data e hora -->
                      <div class="flex items-center gap-1">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ formatBookingDate(booking) }}</span>
                      </div>
                      
                      <!-- Preço se houver -->
                      <div v-if="booking.total_price" class="flex items-center gap-1">
                        <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        <span class="text-green-400 font-medium">R$ {{ formatPrice(booking.total_price) }}</span>
                      </div>
                    </div>
                    
                    <!-- Observações se houver -->
                    <p v-if="booking.notes" class="text-sm text-gray-400 line-clamp-2">
                      {{ booking.notes }}
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Ações -->
              <div class="flex items-center gap-2 shrink-0">
                <NeonButton 
                  @click="viewBooking(booking)"
                  variant="secondary"
                  size="sm"
                >
                  <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </NeonButton>
                
                <NeonButton 
                  v-if="canEdit(booking)"
                  @click="editBooking(booking)"
                  variant="secondary"
                  size="sm"
                >
                  <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Editar
                </NeonButton>
                
                <button 
                  v-if="canCancel(booking)"
                  @click="cancelBooking(booking)"
                  class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-lg border border-red-500/30 hover:border-red-500/50 transition-colors text-sm"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import StatsCard from '../components/ui/StatsCard.vue';
import NeonButton from '../components/NeonButton.vue';
import { apiRequest } from '../utils/api';

export default {
  name: 'AreaBookingsPage',
  components: {
    AuthenticatedLayout,
    StatsCard,
    NeonButton
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const area = ref(null);
    const bookings = ref([]);
    const loading = ref(true);
    const error = ref('');
    
    // Filtros
    const statusFilter = ref('');
    const periodFilter = ref('');
    const apartmentSearch = ref('');

    // Computed properties
    const filteredBookings = computed(() => {
      return bookings.value.filter(booking => {
        const matchesStatus = statusFilter.value === '' || booking.status === statusFilter.value;
        const matchesPeriod = periodFilter.value === '' || matchesPeriodFilter(booking, periodFilter.value);
        const matchesApartment = apartmentSearch.value === '' || 
          formatApartment(booking.apartment).toLowerCase().includes(apartmentSearch.value.toLowerCase());
        
        return matchesStatus && matchesPeriod && matchesApartment;
      });
    });

    const totalBookings = computed(() => bookings.value.length);
    const activeBookings = computed(() => bookings.value.filter(b => ['approved', 'pending'].includes(b.status)).length);
    const pendingBookings = computed(() => bookings.value.filter(b => b.status === 'pending').length);
    const monthlyBookings = computed(() => {
      const now = new Date();
      return bookings.value.filter(b => {
        const bookingDate = new Date(b.start_time);
        return bookingDate.getMonth() === now.getMonth() && bookingDate.getFullYear() === now.getFullYear();
      }).length;
    });

    // Funções auxiliares
    const matchesPeriodFilter = (booking, period) => {
      const now = new Date();
      const bookingDate = new Date(booking.start_time);
      
      switch (period) {
        case 'today':
          return bookingDate.toDateString() === now.toDateString();
        case 'week':
          const weekStart = new Date(now);
          weekStart.setDate(now.getDate() - now.getDay());
          const weekEnd = new Date(weekStart);
          weekEnd.setDate(weekStart.getDate() + 6);
          return bookingDate >= weekStart && bookingDate <= weekEnd;
        case 'month':
          return bookingDate.getMonth() === now.getMonth() && bookingDate.getFullYear() === now.getFullYear();
        case 'upcoming':
          return bookingDate > now;
        case 'past':
          return bookingDate < now;
        default:
          return true;
      }
    };

    const formatPrice = (price) => {
      return parseFloat(price).toFixed(2).replace('.', ',');
    };

    const formatApartment = (apartment) => {
      if (!apartment) return 'Apartamento não informado';
      const block = apartment.block ? `Bloco ${apartment.block.name} - ` : '';
      return `${block}Apto ${apartment.number}`;
    };

    const formatBookingDate = (booking) => {
      const start = new Date(booking.start_time);
      const end = new Date(booking.end_time);
      
      const dateStr = start.toLocaleDateString('pt-BR');
      const startTime = start.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
      const endTime = end.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
      
      return `${dateStr} de ${startTime} às ${endTime}`;
    };

    const getStatusText = (status) => {
      const statusMap = {
        'pending': 'Pendente',
        'approved': 'Aprovada',
        'rejected': 'Rejeitada',
        'cancelled': 'Cancelada',
        'completed': 'Concluída'
      };
      return statusMap[status] || status;
    };

    const getStatusBadgeClass = (status) => {
      const statusClasses = {
        'pending': 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30',
        'approved': 'bg-green-500/20 text-green-300 border border-green-500/30',
        'rejected': 'bg-red-500/20 text-red-300 border border-red-500/30',
        'cancelled': 'bg-gray-500/20 text-gray-300 border border-gray-500/30',
        'completed': 'bg-blue-500/20 text-blue-300 border border-blue-500/30'
      };
      return statusClasses[status] || 'bg-gray-500/20 text-gray-300 border border-gray-500/30';
    };

    const canEdit = (booking) => {
      return ['pending'].includes(booking.status) && new Date(booking.start_time) > new Date();
    };

    const canCancel = (booking) => {
      return ['pending', 'approved'].includes(booking.status) && new Date(booking.start_time) > new Date();
    };

    // Funções principais
    const loadArea = async () => {
      try {
        area.value = await apiRequest(`/common-areas/${route.params.id}`);
      } catch (e) {
        error.value = e.message || 'Erro ao carregar área comum';
      }
    };

    const loadBookings = async () => {
      try {
        const data = await apiRequest(`/common-areas/${route.params.id}/bookings`);
        bookings.value = Array.isArray(data) ? data : (data?.data || []);
      } catch (e) {
        error.value = e.message || 'Erro ao carregar reservas';
      } finally {
        loading.value = false;
      }
    };

    const goBack = () => {
      router.go(-1);
    };

    const createNewBooking = () => {
      router.push({ 
        name: 'bookings.new',
        query: { area_id: route.params.id }
      });
    };

    const viewBooking = (booking) => {
      router.push({ name: 'bookings.show', params: { id: booking.id } });
    };

    const editBooking = (booking) => {
      router.push({ name: 'bookings.edit', params: { id: booking.id } });
    };

    const cancelBooking = async (booking) => {
      if (!confirm('Tem certeza que deseja cancelar esta reserva?')) {
        return;
      }

      try {
        await apiRequest(`/common-areas/${booking.common_area_id}/bookings/${booking.id}/cancel`, { method: 'PATCH' });
        await loadBookings();
      } catch (e) {
        error.value = e.message || 'Erro ao cancelar reserva';
      }
    };

    onMounted(async () => {
      await Promise.all([loadArea(), loadBookings()]);
    });

    return {
      area,
      bookings,
      loading,
      error,
      statusFilter,
      periodFilter,
      apartmentSearch,
      filteredBookings,
      totalBookings,
      activeBookings,
      pendingBookings,
      monthlyBookings,
      formatPrice,
      formatApartment,
      formatBookingDate,
      getStatusText,
      getStatusBadgeClass,
      canEdit,
      canCancel,
      goBack,
      createNewBooking,
      viewBooking,
      editBooking,
      cancelBooking
    };
  }
};
</script>
