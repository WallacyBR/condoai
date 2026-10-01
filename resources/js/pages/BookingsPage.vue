<template>
  <AuthenticatedLayout page-title="Reservas de Áreas Comuns">
    <div class="space-y-6">
      <!-- Top Card -->
      <TopCard 
        title="Reservas de Áreas Comuns"
        subtitle="Gerencie todas as reservas realizadas"
        :icon="BookingIcon"
      >
        <template #actions>
          <AppButton @click="createNewBooking" variant="primary" size="sm">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Reserva
          </AppButton>
        </template>
      </TopCard>

      <!-- Avisos quando não há condomínio selecionado -->
      <div v-if="!selectedCondominiumName" class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl p-6 text-center">
        <div class="flex items-center justify-center space-x-3">
          <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div class="text-left">
            <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-200">Selecione um condomínio</h3>
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">Para visualizar as reservas, primeiro selecione um condomínio no menu superior.</p>
          </div>
        </div>
      </div>

      <div v-else class="space-y-6">
        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <StatsCard
            label="Total de Reservas"
            :value="totalBookings"
            variant="primary"
            default-icon-path="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
          <StatsCard
            label="Reservas Ativas"
            :value="activeBookings"
            variant="green"
            default-icon-path="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
          <StatsCard
            label="Pendentes Aprovação"
            :value="pendingBookings"
            variant="orange"
            default-icon-path="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
          />
          <StatsCard
            label="Canceladas"
            :value="cancelledBookings"
            variant="default"
            default-icon-path="M6 18L18 6M6 6l12 12"
          />
        </div>

        <!-- Filtros -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Buscar</label>
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por área, unidade..."
              class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
            <select
              v-model="statusFilter"
              class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
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
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Área</label>
            <select
              v-model="areaFilter"
              class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            >
              <option value="">Todas as áreas</option>
              <option v-for="area in areas" :key="area.id" :value="area.id">
                {{ area.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Período</label>
            <select
              v-model="periodFilter"
              class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            >
              <option value="">Todos os períodos</option>
              <option value="today">Hoje</option>
              <option value="week">Esta semana</option>
              <option value="month">Este mês</option>
              <option value="upcoming">Próximas</option>
              <option value="past">Passadas</option>
            </select>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <span class="text-slate-500 dark:text-slate-400 text-sm">Carregando reservas...</span>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-4 text-rose-700 dark:text-rose-300 text-sm">
          <div class="flex items-center space-x-2">
            <svg class="h-5 w-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ error }}</span>
          </div>
        </div>

        <!-- Empty State -->
        <EmptyState
          v-else-if="filteredBookings.length === 0"
          :title="search || statusFilter || areaFilter || periodFilter ? 'Nenhuma reserva encontrada' : 'Nenhuma reserva cadastrada'"
          :description="search || statusFilter || areaFilter || periodFilter 
            ? 'Tente ajustar os filtros de busca.' 
            : 'Use o botão \'Nova Reserva\' para agendar o uso de uma área comum.'"
        >
          <template #actions v-if="!search && !statusFilter && !areaFilter && !periodFilter">
            <AppButton @click="createNewBooking" variant="primary">
              Nova Reserva
            </AppButton>
          </template>
        </EmptyState>

        <!-- Lista de Reservas -->
        <div v-else class="grid grid-cols-1 gap-4">
          <div 
            v-for="booking in filteredBookings" 
            :key="booking.id"
            class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition-colors"
          >
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
              <!-- Informações principais -->
              <div class="flex-1">
                <div class="flex items-start gap-3">
                  <!-- Status Badge -->
                  <span 
                    class="px-2.5 py-1 text-xs font-semibold rounded-full shrink-0"
                    :class="getStatusBadgeClass(booking.status)"
                  >
                    {{ getStatusText(booking.status) }}
                  </span>
                  
                  <!-- Detalhes da reserva -->
                  <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-1">
                      {{ booking.common_area?.name || 'Área não informada' }}
                    </h3>
                    
                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600 dark:text-slate-300">
                      <!-- Apartamento -->
                      <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>{{ formatApartment(booking.apartment) }}</span>
                      </div>
                      
                      <!-- Data e hora -->
                      <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ formatBookingDate(booking) }}</span>
                      </div>
                      
                      <!-- Preço se houver -->
                      <div v-if="booking.total_price" class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        <span class="text-emerald-700 dark:text-emerald-400 font-semibold">R$ {{ formatPrice(booking.total_price) }}</span>
                      </div>
                    </div>
                    
                    <!-- Observações se houver -->
                    <p v-if="booking.notes" class="text-xs text-slate-500 dark:text-slate-400 mt-2 line-clamp-2">
                      {{ booking.notes }}
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Ações -->
              <div class="flex items-center gap-2 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-800">
                <AppButton 
                  @click="viewBooking(booking)"
                  variant="secondary"
                  size="sm"
                >
                  <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </AppButton>
                
                <AppButton 
                  v-if="canEdit(booking)"
                  @click="editBooking(booking)"
                  variant="secondary"
                  size="sm"
                >
                  <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Editar
                </AppButton>
                
                <button 
                  v-if="canCancel(booking)"
                  @click="cancelBooking(booking)"
                  class="p-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg border border-rose-200 dark:border-rose-800/40 transition-colors"
                  title="Cancelar Reserva"
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
import { ref, computed, onMounted, h } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import TopCard from '../components/ui/TopCard.vue';
import StatsCard from '../components/ui/StatsCard.vue';
import AppButton from '../components/ui/AppButton.vue';
import EmptyState from '../components/ui/EmptyState.vue';
import NeonButton from '../components/NeonButton.vue';
import { apiRequest } from '../utils/api';

