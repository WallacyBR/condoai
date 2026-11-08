<template>
  <AuthenticatedLayout page-title="Fluxo de Caixa">
    <div class="space-y-6">
      <TopCard title="Fluxo de Caixa" :icon="CashFlowIcon" />

      <div class="flex flex-wrap gap-4 p-4 bg-gray-800 rounded-lg shadow-lg border border-gray-700">
        <input
          type="date"
          v-model="filters.startDate"
          class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
        />
        <input
          type="date"
          v-model="filters.endDate"
          class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
        />
        <select v-model="filters.type" class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
          <option value="">Todos os Tipos</option>
          <option value="receita">Receitas</option>
          <option value="despesa">Despesas</option>
        </select>
        <NeonButton @click="applyFilters" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
          </svg>
          Aplicar Filtros
        </NeonButton>
      </div>

      <div v-if="cashFlows.length === 0" class="text-center py-8 bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhum registro de fluxo de caixa encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">
          Os registros de fluxo de caixa são gerados automaticamente a partir dos lançamentos financeiros.
        </p>
      </div>

      <ul v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <li v-for="cf in cashFlows" :key="cf.id" 
            class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 transition-all duration-300 hover:shadow-2xl hover:scale-105"
            :class="{
              'border-red-500/20 hover:border-red-500/40': cf.type === 'despesa',
              'border-green-500/20 hover:border-green-500/40': cf.type === 'receita'
            }">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-lg font-semibold text-white">{{ cf.description }}</h3>
              <p class="text-sm text-gray-400">Data: {{ new Date(cf.date).toLocaleDateString() }}</p>
            </div>
            <span class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="{
                    'bg-red-500/20 text-red-300 border border-red-500/30': cf.type === 'despesa',
                    'bg-green-500/20 text-green-300 border border-green-500/30': cf.type === 'receita'
                  }">
              {{ cf.type === 'despesa' ? 'Despesa' : 'Receita' }}
            </span>
          </div>

          <div class="bg-gray-800/50 rounded-lg p-4 mb-4 border border-gray-700/50">
            <p class="text-white text-xl font-bold">R$ {{ parseFloat(cf.amount).toFixed(2) }}</p>
          </div>
        </li>
      </ul>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, h } from 'vue';
import api from '@/utils/api';
import { useUser } from '@/composables/useUser';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';

const cashFlows = ref([]);
const filters = ref({
  startDate: '',
  endDate: '',
  type: '',
});

const { user } = useUser();
const condominiumId = user.value?.condominium_id;

const CashFlowIcon = {
  render() {
    return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 00-2 2v10a2 2 0 002 2z' })
    ]);
  }
};

onMounted(() => {
  applyFilters();
});

async function applyFilters() {
  try {
    const { data } = await api.get('/finance/cash-flows', {
      params: {
        condominium_id: condominiumId,
        start_date: filters.value.startDate,
        end_date: filters.value.endDate,
        type: filters.value.type,
      },
    });
    cashFlows.value = data;
  } catch (error) {
    console.error('Erro ao carregar fluxo de caixa:', error);
    alert('Erro ao carregar fluxo de caixa. Verifique o console para mais detalhes.');
  }
}
</script>