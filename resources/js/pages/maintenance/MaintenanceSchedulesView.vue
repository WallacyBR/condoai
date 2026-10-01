<template>
  <AuthenticatedLayout page-title="Agendamentos de Manutenção">
    <div class="space-y-6">
      <!-- Header Card - OBRIGATÓRIO conforme DESIGN_MANUAL.md -->
      <TopCard 
        title="Gerenciar Agendamentos" 
        subtitle="Controle todos os agendamentos de manutenção do condomínio"
        color="cyan"
      >
        <template #actions>
          <NeonButton @click="redirectToCreate()" size="md">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Novo Agendamento
          </NeonButton>
        </template>
      </TopCard>

      <!-- Cards de Estatísticas -->
      <div v-if="!isLoadingSchedules && !error" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatsCard
          label="Total de Agendamentos"
          :value="schedules.length"
          variant="blue"
          default-icon-path="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
        />
        <StatsCard
          label="Recorrentes"
          :value="recurringSchedules"
          variant="green"
          default-icon-path="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
        />
        <StatsCard
          label="Ocorrências Únicas"
          :value="uniqueSchedules"
          variant="orange"
          default-icon-path="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
        />
        <StatsCard
          label="Com Responsáveis"
          :value="schedulesWithResponsibles"
          variant="purple"
          default-icon-path="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
        />
      </div>

      <!-- Filtros -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <label for="equipment-filter" class="block text-sm font-medium text-gray-300 mb-2">
                Filtrar por Equipamento
              </label>
              <div class="relative">
                <input 
                  id="equipment-filter"
                  type="text" 
                  v-model="filterEquipmentInput" 
                  placeholder="Digite o ID do equipamento (ex: 1, 2, 3...)"
                  @input="applyFilter"
                  @keydown.enter="applyFilter"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200"
                  :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': filterError }"
                >
                <div v-if="filterError" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                  </svg>
                </div>
              </div>
              <p v-if="filterError" class="mt-1 text-sm text-red-400">{{ filterError }}</p>
              <p v-else-if="filterEquipmentInput && !filterError" class="mt-1 text-sm text-gray-400">
                Filtrando agendamentos do equipamento ID: {{ filterEquipmentInput }}
              </p>
            </div>
            <div class="flex gap-2 items-end">
              <NeonButton @click="clearFilter" size="md" variant="secondary" :disabled="!filterEquipmentInput">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Limpar Filtro
              </NeonButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoadingSchedules" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-500"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
        {{ error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="schedules.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhum agendamento encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ filterEquipmentInput ? 'Tente ajustar os filtros de busca' : 'Use o botão "Novo Agendamento" no cabeçalho para criar um novo agendamento' }}
        </p>
      </div>

      <!-- Schedules Cards Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="schedule in schedules" 
          :key="schedule.id" 
          class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:border-slate-300 dark:hover:border-slate-700 group"
        >
          <!-- Header do Card -->
          <div class="p-6 border-b border-slate-100 dark:border-slate-800">
            <div class="flex justify-between items-start mb-3">
              <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                {{ schedule.title }}
              </h3>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">
                {{ formatScheduleType(schedule.type) }}
              </span>
            </div>
            
            <div class="flex items-center text-sm text-gray-400 mb-2">
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
              <span>{{ schedule.equipment?.name || 'Equipamento não especificado' }}<span v-if="schedule.due_time"> &bull; {{ schedule.due_time.substring(0,5) }}</span></span>
            </div>
          </div>

          <!-- Conteúdo do Card -->
          <div class="p-6 space-y-4">
            <!-- Próxima Ocorrência -->
            <div class="flex items-start">
              <svg class="h-5 w-5 mr-3 mt-0.5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <div>
                <p class="text-sm font-medium text-gray-300">Próxima Ocorrência</p>
                <p class="text-sm text-gray-400">{{ formatNextOccurrence(schedule) }}</p>
              </div>
            </div>

            <!-- Responsáveis -->
            <div class="flex items-start">
              <svg class="h-5 w-5 mr-3 mt-0.5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
              <div>
                <p class="text-sm font-medium text-gray-300">Responsáveis</p>
                <p class="text-sm text-gray-400">
                  {{ schedule.responsible_users?.length ? schedule.responsible_users.map(u => u.name).join(', ') : 'Nenhum responsável' }}
                </p>
              </div>
            </div>

            <!-- Status de Recorrência -->
            <div class="flex items-start">
              <svg class="h-5 w-5 mr-3 mt-0.5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              <div>
                <p class="text-sm font-medium text-gray-300">Tipo</p>
                <p class="text-sm text-gray-400">
                  {{ schedule.is_recurring ? 'Recorrente' : 'Ocorrência Única' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Footer do Card com Ações -->
          <div class="px-6 py-4 bg-gray-800/30 border-t border-gray-600/30 rounded-b-xl">
            <div class="flex justify-end space-x-2">
              <NeonButton @click="redirectToEdit(schedule.id)" size="sm" variant="secondary">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
              </NeonButton>
              <NeonButton @click="confirmDeleteSchedule(schedule.id)" size="sm" variant="danger">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Excluir
              </NeonButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Paginação -->
      <div v-if="!isLoadingSchedules && pagination.total > pagination.per_page" class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
        <p class="text-sm text-gray-400">
          Mostrando {{ pagination.from || 0 }} a {{ pagination.to || 0 }} de {{ pagination.total || 0 }} resultados.
        </p>
        <div class="flex flex-wrap gap-2">
          <NeonButton @click="fetchSchedules(1)" :disabled="pagination.current_page === 1" size="sm" variant="secondary">
            Primeira
          </NeonButton>
          <NeonButton @click="fetchSchedules(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" size="sm" variant="secondary">
            Anterior
          </NeonButton>
          <span class="px-3 py-2 text-sm text-gray-300 bg-gray-700/50 rounded-md">
            Página {{ pagination.current_page }} de {{ pagination.last_page }}
          </span>
          <NeonButton @click="fetchSchedules(pagination.current_page + 1)" :disabled="!pagination.next_page_url" size="sm" variant="secondary">
            Próxima
          </NeonButton>
          <NeonButton @click="fetchSchedules(pagination.last_page)" :disabled="pagination.current_page === pagination.last_page" size="sm" variant="secondary">
            Última
          </NeonButton>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div v-if="isDeleteConfirmOpen" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" @click="isDeleteConfirmOpen = false">
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-2xl p-6 border-2 border-red-500/30 max-w-md w-full mx-4" @click.stop>
        <div class="flex items-center mb-4">
          <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center mr-4">
            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-white">Confirmar Exclusão</h3>
            <p class="text-sm text-gray-400">Esta ação não pode ser desfeita</p>
          </div>
        </div>

        <div class="mb-6">
          <p class="text-gray-300 mb-2">
            Tem certeza que deseja excluir este agendamento?
          </p>
          
          <div v-if="scheduleToDelete" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3">
            <p class="text-red-300 text-sm font-medium">
              {{ scheduleToDelete.title }}
            </p>
            <!-- Informações adicionais do agendamento -->
            <div class="mt-2 pt-2 border-t border-red-500/20">
              <div class="space-y-1 text-xs text-gray-400">
                <div>
                  <span class="text-gray-500">Equipamento:</span>
                  <span class="text-gray-300 ml-1">{{ scheduleToDelete.equipment?.name || 'Não especificado' }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Tipo:</span>
                  <span class="text-gray-300 ml-1">{{ formatScheduleType(scheduleToDelete.type) }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Recorrência:</span>
                  <span class="text-gray-300 ml-1">{{ scheduleToDelete.is_recurring ? 'Recorrente' : 'Ocorrência Única' }}</span>
                </div>
                <div v-if="scheduleToDelete.start_date">
                  <span class="text-gray-500">Data de Início:</span>
                  <span class="text-gray-300 ml-1">{{ format(parseISO(scheduleToDelete.start_date), 'dd/MM/yyyy', { locale: ptBR }) }}</span>
                </div>
                <div v-if="scheduleToDelete.responsible_users?.length">
                  <span class="text-gray-500">Responsáveis:</span>
                  <span class="text-gray-300 ml-1">{{ scheduleToDelete.responsible_users.map(u => u.name).join(', ') }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-3">
          <button 
            @click="isDeleteConfirmOpen = false"
            class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors"
            :disabled="isDeleting"
          >
            Cancelar
          </button>
          <button 
            @click="deleteSchedule"
            :disabled="isDeleting"
            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-800 disabled:cursor-not-allowed text-white rounded-lg transition-colors flex items-center justify-center"
          >
            <span v-if="!isDeleting">Excluir Agendamento</span>
            <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          </button>
        </div>
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import TopCard from '@/components/ui/TopCard.vue';
import StatsCard from '@/components/ui/StatsCard.vue';
import api from '@/utils/api';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import NeonButton from '@/components/NeonButton.vue';

const router = useRouter();

const schedules = ref([]);
const isLoadingSchedules = ref(false);
const error = ref('');
const filterEquipmentInput = ref('');
const filterError = ref('');
let filterDebounceTimer = null;

const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
    per_page: 15, // Default per page
    next_page_url: null,
    prev_page_url: null,
});

const isDeleteConfirmOpen = ref(false);
const scheduleToDeleteId = ref(null);
const isDeleting = ref(false);

const scheduleToDelete = computed(() => {
    return schedules.value.find(s => s.id === scheduleToDeleteId.value);
});

// Computed properties para estatísticas
const recurringSchedules = computed(() => {
    return schedules.value.filter(s => s.is_recurring).length;
});

const uniqueSchedules = computed(() => {
    return schedules.value.filter(s => !s.is_recurring).length;
});

const schedulesWithResponsibles = computed(() => {
    return schedules.value.filter(s => s.responsible_users && s.responsible_users.length > 0).length;
});

async function fetchSchedules(page = 1) {
  isLoadingSchedules.value = true;
  error.value = '';
  try {
    let url = `/maintenance/schedules?page=${page}&per_page=${pagination.value.per_page}`;
    if (filterEquipmentInput.value && !isNaN(parseInt(filterEquipmentInput.value))) {
      url += `&maintenance_equipment_id=${parseInt(filterEquipmentInput.value)}`;
    }
    const response = await api.get(url);
    schedules.value = response.data.data || [];
    const { current_page, last_page, from, to, total, per_page: respPerPage, next_page_url, prev_page_url } = response.data;
    pagination.value = { current_page, last_page, from, to, total, per_page: respPerPage || pagination.value.per_page, next_page_url, prev_page_url };
  } catch (err) {
    console.error('Erro ao buscar agendamentos:', err);
    error.value = 'Erro ao carregar agendamentos. Tente novamente.';
    schedules.value = [];
  } finally {
    isLoadingSchedules.value = false;
  }
}

function validateFilter() {
    if (!filterEquipmentInput.value.trim()) {
        filterError.value = '';
        return true;
    }
    
    const equipmentId = parseInt(filterEquipmentInput.value.trim());
    if (isNaN(equipmentId) || equipmentId <= 0) {
        filterError.value = 'Por favor, digite um ID de equipamento válido (número inteiro positivo)';
        return false;
    }
    
    filterError.value = '';
    return true;
}

function applyFilter() {
    clearTimeout(filterDebounceTimer);
    filterDebounceTimer = setTimeout(() => {
        if (validateFilter()) {
            fetchSchedules(1); // Resetar para a primeira página ao filtrar
        }
    }, 500); // Espera 500ms após o usuário parar de digitar
}

function clearFilter() {
    filterEquipmentInput.value = '';
    filterError.value = '';
    fetchSchedules(1);
}

watch(filterEquipmentInput, applyFilter);

function redirectToCreate() {
  router.push('/maintenance/schedules/create');
}

function redirectToEdit(id) {
  router.push(`/maintenance/schedules/${id}/edit`);
}

function confirmDeleteSchedule(id) {
    scheduleToDeleteId.value = id;
    isDeleteConfirmOpen.value = true;
}

function formatScheduleType(type) {
  const types = {
    daily: 'Diário', 
    weekly: 'Semanal', 
    biweekly: 'Quinzenal', 
    monthly: 'Mensal',
    semiannual: 'Semestral', 
    annual: 'Anual', 
    specific_days: 'Dias Específicos',
  };
  return types[type] || type;
}

function formatNextOccurrence(schedule) {
    if (!schedule.start_date) return 'N/A';
    let text = `Início: ${format(parseISO(schedule.start_date), 'dd/MM/yyyy', { locale: ptBR })}`;
    if (schedule.due_time) text += ` às ${schedule.due_time.substring(0,5)}`;
    if (schedule.is_recurring) {
      if (schedule.type) text += ` (${formatScheduleType(schedule.type)})`;
    } else {
      text = `Única: ${format(parseISO(schedule.start_date), 'dd/MM/yyyy', { locale: ptBR })}`;
      if (schedule.due_time) text += ` às ${schedule.due_time.substring(0,5)}`;
    }
    return text;
}

async function deleteSchedule() {
    if (!scheduleToDeleteId.value) return;
    isDeleting.value = true;
    try {
        await api.delete(`/maintenance/schedules/${scheduleToDeleteId.value}`);
        // Se a página atual ficar vazia após a exclusão, volte para a página anterior se possível
        let currentPage = pagination.value.current_page;
        if (schedules.value.length === 1 && currentPage > 1) {
            currentPage--;
        }
        fetchSchedules(currentPage);
        // Adicionar notificação de sucesso
    } catch (err) {
        console.error('Erro ao excluir agendamento:', err);
        if (err.response && err.response.data.message) {
            alert(`Erro ao excluir: ${err.response.data.message}`);
        } else {
            alert('Ocorreu um erro ao excluir o agendamento.');
        }
    } finally {
        isDeleteConfirmOpen.value = false;
        scheduleToDeleteId.value = null;
        isDeleting.value = false;
    }
}

onMounted(() => {
  fetchSchedules();
});
</script>