export default {
  name: 'BookingsPage',
  components: {
    AuthenticatedLayout,
    TopCard,
    StatsCard,
    AppButton,
    EmptyState,
    NeonButton
  },
  setup() {
    const router = useRouter();
    const bookings = ref([]);
    const areas = ref([]);
    const loading = ref(true);
    const error = ref('');
    
    // Filtros
    const search = ref('');
    const statusFilter = ref('');
    const areaFilter = ref('');
    const periodFilter = ref('');

    const BookingIcon = {
      render() {
        return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' })
        ]);
      }
    };

    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName') || 'Condomínio atual');

    // Computed properties
    const filteredBookings = computed(() => {
      return bookings.value.filter(booking => {
        const matchesSearch = search.value === '' || 
          booking.common_area?.name?.toLowerCase().includes(search.value.toLowerCase()) ||
          formatApartment(booking.apartment).toLowerCase().includes(search.value.toLowerCase());
        
        const matchesStatus = statusFilter.value === '' || booking.status === statusFilter.value;
        const matchesArea = areaFilter.value === '' || booking.common_area_id == areaFilter.value;
        const matchesPeriod = periodFilter.value === '' || matchesPeriodFilter(booking, periodFilter.value);
        
        return matchesSearch && matchesStatus && matchesArea && matchesPeriod;
      });
    });

    const totalBookings = computed(() => bookings.value.length);
    const activeBookings = computed(() => bookings.value.filter(b => ['approved', 'pending'].includes(b.status)).length);
    const pendingBookings = computed(() => bookings.value.filter(b => b.status === 'pending').length);
    const cancelledBookings = computed(() => bookings.value.filter(b => b.status === 'cancelled').length);

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

    const formatPrice = (price) => {
      return parseFloat(price).toFixed(2).replace('.', ',');
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
        'pending': 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50',
        'approved': 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50',
        'rejected': 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50',
        'cancelled': 'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        'completed': 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/50'
      };
      return statusClasses[status] || 'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
    };

    const canEdit = (booking) => {
      return ['pending'].includes(booking.status) && new Date(booking.start_time) > new Date();
    };

    const canCancel = (booking) => {
      return ['pending', 'approved'].includes(booking.status) && new Date(booking.start_time) > new Date();
    };

    // Funções principais
    const loadBookings = async () => {
      try {
        loading.value = true;
        error.value = '';
        
        const data = await apiRequest('/common-area-bookings');
        bookings.value = Array.isArray(data) ? data : (data?.data || []);
      } catch (e) {
        error.value = e.message || 'Erro ao carregar reservas';
      } finally {
        loading.value = false;
      }
    };

    const loadAreas = async () => {
      try {
        const data = await apiRequest('/common-areas');
        areas.value = Array.isArray(data) ? data : (data?.data || []);
      } catch (e) {
        error.value = e.message || 'Erro ao carregar áreas';
      }
    };

    const createNewBooking = () => {
      router.push({ name: 'bookings.new' });
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

    onMounted(() => {
      loadBookings();
      loadAreas();
    });

    return {
      BookingIcon,
      bookings,
      areas,
      loading,
      error,
      search,
      statusFilter,
      areaFilter,
      periodFilter,
      filteredBookings,
      totalBookings,
      activeBookings,
      pendingBookings,
      cancelledBookings,
      selectedCondominiumName,
      matchesPeriodFilter,
      formatApartment,
      formatBookingDate,
      formatPrice,
      getStatusText,
      getStatusBadgeClass,
      canEdit,
      canCancel,
      createNewBooking,
      viewBooking,
      editBooking,
      cancelBooking
    };
  }
};
</script>
