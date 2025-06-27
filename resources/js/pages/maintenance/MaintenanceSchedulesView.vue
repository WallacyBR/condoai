<template>
  <AuthenticatedLayout page-title="Agendamentos de Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-white">Gerenciar Agendamentos</h2>
          <button @click="openFormModal()"
                  class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg shadow-md transition-colors">
            Novo Agendamento
          </button>
        </div>

        <!-- Filtros (placeholder) -->
        <div class="mb-4">
          <input type="text" v-model="filterEquipment" placeholder="Filtrar por ID do equipamento (numérico)"
                 @input="applyFilter"
                 class="w-full md:w-1/3 bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm p-2">
        </div>

        <!-- Tabela de Agendamentos -->
        <div v-if="isLoadingSchedules" class="text-center text-gray-400 py-8">Carregando agendamentos...</div>
        <div v-else-if="schedules.length === 0" class="text-center text-gray-400 py-8">Nenhum agendamento encontrado.</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700/50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Título</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Equipamento</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Tipo</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Próxima Ocorrência</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Responsáveis</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-gray-800/30 divide-y divide-gray-700">
              <tr v-for="schedule in schedules" :key="schedule.id" class="hover:bg-gray-700/60 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ schedule.title }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ schedule.equipment?.name || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ formatScheduleType(schedule.type) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ formatNextOccurrence(schedule) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                  {{ schedule.responsible_users?.map(u => u.name).join(', ') || 'Nenhum' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <button @click="openFormModal(schedule)" class="text-cyan-400 hover:text-cyan-300 transition-colors">Editar</button>
                  <button @click="confirmDeleteSchedule(schedule.id)" class="text-red-500 hover:text-red-400 transition-colors">Excluir</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Paginação -->
         <div v-if="!isLoadingSchedules && pagination.total > pagination.per_page" class="mt-6 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
            <p class="text-sm text-gray-400">
                Mostrando {{ pagination.from || 0 }} a {{ pagination.to || 0 }} de {{ pagination.total || 0 }} resultados.
            </p>
            <div class="flex space-x-1">
                <button @click="fetchSchedules(1)" :disabled="pagination.current_page === 1"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Primeira
                </button>
                <button @click="fetchSchedules(pagination.current_page - 1)" :disabled="!pagination.prev_page_url"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Anterior
                </button>
                 <span class="px-3 py-1 text-sm text-gray-300">Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
                <button @click="fetchSchedules(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Próxima
                </button>
                 <button @click="fetchSchedules(pagination.last_page)" :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Última
                </button>
            </div>
        </div>
      </div>
    </div>

    <MaintenanceScheduleForm
      :show="isFormModalOpen"
      :schedule-data="selectedSchedule"
      @close="closeFormModal"
      @saved="handleSave"
    />

    <Modal :show="isDeleteConfirmOpen" @close="isDeleteConfirmOpen = false" max-width="md">
        <template #title>Confirmar Exclusão</template>
        <div class="p-6">
            <p class="text-gray-300">Tem certeza que deseja excluir o agendamento "{{ scheduleToDelete?.title || 'selecionado' }}"? Esta ação não pode ser desfeita.</p>
        </div>
        <template #footer>
            <button @click="isDeleteConfirmOpen = false" class="px-4 py-2 text-gray-300 bg-gray-600 hover:bg-gray-500 rounded-md shadow-sm transition-colors">Cancelar</button>
            <button @click="deleteSchedule" :disabled="isDeleting" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md ml-3 shadow-sm transition-colors disabled:opacity-50">
              {{ isDeleting ? 'Excluindo...' : 'Excluir' }}
            </button>
        </template>
    </Modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import MaintenanceScheduleForm from '@/components/maintenance/MaintenanceScheduleForm.vue';
import Modal from '@/components/Modal.vue';
import api from '@/utils/api';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';

const schedules = ref([]);
const isLoadingSchedules = ref(false);
const isFormModalOpen = ref(false);
const selectedSchedule = ref(null);
const filterEquipmentInput = ref('');
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

async function fetchSchedules(page = 1) {
  isLoadingSchedules.value = true;
  try {
    let url = `/maintenance/schedules?page=${page}&per_page=${pagination.value.per_page}`;
    if (filterEquipmentInput.value && !isNaN(parseInt(filterEquipmentInput.value))) {
      url += `&maintenance_equipment_id=${parseInt(filterEquipmentInput.value)}`;
    }
    const response = await api.get(url);
    schedules.value = response.data.data || []; // Garante que é um array

    const { current_page, last_page, from, to, total, per_page: respPerPage, next_page_url, prev_page_url } = response.data;
    pagination.value = { current_page, last_page, from, to, total, per_page: respPerPage || pagination.value.per_page, next_page_url, prev_page_url };

  } catch (error) {
    console.error('Erro ao buscar agendamentos:', error);
    schedules.value = []; // Limpa em caso de erro para evitar dados inconsistentes
    // Tratar erro (ex: notificação para o usuário)
  } finally {
    isLoadingSchedules.value = false;
  }
}

function applyFilter() {
    clearTimeout(filterDebounceTimer);
    filterDebounceTimer = setTimeout(() => {
        fetchSchedules(1); // Resetar para a primeira página ao filtrar
    }, 500); // Espera 500ms após o usuário parar de digitar
}


watch(filterEquipmentInput, applyFilter);


function openFormModal(schedule = null) {
  selectedSchedule.value = schedule ? JSON.parse(JSON.stringify(schedule)) : null; // Deep copy para evitar reatividade indesejada
  isFormModalOpen.value = true;
}

function closeFormModal() {
  isFormModalOpen.value = false;
  selectedSchedule.value = null;
}

function handleSave() {
  fetchSchedules(pagination.value.current_page);
  closeFormModal();
  // Adicionar notificação de sucesso
}

function confirmDeleteSchedule(id) {
    scheduleToDeleteId.value = id;
    isDeleteConfirmOpen.value = true;
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
    } catch (error) {
        console.error('Erro ao excluir agendamento:', error);
        if (error.response && error.response.data.message) {
            alert(`Erro ao excluir: ${error.response.data.message}`);
        } else {
            alert('Ocorreu um erro ao excluir o agendamento.');
        }
    } finally {
        isDeleteConfirmOpen.value = false;
        scheduleToDeleteId.value = null;
        isDeleting.value = false;
    }
}

function formatScheduleType(type) {
  const types = {
    daily: 'Diário', weekly: 'Semanal', biweekly: 'Quinzenal', monthly: 'Mensal',
    semiannual: 'Semestral', annual: 'Anual', specific_days: 'Dias Específicos',
  };
  return types[type] || type;
}

function formatNextOccurrence(schedule) {
    // Simplificado: mostra data de início e tipo.
    // A lógica real de "próxima ocorrência" é complexa e idealmente viria do backend
    // ou seria calculada com uma biblioteca robusta de recorrências.
    if (!schedule.start_date) return 'N/A';
    let text = `Início: ${format(parseISO(schedule.start_date), 'dd/MM/yyyy', { locale: ptBR })}`;
    if (schedule.due_time) text += ` às ${schedule.due_time.substring(0,5)}`;
    if (schedule.is_recurring) {
      text += ` (${formatScheduleType(schedule.type)})`;
    } else {
      text = `Única: ${format(parseISO(schedule.start_date), 'dd/MM/yyyy', { locale: ptBR })} às ${schedule.due_time?.substring(0,5) || ''}`;
    }
    return text;
}

onMounted(() => {
  fetchSchedules();
});
</script>
