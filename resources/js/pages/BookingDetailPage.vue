<template>
  <AuthenticatedLayout page-title="Detalhes da Reserva">
    <div class="space-y-6">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="flex flex-col items-center space-y-3">
          <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-300 border-t-indigo-600 dark:border-slate-700 dark:border-t-indigo-400"></div>
          <span class="text-slate-500 dark:text-slate-400 text-sm">Carregando detalhes da reserva...</span>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-rose-50 dark:bg-rose-950/30 rounded-xl p-4 border border-rose-200 dark:border-rose-800/50">
        <div class="flex items-center space-x-3">
          <div class="h-8 w-8 rounded-lg bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-rose-800 dark:text-rose-300">Erro ao carregar reserva</h3>
            <p class="text-rose-600 dark:text-rose-400 text-xs">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Conteúdo da Reserva -->
      <div v-else-if="booking" class="space-y-6">
        <!-- Top Card com Informações Principais -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-start space-x-3">
              <!-- Botão Voltar -->
              <AppButton
                @click="goBack"
                variant="secondary"
                size="sm"
                class="!px-2.5 !py-2 shrink-0"
                aria-label="Voltar"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </AppButton>
              
              <!-- Informações da Reserva -->
              <div>
                <div class="flex items-center gap-3 mb-2 flex-wrap">
                  <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                    Reserva #{{ booking.id }}
                  </h1>
                  <span 
                    class="px-2.5 py-0.5 text-xs font-semibold rounded-full shrink-0"
                    :class="getStatusBadgeClass(booking.status)"
                  >
                    {{ getStatusText(booking.status) }}
                  </span>
                </div>
                <div class="flex items-center space-x-2 text-slate-700 dark:text-slate-300 text-sm">
                  <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <span class="font-medium">{{ booking.common_area?.name || 'Área não informada' }}</span>
                </div>
                <div class="flex items-center space-x-2 text-slate-500 dark:text-slate-400 mt-1 text-xs">
                  <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>{{ formatDate(booking.start_time) }}</span>
                  <span>•</span>
                  <span>{{ formatTimeRange(booking.start_time, booking.end_time) }}</span>
                </div>
              </div>
            </div>
            
            <!-- Ações -->
            <div class="flex items-center gap-2 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-800">
              <AppButton 
                v-if="canEdit(booking)"
                @click="editBooking"
                variant="secondary"
                size="sm"
              >
                <svg class="h-3.5 w-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
              </AppButton>
              
              <AppButton 
                v-if="canCancel(booking)"
                @click="cancelBooking"
                variant="danger"
                size="sm"
              >
                <svg class="h-3.5 w-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancelar
              </AppButton>
            </div>
          </div>
        </div>

        <!-- Grid de Informações -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Coluna Principal -->
          <div class="xl:col-span-2 space-y-6">
            <!-- Detalhes da Reserva -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
              <div class="flex items-center mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="h-8 w-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 mr-3">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Detalhes da Reserva</h2>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Data da Reserva</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-sm">{{ formatDate(booking.start_time) }}</p>
                  </div>
                </div>
                
                <div>
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Horário</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-sm">{{ formatTimeRange(booking.start_time, booking.end_time) }}</p>
                  </div>
                </div>
                
                <div>
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Tipo de Reserva</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-sm capitalize">{{ booking.booking_type || 'Não informado' }}</p>
                  </div>
                </div>
                
                <div v-if="booking.total_price">
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Valor Total</label>
                  <div class="bg-emerald-50 dark:bg-emerald-950/40 rounded-lg p-3 border border-emerald-200 dark:border-emerald-800/50">
                    <p class="text-emerald-700 dark:text-emerald-300 font-bold text-base">R$ {{ formatPrice(booking.total_price) }}</p>
                  </div>
                </div>
                
                <div>
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Criada em</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-700 dark:text-slate-300 font-medium text-xs">{{ formatDateTime(booking.created_at) }}</p>
                  </div>
                </div>
                
                <div v-if="booking.updated_at !== booking.created_at">
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Última atualização</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-700 dark:text-slate-300 font-medium text-xs">{{ formatDateTime(booking.updated_at) }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Observações -->
              <div v-if="booking.notes" class="mt-5">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Observações</label>
                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3.5 border border-slate-200/60 dark:border-slate-700/60">
                  <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap text-sm leading-relaxed">{{ booking.notes }}</p>
                </div>
              </div>
            </div>

            <!-- Informações da Área Comum -->
            <div v-if="booking.common_area" class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
              <div class="flex items-center mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="h-8 w-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mr-3">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Área Comum</h2>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Nome</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-900 dark:text-slate-100 font-semibold text-sm">{{ booking.common_area.name }}</p>
                  </div>
                </div>
                
                <div v-if="booking.common_area.capacity">
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Capacidade</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-sm">{{ booking.common_area.capacity }} pessoas</p>
                  </div>
                </div>
                
                <div v-if="booking.common_area.price_per_hour">
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Preço por Hora</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-sm">R$ {{ formatPrice(booking.common_area.price_per_hour) }}</p>
                  </div>
                </div>
                
                <div>
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Requer Aprovação</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-sm">{{ booking.common_area.requires_approval ? 'Sim' : 'Não' }}</p>
                  </div>
                </div>
              </div>
              
              <div v-if="booking.common_area.description" class="mt-5">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Descrição</label>
                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3.5 border border-slate-200/60 dark:border-slate-700/60">
                  <p class="text-slate-700 dark:text-slate-300 text-sm leading-relaxed">{{ booking.common_area.description }}</p>
                </div>
              </div>
              
              <div v-if="booking.common_area.rules" class="mt-5">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Regras</label>
                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3.5 border border-slate-200/60 dark:border-slate-700/60">
                  <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap text-sm leading-relaxed">{{ booking.common_area.rules }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Informações do Solicitante -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
              <div class="flex items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="h-7 w-7 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400 mr-2.5">
                  <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Solicitante</h3>
              </div>
              
              <div class="space-y-3">
                <div>
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Apartamento</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-2.5 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-sm">{{ formatApartment(booking.apartment) }}</p>
                  </div>
                </div>
                
                <div v-if="booking.apartment?.residents?.length">
                  <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Responsável</label>
                  <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-2.5 border border-slate-200/60 dark:border-slate-700/60">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-sm">{{ booking.apartment.residents[0].name }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Timeline de Status -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
              <div class="flex items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="h-7 w-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 mr-2.5">
                  <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Linha do Tempo</h3>
              </div>
              
              <div class="space-y-4">
                <div class="flex items-start space-x-3">
                  <div class="relative flex flex-col items-center">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-50 dark:ring-emerald-950/50"></div>
                    <div class="w-0.5 h-8 bg-slate-200 dark:bg-slate-700 my-1"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-xs">Reserva criada</p>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ formatDateTime(booking.created_at) }}</p>
                  </div>
                </div>
                
                <div v-if="booking.status !== 'pending'" class="flex items-start space-x-3">
                  <div class="relative flex flex-col items-center">
                    <div class="w-2.5 h-2.5 rounded-full ring-4" :class="[getStatusDotClass(booking.status), 'ring-slate-100 dark:ring-slate-800']"></div>
                    <div v-if="booking.status === 'approved' && new Date(booking.start_time) < new Date()" class="w-0.5 h-8 bg-slate-200 dark:bg-slate-700 my-1"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-xs">{{ getStatusText(booking.status) }}</p>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ formatDateTime(booking.updated_at) }}</p>
                  </div>
                </div>
                
                <div v-if="booking.status === 'approved' && new Date(booking.start_time) < new Date()" class="flex items-start space-x-3">
                  <div class="w-2.5 h-2.5 rounded-full bg-blue-500 ring-4 ring-blue-50 dark:ring-blue-950/50"></div>
                  <div class="flex-1 min-w-0">
                    <p class="text-slate-900 dark:text-slate-100 font-medium text-xs">Reserva concluída</p>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ formatDateTime(booking.end_time) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Links Rápidos -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
              <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Links Rápidos</h3>
              <div class="space-y-2">
                <AppButton 
                  @click="viewArea" 
                  variant="outline" 
                  size="sm"
                  class="w-full justify-start text-xs"
                >
                  <svg class="h-3.5 w-3.5 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  Ver Área Comum
                </AppButton>
                
                <AppButton 
                  @click="viewAllBookings" 
                  variant="outline" 
                  size="sm"
                  class="w-full justify-start text-xs"
                >
                  <svg class="h-3.5 w-3.5 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>
                  Todas as Reservas
                </AppButton>
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
import AppButton from '../components/ui/AppButton.vue';
import NeonButton from '../components/NeonButton.vue';
import { apiRequest } from '../utils/api';

export default {
  name: 'BookingDetailPage',
  components: {
    AuthenticatedLayout,
    AppButton,
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
        'pending': 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50',
        'approved': 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50',
        'rejected': 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50',
        'cancelled': 'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        'completed': 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/50'
      };
      return statusClasses[status] || 'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
    };

    const getStatusDotClass = (status) => {
      const statusClasses = {
        'pending': 'bg-amber-500',
        'approved': 'bg-emerald-500',
        'rejected': 'bg-rose-500',
        'cancelled': 'bg-slate-400',
        'completed': 'bg-blue-500'
      };
      return statusClasses[status] || 'bg-slate-400';
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

        booking.value = await apiRequest(`/common-area-bookings/${route.params.id}`);
      } catch (e) {
        error.value = e.message || 'Erro ao carregar reserva';
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
        await apiRequest(`/common-areas/${booking.value.common_area_id}/bookings/${booking.value.id}/cancel`, { method: 'PATCH' });
        await loadBooking();
      } catch (e) {
        error.value = e.message || 'Erro ao cancelar reserva';
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
