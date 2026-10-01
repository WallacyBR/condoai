<template>
  <AuthenticatedLayout page-title="Controle Orçamentário">
    <div class="space-y-6">
      <TopCard title="Controle Orçamentário" :icon="BudgetIcon" />

      <!-- Barra de Filtros e Ações -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-3">
          <label for="year-select" class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            Exercício:
          </label>
          <select 
            id="year-select" 
            v-model="selectedYear" 
            @change="loadBudgetData" 
            class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm font-medium"
          >
            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>

        <div>
          <NeonButton 
            @click="saveBudget" 
            :is-loading="isSaving"
            variant="primary"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Salvar Orçamento
          </NeonButton>
        </div>
      </div>

      <!-- Tabela Orçamentária -->
      <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-slate-900 dark:text-slate-100">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
              <tr>
                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky left-0 bg-slate-50 dark:bg-slate-800/90 backdrop-blur-sm z-10 min-w-[200px]">
                  Conta de Despesa
                </th>
                <th 
                  v-for="month in months" 
                  :key="month.value" 
                  scope="col" 
                  class="px-3 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider min-w-[110px]"
                >
                  {{ month.name }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
              <tr 
                v-for="account in expenseAccounts" 
                :key="account.id"
                class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
              >
                <td class="px-5 py-3 whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-100 sticky left-0 bg-white dark:bg-slate-900 z-10 border-r border-slate-100 dark:border-slate-800">
                  {{ account.name }}
                </td>
                <td v-for="month in months" :key="month.value" class="px-2 py-2">
                  <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 select-none">R$</span>
                    <input
                      type="number"
                      step="0.01"
                      v-model="budgetData[account.id][month.value]"
                      class="w-full pl-8 pr-2 py-1.5 text-right bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                    />
                  </div>
                </td>
              </tr>
              <tr v-if="expenseAccounts.length === 0">
                <td :colspan="months.length + 1" class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                  Nenhuma conta de despesa encontrada.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed, h } from 'vue';
import api from '@/utils/api';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';
import { useToast } from '@/composables/useToast';

const { showToast } = useToast();

const selectedYear = ref(new Date().getFullYear());
const availableYears = computed(() => {
  const currentYear = new Date().getFullYear();
  return Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
});

const months = ref([
  { name: 'Jan', value: 1 }, { name: 'Fev', value: 2 }, { name: 'Mar', value: 3 },
  { name: 'Abr', value: 4 }, { name: 'Mai', value: 5 }, { name: 'Jun', value: 6 },
  { name: 'Jul', value: 7 }, { name: 'Ago', value: 8 }, { name: 'Set', value: 9 },
  { name: 'Out', value: 10 }, { name: 'Nov', value: 11 }, { name: 'Dez', value: 12 },
]);

const expenseAccounts = ref([]);
const budgetData = ref({});
const isSaving = ref(false);

const BudgetIcon = {
  render() {
    return h('svg', { class: 'h-5 w-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 14h.01M9 11h.01M12 11h.01M15 11h.01M5.75 21H18.25a2 2 0 002-2V7.75a2 2 0 00-2-2H5.75a2 2 0 00-2 2v11.25a2 2 0 002 2z' })
    ]);
  }
};

const initializeBudgetData = () => {
  const data = {};
  expenseAccounts.value.forEach(account => {
    data[account.id] = {};
    months.value.forEach(month => {
      data[account.id][month.value] = 0;
    });
  });
  budgetData.value = data;
};

const fetchExpenseAccounts = async () => {
  try {
    const { data } = await api.get('/finance/chart-accounts', {
      params: { type: 'despesa' }
    });
    expenseAccounts.value = data;
    initializeBudgetData();
  } catch (error) {
    console.error('Erro ao carregar contas de despesa:', error);
    showToast('Erro ao carregar contas de despesa.', 'error');
  }
};

const loadBudgetData = async () => {
  try {
    const { data } = await api.get('/finance/budget-items', {
      params: {
        year: selectedYear.value
      }
    });

    initializeBudgetData(); // Reset first
    data.forEach(item => {
      if (budgetData.value[item.chart_account_id] && budgetData.value[item.chart_account_id][item.month] !== undefined) {
        budgetData.value[item.chart_account_id][item.month] = parseFloat(item.amount);
      }
    });
  } catch (error) {
    console.error('Erro ao carregar dados do orçamento:', error);
    showToast('Erro ao carregar dados do orçamento.', 'error');
  }
};

const saveBudget = async () => {
  isSaving.value = true;

  const itemsToSave = [];
  for (const accountId in budgetData.value) {
    for (const month in budgetData.value[accountId]) {
      itemsToSave.push({
        chart_account_id: accountId,
        month: month,
        year: selectedYear.value,
        amount: budgetData.value[accountId][month],
      });
    }
  }

  try {
    await api.post('/finance/budget-items/bulk', {
      items: itemsToSave
    });
    showToast('Orçamento salvo com sucesso!', 'success');
  } catch (error) {
    console.error('Erro ao salvar orçamento:', error);
    showToast('Erro ao salvar orçamento.', 'error');
  } finally {
    isSaving.value = false;
  }
};

onMounted(async () => {
  await fetchExpenseAccounts();
  await loadBudgetData();
});
</script>
