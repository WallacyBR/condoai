<template>
  <AuthenticatedLayout page-title="Dashboard Financeiro">
    <div class="space-y-6">
      <TopCard title="Dashboard Financeiro" :icon="DashboardIcon" />

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-blue-500/20">
          <h2 class="text-xl font-semibold mb-2 text-white">Previsão Financeira</h2>
          <p class="text-gray-300">Média Móvel: <span class="font-bold text-blue-400">R$ {{ parseFloat(forecastData.moving_average).toFixed(2) }}</span></p>
          <p class="text-gray-300">Tendência: <span class="font-bold text-blue-400">{{ forecastData.trend }}</span></p>
        </div>

        <!-- Add more dashboard widgets here -->
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, h } from 'vue';
import api from '@/utils/api';
import { useUser } from '@/composables/useUser';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import TopCard from '../components/ui/TopCard.vue';

const forecastData = ref({
  moving_average: 0,
  trend: 0,
});

const { user } = useUser();
const condominiumId = user.value?.condominium_id;

const DashboardIcon = {
  render() {
    return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' })
    ]);
  }
};

onMounted(() => {
  fetchForecast();
});

async function fetchForecast() {
  try {
    const { data } = await api.get('/finance/dashboard/forecast', {
      params: {
        condominium_id: condominiumId,
      },
    });
    forecastData.value = data;
  } catch (error) {
    console.error('Erro ao carregar previsão financeira:', error);
  }
}
</script>