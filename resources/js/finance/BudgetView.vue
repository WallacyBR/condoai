<template>
  <AuthenticatedLayout page-title="Controle Orçamentário">
    <div class="space-y-6">
      <TopCard title="Controle Orçamentário" :icon="BudgetIcon" />

      <div class="flex justify-between items-center bg-gray-800 p-4 rounded-lg shadow-lg border border-gray-700">
        <div>
          <label for="year-select" class="text-white mr-2">Ano:</label>
          <select id="year-select" v-model="selectedYear" @change="loadBudgetData" class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500">
            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>
        <NeonButton @click="saveBudget" :is-loading="isSaving">
          Salvar Orçamento
        </NeonButton>
      </div>

      <div class="overflow-x-auto bg-gray-800/50 p-4 rounded-lg shadow-lg border-2 border-gray-700/50">
        <table class="min-w-full text-white">
          <thead class="bg-gray-900/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Conta</th>
              <th v-for="month in months" :key="month.value" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">{{ month.name }}</th>
            </tr>
          </thead>
          <tbody class="bg-gray-800/80">
            <tr v-for="account in expenseAccounts" :key="account.id">
              <td class="px-6 py-4 whitespace-nowrap font-medium">{{ account.name }}</td>
              <td v-for="month in months" :key="month.value" class="px-6 py-4">
                <input
                  type="number"
                  step="0.01"
                  v-model="budgetData[account.id][month.value]"
                  class="w-full p-1 bg-gray-700 border border-gray-600 rounded-md focus:ring-cyan-500 focus:border-cyan-500"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed, h } from 'vue';
import api from '@/utils/api';
import { useUser } from '@/composables/useUser';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';
import { useToast } from '@/composables/useToast';

const { user } = useUser();
const { showToast } = useToast();
const condominiumId = computed(() => user.value?.condominium_id);

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
    return h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
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
      params: { type: 'despesa', condominium_id: condominiumId.value }
    });
    expenseAccounts.value = data;
    initializeBudgetData();
  } catch (error) {
    console.error('Erro ao carregar contas de despesa:', error);
    showToast('Erro ao carregar contas de despesa.', 'error');
  }
};

const loadBudgetData = async () => {
  if (!condominiumId.value) return;
  try {
    const { data } = await api.get('/finance/budget-items', {
      params: {
        year: selectedYear.value,
        condominium_id: condominiumId.value
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
  if (!condominiumId.value) return;
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
      items: itemsToSave,
      condominium_id: condominiumId.value
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
