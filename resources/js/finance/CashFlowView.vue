<template>
  <AuthenticatedLayout page-title="Fluxo de Caixa">
    <div class="space-y-6">
      <TopCard title="Fluxo de Caixa" :icon="CashFlowIcon" />

      <!-- Barra de Filtros -->
      <div class="flex flex-wrap items-center gap-3 p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <input
          type="date"
          v-model="filters.startDate"
          class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
        />
        <input
          type="date"
          v-model="filters.endDate"
          class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
        />
        <select 
          v-model="filters.type" 
          class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
        >
          <option value="">Todos os Tipos</option>
          <option value="receita">Receitas</option>
          <option value="despesa">Despesas</option>
        </select>
        <AppButton @click="applyFilters" variant="primary" size="md">
          <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
          </svg>
          Aplicar Filtros
        </AppButton>
      </div>

      <!-- Estado Vazio -->
      <div v-if="cashFlows.length === 0" class="text-center py-12 bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
        <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-100">Nenhum registro de fluxo de caixa encontrado</h3>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          Os registros de fluxo de caixa são gerados automaticamente a partir dos lançamentos financeiros.
        </p>
      </div>

      <!-- Lista de Registros -->
      <ul v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <li 
          v-for="cf in cashFlows" 
          :key="cf.id" 
          class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors"
        >
          <div class="flex justify-between items-start mb-3 gap-3">
            <div>
              <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ cf.description }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Data: {{ new Date(cf.date).toLocaleDateString() }}</p>
            </div>
            <span 
              class="px-2.5 py-0.5 text-xs font-semibold rounded-full shrink-0"
              :class="cf.type === 'despesa' 
                ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50' 
                : 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50'"
            >
              {{ cf.type === 'despesa' ? 'Despesa' : 'Receita' }}
            </span>
          </div>

          <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3.5 border border-slate-200/60 dark:border-slate-700/60">
            <p 
              class="text-lg font-bold"
              :class="cf.type === 'despesa' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'"
            >
              R$ {{ parseFloat(cf.amount).toFixed(2) }}
            </p>
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
import AppButton from '../components/ui/AppButton.vue';
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