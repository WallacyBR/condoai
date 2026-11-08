<template>
  <AuthenticatedLayout page-title="Dashboard de Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <h2 class="text-xl font-semibold text-white mb-4">Visão Geral da Manutenção</h2>

        <div v-if="isLoadingStats" class="text-center text-gray-400 py-10">Carregando estatísticas...</div>
        <div v-else class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatsCard 
            label="Manutenções Vencidas" 
            :value="generalStats.overdue_maintenance"
            variant="red"
            default-icon-path="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
          />
          
          <StatsCard 
            label="Manutenções Concluídas" 
            :value="generalStats.completed_maintenance"
            variant="green"
            default-icon-path="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
          
          <StatsCard 
            label="Manutenções Pendentes" 
            :value="generalStats.pending_maintenance"
            variant="orange"
            default-icon-path="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
          
          <StatsCard 
            label="Equipamentos Registrados" 
            :value="generalStats.total_equipment"
            variant="blue"
            default-icon-path="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
          />
        </div>

        <!-- Cards de Acesso Rápido -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Card Minhas Tarefas de Hoje -->
          <div class="bg-gradient-to-br from-blue-900/50 via-blue-800/50 to-blue-900/50 rounded-2xl p-6 border-2 border-blue-500/30 shadow-xl hover:shadow-2xl hover:border-blue-500/50 transition-all duration-300 cursor-pointer group"
               @click="goToMyTodayTasks">
            <div class="flex items-center justify-between mb-4">
              <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center group-hover:bg-blue-500/30 transition-colors">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
              </div>
              <svg class="h-5 w-5 text-blue-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-blue-100 mb-2">Minhas Tarefas de Hoje</h3>
            <p class="text-blue-300 text-sm">Visualize e execute suas manutenções programadas para hoje</p>
          </div>

          <!-- Card Manutenções Concluídas -->
          <div class="bg-gradient-to-br from-green-900/50 via-green-800/50 to-green-900/50 rounded-2xl p-6 border-2 border-green-500/30 shadow-xl hover:shadow-2xl hover:border-green-500/50 transition-all duration-300 cursor-pointer group"
               @click="goToCompletedMaintenances">
            <div class="flex items-center justify-between mb-4">
              <div class="h-12 w-12 rounded-full bg-green-500/20 flex items-center justify-center group-hover:bg-green-500/30 transition-colors">
                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <svg class="h-5 w-5 text-green-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-green-100 mb-2">Manutenções Concluídas</h3>
            <p class="text-green-300 text-sm">Visualize o histórico das suas manutenções realizadas</p>
          </div>
        </div>

        <h3 class="text-lg font-semibold text-white mt-8 mb-4">Estatísticas por Equipamento</h3>
        <div v-if="isLoadingStats" class="text-center text-gray-400 py-10">Carregando estatísticas por equipamento...</div>
        <div v-else-if="equipmentStats.length === 0" class="text-center text-gray-400 py-10">Nenhuma estatística por equipamento encontrada.</div>
        <div v-else class="overflow-x-auto mt-4">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Equipamento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Área</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Vencidas</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Pendentes</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Concluídas</th>
              </tr>
            </thead>
            <tbody class="bg-gray-800/30 divide-y divide-gray-700">
              <tr v-for="stat in equipmentStats" :key="stat.id" class="hover:bg-gray-700/60 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ stat.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ stat.maintenance_area?.name || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-400 text-center">{{ stat.overdue_schedules_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-400 text-center">{{ stat.pending_schedules_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-400 text-center">{{ stat.completed_schedules_count }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h3 class="text-lg font-semibold text-white mt-8 mb-4">Estatísticas por Área</h3>
        <div v-if="isLoadingStats" class="text-center text-gray-400 py-10">Carregando estatísticas por área...</div>
        <div v-else-if="areaStats.length === 0" class="text-center text-gray-400 py-10">Nenhuma estatística por área encontrada.</div>
        <div v-else class="overflow-x-auto mt-4">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Área</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Equipamentos</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Vencidas</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Pendentes</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Concluídas</th>
              </tr>
            </thead>
            <tbody class="bg-gray-800/30 divide-y divide-gray-700">
              <tr v-for="stat in areaStats" :key="stat.id" class="hover:bg-gray-700/60 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ stat.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300 text-center">{{ stat.equipments_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-400 text-center">{{ stat.overdue_schedules_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-400 text-center">{{ stat.pending_schedules_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-400 text-center">{{ stat.completed_schedules_count }}</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import StatsCard from '@/components/ui/StatsCard.vue';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/api';

const router = useRouter();
const generalStats = ref({});
const equipmentStats = ref([]);
const areaStats = ref([]);
const isLoadingStats = ref(true);

async function fetchDashboardStats() {
  isLoadingStats.value = true;
  try {
    const [generalRes, equipmentRes, areaRes] = await Promise.all([
      api.get('/maintenance/dashboard/general-stats'),
      api.get('/maintenance/dashboard/by-equipment'),
      api.get('/maintenance/dashboard/by-area'),
    ]);
    generalStats.value = generalRes.data;
    equipmentStats.value = equipmentRes.data;
    areaStats.value = areaRes.data;
  } catch (error) {
    console.error('Erro ao buscar dados do dashboard:', error);
  } finally {
    isLoadingStats.value = false;
  }
}

const goToMyTodayTasks = () => {
  router.push({ name: 'maintenance.my-today-tasks' });
};

const goToCompletedMaintenances = () => {
  router.push({ name: 'maintenance.my-completed' });
};

onMounted(() => {
  fetchDashboardStats();
});
</script>

<style scoped>
/* Estilos específicos para o dashboard de manutenção */
</style>
