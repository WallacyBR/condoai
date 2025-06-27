<template>
  <AuthenticatedLayout page-title="Dashboard de Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <h2 class="text-xl font-semibold text-white mb-4">Visão Geral da Manutenção</h2>

        <div v-if="isLoadingStats" class="text-center text-gray-400 py-10">Carregando estatísticas...</div>
        <div v-else class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="stats-blue p-5 rounded-xl shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-blue-300">Manutenções Vencidas</p>
                <p class="text-3xl font-bold text-blue-100">{{ generalStats.overdue_maintenance }}</p>
              </div>
              <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </div>
            </div>
          </div>

          <div class="stats-green p-5 rounded-xl shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-green-300">Manutenções Concluídas</p>
                <p class="text-3xl font-bold text-green-100">{{ generalStats.completed_maintenance }}</p>
              </div>
              <div class="h-12 w-12 rounded-full bg-green-500/20 flex items-center justify-center">
                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </div>
            </div>
          </div>

          <div class="stats-yellow p-5 rounded-xl shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-yellow-300">Manutenções Pendentes</p>
                <p class="text-3xl font-bold text-yellow-100">{{ generalStats.pending_maintenance }}</p>
              </div>
              <div class="h-12 w-12 rounded-full bg-yellow-500/20 flex items-center justify-center">
                <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
              </div>
            </div>
          </div>

          <div class="stats-purple p-5 rounded-xl shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-purple-300">Equipamentos Registrados</p>
                <p class="text-3xl font-bold text-purple-100">{{ generalStats.total_equipment }}</p>
              </div>
              <div class="h-12 w-12 rounded-full bg-purple-500/20 flex items-center justify-center">
                <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              </div>
            </div>
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
import { ref, onMounted } from 'vue';
import api from '@/utils/api';

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

onMounted(() => {
  fetchDashboardStats();
});
</script>

<style scoped>
/* Estilos do DESIGN_MANUAL.md para stats cards */
.stats-blue {
  background: linear-gradient(to bottom right, rgba(29, 78, 216, 0.5), rgba(30, 64, 175, 0.5), rgba(29, 78, 216, 0.5)); /* from-blue-900/50 via-blue-800/50 to-blue-900/50 */
  border: 1px solid rgba(59, 130, 246, 0.3); /* border-blue-500/30 */
}
.stats-blue .text-blue-300 { color: #93c5fd; }
.stats-blue .text-blue-100 { color: #dbeafe; }
.stats-blue .bg-blue-500\/20 { background-color: rgba(59, 130, 246, 0.2); }
.stats-blue .text-blue-400 { color: #60a5fa; }

.stats-green {
  background: linear-gradient(to bottom right, rgba(22, 163, 74, 0.5), rgba(21, 128, 61, 0.5), rgba(22, 163, 74, 0.5));
  border: 1px solid rgba(34, 197, 94, 0.3);
}
.stats-green .text-green-300 { color: #86efac; }
.stats-green .text-green-100 { color: #dcfce7; }
.stats-green .bg-green-500\/20 { background-color: rgba(34, 197, 94, 0.2); }
.stats-green .text-green-400 { color: #4ade80; }

.stats-yellow {
  background: linear-gradient(to bottom right, rgba(202, 138, 4, 0.5), rgba(161, 98, 7, 0.5), rgba(202, 138, 4, 0.5));
  border: 1px solid rgba(250, 204, 21, 0.3);
}
.stats-yellow .text-yellow-300 { color: #fde047; }
.stats-yellow .text-yellow-100 { color: #fef9c3; }
.stats-yellow .bg-yellow-500\/20 { background-color: rgba(250, 204, 21, 0.2); }
.stats-yellow .text-yellow-400 { color: #facc15; }

.stats-purple {
  background: linear-gradient(to bottom right, rgba(124, 58, 237, 0.5), rgba(109, 40, 217, 0.5), rgba(124, 58, 237, 0.5));
  border: 1px solid rgba(168, 85, 247, 0.3);
}
.stats-purple .text-purple-300 { color: #d8b4fe; }
.stats-purple .text-purple-100 { color: #f3e8ff; }
.stats-purple .bg-purple-500\/20 { background-color: rgba(168, 85, 247, 0.2); }
.stats-purple .text-purple-400 { color: #c084fc; }
</style>
