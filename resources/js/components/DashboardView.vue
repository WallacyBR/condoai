<template>
  <AuthenticatedLayout page-title="Dashboard">
    <TopCard
      v-if="selectedCondoName"
      title="Dashboard"
      :subtitle="selectedCondoName"
      :icon="BuildingIcon"
    >
      <template #actions>
        <button @click="changeCondominium" class="px-2 py-1 text-xs bg-cyan-600/20 text-cyan-300 rounded hover:bg-cyan-600/40 transition-colors">
          Trocar
        </button>
      </template>
    </TopCard>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-8 border-2 border-cyan-500/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
        <h3 class="text-lg font-medium text-cyan-300">Total de Unidades</h3>
        <p class="mt-2 text-4xl font-bold text-cyan-400 drop-shadow">{{ condoStats.apartments_count }}</p>
      </div>
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-8 border-2 border-green-500/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
        <h3 class="text-lg font-medium text-green-300">Total de Moradores</h3>
        <p class="mt-2 text-4xl font-bold text-green-400 drop-shadow">{{ condoStats.residents_count }}</p>
      </div>
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-8 border-2 border-blue-500/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
        <h3 class="text-lg font-medium text-blue-300">Vagas de Garagem</h3>
        <p class="mt-2 text-4xl font-bold text-blue-400 drop-shadow">{{ condoStats.parking_spots_count }}</p>
      </div>
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-8 border-2 border-red-500/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
        <h3 class="text-lg font-medium text-red-300">Chamados Abertos</h3>
        <p class="mt-2 text-4xl font-bold text-red-400 drop-shadow">N/A</p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import TopCard from './ui/TopCard.vue';
import { ref, onMounted, h } from 'vue';
import { useRouter } from 'vue-router';
import api from '../utils/api';

export default {
  name: 'DashboardView',
  components: {
    AuthenticatedLayout,
    TopCard,
  },
  setup() {
    const router = useRouter();
    const selectedCondoName = ref(localStorage.getItem('selectedCondominiumName'));
    const BuildingIcon = {
      render() {
        return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' })
        ]);
      }
    };
    const condoStats = ref({
      apartments_count: 0,
      residents_count: 0,
      parking_spots_count: 0,
    });

    const fetchCondoStats = async () => {
      const condoId = localStorage.getItem('selectedCondominium');
      if (condoId) {
        try {
          const response = await api.get(`/condominiums/${condoId}`);
          condoStats.value = response.data;
        } catch (error) {
          console.error('Erro ao buscar estatísticas do condomínio:', error);
        }
      }
    };

    onMounted(fetchCondoStats);

    const updateSelectedCondo = () => {
      selectedCondoName.value = localStorage.getItem('selectedCondominiumName');
      fetchCondoStats();
    };

    window.addEventListener('condominiumSelected', updateSelectedCondo);

    const changeCondominium = () => {
      localStorage.removeItem('selectedCondominium');
      localStorage.removeItem('selectedCondominiumName');
      router.push('/condominium-select');
    };

    return {
      selectedCondoName,
      condoStats,
      changeCondominium,
      BuildingIcon
    };
  }
};
</script>
