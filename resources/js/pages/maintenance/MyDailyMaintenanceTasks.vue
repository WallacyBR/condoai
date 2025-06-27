<template>
  <AuthenticatedLayout page-title="Minhas Tarefas de Manutenção Hoje">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-white mb-3 sm:mb-0">Minhas Tarefas para {{ formattedTargetDate }}</h2>
          <div class="w-full sm:w-auto">
            <label for="target-date" class="sr-only">Selecionar Data</label>
            <input type="date" id="target-date" v-model="targetDate" @change="fetchMyTasks"
                   class="bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm p-2">
          </div>
        </div>

        <div v-if="isLoading" class="text-center text-gray-400 py-10">
          <svg class="animate-spin h-8 w-8 text-cyan-500 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Carregando suas tarefas...
        </div>
        <div v-else-if="tasks.length === 0" class="text-center text-gray-400 py-10">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Nenhuma tarefa de manutenção agendada para você em {{ formattedTargetDate }}.
        </div>
        <div v-else class="space-y-4">
          <div v-for="task in tasks" :key="task.id"
               class="bg-gray-700/70 p-4 rounded-lg shadow-md border border-gray-600/50 hover:border-cyan-500/70 transition-all duration-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
              <h3 class="text-lg font-semibold text-cyan-400 mb-1 sm:mb-0">{{ task.title }}</h3>
              <span class="text-sm text-gray-400 bg-gray-600 px-2 py-1 rounded">
                {{ task.due_time ? `Previsto para: ${task.due_time.substring(0,5)}` : 'Horário não definido' }}
              </span>
            </div>
            <p class="text-sm text-gray-300 mt-1">
              <span class="font-semibold">Equipamento:</span> {{ task.equipment?.name || 'N/A' }}
            </p>
            <p v-if="task.description" class="text-sm text-gray-400 mt-2">{{ task.description }}</p>
            <div class="mt-3 pt-3 border-t border-gray-600/50">
              <!-- TODO: Adicionar link para registrar a execução da tarefa (MaintenanceRecordForm) -->
              <button @click="goToRecordForm(task)"
                      class="text-sm text-cyan-500 hover:text-cyan-300 transition-colors">
                Registrar Execução / Ver Detalhes
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import api from '@/utils/api';
import { format, parseISO,startOfToday } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useRouter } from 'vue-router';

const router = useRouter();

const tasks = ref([]);
const isLoading = ref(false);
const targetDate = ref(format(startOfToday(), 'yyyy-MM-dd')); // Data alvo no formato YYYY-MM-DD

const formattedTargetDate = computed(() => {
  try {
    return format(parseISO(targetDate.value), "dd 'de' MMMM 'de' yyyy", { locale: ptBR });
  } catch (e) {
    return "Data Inválida";
  }
});

async function fetchMyTasks() {
  isLoading.value = true;
  try {
    const response = await api.get(`/maintenance/schedules/my-today?date=${targetDate.value}`);
    tasks.value = response.data; // A API retorna um array diretamente
  } catch (error) {
    console.error('Erro ao buscar minhas tarefas de manutenção:', error);
    tasks.value = [];
    // Tratar erro (ex: notificação para o usuário)
  } finally {
    isLoading.value = false;
  }
}

function goToRecordForm(task) {
  router.push({ name: 'MaintenanceRecordForm', params: { scheduleId: task.id } });
}

onMounted(() => {
  fetchMyTasks();
});
</script>

<style scoped>
/* Estilização adicional se necessário */
</style>
