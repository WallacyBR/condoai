import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/utils/api';

export const useAccountsStore = defineStore('accounts', () => {
  const accounts = ref([]);

  async function fetchAccounts() {
    try {
      const { data } = await api.get('/finance/banks');
      accounts.value = data;
    } catch (e) {
      console.error('Erro ao carregar contas:', e);
    }
  }

  return { accounts, fetchAccounts };
});
