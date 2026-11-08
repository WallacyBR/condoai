<template>
  <AuthenticatedLayout page-title="Detalhes da Reserva">
    <div class="space-y-6">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="flex flex-col items-center space-y-3">
          <div class="relative">
            <div class="animate-spin rounded-full h-8 w-8 border-4 border-gray-700"></div>
            <div class="animate-spin rounded-full h-8 w-8 border-4 border-t-cyan-500 absolute top-0 left-0"></div>
          </div>
          <span class="text-gray-400 text-sm">Carregando detalhes da reserva...</span>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-gradient-to-r from-red-900/20 via-red-800/20 to-red-900/20 backdrop-blur-xl rounded-xl p-4 border border-red-500/30">
        <div class="flex items-center space-x-3">
          <div class="h-8 w-8 rounded-lg bg-red-500/20 flex items-center justify-center border border-red-500/30">
            <svg class="h-4 w-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-base font-semibold text-red-300">Erro ao carregar reserva</h3>
            <p class="text-red-400/80 text-sm">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Conteúdo da Reserva -->
      <div v-else-if="booking" class="space-y-6">
        <!-- Top Card com Informações Principais -->
        <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-start space-x-3">
              <!-- Botão Voltar -->
              <NeonButton
                @click="goBack"
                variant="secondary"
                size="sm"
                class="!px-2 !py-2"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </NeonButton>
              
              <!-- Informações da Reserva -->
              <div>
                <div class="flex items-center gap-3 mb-2">
                  <h1 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-blue-400 to-purple-500">
                    Reserva #{{ booking.id }}
                  </h1>
                  <span 
                    class="px-3 py-1 text-xs font-bold rounded-full"
                    :class="getStatusBadgeClass(booking.status)"
                  >
                    {{ getStatusText(booking.status) }}
                  </span>
                </div>
                <div class="flex items-center space-x-2 text-gray-300">
                  <svg class="h-4 w-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <span class="font-medium">{{ booking.common_area?.name || 'Área não informada' }}</span>
                </div>
                <div class="flex items-center space-x-2 text-gray-400 mt-1 text-sm">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>{{ formatDate(booking.start_time) }}</span>
                  <span class="text-gray-500">•</span>
                  <span>{{ formatTimeRange(booking.start_time, booking.end_time) }}</span>
                </div>
              </div>
            </div>
            
            <!-- Ações -->
            <div class="flex items-center gap-2">
              <NeonButton 
                v-if="canEdit(booking)"
                @click="editBooking"
                variant="secondary"
                size="sm"
              >
                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
              </NeonButton>
              
              <NeonButton 
                v-if="canCancel(booking)"
                @click="cancelBooking"
                variant="danger"
                size="sm"
              >
                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancelar
              </NeonButton>
            </div>
          </div>
        </div>

        <!-- Grid de Informações -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Coluna Principal -->
          <div class="xl:col-span-2 space-y-6">
            <!-- Detalhes da Reserva -->
            <div class="bg-gradient-to-br from-gray-900/50 via-gray-800/50 to-gray-900/50 backdrop-blur-xl rounded-xl p-5 border border-gray-700/30">
              <div class="flex items-center mb-4">
                <div class="h-8 w-8 rounded-lg bg-gradient-to-r from-indigo-500/20 to-purple-500/20 flex items-center justify-center border border-indigo-500/30 mr-3">
                  <svg class="h-4 w-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <h2 class="text-lg font-bold text-white">Detalhes da Reserva</h2>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="group">
                  <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Data da Reserva</label>
                  <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700/50 group-hover:border-gray-600/50 transition-colors">
                    <p class="text-white font-medium">{{ formatDate(booking.start_time) }}</p>
                  </div>
                </div>
                
                <div class="group">
                  <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Horário</label>
                  <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700/50 group-hover:border-gray-600/50 transition-colors">
                    <p class="text-white font-medium">{{ formatTimeRange(booking.start_time, booking.end_time) }}</p>
                  </div>
                </div>
                
                <div class="group">
                  <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Tipo de Reserva</label>
                  <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700/50 group-hover:border-gray-600/50 transition-colors">
                    <p class="text-white font-medium capitalize">{{ booking.booking_type || 'Não informado' }}</p>
                  </div>
                </div>
                
                <div v-if="booking.total_price" class="group">
                  <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Valor Total</label>
                  <div class="bg-gradient-to-r from-green-900/30 to-green-800/30 rounded-lg p-3 border border-green-500/30 group-hover:border-green-400/50 transition-colors">
                    <p class="text-green-400 font-bold text-lg">R$ {{ formatPrice(booking.total_price) }}</p>
                  </div>
                </div>
                
                <div class="group">
                  <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Criada em</label>
                  <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700/50 group-hover:border-gray-600/50 transition-colors">
                    <p class="text-gray-300 font-medium text-sm">{{ formatDateTime(booking.created_at) }}</p>
                  </div>
                </div>
                
                <div v-if="booking.updated_at !== booking.created_at" class="group">
                  <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Última atualização</label>
                  <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700/50 group-hover:border-gray-600/50 transition-colors">
                    <p class="text-gray-300 font-medium text-sm">{{ formatDateTime(booking.updated_at) }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Observações -->
              <div v-if="booking.notes" class="mt-6">
                <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Observações</label>
                <div class="bg-gradient-to-r from-gray-800/50 to-gray-700/50 rounded-lg p-4 border border-gray-700/50">
                  <p class="text-gray-300 whitespace-pre-wrap text-sm leading-relaxed">{{ booking.notes }}</p>
                </div>
              </div>
            </div>

            <!-- Informações da Área Comum -->
            <div v-if="booking.common_area" class="bg-gradient-to-br from-gray-900/50 via-gray-800/50 to-gray-900/50 backdrop-blur-xl rounded-xl p-5 border border-gray-700/30">
              <div class="flex items-center mb-4">
                <div class="h-8 w-8 rounded-lg bg-gradient-to-r from-green-500/20 to-emerald-500/20 flex items-center justify-center border border-green-500/30 mr-3">
                  <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <h2 class="text-lg font-bold text-white">Área Comum</h2>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="group">
                  <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Nome</label>
                  <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700/50 group-hover:border-gray-600/50 transition-colors">
                    <p class="text-white font-bold">{{ booking.common_area.name }}</p>
                  </div>
                </div>
                
                <div v-if="booking.common_area.capacity" class="group">
                  <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Capacidade</label>
                  <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700/50 group-hover:border-gray-600/50 transition-colors">
                    <p class="text-white font-medium">{{ booking.common_area.capacity }} pessoas</p>
                  </div>
                </div>
                
                <div v-if="booking.common_area.price_per_hour" class="group">
                  <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Preço por Hora</label>
                  <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700/50 group-hover:border-gray-600/50 transition-colors">
                    <p class="text-white font-medium">R$ {{ formatPrice(booking.common_area.price_per_hour) }}</p>
                  </div>
                </div>
                
                <div class="group">
                  <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Requer Aprovação</label>
                  <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700/50 group-hover:border-gray-600/50 transition-colors">
                    <p class="text-white font-medium">{{ booking.common_area.requires_approval ? 'Sim' : 'Não' }}</p>
                  </div>
                </div>
              </div>
              
              <div v-if="booking.common_area.description" class="mt-6">
                <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Descrição</label>
                <div class="bg-gradient-to-r from-gray-800/50 to-gray-700/50 rounded-lg p-4 border border-gray-700/50">
                  <p class="text-gray-300 text-sm leading-relaxed">{{ booking.common_area.description }}</p>
                </div>
              </div>
              
              <div v-if="booking.common_area.rules" class="mt-6">
                <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Regras</label>
                <div class="bg-gradient-to-r from-gray-800/50 to-gray-700/50 rounded-lg p-4 border border-gray-700/50">
                  <p class="text-gray-300 whitespace-pre-wrap text-sm leading-relaxed">{{ booking.common_area.rules }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Informações do Solicitante -->
            <div class="bg-gradient-to-br from-gray-900/50 via-gray-800/50 to-gray-900/50 backdrop-blur-xl rounded-xl p-4 border border-gray-700/30">
              <div class="flex items-center mb-4">
                <div class="h-7 w-7 rounded-lg bg-gradient-to-r from-blue-500/20 to-cyan-500/20 flex items-center justify-center border border-blue-500/30 mr-2">
                  <svg class="h-3 w-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <h3 class="text-base font-bold text-white">Solicitante</h3>
              </div>
              
              <div class="space-y-3">
                <div>
                  <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Apartamento</label>
                  <div class="bg-gray-800/50 rounded-lg p-2 border border-gray-700/50">
                    <p class="text-white font-medium text-sm">{{ formatApartment(booking.apartment) }}</p>
                  </div>
                </div>
                
                <div v-if="booking.apartment?.residents?.length">
                  <label class="block text-xs font-semibold text-gray-400 mb-1 uppercase tracking-wider">Responsável</label>
                  <div class="bg-gray-800/50 rounded-lg p-2 border border-gray-700/50">
                    <p class="text-white font-medium text-sm">{{ booking.apartment.residents[0].name }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Timeline de Status -->
            <div class="bg-gradient-to-br from-gray-900/50 via-gray-800/50 to-gray-900/50 backdrop-blur-xl rounded-xl p-4 border border-gray-700/30">
              <div class="flex items-center mb-4">
                <div class="h-7 w-7 rounded-lg bg-gradient-to-r from-purple-500/20 to-pink-500/20 flex items-center justify-center border border-purple-500/30 mr-2">
                  <svg class="h-3 w-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <h3 class="text-base font-bold text-white">Timeline</h3>
              </div>
              
              <div class="space-y-4">
                <div class="flex items-start space-x-3">
                  <div class="relative">
                    <div class="w-3 h-3 rounded-full bg-green-500 border-2 border-green-400"></div>
                    <div class="absolute top-3 left-1.5 w-0.5 h-6 bg-gradient-to-b from-green-500 to-transparent"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-white font-medium text-sm">Reserva criada</p>
                    <p class="text-gray-400 text-xs mt-0.5">{{ formatDateTime(booking.created_at) }}</p>
                  </div>
                </div>
                
                <div v-if="booking.status !== 'pending'" class="flex items-start space-x-3">
                  <div class="relative">
                    <div class="w-3 h-3 rounded-full border-2" :class="'bg-' + getStatusDotClass(booking.status).replace('bg-', '') + ' border-' + getStatusDotClass(booking.status).replace('bg-', '') + '-400'"></div>
                    <div v-if="booking.status === 'approved' && new Date(booking.start_time) < new Date()" class="absolute top-3 left-1.5 w-0.5 h-6 bg-gradient-to-b from-blue-500 to-transparent"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-white font-medium text-sm">{{ getStatusText(booking.status) }}</p>
                    <p class="text-gray-400 text-xs mt-0.5">{{ formatDateTime(booking.updated_at) }}</p>
                  </div>
                </div>
                
                <div v-if="booking.status === 'approved' && new Date(booking.start_time) < new Date()" class="flex items-start space-x-3">
                  <div class="w-3 h-3 rounded-full bg-blue-500 border-2 border-blue-400"></div>
                  <div class="flex-1 min-w-0">
                    <p class="text-white font-medium text-sm">Reserva concluída</p>
                    <p class="text-gray-400 text-xs mt-0.5">{{ formatDateTime(booking.end_time) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="bg-gradient-to-br from-gray-900/50 via-gray-800/50 to-gray-900/50 backdrop-blur-xl rounded-xl p-4 border border-gray-700/30">
              <h3 class="text-base font-bold text-white mb-4">Ações Rápidas</h3>
              
              <div class="space-y-3">
                <NeonButton
                  @click="viewArea"
                  variant="secondary"
                  size="sm"
                  class="w-full justify-center"
                >
                  <svg class="h-3 w-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  Ver Área Comum
                </NeonButton>
                
                <NeonButton
                  @click="viewAllBookings"
                  variant="outline"
                  size="sm"
                  class="w-full justify-center"
                >
                  <svg class="h-3 w-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>
                  Todas as Reservas
                </NeonButton>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';

export default {
  name: 'BookingDetailPage',
  components: {
    AuthenticatedLayout,
    NeonButton
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const booking = ref(null);
    const loading = ref(true);
    const error = ref('');

    // Funções auxiliares
    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('pt-BR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    const formatDateTime = (dateString) => {
      return new Date(dateString).toLocaleString('pt-BR');
    };

    const formatTimeRange = (startTime, endTime) => {
      const start = new Date(startTime).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
      const end = new Date(endTime).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
      return `${start} às ${end}`;
    };

    const formatPrice = (price) => {
      return parseFloat(price).toFixed(2).replace('.', ',');
    };

    const formatApartment = (apartment) => {
      if (!apartment) return 'Apartamento não informado';
      const block = apartment.block ? `Bloco ${apartment.block.name} - ` : '';
      return `${block}Apto ${apartment.number}`;
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
        'pending': 'bg-gradient-to-r from-yellow-600/30 to-yellow-500/30 text-yellow-300 border border-yellow-500/50 shadow-lg',
        'approved': 'bg-gradient-to-r from-green-600/30 to-green-500/30 text-green-300 border border-green-500/50 shadow-lg',
        'rejected': 'bg-gradient-to-r from-red-600/30 to-red-500/30 text-red-300 border border-red-500/50 shadow-lg',
        'cancelled': 'bg-gradient-to-r from-gray-600/30 to-gray-500/30 text-gray-300 border border-gray-500/50 shadow-lg',
        'completed': 'bg-gradient-to-r from-blue-600/30 to-blue-500/30 text-blue-300 border border-blue-500/50 shadow-lg'
      };
      return statusClasses[status] || 'bg-gradient-to-r from-gray-600/30 to-gray-500/30 text-gray-300 border border-gray-500/50 shadow-lg';
    };

    const getStatusDotClass = (status) => {
      const statusClasses = {
        'pending': 'bg-yellow-500',
        'approved': 'bg-green-500',
        'rejected': 'bg-red-500',
        'cancelled': 'bg-gray-500',
        'completed': 'bg-blue-500'
      };
      return statusClasses[status] || 'bg-gray-500';
    };

    const canEdit = (booking) => {
      return ['pending'].includes(booking.status) && new Date(booking.start_time) > new Date();
    };

    const canCancel = (booking) => {
      return ['pending', 'approved'].includes(booking.status) && new Date(booking.start_time) > new Date();
    };

    // Funções principais
    const loadBooking = async () => {
      try {
        loading.value = true;
        error.value = '';
        
        const token = localStorage.getItem('token');
        const response = await fetch(`/api/common-area-bookings/${route.params.id}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          booking.value = await response.json();
        } else {
          error.value = 'Erro ao carregar reserva';
        }
      } catch (e) {
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    const goBack = () => {
      router.go(-1);
    };

    const editBooking = () => {
      if (booking.value) {
        router.push({ name: 'bookings.edit', params: { id: booking.value.id } });
      }
    };

    const cancelBooking = async () => {
      if (!confirm('Tem certeza que deseja cancelar esta reserva?')) {
        return;
      }

      try {
        const token = localStorage.getItem('token');
        const response = await fetch(`/api/common-areas/${booking.value.common_area_id}/bookings/${booking.value.id}/cancel`, {
          method: 'PATCH',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          await loadBooking();
        } else {
          error.value = 'Erro ao cancelar reserva';
        }
      } catch (e) {
        error.value = 'Erro de conexão';
      }
    };

    const viewArea = () => {
      if (booking.value?.common_area_id) {
        router.push({ name: 'common-areas.bookings', params: { id: booking.value.common_area_id } });
      }
    };

    const viewAllBookings = () => {
      router.push({ name: 'bookings' });
    };

    onMounted(() => {
      loadBooking();
    });

    return {
      booking,
      loading,
      error,
      formatDate,
      formatDateTime,
      formatTimeRange,
      formatPrice,
      formatApartment,
      getStatusText,
      getStatusBadgeClass,
      getStatusDotClass,
      canEdit,
      canCancel,
      goBack,
      editBooking,
      cancelBooking,
      viewArea,
      viewAllBookings
    };
  }
};
</script> 