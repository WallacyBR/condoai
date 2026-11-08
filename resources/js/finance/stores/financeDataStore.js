import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/utils/api';

export const useFinanceDataStore = defineStore('financeData', () => {
  const chartAccounts = ref([]);
  const banks = ref([]);

  async function fetchChartAccounts() {
    try {
      const { data } = await api.get('/finance/chart-accounts');
      chartAccounts.value = data;
    } catch (e) {
      console.error('Erro ao carregar contas contábeis:', e);
    }
  }

  async function fetchBanks() {
    try {
      const { data } = await api.get('/finance/banks');
      banks.value = data;
    } catch (e) {
      console.error('Erro ao carregar bancos:', e);
    }
  }

  return { chartAccounts, banks, fetchChartAccounts, fetchBanks };
});
