<template>
  <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-8 border-2 border-purple-500/20">
    <h3 class="text-lg font-medium text-purple-300 mb-4">Unidades por Bloco</h3>
    <Bar v-if="chartData.datasets.length" :data="chartData" :options="chartOptions" />
    <p v-else class="text-center text-gray-400">Carregando dados do gráfico...</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import api from '../utils/api';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const chartData = ref({
  labels: [],
  datasets: []
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        color: '#9ca3af' // text-gray-400
      },
      grid: {
        color: '#4b5563' // border-gray-700
      }
    },
    x: {
      ticks: {
        color: '#9ca3af' // text-gray-400
      },
      grid: {
        color: '#4b5563' // border-gray-700
      }
    }
  },
  plugins: {
    legend: {
      labels: {
        color: '#d1d5db' // text-gray-300
      }
    }
  }
});

onMounted(async () => {
  const condoId = localStorage.getItem('selectedCondominium');
  if (condoId) {
    try {
      const response = await api.get(`/condominiums/${condoId}/stats`);
      const blocks = response.data;
      
      if (blocks && blocks.length) {
        chartData.value = {
          labels: blocks.map(block => block.name),
          datasets: [
            {
              label: 'Nº de Unidades',
              backgroundColor: 'rgba(168, 85, 247, 0.5)', // purple-500 with opacity
              borderColor: 'rgba(168, 85, 247, 1)',
              borderWidth: 1,
              data: blocks.map(block => block.apartments_count)
            }
          ]
        };
      }
    } catch (error) {
      console.error('Erro ao buscar dados para o gráfico:', error);
    }
  }
});
</script> 