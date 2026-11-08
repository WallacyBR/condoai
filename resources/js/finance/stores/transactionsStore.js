import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/utils/api';

export const useTransactionsStore = defineStore('transactions', () => {
  const transactions = ref([]);

  async function fetchTransactions() {
    try {
      const { data } = await api.get('/finance/transactions');
      transactions.value = data;
    } catch (e) {
      console.error('Erro ao carregar lançamentos:', e);
    }
  }

  return { transactions, fetchTransactions };
});
