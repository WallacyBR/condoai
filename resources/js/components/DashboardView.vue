<template>
  <AuthenticatedLayout page-title="Dashboard">
    <p v-if="selectedCondoName" class="text-cyan-400 mb-4">Condomínio atual: {{ selectedCondoName }}</p>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-8 border-2 border-cyan-500/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
        <h3 class="text-lg font-medium text-cyan-300">Total de Condomínios</h3>
        <p class="mt-2 text-4xl font-bold text-cyan-400 drop-shadow">{{ totalCondominiums }}</p>
      </div>
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-8 border-2 border-green-500/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
        <h3 class="text-lg font-medium text-green-300">Condomínios Ativos</h3>
        <p class="mt-2 text-4xl font-bold text-green-400 drop-shadow">{{ activeCondominiums }}</p>
      </div>
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-8 border-2 border-blue-500/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
        <h3 class="text-lg font-medium text-blue-300">Última Atualização</h3>
        <p class="mt-2 text-4xl font-bold text-blue-400 drop-shadow">{{ lastUpdate }}</p>
      </div>
    </div>

    <!-- Condominium List -->
    <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl border-2 border-cyan-500/20">
      <div class="px-8 py-6 border-b border-cyan-500/20">
        <h2 class="text-xl font-semibold text-cyan-300">Meus Condomínios</h2>
      </div>
      <CondominiumList @update-stats="updateStats" />
    </div>
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import CondominiumList from './CondominiumList.vue';
import { ref } from 'vue';

export default {
  name: 'DashboardView',
  components: { 
    AuthenticatedLayout,
    CondominiumList 
  },
  setup() {
    const totalCondominiums = ref(0);
    const activeCondominiums = ref(0);
    const lastUpdate = ref('');
    const selectedCondoName = ref(localStorage.getItem('selectedCondominiumName'));

    const updateStats = (stats) => {
      totalCondominiums.value = stats.total;
      activeCondominiums.value = stats.active;
      lastUpdate.value = new Date().toLocaleDateString('pt-BR');
    };

    return { 
      totalCondominiums, 
      activeCondominiums, 
      lastUpdate,
      selectedCondoName,
      updateStats
    };
  }
};
</script>
